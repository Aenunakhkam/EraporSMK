<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gurus = App\Models\Ptk::all();
echo "TOTAL GURU IN DB: " . $gurus->count() . "\n\n";

$no_user = [];
$has_user_no_role = [];
$active_semester = App\Models\Semester::where('periode_aktif', 1)->first();
$active_semester_name = $active_semester ? $active_semester->nama : null;

foreach ($gurus as $guru) {
    $user = App\Models\User::where('guru_id', $guru->guru_id)->first();
    if (!$user) {
        $no_user[] = [
            'guru_id' => $guru->guru_id,
            'nama' => $guru->nama_lengkap,
            'email' => $guru->email,
            'deleted_at' => $guru->deleted_at,
        ];
    } else {
        // has user, check role in active semester
        $hasRole = $user->hasRole(['guru', 'siswa', 'tu', 'user'], $active_semester_name);
        if (!$hasRole) {
            $has_user_no_role[] = [
                'user_id' => $user->user_id,
                'nama' => $user->name,
                'email' => $user->email,
                'roles_in_db' => DB::table('role_user')->where('user_id', $user->user_id)->get()->toArray(),
            ];
        }
    }
}

echo "GURUS WITH NO USER RECORD AT ALL (" . count($no_user) . "):\n";
print_r($no_user);

echo "\n====================================\n";
echo "USERS THAT EXIST BUT HAVE NO ROLE IN ACTIVE SEMESTER '$active_semester_name' (" . count($has_user_no_role) . "):\n";
print_r($has_user_no_role);
