<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\DB::select("SELECT column_name, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'guru' AND is_nullable = 'NO' AND column_default IS NULL");
foreach($columns as $col) {
    echo $col->column_name . "\n";
}
