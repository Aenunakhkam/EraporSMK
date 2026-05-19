<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$team = App\Models\Team::where('name', '2025/2026 Ganjil')->first();
$where = function($query) {
    $query->whereHasRole(['guru', 'siswa', 'tu'], '2025/2026 Ganjil');
    $query->where(function($q) {
        $q->whereNull('guru_id')
          ->orWhereIn('guru_id', function($subQuery) {
              $subQuery->select('guru_id')
                       ->from('guru')
                       ->whereNull('deleted_at');
          });
    });
    $query->where(function($q) {
        $q->whereNull('peserta_didik_id')
          ->orWhereIn('peserta_didik_id', function($subQuery) {
              $subQuery->select('peserta_didik_id')
                       ->from('peserta_didik')
                       ->whereNull('deleted_at');
          });
    });
};

$user = App\Models\User::where($where)->where('email', 'akhkamaenun45@gmail.com')->first();
if ($user) {
    echo "USER FOUND IN LIST:\n";
    print_r($user->toArray());
} else {
    echo "USER NOT FOUND IN LIST!\n";
    $rawUser = App\Models\User::where('email', 'akhkamaenun45@gmail.com')->first();
    if ($rawUser) {
        echo "Raw user exists: \n";
        print_r($rawUser->toArray());
        
        // Let's check why it's excluded:
        // 1. role check:
        echo "Has Role: " . ($rawUser->hasRole(['guru', 'siswa', 'tu'], '2025/2026 Ganjil') ? 'YES' : 'NO') . "\n";
        // 2. guru check:
        if ($rawUser->guru_id) {
            $guru = App\Models\Ptk::find($rawUser->guru_id);
            echo "Guru exists in Ptk model (active): " . ($guru ? 'YES' : 'NO') . "\n";
            $rawGuru = App\Models\Ptk::withTrashed()->find($rawUser->guru_id);
            echo "Guru exists in db (any status): " . ($rawGuru ? 'YES' : 'NO') . "\n";
            if ($rawGuru) {
                echo "Guru deleted_at: " . ($rawGuru->deleted_at ?: 'NULL') . "\n";
            }
        }
    } else {
        echo "Raw user does not exist in DB at all!\n";
    }
}
