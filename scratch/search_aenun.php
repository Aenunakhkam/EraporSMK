<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Ptk;

echo "--- Searching in USERS table ---\n";
$users = User::where('name', 'ILIKE', '%AENUN AKHKAM%')->get();
if ($users->isEmpty()) {
    echo "No users found with name containing AENUN AKHKAM\n";
} else {
    foreach ($users as $user) {
        echo "User ID: " . $user->id . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Guru ID: " . ($user->guru_id ?: 'NULL') . "\n";
        echo "Sekolah ID: " . ($user->sekolah_id ?: 'NULL') . "\n";
        echo "Roles: " . implode(', ', $user->roles->pluck('name')->toArray()) . "\n";
        echo "---------------------------------\n";
    }
}

echo "\n--- Searching in GURU table ---\n";
$gurus = Ptk::where('nama', 'ILIKE', '%AENUN AKHKAM%')->withTrashed()->get();
if ($gurus->isEmpty()) {
    echo "No gurus found with name containing AENUN AKHKAM\n";
} else {
    foreach ($gurus as $guru) {
        echo "Guru ID: " . $guru->guru_id . "\n";
        echo "Nama: " . $guru->nama . "\n";
        echo "Sekolah ID: " . $guru->sekolah_id . "\n";
        echo "Jenis PTK ID: " . $guru->jenis_ptk_id . "\n";
        echo "Deleted At: " . ($guru->deleted_at ?: 'NULL') . "\n";
        
        // Let's check ptk_keluar
        $keluar = $guru->ptk_keluar;
        echo "Ptk Keluar: " . ($keluar ? "Yes (Semester ID: " . $keluar->semester_id . ")" : "No") . "\n";
        echo "---------------------------------\n";
    }
}
