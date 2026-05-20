<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileUploadHelper
{
    /**
     * Simpan file ke disk 'public' Laravel (storage/app/public/...).
     * File akan dapat diakses via URL: asset('storage/subfolder/namafile')
     *
     * Ini adalah satu-satunya metode upload yang digunakan di seluruh aplikasi
     * agar konsisten dan bekerja baik di Laragon (lokal) maupun cPanel (hosting).
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $subfolder  Subfolder di dalam storage/app/public (contoh: 'work_orders')
     * @param string $prefix     Prefix nama file (contoh: 'req', 'chk', 'prog', 'rep')
     * @return string            Path relatif yang disimpan ke database (contoh: 'work_orders/xxxx_req_file.jpg')
     */
    public static function upload($file, string $subfolder = 'uploads', string $prefix = 'file'): string
    {
        $originalName = $file->getClientOriginalName();
        // Bersihkan nama file dari karakter spesial
        $safeName = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $originalName);
        $filename  = time() . '_' . $prefix . '_' . $safeName;

        // Simpan ke storage/app/public/{subfolder}/{filename}
        $file->storeAs($subfolder, $filename, 'public');

        // Path yang disimpan ke database (relatif dari storage/app/public)
        return $subfolder . '/' . $filename;
    }

    /**
     * Hapus file dari storage jika path tidak null.
     *
     * @param string|null $path  Path yang tersimpan di database
     */
    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Generate URL publik dari path yang tersimpan di database.
     * Gunakan ini di controller jika perlu, atau langsung pakai asset('storage/'.$path) di Blade.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return Storage::disk('public')->url($path);
    }
}
