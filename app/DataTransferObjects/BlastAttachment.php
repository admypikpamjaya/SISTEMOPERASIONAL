<?php

namespace App\DataTransferObjects;

class BlastAttachment
{
    public string $path;
    public string $filename;
    public string $mime;

    public function __construct(
        string $path,
        string $filename,
        string $mime
    ) {
        $this->path = $path;
        $this->filename = $filename;
        $this->mime = $mime;
    }

    /**
     * Untuk logging / debug / provider dummy
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'filename' => $this->filename,
            'mime' => $this->mime,
        ];
    }
}
