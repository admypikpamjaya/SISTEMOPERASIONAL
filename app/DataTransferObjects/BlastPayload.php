<?php

namespace App\DataTransferObjects;

class BlastPayload
{
    public string $message;

    /** @var BlastAttachment[] */
    public array $attachments = [];

    /** metadata tambahan (announcement_id, billing_id, dll) */
    public array $meta = [];

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function addAttachment(BlastAttachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function setMeta(string $key, mixed $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'attachments' => array_map(
                fn (BlastAttachment $a) => $a->toArray(),
                $this->attachments
            ),
            'meta' => $this->meta,
        ];
    }
}
