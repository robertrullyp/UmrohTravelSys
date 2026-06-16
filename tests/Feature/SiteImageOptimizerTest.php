<?php

namespace Tests\Feature;

use App\Services\SiteImageOptimizer;
use Tests\TestCase;

class SiteImageOptimizerTest extends TestCase
{
    public function test_logo_upload_is_resized_to_safe_bounds(): void
    {
        $this->skipIfGdIsMissing();

        $path = $this->createUploadImage('logo', 1200, 800);

        try {
            $result = app(SiteImageOptimizer::class)->optimizePublicUpload($path['relative'], 'logo');
            [$width, $height] = getimagesize($path['absolute']);

            $this->assertNotNull($result);
            $this->assertLessThanOrEqual(640, $width);
            $this->assertLessThanOrEqual(360, $height);
        } finally {
            @unlink($path['absolute']);
        }
    }

    public function test_favicon_upload_is_cropped_square(): void
    {
        $this->skipIfGdIsMissing();

        $path = $this->createUploadImage('favicon', 900, 500);

        try {
            $result = app(SiteImageOptimizer::class)->optimizePublicUpload($path['relative'], 'favicon');
            [$width, $height] = getimagesize($path['absolute']);

            $this->assertNotNull($result);
            $this->assertSame($width, $height);
            $this->assertLessThanOrEqual(512, $width);
        } finally {
            @unlink($path['absolute']);
        }
    }

    private function skipIfGdIsMissing(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for image optimizer tests.');
        }
    }

    /**
     * @return array{relative: string, absolute: string}
     */
    private function createUploadImage(string $prefix, int $width, int $height): array
    {
        $directory = public_path('images/site/uploads');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $prefix . '-' . uniqid('', true) . '.png';
        $absolute = $directory . DIRECTORY_SEPARATOR . $filename;
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 214, 26, 106);

        imagefilledrectangle($image, 0, 0, $width, $height, $color);
        imagepng($image, $absolute);
        imagedestroy($image);

        return [
            'relative' => 'images/site/uploads/' . $filename,
            'absolute' => $absolute,
        ];
    }
}
