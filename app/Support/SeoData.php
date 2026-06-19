<?php

namespace App\Support;

final readonly class SeoData
{
    /**
     * @param  array<string, mixed>  $structuredData
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $robots,
        public ?string $canonical,
        public string $image,
        public string $imageAlt,
        public string $type,
        public string $siteName,
        public ?string $googleSiteVerification,
        public array $structuredData,
    ) {}
}
