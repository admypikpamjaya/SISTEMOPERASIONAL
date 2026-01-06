<?php

namespace App\Http\Controllers\User;

use App\DTOs\User\RegisterUserDTO;
use App\DTOs\User\UserDataDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request)
    {
        $users = $this->service->getUsers($request->keyword, $request->page);
        return view('user-management.index', [
            'users' => $users,
        ]);
    }

    public function show(string $userId)
    {
        try
        {
            $user = $this->service->getUser($userId);
            return response()->json([
                'message' => 'User berhasil ditemukan',
                'data' => $user
            ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function store(RegisterUserRequest $request)
    {
        $this->service->createUser(RegisterUserDTO::fromArray($request->validated()));

        session()->flash('success', 'User berhasil ditambahkan');
        return response()->json(['success' => true]);
    }

    public function update(EditUserRequest $request)
    {
        try 
        {
            $userDTO = UserDataDTO::fromArray($request->validated());
            $this->service->updateUser($userDTO->id, $userDTO);


            session()->flash('success', 'User berhasil diupdate');
            return response()->json(['success' => true]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }
    
    public function delete(string $id)
    {
        try 
        {
            $this->service->deleteUser($id);

            session()->flash('success', 'User berhasil dihapus');
            return response()->json(['success' => true]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }
}
