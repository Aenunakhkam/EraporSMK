<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ptk;

$guru = Ptk::withTrashed()->find('927373e1-8e07-4c28-b2d2-ccff1a5fcc5d');
if ($guru) {
    $guru->deleted_at = null;
    $guru->jenis_ptk_id = 92; // Change to Guru Mapel
    $guru->save();
    echo "Success: Restored and updated Aenun Akhkam to Guru!\n";
} else {
    echo "Guru not found.\n";
}
