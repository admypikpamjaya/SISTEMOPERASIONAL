@extends('layouts.app') 

@section('content')
@include('shared.modal')
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">{{ __('app.user_management.title') }}</span>
            </div>
            <div class="col-md-6">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <input 
                                type="text" 
                                name="keyword" 
                                value="{{ request('keyword') }}" 
                                class="form-control float-right" 
                                placeholder="{{ __('app.user_management.search_placeholder') }}"
                            />

                            <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-1">
                        <button id="toggle-user-registration-modal" type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 app-table-compact">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ strtoupper(__('app.user_management.name')) }}</th>
                    <th scope="col">{{ strtoupper(__('app.user_management.email')) }}</th>
                    <th scope="col">{{ strtoupper(__('app.user_management.role')) }}</th>
                    <th scope="col" class="text-center">{{ strtoupper(__('app.user_management.actions')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td class="text-left">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td class="text-center">
                            @if($user->id != auth()->user()->id)
                                <div class="app-table-actions">
                                    <button 
                                        type="button"
                                        id="send-reset-password-link-button"
                                        data-url="{{ route('user-database.send-reset-password-link', $user->id) }}"
                                        class="app-icon-btn is-info"
                                        title="{{ __('app.user_management.reset_password') }}"
                                        aria-label="{{ __('app.user_management.reset_password') }}"
                                    >
                                        <i class="fas fa-link"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        id="toggle-update-user-button" 
                                        data-user-id="{{ $user->id }}" 
                                        data-url="{{ route('user-database.show', $user->id) }}"
                                        class="app-icon-btn is-warning"
                                        title="{{ __('app.user_management.edit_user') }}"
                                        aria-label="{{ __('app.user_management.edit_user') }}"
                                    >
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        id="delete-user-button" 
                                        data-user-id="{{ $user->id }}" 
                                        data-url="{{ route('user-database.delete', $user->id) }}"
                                        class="app-icon-btn is-danger"
                                        title="{{ __('app.user_management.delete_user') }}"
                                        aria-label="{{ __('app.user_management.delete_user') }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ __('app.user_management.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</form>
@stop

@section('js')
@if(session()->has('success'))
<script>
    Notification.success("{{ session()->get('success') }}");
</script>
@endif

<script>
    const userRoleOptions = @json($roleOptions);
    const userManagementI18n = @json($userManagementI18n);

    function escapeHtml(value)
    {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function constructUserForm(user = null)
    {
        const selectedRole = user?.role ?? '';
        const roleOptions = userRoleOptions
            .map((role) => `
                <option value="${escapeHtml(role.value)}" ${role.value === selectedRole ? 'selected' : ''}>
                    ${escapeHtml(role.label)}
                </option>
            `)
            .join('');

        return `
            <form id="user-form">
                <div class="form-group">
                    <label for="name">${escapeHtml(userManagementI18n.name)}</label>
                    <input type="text" name="name" class="form-control" value="${escapeHtml(user?.name ?? '')}" placeholder="${escapeHtml(userManagementI18n.namePlaceholder)}" required>
                </div>
                <div class="form-group">
                    <label for="email">${escapeHtml(userManagementI18n.email)}</label>
                    <input type="email" name="email" class="form-control" value="${escapeHtml(user?.email ?? '')}" placeholder="${escapeHtml(userManagementI18n.emailPlaceholder)}" required>
                </div>
                <div class="form-group">
                    <label for="role">${escapeHtml(userManagementI18n.role)}</label>
                    <select name="role" class="form-control" required>
                        <option value="">${escapeHtml(userManagementI18n.selectRole)}</option>
                        ${roleOptions}
                    </select>
                </div>
            </form>
        `;
    }

    $(function() {
        $('#toggle-user-registration-modal').click(function() {
            const form = constructUserForm();
            const buttons = `
                <button id="register-user-button" class="btn btn-sm btn-primary">${escapeHtml(userManagementI18n.save)}</button>
            `;

            modal.show(@json(__('app.user_management.add_user')), form, buttons);
        });

        $(document).on('click', '#register-user-button', async function() {
            Loading.show()

            try 
            {
                const form = document.getElementById('user-form');
                const formData = new FormData(form);

                const { message } = await Http.post("{{ route('user-database.store') }}", formData);

                modal.hide(); 
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '#send-reset-password-link-button', async function() {
            try 
            {
                const confirmation = await Notification.confirmation(@json(__('app.user_management.reset_password_confirm')));
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();
                
                await Http.post($(this).data('url'));
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '#toggle-update-user-button', async function() {
            Loading.show();
            try
            {
                const userId = $(this).data('user-id');
                const url = $(this).data('url');

                const user = await Http.get(url);

                const form = constructUserForm(user.data);
                const buttons = `
                    <button id="update-user-button" data-user-id="${userId}" class="btn btn-sm btn-warning">${escapeHtml(userManagementI18n.save)}</button>
                `;

                modal.show(@json(__('app.user_management.update_user')), form, buttons);
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '#update-user-button', async function() {
            Loading.show();
            try 
            {
                const userId = $(this).data('user-id');

                const form = document.getElementById('user-form');
                const formData = new FormData(form);

                formData.append('id', userId);
                formData.set('_method', 'PUT');

                const { message } = await Http.post("{{ route('user-database.update') }}", formData);

                modal.hide();
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally 
            {
                Loading.hide();
            }
        })

        $(document).on('click', '#delete-user-button', async function() {
            const confirmation = await Notification.confirmation(@json(__('app.user_management.delete_confirm')));
            if(!confirmation.isConfirmed)
                return;

            Loading.show();
            try
            {
                const url = $(this).data('url');
                const { message } = await Http.delete(url);

                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });
    });
</script>
@stop
