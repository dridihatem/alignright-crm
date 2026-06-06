<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'type',
        'group',
        'label',
        'description'
    ];

    /**
     * Get setting value by name
     */
    public static function getValue($name, $default = null)
    {
        $setting = self::where('name', $name)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by name
     */
    public static function setValue($name, $value)
    {
        $setting = self::where('name', $name)->first();
        
        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            self::create([
                'name' => $name,
                'value' => $value,
                'type' => 'text',
                'group' => 'general',
                'label' => ucfirst(str_replace('_', ' ', $name))
            ]);
        }
        
        return $setting;
    }

    /**
     * Get all settings grouped by group
     */
    public static function getGrouped()
    {
        return self::all()->groupBy('group');
    }

    /**
     * Get settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }
}
