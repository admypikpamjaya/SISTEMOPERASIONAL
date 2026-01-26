<?php

namespace App\DataTransferObjects\Recipient;

class RecipientImportResultDTO
{
    public array $valid = [];
    public array $invalid = [];
    public array $duplicate = [];
    public array $missing = [];
}
