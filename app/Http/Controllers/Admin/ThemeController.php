<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        } catch (Throwable) {
            return back()
                ->withInput()
                ->with('error', __('app.website_theme.saved_failed'));
        }
    }

    public function image(Request $request, ThemeService $themeService): RedirectResponse
    {
        $validated = $request->validate([
            'theme_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        try {
            $themeService->saveFromImage($validated['theme_image'], $request->user()?->id);

            return back()->with('success', __('app.website_theme.image_success'));
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage() ?: __('app.website_theme.image_failed'));
        }
    }

    public function reset(ThemeService $themeService): RedirectResponse
    {
        $themeService->reset();

        return back()->with('success', __('app.website_theme.reset_success'));
    }
}
