<?php

namespace App\Http\Controllers\User;

use App\DTOs\User\RegisterUserDTO;
use App\DTOs\User\UserDataDTO;
use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Models\LoginHistory;
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
            'roleOptions' => $this->getRoleOptions(),
            'userManagementI18n' => $this->getUserManagementI18n(),
        ]);
    }

    public function loginHistory(Request $request)
    {
        $this->ensureLoginHistoryAccess();

        $keyword = trim((string) $request->keyword);
        $loginHistoryQuery = LoginHistory::query()
            ->with('user:id,name,email,role')
            ->latest('logged_in_at');

        if ($keyword !== '') {
            $loginHistoryQuery->where(function ($query) use ($keyword) {
                $query->where('ip_address', 'like', '%' . $keyword . '%')
                    ->orWhere('user_agent', 'like', '%' . $keyword . '%')
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%')
                            ->orWhere('role', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $loginHistories = $loginHistoryQuery
            ->paginate(10)
            ->withQueryString();

        return view('user-management.login-history', [
            'loginHistories' => $loginHistories,
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

        session()->flash(
            'success',
            'User berhasil ditambahkan. Password sementara: ' . $this->service->getInitialUserPassword()
        );
        return response()->json(['success' => true]);
    }

    public function sendResetPasswordLink(string $id)
    {
        try 
        {
            $this->service->sendResetPasswordLink($id);

            session()->flash('success', 'Link reset password berhasil dikirim');
            return response()->json(['success' => true]);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
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

    private function ensureLoginHistoryAccess(): void
    {
        abort_unless(
            auth()->check()
            && in_array(auth()->user()->role, [UserRole::IT_SUPPORT->value, UserRole::ADMIN->value], true),
            403
        );
    }

    private function getRoleOptions(): array
    {
        return collect(UserRole::cases())
            ->map(function (UserRole $role): array {
                $label = match ($role) {
                    UserRole::USER => __('app.user_management.roles.user'),
                    UserRole::ADMIN => __('app.user_management.roles.admin'),
                    UserRole::IT_SUPPORT => __('app.user_management.roles.it_support'),
                    UserRole::ASSET_MANAGER => __('app.user_management.roles.asset_manager'),
                    UserRole::FINANCE => __('app.user_management.roles.finance'),
                    UserRole::PEMBINA => __('app.user_management.roles.pembina'),
                    UserRole::BLASTING => __('app.user_management.roles.blasting'),
                    UserRole::QC => __('app.user_management.roles.qc'),
                };

                return [
                    'value' => $role->value,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    private function getUserManagementI18n(): array
    {
        return [
            'name' => __('app.user_management.name'),
            'namePlaceholder' => __('app.user_management.name_placeholder'),
            'email' => __('app.user_management.email'),
            'emailPlaceholder' => __('app.user_management.email_placeholder'),
            'role' => __('app.user_management.role'),
            'selectRole' => __('app.user_management.select_role'),
            'save' => __('app.user_management.save'),
        ];
    }
}
