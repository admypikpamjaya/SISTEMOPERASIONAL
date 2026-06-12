@extends('layouts.app')

@section('content')
<style>
    .fc-shell { display: grid; gap: 1rem; }
    .fc-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .fc-title { display:flex; align-items:center; gap:.75rem; }
    .fc-title-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; background:linear-gradient(135deg,#2563eb,#06b6d4); box-shadow:0 10px 24px rgba(37,99,235,.24); }
    .fc-title h1 { margin:0; font-size:1.35rem; font-weight:800; color:var(--app-text,#0f172a); }
    .fc-title p { margin:.12rem 0 0; color:var(--app-text-muted,#64748b); font-size:.86rem; }
    .fc-grid { display:grid; grid-template-columns: minmax(280px, 380px) minmax(0,1fr); gap:1rem; align-items:start; }
    @media(max-width: 991px) { .fc-grid { grid-template-columns:1fr; } }
    .fc-card { background:var(--app-surface,#fff); border:1px solid var(--app-border,rgba(148,163,184,.22)); border-radius:14px; box-shadow:var(--app-shadow,0 10px 30px rgba(15,23,42,.08)); overflow:hidden; }
    .fc-card-head { padding:1rem 1.15rem; border-bottom:1px solid var(--app-border,rgba(148,163,184,.18)); display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
    .fc-card-head h2 { margin:0; font-size:.95rem; font-weight:800; color:var(--app-text,#0f172a); display:flex; align-items:center; gap:.5rem; }
    .fc-body { padding:1.15rem; }
    .fc-field { margin-bottom:.9rem; }
    .fc-label { display:flex; align-items:center; gap:.35rem; margin-bottom:.38rem; font-size:.74rem; text-transform:uppercase; letter-spacing:.06em; font-weight:800; color:var(--app-text-muted,#64748b); }
    .fc-control { width:100%; min-height:40px; border:1px solid var(--app-border,#dbe4f0); border-radius:10px; padding:.55rem .7rem; background:var(--app-surface,#fff); color:var(--app-text,#0f172a); font-weight:600; }
    textarea.fc-control { min-height:110px; resize:vertical; }
    .fc-actions { display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; }
    .fc-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; min-height:40px; padding:.55rem .9rem; border-radius:10px; border:1px solid transparent; font-weight:800; font-size:.82rem; text-decoration:none; cursor:pointer; }
    .fc-btn-primary { color:#fff; background:linear-gradient(135deg,#2563eb,#1d4ed8); box-shadow:0 8px 20px rgba(37,99,235,.25); }
    .fc-btn-muted { color:var(--app-text,#0f172a); background:var(--app-surface-soft,#f8fafc); border-color:var(--app-border,#dbe4f0); }
    .fc-btn-danger { color:#dc2626; background:rgba(220,38,38,.08); border-color:rgba(220,38,38,.2); }
    .fc-btn-warning { color:#b45309; background:rgba(245,158,11,.1); border-color:rgba(245,158,11,.28); }
    .fc-btn-success { color:#047857; background:rgba(16,185,129,.1); border-color:rgba(16,185,129,.24); }
    .fc-btn:disabled { opacity:.48; cursor:not-allowed; box-shadow:none; }
    .fc-filter { display:grid; grid-template-columns:minmax(180px,1fr) 150px auto; gap:.6rem; align-items:end; }
    @media(max-width: 768px) { .fc-filter { grid-template-columns:1fr; } }
    .fc-two-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
    @media(max-width: 575px) { .fc-two-grid { grid-template-columns:1fr; } }
    .fc-table { width:100%; border-collapse:collapse; }
    .fc-table th { text-align:left; padding:.8rem .95rem; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--app-text-muted,#64748b); background:var(--app-surface-soft,#f8fafc); border-bottom:1px solid var(--app-border,#e2e8f0); white-space:nowrap; }
    .fc-table td { padding:.85rem .95rem; border-bottom:1px solid var(--app-border,#e2e8f0); color:var(--app-text-soft,#334155); vertical-align:top; }
    .fc-table tr:last-child td { border-bottom:0; }
    .fc-name { font-weight:800; color:var(--app-text,#0f172a); }
    .fc-desc { font-size:.78rem; color:var(--app-text-muted,#64748b); margin-top:.18rem; }
    .fc-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.24rem .58rem; font-size:.72rem; font-weight:800; }
    .fc-badge.active { background:rgba(16,185,129,.12); color:#047857; }
    .fc-badge.inactive { background:rgba(148,163,184,.16); color:#475569; }
    .fc-badge.group { background:rgba(37,99,235,.12); color:#1d4ed8; }
    .fc-badge.single { background:rgba(14,165,233,.12); color:#0369a1; }
    .fc-badge.static { background:rgba(245,158,11,.14); color:#b45309; }
    .fc-badge.custom { background:rgba(139,92,246,.13); color:#6d28d9; }
    .fc-member-box { display:grid; gap:.45rem; max-height:170px; overflow:auto; padding:.6rem; border:1px solid var(--app-border,#dbe4f0); border-radius:10px; background:var(--app-surface-soft,#f8fafc); }
    .fc-member-option { display:flex; align-items:center; gap:.45rem; font-size:.82rem; font-weight:700; color:var(--app-text-soft,#334155); }
    .fc-member-option input { width:16px; height:16px; }
    .fc-member-chips { display:flex; gap:.35rem; flex-wrap:wrap; margin-top:.45rem; }
    .fc-member-chip { display:inline-flex; align-items:center; border-radius:999px; padding:.2rem .5rem; background:rgba(37,99,235,.09); color:#1d4ed8; font-size:.72rem; font-weight:800; }
    .fc-row-actions { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
    .fc-action-note { flex-basis:100%; font-size:.7rem; color:var(--app-text-muted,#64748b); font-weight:700; }
    .fc-empty { padding:2.4rem 1rem; text-align:center; color:var(--app-text-muted,#64748b); font-weight:700; }
    .fc-help { display:grid; gap:.35rem; margin-bottom:1rem; padding:.8rem .9rem; border-radius:12px; background:rgba(37,99,235,.07); border:1px solid rgba(37,99,235,.14); color:var(--app-text-soft,#334155); font-size:.78rem; font-weight:600; line-height:1.45; }
    .fc-help strong { color:var(--app-text,#0f172a); }
</style>

@php
    $typeOptions = $typeOptions ?? \App\Models\FinanceCategory::typeOptions();
    $sourceOptions = $sourceOptions ?? \App\Models\FinanceCategory::sourceOptions();
    $memberOptions = $memberOptions ?? collect();
    $editCategoryMembers = $editCategory && $editCategory->relationLoaded('members')
        ? $editCategory->members
        : collect();
    $selectedMemberIds = collect(old(
        'member_ids',
        $editCategoryMembers->pluck('id')->all()
    ))->map(static fn ($id): string => (string) $id)->all();
    $selectedType = old('category_type', $editCategory->category_type ?? \App\Models\FinanceCategory::TYPE_SINGLE);
    $selectedSource = old('source_type', $editCategory->source_type ?? \App\Models\FinanceCategory::SOURCE_CUSTOM);
@endphp

<div class="fc-shell">
    <div class="fc-header">
        <div class="fc-title">
            <div class="fc-title-icon"><i class="fas fa-tags"></i></div>
            <div>
                <h1>{{ __('app.finance_categories.title') }}</h1>
                <p>{{ __('app.finance_categories.subtitle') }}</p>
            </div>
        </div>
        <a href="{{ route('finance.dashboard') }}" class="fc-btn fc-btn-muted">
            <i class="fas fa-arrow-left"></i> {{ __('app.finance_categories.back_to_dashboard') }}
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>{{ __('app.finance_categories.validation_failed') }}</strong> {{ $errors->first() }}
        </div>
    @endif

    <div class="fc-help">
        <div><strong>{{ __('app.finance_categories.edit') }}</strong> {{ __('app.finance_categories.help_edit') }}</div>
        <div><strong>{{ __('app.finance_categories.hide') }}</strong> {{ __('app.finance_categories.help_hide') }}</div>
        <div><strong>{{ __('app.finance_categories.delete') }}</strong> {{ __('app.finance_categories.help_delete') }}</div>
    </div>

    <div class="fc-grid">
        <div class="fc-card">
            <div class="fc-card-head">
                <h2><i class="fas fa-{{ $editCategory ? 'pen' : 'plus' }}"></i> {{ $editCategory ? __('app.finance_categories.edit_category') : __('app.finance_categories.add_category') }}</h2>
            </div>
            <div class="fc-body">
                <form method="POST" action="{{ $editCategory ? route('finance.categories.update', $editCategory->id) : route('finance.categories.store') }}">
                    @csrf
                    @if($editCategory)
                        @method('PUT')
                    @endif

                    <div class="fc-field">
                        <label class="fc-label" for="name"><i class="fas fa-tag"></i> {{ __('app.finance_categories.name') }}</label>
                        <input id="name" name="name" class="fc-control" value="{{ old('name', $editCategory->name ?? '') }}" placeholder="{{ __('app.finance_categories.name_placeholder') }}">
                    </div>

                    <div class="fc-two-grid">
                        <div class="fc-field">
                            <label class="fc-label" for="category_type"><i class="fas fa-sitemap"></i> {{ __('app.finance_categories.type_label') }}</label>
                            <select id="category_type" name="category_type" class="fc-control">
                                @foreach($typeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fc-field">
                            <label class="fc-label" for="source_type"><i class="fas fa-shield-alt"></i> {{ __('app.finance_categories.source_label') }}</label>
                            <select id="source_type" name="source_type" class="fc-control">
                                @foreach($sourceOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedSource === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="fc-field" id="member_ids_group">
                        <label class="fc-label"><i class="fas fa-link"></i> {{ __('app.finance_categories.members_label') }}</label>
                        <div class="fc-member-box">
                            @foreach($memberOptions as $option)
                                @continue($editCategory && (string) $editCategory->id === (string) $option->id)
                                <label class="fc-member-option">
                                    <input type="checkbox" name="member_ids[]" value="{{ $option->id }}" {{ in_array((string) $option->id, $selectedMemberIds, true) ? 'checked' : '' }}>
                                    <span>{{ $option->name }}</span>
                                    @if(($option->category_type ?? 'single') === 'group')
                                        <small class="fc-badge group" style="padding:.08rem .35rem;">{{ __('app.finance_categories.group_badge') }}</small>
                                    @endif
                                    @if(($option->status ?? \App\Models\FinanceCategory::STATUS_ACTIVE) !== \App\Models\FinanceCategory::STATUS_ACTIVE)
                                        <small class="fc-badge inactive" style="padding:.08rem .35rem;">{{ __('app.finance_categories.hidden_badge') }}</small>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="fc-field">
                        <label class="fc-label" for="description"><i class="fas fa-align-left"></i> {{ __('app.finance_categories.description') }}</label>
                        <textarea id="description" name="description" class="fc-control" placeholder="{{ __('app.finance_categories.description_placeholder') }}">{{ old('description', $editCategory->description ?? '') }}</textarea>
                    </div>

                    <div class="fc-field">
                        <label class="fc-label" for="status"><i class="fas fa-eye"></i> {{ __('app.finance_categories.visibility') }}</label>
                        <select id="status" name="status" class="fc-control">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $editCategory->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fc-actions">
                        <button type="submit" class="fc-btn fc-btn-primary">
                            <i class="fas fa-save"></i> {{ $editCategory ? __('app.finance_categories.save_changes') : __('app.finance_categories.add_submit') }}
                        </button>
                        @if($editCategory)
                            <a href="{{ route('finance.categories.index') }}" class="fc-btn fc-btn-muted">{{ __('app.finance_categories.cancel') }}</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="fc-card">
            <div class="fc-card-head">
                <h2><i class="fas fa-list"></i> {{ __('app.finance_categories.list_title') }}</h2>
            </div>
            <div class="fc-body">
                <form method="GET" action="{{ route('finance.categories.index') }}" class="fc-filter">
                    <div>
                        <label class="fc-label" for="q"><i class="fas fa-search"></i> {{ __('app.finance_categories.search') }}</label>
                        <input id="q" name="q" class="fc-control" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('app.finance_categories.search_placeholder') }}">
                    </div>
                    <div>
                        <label class="fc-label" for="status-filter"><i class="fas fa-filter"></i> {{ __('app.finance_categories.visibility') }}</label>
                        <select id="status-filter" name="status" class="fc-control">
                            <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('app.finance_categories.all') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fc-actions">
                        <button type="submit" class="fc-btn fc-btn-primary"><i class="fas fa-search"></i> {{ __('app.finance_categories.filter') }}</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th>{{ __('app.finance_categories.category') }}</th>
                            <th>{{ __('app.finance_categories.type_label') }}</th>
                            <th>{{ __('app.finance_categories.visibility') }}</th>
                            <th>{{ __('app.finance_categories.members') }}</th>
                            <th>{{ __('app.finance_categories.used_by') }}</th>
                            <th>{{ __('app.finance_categories.created_by') }}</th>
                            <th>{{ __('app.finance_categories.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            @php($usage = (int) ($usageCounts[$category->id] ?? 0))
                            <tr>
                                <td>
                                    <div class="fc-name">{{ $category->name }}</div>
                                    @if(!empty($category->description))
                                        <div class="fc-desc">{{ $category->description }}</div>
                                    @endif
                                    <div class="fc-member-chips">
                                        <span class="fc-badge {{ $category->source_type ?? 'custom' }}">
                                            {{ $sourceOptions[$category->source_type ?? 'custom'] ?? ($category->source_type ?? 'Custom') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fc-badge {{ $category->category_type ?? 'single' }}">
                                        {{ $typeOptions[$category->category_type ?? 'single'] ?? ($category->category_type ?? 'Berdiri Sendiri') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fc-badge {{ $category->status }}">
                                        <i class="fas fa-{{ $category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? 'eye' : 'eye-slash' }}"></i>
                                        {{ $statusOptions[$category->status] ?? $category->status }}
                                    </span>
                                </td>
                                <td>
                                    @php($rowMembers = $category->relationLoaded('members') ? $category->members : collect())
                                    @if($rowMembers->isNotEmpty())
                                        <div class="fc-member-chips">
                                            @foreach($rowMembers as $member)
                                                <span class="fc-member-chip">{{ $member->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="fc-desc">-</span>
                                    @endif
                                </td>
                                <td>{{ __('app.finance_categories.usage_count', ['count' => number_format($usage, 0, ',', '.')]) }}</td>
                                <td>{{ $category->creator?->name ?? '-' }}</td>
                                <td>
                                    <div class="fc-row-actions">
                                        <a href="{{ route('finance.categories.index', array_merge(request()->query(), ['edit' => $category->id])) }}" class="fc-btn fc-btn-muted">
                                            <i class="fas fa-pen"></i> {{ __('app.finance_categories.edit') }}
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('finance.categories.visibility', $category->id) }}"
                                            onsubmit='return confirm(@json($category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? __('app.finance_categories.hide_confirm') : __('app.finance_categories.show_confirm')))'
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="visible" value="{{ $category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? '0' : '1' }}">
                                            <button type="submit" class="fc-btn {{ $category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? 'fc-btn-warning' : 'fc-btn-success' }}">
                                                <i class="fas fa-{{ $category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? 'eye-slash' : 'eye' }}"></i>
                                                {{ $category->status === \App\Models\FinanceCategory::STATUS_ACTIVE ? __('app.finance_categories.hide') : __('app.finance_categories.show') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('finance.categories.destroy', $category->id) }}" onsubmit='return confirm(@json(__('app.finance_categories.delete_confirm')))'>
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="fc-btn fc-btn-danger"
                                                {{ $usage > 0 ? 'disabled' : '' }}
                                                title="{{ $usage > 0 ? __('app.finance_categories.delete_locked_title') : __('app.finance_categories.delete_title') }}"
                                            >
                                                <i class="fas fa-trash"></i> {{ __('app.finance_categories.delete') }}
                                            </button>
                                        </form>
                                        @if($usage > 0)
                                            <div class="fc-action-note">{{ __('app.finance_categories.delete_locked_note') }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="fc-empty">{{ __('app.finance_categories.empty') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const typeSelect = document.getElementById('category_type');
        const membersGroup = document.getElementById('member_ids_group');
        if (!typeSelect || !membersGroup) {
            return;
        }

        function syncMembersVisibility() {
            const isGroup = typeSelect.value === 'group';
            membersGroup.style.display = isGroup ? '' : 'none';
            membersGroup.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                input.disabled = !isGroup;
            });
        }

        typeSelect.addEventListener('change', syncMembersVisibility);
        syncMembersVisibility();
    })();
</script>
@endsection
