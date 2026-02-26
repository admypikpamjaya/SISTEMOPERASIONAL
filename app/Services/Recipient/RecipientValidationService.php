<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientRowDTO;

class RecipientValidationService
{
    public function validate(RecipientRowDTO $row): RecipientRowDTO
    {
        $errors = [];

        if (!$row->email && !$row->phone && !$row->phoneSecondary) {
            $errors[] = 'Email dan WhatsApp kosong';
        }

        if ($row->email && !filter_var($row->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        if ($row->phone && !preg_match('/^(0|62)[0-9]{8,}$/', $row->phone)) {
            $errors[] = 'Format WhatsApp tidak valid';
        }

        if ($row->phoneSecondary && !preg_match('/^(0|62)[0-9]{8,}$/', $row->phoneSecondary)) {
            $errors[] = 'Format WhatsApp 2 tidak valid';
        }

        $row->isValid = empty($errors);
        $row->errors  = $errors;

        return $row;
    }
}
