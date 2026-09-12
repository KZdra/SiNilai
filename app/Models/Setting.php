<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, $default = null)
    {
        try {
            $record = static::where('key', $key)->first();
            return $record ? $record->value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Check whether a specific feature module is enabled.
     */
    public static function isModuleEnabled(string $module, bool $default = true): bool
    {
        $val = static::get("module_{$module}", $default ? '1' : '0');
        return $val === '1' || $val === 1 || $val === true || $val === 'true';
    }
}
