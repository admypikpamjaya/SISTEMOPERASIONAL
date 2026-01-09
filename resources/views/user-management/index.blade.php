@extends('layouts.app') 

@section('content')
@include('shared.modal')
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">User Database</span>
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
                                placeholder="Cari user..."
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
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">NAMA</th>
                    <th scope="col">EMAIL</th>
                    <th scope="col">ROLE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td class="text-left">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @if($user->id != auth()->user()->id)
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button"
                                        id="send-reset-password-link-button"
                                        data-url="{{ route('user-database.send-reset-password-link', $user->id) }}"
                                        class="btn btn-outline-info"
                                    >
                                        <i class="fas fa-link"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        id="toggle-update-user-button" 
                                        data-user-id="{{ $user->id }}" 
                                        data-url="{{ route('user-database.show', $user->id) }}"
                                        class="btn btn-outline-warning"
                                    >
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        id="delete-user-button" 
                                        data-user-id="{{ $user->id }}" 
                                        data-url="{{ route('user-database.delete', $user->id) }}"
                                        class="btn btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data user</td>
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
    function constructUserForm(user = null)
    {
        return `
            <form id="user-form">
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" name="name" class="form-control" value="${user ? user.name : ''}" placeholder="Masukkan nama" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="${user ? user.email : ''}" placeholder="Masukkan email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="IT Support" ${user && user.role === 'IT Support' ? 'selected' : ''}>IT Support</option>
                        <option value="Asset Manager" ${user && user.role === 'Asset Manager' ? 'selected' : ''}>Asset Manager</option>
                        <option value="Finance" ${user && user.role === 'Finance' ? 'selected' : ''}>Finance</option>
                    </select>
                </div>
            </form>
        `;
    }

    $(function() {
        $('#toggle-user-registration-modal').click(function() {
            const form = constructUserForm();
            const buttons = `
                <button id="register-user-button" class="btn btn-sm btn-primary">Simpan</button>
            `;

            modal.show('Tambah User', form, buttons);
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
                const confirmation = await Notification.confirmation('Anda yakin ingin mengirimkan link reset password?');
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
                    <button id="update-user-button" data-user-id="${userId}" class="btn btn-sm btn-warning">Simpan</button>
                `;

                modal.show('Update User', form, buttons);
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
            const confirmation = await Notification.confirmation('Anda yakin ingin menghapus user ini?');
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