<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate SVG string for given content.
     */
    public function generateSvg(string $content, int $size = 280): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($content);
    }

    /**
     * Generate base64 data URI for SVG.
     */
    public function generateDataUri(string $content, int $size = 280): string
    {
        $svg = $this->generateSvg($content, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
