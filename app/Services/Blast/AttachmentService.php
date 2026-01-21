<?php

namespace App\Services\Blast;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\DataTransferObjects\BlastAttachment;

class AttachmentService
{
    /**
     * @param UploadedFile[] $files
     * @param string $blastUuid
     * @return BlastAttachment[]
     */
    public function store(array $files, string $blastUuid): array
    {
        $attachments = [];

        foreach ($files as $file) {
            $path = $file->store(
                "blasts/{$blastUuid}",
                'local'
            );

            $attachments[] = new BlastAttachment(
                path: $path,
                filename: $file->getClientOriginalName(),
                mime: $file->getMimeType()
            );
        }

        return $attachments;
    }
}
