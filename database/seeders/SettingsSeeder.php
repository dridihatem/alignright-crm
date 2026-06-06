<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'name' => 'site_name',
                'value' => 'Dental Clinic Management',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name',
                'description' => 'The name of your dental clinic management system'
            ],
            [
                'name' => 'site_description',
                'value' => 'Professional dental clinic management system',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Site Description',
                'description' => 'A brief description of your system'
            ],
            [
                'name' => 'timezone',
                'value' => 'UTC',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Timezone',
                'description' => 'System timezone'
            ],
            [
                'name' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Date Format',
                'description' => 'Date format for the system'
            ],
            [
                'name' => 'currency',
                'value' => 'TND',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Currency',
                'description' => 'Default currency for the system'
            ],
            [
                'name' => 'language',
                'value' => 'en',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Language',
                'description' => 'Default language for the system'
            ],

            // Appearance Settings
            [
                'name' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'appearance',
                'label' => 'Site Logo',
                'description' => 'Upload your clinic logo (recommended size: 200x60px)'
            ],
            [
                'name' => 'favicon',
                'value' => null,
                'type' => 'image',
                'group' => 'appearance',
                'label' => 'Favicon',
                'description' => 'Upload favicon (recommended size: 32x32px)'
            ],
            [
                'name' => 'primary_color',
                'value' => '#696cff',
                'type' => 'text',
                'group' => 'appearance',
                'label' => 'Primary Color',
                'description' => 'Primary color for the theme (hex code)'
            ],

            // Email Settings
            [
                'name' => 'mail_host',
                'value' => 'smtp.gmail.com',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Mail Host',
                'description' => 'SMTP server host'
            ],
            [
                'name' => 'mail_port',
                'value' => '587',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Mail Port',
                'description' => 'SMTP server port'
            ],
            [
                'name' => 'mail_username',
                'value' => '',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Mail Username',
                'description' => 'SMTP username'
            ],
            [
                'name' => 'mail_password',
                'value' => '',
                'type' => 'password',
                'group' => 'email',
                'label' => 'Mail Password',
                'description' => 'SMTP password'
            ],
            [
                'name' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'select',
                'group' => 'email',
                'label' => 'Mail Encryption',
                'description' => 'SMTP encryption type'
            ],
            [
                'name' => 'mail_from_address',
                'value' => 'noreply@example.com',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Mail From Address',
                'description' => 'Default sender email address'
            ],
            [
                'name' => 'mail_from_name',
                'value' => 'Dental Clinic',
                'type' => 'text',
                'group' => 'email',
                'label' => 'Mail From Name',
                'description' => 'Default sender name'
            ],

            // Google Drive Settings
            [
                'name' => 'google_client_id',
                'value' => '',
                'type' => 'text',
                'group' => 'google_drive',
                'label' => 'Google Client ID',
                'description' => 'Google Drive API Client ID'
            ],
            [
                'name' => 'google_client_secret',
                'value' => '',
                'type' => 'password',
                'group' => 'google_drive',
                'label' => 'Google Client Secret',
                'description' => 'Google Drive API Client Secret'
            ],
            [
                'name' => 'google_redirect_uri',
                'value' => '',
                'type' => 'text',
                'group' => 'google_drive',
                'label' => 'Google Redirect URI',
                'description' => 'Google Drive OAuth redirect URI'
            ],
            [
                'name' => 'google_folder_id',
                'value' => '',
                'type' => 'text',
                'group' => 'google_drive',
                'label' => 'Google Folder ID',
                'description' => 'Default Google Drive folder ID'
            ],
            [
                'name' => 'google_drive_enabled',
                'value' => '0',
                'type' => 'checkbox',
                'group' => 'google_drive',
                'label' => 'Enable Google Drive',
                'description' => 'Enable Google Drive integration'
            ],
            [
                'name' => 'default_upload_storage',
                'value' => 'local',
                'type' => 'select',
                'group' => 'google_drive',
                'label' => 'Default Upload Storage',
                'description' => 'Choose where to store uploaded files by default'
            ],

            // System Settings
            [
                'name' => 'max_file_size',
                'value' => '10',
                'type' => 'text',
                'group' => 'system',
                'label' => 'Max File Size (MB)',
                'description' => 'Maximum file upload size in megabytes'
            ],
            [
                'name' => 'session_timeout',
                'value' => '120',
                'type' => 'text',
                'group' => 'system',
                'label' => 'Session Timeout (minutes)',
                'description' => 'User session timeout in minutes'
            ],
            [
                'name' => 'pagination_limit',
                'value' => '10',
                'type' => 'select',
                'group' => 'system',
                'label' => 'Pagination Limit',
                'description' => 'Default number of items per page'
            ],
            [
                'name' => 'maintenance_mode',
                'value' => '0',
                'type' => 'checkbox',
                'group' => 'system',
                'label' => 'Maintenance Mode',
                'description' => 'Enable maintenance mode'
            ],
            [
                'name' => 'debug_mode',
                'value' => '0',
                'type' => 'checkbox',
                'group' => 'system',
                'label' => 'Debug Mode',
                'description' => 'Enable debug mode'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['name' => $setting['name']],
                $setting
            );
        }
    }
}
