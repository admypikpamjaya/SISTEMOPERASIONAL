<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ThemeController extends Controller
{
    public function index(ThemeService $themeService): View
    {
        return view('admin.theme.index', [
            'theme' => $themeService->current(),
            'defaults' => $themeService->defaults(),
        ]);
    }

    public function update(Request $request, ThemeService $themeService): RedirectResponse
    {
        $validated = $request->validate([
            'primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sidebar' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'surface' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            '*.regex' => __('app.website_theme.validation.hex'),
        ]);

        try {
            $themeService->saveManual($validated, $request->user()?->id);

            return back()->with('success', __('app.website_theme.saved_success'));
        } catch (Throwable $exception) {
            Log::warning('[WEBSITE THEME MANUAL SAVE FAILED]', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);
            report($exception);

            return back()
                ->withInput()
                ->with('error', __('app.website_theme.saved_failed'));
        }
    }

    public function image(Request $request, ThemeService $themeService): RedirectResponse
    {
        $validated = $request->validate([
            'theme_image' => [
                'required',
                'file',
                'max:8192',
            ],
        ]);

        try {
            $themeService->saveFromImage($validated['theme_image'], $request->user()?->id);

            return back()->with('success', __('app.website_theme.image_success'));
        } catch (Throwable $exception) {
            Log::warning('[WEBSITE THEME IMAGE SAVE FAILED]', [
                'user_id' => $request->user()?->id,
                'file_name' => $request->file('theme_image')?->getClientOriginalName(),
                'mime' => $request->file('theme_image')?->getMimeType(),
                'message' => $exception->getMessage(),
            ]);
            report($exception);

            return back()->with('error', $exception->getMessage() ?: __('app.website_theme.image_failed'));
        }
    }

    public function reset(ThemeService $themeService): RedirectResponse
    {
        $themeService->reset();

        return back()->with('success', __('app.website_theme.reset_success'));
    }
}
