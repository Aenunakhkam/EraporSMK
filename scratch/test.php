<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api/referensi/ptk/simpan-manual', 'POST', [
    'nama' => 'Test Guru',
    'jenis_gtk' => 'guru',
    'sekolah_id' => \App\Models\Sekolah::first()->sekolah_id,
    'jenis_kelamin' => 'L',
]);
$response = $kernel->handle($request);
echo $response->getContent();
