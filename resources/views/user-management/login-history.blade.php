@extends('layouts.app')

@section('content')
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-5">
                <span class="card-title">{{ __('app.user_management.login_history') }}</span>
                <div class="text-muted small">{{ __('app.user_management.latest_logins') }}</div>
            </div>
            <div class="col-md-7">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="keyword"
                                value="{{ request('keyword') }}"
                                class="form-control float-right"
                                placeholder="{{ __('app.user_management.login_history_search_placeholder') }}"
                            />

                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('user-database.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left mr-1"></i>
                            {{ __('app.user_management.back_to_users') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 app-table-compact">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ strtoupper(__('app.user_management.name')) }}</th>
                    <th>{{ strtoupper(__('app.user_management.email')) }}</th>
                    <th>{{ strtoupper(__('app.user_management.role')) }}</th>
                    <th>{{ strtoupper(__('app.user_management.ip_address')) }}</th>
                    <th>{{ strtoupper(__('app.user_management.browser')) }}</th>
                    <th>{{ strtoupper(__('app.user_management.login_at')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loginHistories as $history)
                    <tr>
                        <td>{{ $loginHistories->firstItem() + $loop->index }}</td>
                        <td>{{ $history->user?->name ?? '-' }}</td>
                        <td>{{ $history->user?->email ?? '-' }}</td>
                        <td>{{ $history->user?->role ?? '-' }}</td>
                        <td>{{ $history->ip_address ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit((string) ($history->user_agent ?? '-'), 60) }}</td>
                        <td>{{ $history->logged_in_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('app.user_management.no_login_history') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $loginHistories->links() }}
    </div>
</form>
@stop
