<?php

namespace App\DataTransferObjects;

class BlastPayload
{
    public string $message;
    public array $attachments = [];
    public array $meta = [];

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /* ===============================
     | Attachments
     =============================== */

    public function addAttachment(BlastAttachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function hasAttachments(): bool
    {
        return ! empty($this->attachments);
    }

    /* ===============================
     | Meta
     =============================== */

    public function setMeta(string $key, mixed $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /* ===============================
     | Serialization
     =============================== */

    public function toArray(): array
    {
        return [
            'message'     => $this->message,
            'attachments' => array_map(
                fn ($a) => $a->toArray(),
                $this->attachments
            ),
            'meta'        => $this->meta,
        ];
    }
}
