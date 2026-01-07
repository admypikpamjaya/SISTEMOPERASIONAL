<?php

namespace App\DTOs\File;

class DownloadFileDTO
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $content
    ) {}
}