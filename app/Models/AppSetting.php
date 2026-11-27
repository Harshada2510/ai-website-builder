<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'default_theme_preset',
        'default_primary_color',
        'default_secondary_color',
        'default_background_color',
        'default_text_color',
        'generation_tone',
    ];

    public static function current(): self
    {
        $setting = static::query()->first();
        if ($setting) {
            return $setting;
        }

        return static::query()->create([
            'default_theme_preset' => 'indigo',
            'default_primary_color' => '#4F46E5',
            'default_secondary_color' => '#0EA5E9',
            'default_background_color' => '#FFFFFF',
            'default_text_color' => '#1F2937',
            'generation_tone' => 'professional',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function themePresets(): array
    {
        return [
            'indigo' => 'Indigo',
            'teal' => 'Teal',
            'warm' => 'Warm',
            'blue' => 'Blue',
            'mono' => 'Mono',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function toneOptions(): array
    {
        return [
            'professional' => 'Professional',
            'friendly' => 'Friendly',
            'bold' => 'Bold',
            'minimal' => 'Minimal',
        ];
    }
}
