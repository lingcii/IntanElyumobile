<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\TouristSpot;
use App\Models\TouristSpotAudit;
use App\Models\TouristSpotImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TouristSpotController extends Controller
{
    private const UPLOAD_DIR = 'tourist_spots';
    // UPLOAD_URL derived from APP_URL env — never hardcode a host
    private static function uploadUrl(): string
    {
        return rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/storage/tourist_spots/';
    }
    
    // Cache column check results to avoid hitting the database every time
    private static ?bool $hasBarangayColumn = null;
    private static ?bool $hasUpdatedAtColumn = null;

    // Check if user is PICTO and restrict write operations
    private function checkPICTOAccess(Request $request): bool
    {
        $role = $request->session()->get('user_role');
        // If user is PICTO, return false to restrict write access
        return $role !== 'picto';
    }
    
    // Check if the tourist_spots table has a specific column (cached)
    private function hasColumn(string $column): bool
    {
        $cacheProperty = match ($column) {
            'barangay' => 'hasBarangayColumn',
            'updated_at' => 'hasUpdatedAtColumn',
            default => null,
        };
        
        if ($cacheProperty && self::$$cacheProperty !== null) {
            return self::$$cacheProperty;
        }
        
        $result = false;
        try {
            $result = Schema::hasColumn('tourist_spots', $column);
        } catch (\Exception $e) {
            $result = false;
        }
        
        if ($cacheProperty) {
            self::$$cacheProperty = $result;
        }
        
        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  READ
    // ──────────────────────────────────────────────────────────────────────────

    /** GET /api/tourist-spots  (all roles: access allowed) */
    public function index(Request $request): JsonResponse
    {
        $role           = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        // Exclude 'description' from the list response — it can be large (~500 chars/row)
        // and is only needed when opening the detail/edit view (show() endpoint).
        $query = TouristSpot::select([
            'id', 'name', 'municipality_id', 'barangay', 'category', 'entrance_fee',
            'status', 'photo_url', 'latitude', 'longitude', 'opening_time',
            'closing_time', 'is_maintenance', 'accessible_by_private_vehicle', 'classification_status', 'visits', 'rating', 'created_at'
        ])->with(['municipality:id,name', 'images']);

        // Municipal users only see their own municipality's spots
        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $query->where('municipality_id', $municipalityId);
        }

        $spots = $query->latest()->get();
        $spots = $this->attachPrimaryPhoto($spots);

        return response()->json($spots);
    }

    /** GET /api/tourist-spots/{id} (all roles: access allowed) */
    public function show(Request $request, int $id): JsonResponse
    {
        $role           = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        $query = TouristSpot::with(['municipality:id,name', 'images'])->where('id', $id);

        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $query->where('municipality_id', $municipalityId);
        }

        $spot = $query->first();
        if (!$spot) return response()->json(['error' => 'Spot not found.'], 404);

        $spot = $this->setPhotoUrl($spot);
        return response()->json($spot);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  WRITE
    // ──────────────────────────────────────────────────────────────────────────

    /** POST /api/tourist-spots/upload-image */
    public function uploadImage(Request $request): JsonResponse
    {
        if (!$this->checkPICTOAccess($request)) {
            return response()->json(['error' => 'PICTO users are not authorized to perform this action.'], 403);
        }

        $request->validate(['image' => 'required|image|mimes:jpeg,jpg,png|max:10240']); // 10MB

        $file     = $request->file('image');
        $filename = 'spot_' . uniqid() . '.' . $file->extension();
        
        // Ensure directory exists
        $directory = storage_path('app/public/' . self::UPLOAD_DIR);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $file->move($directory, $filename);

        // Return the proxy URL instead of the full storage URL
        $url = '/api/serve-image.php?file=' . urlencode($filename);

        return response()->json([
            'success'   => true,
            'photo_url' => $url,
            'filename'  => $filename,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->checkPICTOAccess($request)) {
            return response()->json(['error' => 'PICTO users are not authorized to perform this action.'], 403);
        }

        $data           = $request->validate([
            'name'                  => 'required|string|max:255',
            'barangay'              => 'nullable|string|max:255',
            'category'              => 'required|string',
            'description'           => 'required|string',
            'classification_status' => 'required|string',
            'municipality_id'       => 'sometimes|integer',
            'entrance_fee'          => 'nullable|numeric',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'opening_time'          => 'nullable|string',
            'closing_time'          => 'nullable|string',
            'is_maintenance'        => 'nullable|boolean',
            'accessible_by_private_vehicle' => 'nullable|boolean',
            'images'                => 'nullable|array',
        ]);

        $role           = $request->session()->get('user_role');
        $sessionMuniId  = (int) $request->session()->get('user_municipality_id', 0);

        // Municipal users always use their own municipality
        if (in_array($role, User::$MUNICIPAL_ROLES)) {
            $data['municipality_id'] = $sessionMuniId;
        }

        if (empty($data['municipality_id'])) {
            return response()->json(['error' => 'municipality_id is required.'], 422);
        }

        // Normalize category: accept comma-separated multi-category values
        $data['category'] = self::normalizeCategories($data['category']);

        $mapped = TouristSpot::$STATUS_MAP[strtoupper($data['classification_status'])] ?? null;
        if (!in_array($mapped, TouristSpot::$VALID_STATUSES)) {
            return response()->json(['error' => 'Invalid classification status.'], 422);
        }
        $data['classification_status'] = $mapped;

        $photoUrl = $data['images'][0]['photo_url'] ?? null;

        $spot = DB::transaction(function () use ($data, $photoUrl, $request) {
            // Create the spot data array without barangay first, then add it only if it exists
            $spotData = [
                'name'                  => $data['name'],
                'municipality_id'       => $data['municipality_id'],
                'category'              => $data['category'],
                'entrance_fee'          => $data['entrance_fee'] ?? 0,
                'description'           => $data['description'],
                'photo_url'             => $photoUrl,
                'latitude'              => $data['latitude']  ?? null,
                'longitude'             => $data['longitude'] ?? null,
                'opening_time'          => $data['opening_time']  ?? null,
                'closing_time'          => $data['closing_time']  ?? null,
                'is_maintenance'        => $data['is_maintenance'] ?? false,
                'accessible_by_private_vehicle' => $data['accessible_by_private_vehicle'] ?? true,
                'status'                => 'approved',
                'classification_status' => $data['classification_status'],
            ];

            // Use the cached column checks
            if ($this->hasColumn('barangay')) {
                $spotData['barangay'] = $data['barangay'] ?? null;
            }

            // Create the spot manually to bypass automatic timestamps
            $spot = new TouristSpot($spotData);
            $spot->save();

            $this->syncImages($spot->id, $data['images'] ?? []);
            Municipality::where('id', $spot->municipality_id)->increment('attraction_count');
            $this->auditLog($spot->id, (int) $request->session()->get('user_id'), 'created', ['name' => $spot->name, 'category' => $spot->category], $request);

            return $spot;
        });

        return response()->json(['success' => true, 'message' => 'Tourist spot created successfully.', 'id' => $spot->id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPICTOAccess($request)) {
            return response()->json(['error' => 'PICTO users are not authorized to perform this action.'], 403);
        }

        $data          = $request->validate([
            'name'                  => 'required|string|max:255',
            'barangay'              => 'nullable|string|max:255',
            'category'              => 'required|string',
            'description'           => 'required|string',
            'classification_status' => 'required|string',
            'entrance_fee'          => 'nullable|numeric',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'opening_time'          => 'nullable|string',
            'closing_time'          => 'nullable|string',
            'is_maintenance'        => 'nullable|boolean',
            'accessible_by_private_vehicle' => 'nullable|boolean',
            'images'                => 'nullable|array',
        ]);

        $role           = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        $query = TouristSpot::where('id', $id);
        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $query->where('municipality_id', $municipalityId);
        }
        $spot = $query->firstOrFail();
        $old  = $spot->only(['name', 'category', 'entrance_fee', 'classification_status']);

        // Normalize category: accept comma-separated multi-category values
        $data['category'] = self::normalizeCategories($data['category']);

        $mapped = TouristSpot::$STATUS_MAP[strtoupper($data['classification_status'])] ?? null;
        if (!in_array($mapped, TouristSpot::$VALID_STATUSES)) {
            return response()->json(['error' => 'Invalid classification status.'], 422);
        }
        $data['classification_status'] = $mapped;

        $photoUrl = $data['images'][0]['photo_url'] ?? null;

        DB::transaction(function () use ($spot, $data, $photoUrl, $old, $request) {
            // Create the update data array without barangay first
            $updateData = [
                'name'                  => $data['name'],
                'category'              => $data['category'],
                'entrance_fee'          => $data['entrance_fee'] ?? 0,
                'description'           => $data['description'],
                'photo_url'             => $photoUrl,
                'latitude'              => $data['latitude']  ?? null,
                'longitude'             => $data['longitude'] ?? null,
                'opening_time'          => $data['opening_time']  ?? null,
                'closing_time'          => $data['closing_time']  ?? null,
                'is_maintenance'        => $data['is_maintenance'] ?? false,
                'accessible_by_private_vehicle' => $data['accessible_by_private_vehicle'] ?? true,
                'classification_status' => $data['classification_status'],
            ];

            // Use the cached column check
            if ($this->hasColumn('barangay')) {
                $updateData['barangay'] = $data['barangay'] ?? null;
            }

            // Manually update the spot attributes and save
            $spot->fill($updateData);
            $spot->save();

            $this->syncImages($spot->id, $data['images'] ?? []);
            $this->auditLog($spot->id, (int) $request->session()->get('user_id'), 'updated', ['old' => $old, 'new' => $data], $request);
        });

        return response()->json(['success' => true, 'message' => 'Tourist spot updated successfully.']);
    }

    /** DELETE /api/tourist-spots/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$this->checkPICTOAccess($request)) {
            return response()->json(['error' => 'PICTO users are not authorized to perform this action.'], 403);
        }

        $role           = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        $query = TouristSpot::where('id', $id);
        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $query->where('municipality_id', $municipalityId);
        }
        $spot = $query->firstOrFail();

        DB::transaction(function () use ($spot, $request) {
            Municipality::where('id', $spot->municipality_id)
                ->decrement('attraction_count');

            $this->auditLog($spot->id, (int) $request->session()->get('user_id'), 'deleted', ['name' => $spot->name], $request);
            $spot->delete();
        });

        return response()->json(['success' => true, 'message' => 'Tourist spot deleted successfully.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Accept a comma-separated category string, validate each part,
     * and return a cleaned comma-separated string.
     * e.g. "Beach,Mountain,Foo" → "Beach,Mountain"
     * Falls back to "Other" only if nothing valid is found.
     */
    private static function normalizeCategories(string $raw): string
    {
        $parts = array_map('trim', explode(',', $raw));
        $valid = array_filter($parts, fn($p) => in_array($p, TouristSpot::$VALID_CATEGORIES));
        return implode(',', $valid) ?: 'Other';
    }

    private function syncImages(int $spotId, array $images): void
    {
        if (empty($images)) {
            // No images supplied — leave existing images intact
            return;
        }

        // Build a set of normalized URLs from the incoming payload
        $incomingUrls = array_map(fn($img) => $this->normalizePhotoUrl($img['photo_url']), $images);

        // Delete only images that are no longer in the incoming list
        TouristSpotImage::where('spot_id', $spotId)
            ->whereNotIn('photo_url', $incomingUrls)
            ->delete();

        // Upsert remaining images (preserves existing rows, inserts new ones)
        foreach ($images as $i => $image) {
            $url = $this->normalizePhotoUrl($image['photo_url']);
            TouristSpotImage::updateOrCreate(
                ['spot_id' => $spotId, 'photo_url' => $url],
                ['is_primary' => $i === 0 ? 1 : 0, 'sort_order' => $i]
            );
        }
    }

    private function setPhotoUrl(TouristSpot $spot): TouristSpot
    {
        $images = $spot->images;
        if ($images->isNotEmpty()) {
            $primary = $images->firstWhere('is_primary', 1) ?? $images->first();
            $spot->photo_url = $this->normalizePhotoUrl($primary->photo_url);
        } elseif ($spot->photo_url) {
            $spot->photo_url = $this->normalizePhotoUrl($spot->photo_url);
        } else {
            $spot->photo_url = null;
        }
        $spot->municipality_name = $spot->municipality?->name ?? null;
        return $spot;
    }

    /**
     * Ensure a stored photo_url is always a URL that works with the frontend.
     * Handles:
     * - Bare filenames (e.g. "urbiztondo.jpg")
     * - Full storage URLs
     * - Legacy paths (e.g. "/Gaw-at-GO-System/backendWebsite/uploads/tourist_spots/...")
     */
    private function normalizePhotoUrl(?string $url): ?string
    {
        if (!$url) return null;
        
        // Case 1: It's already using our proxy URL
        if (str_contains($url, 'serve-image.php')) {
            // If it's relative, make it absolute
            if (str_starts_with($url, '../')) {
                $filename = $_GET['file'] ?? '';
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                $filename = $params['file'] ?? '';
                return '/api/serve-image.php?file=' . urlencode($filename);
            }
            return $url;
        }
        
        // Extract filename from any path/URL
        $filename = basename(parse_url($url, PHP_URL_PATH));
        
        if ($filename) {
            return '/api/serve-image.php?file=' . urlencode($filename);
        }
        
        // Fallback: return original
        return $url;
    }

    private function attachPrimaryPhoto($spots)
    {
        return $spots->map(function($s) {
            return $this->setPhotoUrl($s);
        });
    }

    private function auditLog(int $spotId, int $userId, string $action, array $changes, Request $request): void
    {
        try {
            TouristSpotAudit::create([
                'spot_id'    => $spotId,
                'user_id'    => $userId,
                'action'     => $action,
                'changes'    => json_encode($changes),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception) {}
    }
}
