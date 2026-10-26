<?php

namespace Tests\Unit;

use App\Services\QrCodeService;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function test_it_generates_svg_qr_code(): void
    {
        $service = new QrCodeService();
        $svg = $service->generateSvg('https://example.com/meja/01?token=test123');

        $this->assertNotEmpty($svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_it_generates_data_uri(): void
    {
        $service = new QrCodeService();
        $dataUri = $service->generateDataUri('https://example.com/meja/01');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
    }
}
