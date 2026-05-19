<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$classes = App\Models\RombonganBelajar::orderBy('created_at', 'desc')->take(10)->get(['rombongan_belajar_id', 'nama', 'kurikulum_id']);
foreach($classes as $c) {
    echo "Nama: {$c->nama} | Kurikulum ID: {$c->kurikulum_id}\n";
}
