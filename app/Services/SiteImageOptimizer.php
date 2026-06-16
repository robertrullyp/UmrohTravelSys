<?php

namespace App\Services;

class SiteImageOptimizer
{
    /**
     * @return array{width: int, height: int, bytes: int}|null
     */
    public function optimizePublicUpload(string $relativePath, string $profile): ?array
    {
        $relativePath = ltrim($relativePath, '/');

        if (! str_starts_with($relativePath, 'images/site/uploads/')) {
            return null;
        }

        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            return null;
        }

        return match ($profile) {
            'logo' => $this->optimize($absolutePath, maxWidth: 640, maxHeight: 360, cropSquare: false, quality: 82),
            'favicon' => $this->optimize($absolutePath, maxWidth: 512, maxHeight: 512, cropSquare: true, quality: 82),
            default => null,
        };
    }

    /**
     * @return array{width: int, height: int, bytes: int}|null
     */
    private function optimize(string $absolutePath, int $maxWidth, int $maxHeight, bool $cropSquare, int $quality): ?array
    {
        $size = @getimagesize($absolutePath);

        if (! is_array($size)) {
            return null;
        }

        [$width, $height, $type] = $size;

        $source = $this->createImage($absolutePath, $type);

        if (! $source) {
            return null;
        }

        $srcX = 0;
        $srcY = 0;
        $srcWidth = $width;
        $srcHeight = $height;

        if ($cropSquare) {
            $side = min($width, $height);
            $srcX = (int) floor(($width - $side) / 2);
            $srcY = (int) floor(($height - $side) / 2);
            $srcWidth = $side;
            $srcHeight = $side;
            $targetWidth = min($maxWidth, $side);
            $targetHeight = min($maxHeight, $side);
        } else {
            $scale = min($maxWidth / $width, $maxHeight / $height, 1);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $targetWidth,
            $targetHeight,
            $srcWidth,
            $srcHeight,
        );

        $temporaryPath = $absolutePath . '.optimized';
        $saved = $this->saveImage($target, $temporaryPath, $type, $quality);

        imagedestroy($source);
        imagedestroy($target);

        if (! $saved) {
            @unlink($temporaryPath);

            return null;
        }

        rename($temporaryPath, $absolutePath);

        return [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'bytes' => filesize($absolutePath) ?: 0,
        ];
    }

    /**
     * @return resource|\GdImage|false
     */
    private function createImage(string $path, int $type): mixed
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function saveImage(\GdImage $image, string $path, int $type, int $quality): bool
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
            IMAGETYPE_PNG => imagepng($image, $path, max(0, min(9, (int) round((100 - $quality) / 11)))),
            IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($image, $path, $quality),
            default => false,
        };
    }
}
