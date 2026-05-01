<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlastMessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $channel = strtolower(trim((string) $request->query('channel', '')));
        if (!in_array($channel, ['whatsapp', 'email'], true)) {
            $channel = '';
        }

        $templates = BlastMessageTemplate::query()
            ->when($channel !== '', function ($query) use ($channel) {
                $query->where('channel', $channel);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.blast.templates.index', [
            'templates' => $templates,
            'channel' => $channel,
        ]);
    }

    public function create(Request $request)
    {
        $channel = strtolower(trim((string) $request->query('channel', 'whatsapp')));
        if (!in_array($channel, ['whatsapp', 'email'], true)) {
            $channel = 'whatsapp';
        }

        $returnTo = $this->sanitizeReturnTo(
            $request,
            $request->query('return_to')
        );

        return view('admin.blast.templates.create', [
            'channel' => $channel,
            'returnTo' => $returnTo,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:whatsapp,email',
            'name' => 'required|string|max:150',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
            'return_to' => 'nullable|string|max:2048',
        ]);

        $template = BlastMessageTemplate::create([
            'channel' => strtolower((string) $data['channel']),
            'name' => trim((string) $data['name']),
            'content' => (string) $data['content'],
            'created_by' => Auth::id(),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $this->redirectAfterSave(
            request: $request,
            fallbackChannel: strtolower((string) $data['channel']),
            templateId: (string) $template->id,
            successMessage: 'Template berhasil dibuat.'
        );
    }

    public function edit(Request $request, string $id)
    {
        $template = BlastMessageTemplate::findOrFail($id);
        $returnTo = $this->sanitizeReturnTo(
            $request,
            $request->query('return_to')
        );

        return view('admin.blast.templates.edit', [
            'template' => $template,
            'returnTo' => $returnTo,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $template = BlastMessageTemplate::findOrFail($id);

        $data = $request->validate([
            'channel' => 'required|in:whatsapp,email',
            'name' => 'required|string|max:150',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
            'return_to' => 'nullable|string|max:2048',
        ]);

        $template->update([
            'channel' => strtolower((string) $data['channel']),
            'name' => trim((string) $data['name']),
            'content' => (string) $data['content'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return $this->redirectAfterSave(
            request: $request,
            fallbackChannel: strtolower((string) $data['channel']),
            templateId: (string) $template->id,
            successMessage: 'Template diperbarui.'
        );
    }

    public function toggle(Request $request, string $id)
    {
        $template = BlastMessageTemplate::findOrFail($id);

        $data = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $isActive = (bool) $data['is_active'];

        $template->update([
            'is_active' => $isActive,
        ]);

        return back()->with(
            'success',
            $isActive
                ? 'Template berhasil diaktifkan.'
                : 'Template berhasil dinonaktifkan.'
        );
    }

    public function destroy(string $id)
    {
        BlastMessageTemplate::findOrFail($id)->delete();

        return back()->with('success', 'Template dihapus.');
    }

    private function redirectAfterSave(
        Request $request,
        string $fallbackChannel,
        string $templateId,
        string $successMessage
    ) {
        $returnTo = $this->sanitizeReturnTo(
            $request,
            $request->input('return_to')
        );

        if ($returnTo !== null) {
            $separator = str_contains($returnTo, '?') ? '&' : '?';

            return redirect()
                ->to($returnTo . $separator . 'template_created=' . urlencode($templateId))
                ->with('success', $successMessage);
        }

        return redirect()
            ->route('admin.blast.templates.index', ['channel' => $fallbackChannel])
            ->with('success', $successMessage);
    }

    private function sanitizeReturnTo(Request $request, mixed $returnTo): ?string
    {
        $returnTo = trim((string) $returnTo);
        if ($returnTo === '') {
            return null;
        }

        if (Str::startsWith($returnTo, '/')) {
            return $returnTo;
        }

        $parts = parse_url($returnTo);
        if ($parts === false) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $requestHost = strtolower((string) $request->getHost());

        if ($host === '' || $host !== $requestHost) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '/');
        if (!Str::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        $query = isset($parts['query']) && $parts['query'] !== ''
            ? '?' . $parts['query']
            : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== ''
            ? '#' . $parts['fragment']
            : '';

        return $path . $query . $fragment;
    }
}
