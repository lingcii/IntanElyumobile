<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    //  GET
    // ──────────────────────────────────────────────────────────────────────────

    /** GET /api/{role}/users */
    public function index(Request $request): JsonResponse
    {
        $search       = $request->get('search', '');
        $roleFilter   = $request->get('role', '');
        $statusFilter = $request->get('status', '');
        $limit        = min(max((int) $request->get('limit', 25), 1), 100);
        $offset       = max((int) $request->get('offset', 0), 0);
        $sortCol      = in_array($request->get('sort'), ['id','name','email','role','status','last_activity','created_at'])
                            ? $request->get('sort') : 'created_at';
        $sortDir      = strtoupper($request->get('dir', 'DESC')) === 'ASC' ? 'asc' : 'desc';

        $query = User::with('municipality:id,name');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ["%{$search}%"]));
        }
        if ($request->filled('role'))   $query->where('role', $request->get('role'));
        if ($request->filled('status')) $query->where('status', $request->get('status'));

        $total = $query->count();
        $users = $query->orderBy($sortCol, $sortDir)->skip($offset)->take($limit)->get();

        $roleStats = Cache::remember('users:role_stats', 600, function () {
            return User::selectRaw("role, COUNT(*) as cnt, SUM(status='active') as active_cnt")
                ->groupBy('role')->get();
        });

        return response()->json([
            'success'    => true,
            'users'      => $users,
            'total'      => $total,
            'offset'     => $offset,
            'limit'      => $limit,
            'role_stats' => $roleStats,
        ]);
    }

    /** GET /api/{role}/users/{id} */
    public function show(int $id): JsonResponse
    {
        $user = User::with('municipality:id,name')->find($id);
        if (!$user) return response()->json(['error' => 'User not found.'], 404);

        return response()->json(['success' => true, 'user' => $user]);
    }

    /** GET /api/{role}/users/municipalities */
    public function municipalities(): JsonResponse
    {
        // Cache for 1 hour (not forever) so municipality additions don't go stale
        $municipalities = Cache::remember('municipalities:list', 3600, function () {
            return Municipality::orderBy('name')->get(['id', 'name']);
        });
        return response()->json(['success' => true, 'municipalities' => $municipalities]);
    }

    /** GET /api/{role}/users/audit-logs */
    public function auditLogs(): JsonResponse
    {
        $logs = Alert::where('type', 'user_action')
            ->latest()
            ->take(50)
            ->get(['id', 'message', 'created_at']);

        return response()->json(['success' => true, 'logs' => $logs]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  POST / PUT
    // ──────────────────────────────────────────────────────────────────────────

    /** POST /api/{role}/users  – create user (PITCO) */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'role'            => ['required', Rule::in(User::$ALL_ROLES)],
            'status'          => 'nullable|in:active,inactive',
            'municipality_id' => 'nullable|integer',
            'password'        => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => $request->role,
            'status'          => $request->get('status', 'active'),
            'municipality_id' => $request->municipality_id,
        ]);

        $this->writeAuditLog($request, 'ADD_USER', $user->id, "Name: {$user->name} | Email: {$user->email} | Role: {$user->role}");

        Cache::forget('users:role_stats');

        return response()->json(['success' => true, 'user_id' => $user->id, 'message' => 'User created successfully.'], 201);
    }

    /** PUT /api/{role}/users/{id}  – update user */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'role'            => ['required', Rule::in(User::$ALL_ROLES)],
            'status'          => 'nullable|in:active,inactive',
            'municipality_id' => 'nullable|integer',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name'            => $request->name,
            'email'           => $request->email,
            'role'            => $request->role,
            'status'          => $request->get('status', 'active'),
            'municipality_id' => $request->municipality_id,
        ]);

        $this->writeAuditLog($request, 'EDIT_USER', $id, "Name: {$request->name} | Role: {$request->role}");

        Cache::forget('users:role_stats');

        return response()->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    /** PATCH /api/{role}/users/{id}/status */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,inactive']);

        $sessionUserId = (int) $request->session()->get('user_id');
        if ($id === $sessionUserId && $request->status === 'inactive') {
            return response()->json(['error' => 'You cannot deactivate your own account.'], 400);
        }

        User::findOrFail($id)->update(['status' => $request->status]);

        $verb = $request->status === 'active' ? 'ACTIVATE_USER' : 'DEACTIVATE_USER';
        $this->writeAuditLog($request, $verb, $id, "Status set to {$request->status}");

        Cache::forget('users:role_stats');

        return response()->json(['success' => true, 'message' => 'Account status updated.']);
    }

    /** PATCH /api/{role}/users/{id}/password */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $request->validate(['password' => 'required|string|min:6']);

        User::findOrFail($id)->update(['password' => Hash::make($request->password)]);
        $this->writeAuditLog($request, 'RESET_PASSWORD', $id, 'Password reset by admin');

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function writeAuditLog(Request $request, string $action, int $targetId, string $details): void
    {
        try {
            $actorId = (int) $request->session()->get('user_id');
            Alert::create([
                'type'    => 'user_action',
                'message' => "[#{$actorId}] {$action} on User #{$targetId} — {$details}",
                'is_read' => false,
            ]);
        } catch (\Exception) {}
    }
}
