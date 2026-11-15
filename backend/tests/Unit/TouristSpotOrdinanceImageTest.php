<?php

namespace Tests\Unit;

use App\Models\TouristSpotOrdinanceImage;
use Tests\TestCase;

class TouristSpotOrdinanceImageTest extends TestCase
{
    public function test_formats_cloudflare_path_into_proxy_and_r2_fallback_urls(): void
    {
        $row = (object) [
            'id'                => 4,
            'original_filename' => 'ordinance for san juan.jpg',
            'stored_filename'   => 'ordinance_a5efbae5-2e5b-403a-9705-10b777.jpg',
            'cloudflare_path'   => 'OrdinanceImages/ordinance_a5efbae5-2e5b-403a-9705-10b777.jpg',
        ];

        $formatted = TouristSpotOrdinanceImage::formatRow($row, 'https://pub-test.r2.dev');

        $this->assertNotNull($formatted);
        $this->assertSame(4, $formatted['id']);
        $this->assertSame('ordinance for san juan.jpg', $formatted['title']);
        $this->assertSame('ordinance for san juan.jpg', $formatted['original_filename']);
        $this->assertStringEndsWith('/api/image/OrdinanceImages/ordinance_a5efbae5-2e5b-403a-9705-10b777.jpg', $formatted['url']);
        $this->assertSame('https://pub-test.r2.dev/OrdinanceImages/ordinance_a5efbae5-2e5b-403a-9705-10b777.jpg', $formatted['fallback_url']);
    }

    public function test_falls_back_to_stored_filename_when_cloudflare_path_is_empty(): void
    {
        $row = (object) [
            'id'                => 6,
            'original_filename' => null,
            'stored_filename'   => 'ordinance_e7b4183f.jpg',
            'cloudflare_path'   => '',
        ];

        $formatted = TouristSpotOrdinanceImage::formatRow($row, 'https://pub-test.r2.dev');

        $this->assertNotNull($formatted);
        $this->assertSame('Site Ordinance', $formatted['title']);
        $this->assertNull($formatted['original_filename']);
        $this->assertStringEndsWith('/api/image/OrdinanceImages/ordinance_e7b4183f.jpg', $formatted['url']);
        $this->assertSame('https://pub-test.r2.dev/OrdinanceImages/ordinance_e7b4183f.jpg', $formatted['fallback_url']);
    }

    public function test_extracts_ordinance_path_from_full_r2_url(): void
    {
        $row = (object) [
            'id'                => 9,
            'original_filename' => 'page1.png',
            'stored_filename'   => 'ordinance_abc.png',
            'cloudflare_path'   => 'https://pub-test.r2.dev/OrdinanceImages/ordinance_abc.png',
        ];

        $formatted = TouristSpotOrdinanceImage::formatRow($row, 'https://pub-test.r2.dev');

        $this->assertStringEndsWith('/api/image/OrdinanceImages/ordinance_abc.png', $formatted['url']);
        $this->assertSame('https://pub-test.r2.dev/OrdinanceImages/ordinance_abc.png', $formatted['fallback_url']);
    }

    public function test_returns_null_when_no_path_or_filename_exists(): void
    {
        $row = (object) [
            'id'                => 10,
            'original_filename' => 'empty.jpg',
            'stored_filename'   => '',
            'cloudflare_path'   => null,
        ];

        $this->assertNull(TouristSpotOrdinanceImage::formatRow($row, 'https://pub-test.r2.dev'));
    }
}
