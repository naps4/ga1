<?php
/**
 * FILE INI UNTUK MEMBUAT SYMBOLIC LINK DI HOSTING cPANEL
 * -------------------------------------------------------
 * CARA PAKAI:
 * 1. Upload file ini ke folder public_html Anda
 * 2. Akses lewat browser: https://domain-anda.com/buat_storage_link.php
 * 3. Setelah muncul pesan SUKSES, SEGERA HAPUS file ini dari hosting!
 * -------------------------------------------------------
 */

// ====================================================
// SESUAIKAN 2 BARIS INI DENGAN PATH HOSTING ANDA:
// ====================================================
$laravelRoot   = dirname(__DIR__);                  // folder fpuga (otomatis, tidak perlu diubah)
$target        = $laravelRoot . '/storage/app/public'; // Sumber file: storage/app/public
$link          = __DIR__ . '/storage';              // Tujuan link: public_html/storage
// ====================================================

echo '<h2>🔗 Pembuat Symbolic Link - Laravel Storage</h2>';
echo '<pre>';
echo 'Laravel Root  : ' . $laravelRoot . "\n";
echo 'Target (sumber): ' . $target . "\n";
echo 'Link   (tujuan): ' . $link . "\n\n";

// Cek apakah folder sumber ada
if (!is_dir($target)) {
    die("❌ GAGAL: Folder sumber tidak ditemukan:\n" . $target . "\n\nPastikan Laravel root path sudah benar.");
}

// Hapus symlink/folder lama jika ada
if (file_exists($link) || is_link($link)) {
    if (is_link($link)) {
        unlink($link);
        echo "🗑️  Symlink lama dihapus.\n";
    } else {
        rename($link, $link . '_backup_' . time());
        echo "📦 Folder 'storage' lama dibackup.\n";
    }
}

// Buat symlink baru
if (symlink($target, $link)) {
    echo "\n✅ SUKSES! Symbolic link berhasil dibuat.\n";
    echo "   File yang diupload sekarang dapat diakses via: https://domain-anda.com/storage/...\n\n";
    echo "⚠️  PENTING: Segera HAPUS file buat_storage_link.php ini dari hosting Anda!\n";
} else {
    echo "\n❌ GAGAL membuat symlink.\n";
    echo "Kemungkinan penyebab:\n";
    echo "- PHP disable_functions mencegah symlink()\n";
    echo "- Gunakan cara alternatif melalui cPanel File Manager atau SSH:\n";
    echo "  ln -s " . $target . " " . $link . "\n";
}
echo '</pre>';
