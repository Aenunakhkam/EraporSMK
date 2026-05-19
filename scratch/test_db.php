<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $ptk = \App\Models\Ptk::create([
        'guru_id' => \Illuminate\Support\Str::uuid(),
        'sekolah_id' => \App\Models\Sekolah::first()->sekolah_id,
        'status_kepegawaian_id' => 0,
        'kode_wilayah' => '000000',
        'nama' => 'Testing Guru',
        'jenis_kelamin' => 'L',
        'jenis_ptk_id' => 92,
        'last_sync' => now()
    ]);
    echo "Success: " . $ptk->guru_id;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
