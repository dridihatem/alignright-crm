<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    /**
     * Get a setting value by name
     */
    public static function get($name, $default = null)
    {
        return Setting::getValue($name, $default);
    }

    /**
     * Set a setting value by name
     */
    public static function set($name, $value)
    {
        return Setting::setValue($name, $value);
    }

    /**
     * Get site logo URL
     */
    public static function getLogoUrl()
    {
        $logo = self::get('site_logo');
        return $logo ? asset('storage/' . $logo) : null;
    }

    /**
     * Get favicon URL
     */
    public static function getFaviconUrl()
    {
        $favicon = self::get('favicon');
        return $favicon ? asset('storage/' . $favicon) : null;
    }

    /**
     * Get site name
     */
    public static function getSiteName()
    {
        return self::get('site_name', 'Dental Clinic Management');
    }

    /**
     * Get primary color
     */
    public static function getPrimaryColor()
    {
        return self::get('primary_color', '#696cff');
    }

    /**
     * Get email settings for mail configuration
     */
    public static function getEmailSettings()
    {
        return [
            'host' => self::get('mail_host', 'smtp.gmail.com'),
            'port' => self::get('mail_port', '587'),
            'username' => self::get('mail_username', ''),
            'password' => self::get('mail_password', ''),
            'encryption' => self::get('mail_encryption', 'tls'),
            'from_address' => self::get('mail_from_address', 'noreply@example.com'),
            'from_name' => self::get('mail_from_name', 'Dental Clinic'),
        ];
    }

    /**
     * Configure mail settings dynamically
     */
    public static function configureMail()
    {
        $emailSettings = self::getEmailSettings();
        
        config([
            'mail.mailers.smtp.host' => $emailSettings['host'],
            'mail.mailers.smtp.port' => $emailSettings['port'],
            'mail.mailers.smtp.username' => $emailSettings['username'],
            'mail.mailers.smtp.password' => $emailSettings['password'],
            'mail.mailers.smtp.encryption' => $emailSettings['encryption'],
            'mail.from.address' => $emailSettings['from_address'],
            'mail.from.name' => $emailSettings['from_name'],
        ]);
    }


    public static function getLanguage()
    {
        return self::get('language', 'fr');
    }
}
