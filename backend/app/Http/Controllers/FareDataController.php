<?php

namespace App\Http\Controllers;

use App\Models\FareGuide;
use App\Models\FareMatrix;
use App\Models\FareUpload;
use App\Models\ImportLog;
use App\Models\User;
use App\Models\ValidationError;
use App\Services\FareDataProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FareDataController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    //  READ endpoints (all roles)
    // ──────────────────────────────────────────────────────────────────────────

    /** GET /api/{role}/fare-data/stats  (PITCO only) */
    public function stats(): JsonResponse
    {
        $payload = \Illuminate\Support\Facades\Cache::remember('fare-data:stats', 3600, function () {
            $guides  = FareGuide::where('status', '!=', 'archived')
                ->selectRaw("COUNT(*) as total, SUM(status='active') as active_cnt")
                ->first();

            $entries = FareMatrix::whereIn('fare_guide_id', FareGuide::where('status', '!=', 'archived')->pluck('id'))
                ->selectRaw('COUNT(*) as total, MIN(regular_fare) as min_fare, MAX(regular_fare) as max_fare, AVG(regular_fare) as avg_fare')
                ->first();

            return [
                'total_guides'  => (int) $guides->total,
                'active_guides' => (int) $guides->active_cnt,
                'total_entries' => (int) $entries->total,
                'lowest_fare'   => $entries->min_fare  !== null ? round((float) $entries->min_fare,  2) : null,
                'highest_fare'  => $entries->max_fare  !== null ? round((float) $entries->max_fare,  2) : null,
                'avg_fare'      => $entries->avg_fare  !== null ? round((float) $entries->avg_fare,  2) : null,
            ];
        });

        return response()->json(array_merge(['success' => true], $payload));
    }

    /** GET /api/{role}/fare-data/guides */
    public function guides(Request $request): JsonResponse
    {
        $role  = $request->session()->get('user_role');
        $cacheKey = "fare-data:guides:{$role}";

        $guides = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($role) {
            $query = FareGuide::with('creator:id,name')
                ->select('id', 'title', 'vehicle_type', 'region', 'status', 'effective_date', 'created_by', 'created_at', 'updated_at');

            // LUPTO sees only active 
            if ($role === 'lupto') {
                $query->where('status', 'active');
            } else {
                // PITCO / Municipal — non-archived
                $query->where('status', '!=', 'archived');
            }

            // Municipal users only see Tricycle fare guides
            if (in_array($role, \App\Models\User::$MUNICIPAL_ROLES)) {
                $query->where('vehicle_type', 'Tricycle');
            }

            return $query->latest()->get()->map(function ($guide) {
                $guideArray = $guide->toArray();
                $guideArray['created_by_name'] = $guide->creator?->name ?? '—';
                return $guideArray;
            });
        });

        return response()->json(['success' => true, 'fare_guides' => $guides]);
    }

    /** GET /api/{role}/fare-data/matrices?guide_id= */
    public function matrices(Request $request): JsonResponse
    {
        $request->validate(['guide_id' => 'required|integer']);
        $matrices = FareMatrix::where('fare_guide_id', $request->guide_id)->orderBy('distance_km')->get();

        return response()->json(['success' => true, 'fare_matrices' => $matrices]);
    }

    /** GET /api/{role}/fare-data/uploads */
    public function uploads(Request $request): JsonResponse
    {
        $role       = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        $query = FareUpload::with('uploader:id,name');

        // Municipal users see only their own municipality's uploads
        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $query->whereHas('uploader', fn($q) => $q->where('municipality_id', $municipalityId));
        }

        $uploads = $query->latest()->get()->map(function ($upload) {
            $uploadArray = $upload->toArray();
            $uploadArray['uploaded_by_name'] = $upload->uploader?->name ?? '—';
            return $uploadArray;
        });

        return response()->json(['success' => true, 'uploads' => $uploads]);
    }

    /** GET /api/{role}/fare-data/import-logs?upload_id= */
    public function importLogs(Request $request): JsonResponse
    {
        $request->validate(['upload_id' => 'required|integer']);
        return response()->json(['success' => true, 'import_logs' => ImportLog::where('fare_upload_id', $request->upload_id)->orderBy('created_at')->get()]);
    }

    /** GET /api/{role}/fare-data/validation-errors?upload_id= */
    public function validationErrors(Request $request): JsonResponse
    {
        $request->validate(['upload_id' => 'required|integer']);
        return response()->json(['success' => true, 'validation_errors' => ValidationError::where('fare_upload_id', $request->upload_id)->orderBy('row_number')->get()]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  WRITE endpoints (PITCO + Municipal)
    // ──────────────────────────────────────────────────────────────────────────

    /** POST /api/{role}/fare-data/upload */
    public function upload(Request $request): JsonResponse
    {
        $request->validate(['pdf_file' => 'required|file|mimes:pdf|max:20480']);

        $role   = $request->session()->get('user_role');
        $prefix = ($role === 'picto') ? 'fare_pitco_' : 'fare_mto_';

        $file              = $request->file('pdf_file');
        $originalName      = $file->getClientOriginalName();
        $fileSize          = $file->getSize();
        $mimeType          = $file->getMimeType();
        $fileName          = uniqid($prefix, true) . '_' . $originalName;
        $filePath          = storage_path('app/uploads/' . $fileName);

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        $file->move(dirname($filePath), $fileName);

        $allowedVehicleType = in_array($role, User::$MUNICIPAL_ROLES) ? 'Tricycle' : null;

        $processor = new FareDataProcessor((int) $request->session()->get('user_id'));
        $result    = $processor->processUpload($filePath, $originalName, $fileSize, $mimeType, $allowedVehicleType);

        $this->clearFareDataCaches();

        return response()->json($result);
    }

    /** POST /api/{role}/fare-data/sync  – activate / archive / draft */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'guide_id' => 'required|integer',
            'status'   => 'required|in:active,archived,draft',
        ]);

        $guideId = $request->guide_id;
        $status  = $request->status;
        $userId  = (int) $request->session()->get('user_id');
        $role    = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);

        // Municipal can only manage Tricycle guides from their own municipality
        if (in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId) {
            $exists = FareGuide::where('id', $guideId)
                ->where('vehicle_type', 'Tricycle')
                ->whereHas('creator', fn($q) => $q->where('municipality_id', $municipalityId))
                ->exists();
            if (!$exists) {
                return response()->json(['error' => 'Forbidden: You can only manage Tricycle fare guides from your municipality.'], 403);
            }
        }

        if ($status === 'archived') {
            FareGuide::where('id', $guideId)->update([
                'status'     => 'archived',
                'updated_at' => now(),
            ]);
        } else {
            FareGuide::where('id', $guideId)->update([
                'status'     => $status,
                'updated_at' => now(),
            ]);

            // Auto-archive conflicting active guides
            if ($status === 'active') {
                $guide = FareGuide::find($guideId);
                FareGuide::where('id', '!=', $guideId)
                    ->where('vehicle_type', $guide->vehicle_type)
                    ->where('region', $guide->region)
                    ->where('status', 'active')
                    ->update([
                        'status'     => 'archived',
                        'updated_at' => now(),
                    ]);
            }
        }

        $this->clearFareDataCaches();

        return response()->json(['success' => true, 'fare_guide_id' => $guideId, 'status' => $status]);
    }

    /** DELETE /api/pitco/fare-data/{id}  – PITCO only */
    public function destroy(int $id): JsonResponse
    {
        DB::transaction(function () use ($id) {
            FareMatrix::where('fare_guide_id', $id)->delete();
            FareGuide::where('id', $id)->delete();
        });

        $this->clearFareDataCaches();

        return response()->json(['success' => true]);
    }

    private function clearFareDataCaches(): void
    {
        \Illuminate\Support\Facades\Cache::forget('fare-data:stats');
        \Illuminate\Support\Facades\Cache::forget('fare-data:guides:lupto');
        \Illuminate\Support\Facades\Cache::forget('fare-data:guides:pitco');
        foreach (User::$MUNICIPAL_ROLES as $role) {
            \Illuminate\Support\Facades\Cache::forget("fare-data:guides:{$role}");
        }
    }
}
