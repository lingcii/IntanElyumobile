<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouristSpotOrdinanceImage extends Model
{
    protected $table = 'tourist_spot_ordinance_images';

    public $timestamps = false;

    protected $fillable = [
        'tourist_spot_id',
        'original_filename',
        'stored_filename',
        'cloudflare_path',
    ];

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'tourist_spot_id');
    }

    /**
     * Format a DB row (Model or stdClass) for API consumers with /api/image proxy + R2 fallback URL.
     */
    public static function formatRow(object $row, ?string $r2PublicUrl = null): ?array
    {
        $r2Base = rtrim($r2PublicUrl ?: env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
        $rawPath = trim((string) ($row->cloudflare_path ?? ''));
        if ($rawPath === '' && !empty($row->stored_filename)) {
            $rawPath = 'OrdinanceImages/' . ltrim((string) $row->stored_filename, '/');
        }
        if ($rawPath === '') {
            return null;
        }

        if (preg_match('#^(?:https?://[^/]+/)?(OrdinanceImages/.+)$#i', $rawPath, $m)) {
            $cleanRel = ltrim($m[1], '/');
            $primaryUrl = url('/api/image/' . $cleanRel);
            $fallbackUrl = $r2Base . '/' . $cleanRel;
        } elseif (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            $primaryUrl = $rawPath;
            $fallbackUrl = $rawPath;
        } else {
            $cleanRel = ltrim($rawPath, '/');
            $primaryUrl = url('/api/image/' . $cleanRel);
            $fallbackUrl = $r2Base . '/' . $cleanRel;
        }

        $origName = trim((string) ($row->original_filename ?? ''));
        return [
            'id'                => (int) ($row->id ?? 0),
            'title'             => $origName !== '' ? $origName : 'Site Ordinance',
            'original_filename' => $origName !== '' ? $origName : null,
            'url'               => $primaryUrl,
            'fallback_url'      => $fallbackUrl,
        ];
    }
}
