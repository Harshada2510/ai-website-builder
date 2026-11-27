<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_theme_preset' => ['nullable', 'string', 'in:indigo,teal,warm,blue,mono'],
            'default_primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'generation_tone' => ['required', 'string', 'in:professional,friendly,bold,minimal'],
        ], [
            'default_primary_color.regex' => 'Primary color must be a valid hex color (e.g. #4F46E5).',
            'default_secondary_color.regex' => 'Secondary color must be a valid hex color.',
            'default_background_color.regex' => 'Background color must be a valid hex color.',
            'default_text_color.regex' => 'Text color must be a valid hex color.',
        ]);

        $setting = AppSetting::current();
        $setting->fill([
            'default_theme_preset' => $validated['default_theme_preset'] ?? null,
            'default_primary_color' => strtoupper($validated['default_primary_color']),
            'default_secondary_color' => strtoupper($validated['default_secondary_color']),
            'default_background_color' => strtoupper($validated['default_background_color']),
            'default_text_color' => strtoupper($validated['default_text_color']),
            'generation_tone' => $validated['generation_tone'],
        ]);
        $setting->save();

        return redirect()
            ->route('settings')
            ->with('success', 'Settings saved successfully.');
    }
}
