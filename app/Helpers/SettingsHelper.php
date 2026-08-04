<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class SettingsHelper
{
    /**
     * Get a system setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $path = storage_path('app/system-settings.json');
        
        if (!File::exists($path)) {
            // Default fallback
            $defaults = [
                'app_name' => config('app.name', 'Antrian Project'),
                'app_logo' => '',
                'app_favicon' => '',
                'report_logo' => '',
                'admin_email' => 'admin@antrian.com',
                'per_page' => 15,
                'email_notifications' => true,
                'notification_window_days' => 3,
                'maintenance_mode' => false,
                'company_name' => 'PT. HERBATECH INNOPHARMA INDUSTRY',
                'company_address' => 'Jl. Contoh Perusahaan No. 123, Gedung Perkantoran Lt. 4, Jakarta Pusat',
                'company_phone' => '(021) 1234567',
                'company_email' => 'info@herbatech.com',
            ];
            
            return $defaults[$key] ?? $default;
        }

        $settings = json_decode(File::get($path), true);
        
        if (is_array($settings) && array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    /**
     * Get report/print logo path (falls back to app_logo).
     *
     * @return string|null
     */
    public static function getReportLogo()
    {
        $reportLogo = static::get('report_logo');
        if (!empty($reportLogo)) {
            return $reportLogo;
        }
        return static::get('app_logo');
    }
}
