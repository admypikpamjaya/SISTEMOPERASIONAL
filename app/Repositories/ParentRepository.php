<?php

namespace App\Repositories;

use App\Models\ParentUser;

class ParentRepository
{
    public function findById(int $id): ?ParentUser
    {
        return ParentUser::find($id);
    }

    public function getAll()
    {
        return ParentUser::orderBy('name')->get();
    }
}
