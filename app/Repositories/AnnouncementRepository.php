<?php

namespace App\Repositories;

use App\Models\Announcement;

class AnnouncementRepository
{
    public function paginate(int $perPage = 10)
    {
        return Announcement::latest()->paginate($perPage);
    }

    public function findById(int $id): ?Announcement
    {
        return Announcement::find($id);
    }

    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }
}
