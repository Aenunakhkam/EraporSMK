<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\PesertaDidik;
use App\Models\JurusanSp;
use App\Models\RombonganBelajar;
use App\Models\Pekerjaan;
use App\Models\User;
use App\Models\Agama;
use App\Models\AnggotaRombel;
use App\Models\Role;
use App\Models\Sekolah;

class PdController extends Controller
{
    private function kondisi(){
        return function($query){
            if(request()->status == 'aktif' || request()->status == 'password'){
                $query->where(function($q) {
                    $q->whereHas('anggota_rombel', function($query){
                        $query->where('sekolah_id', request()->sekolah_id);
                        $query->where('semester_id', request()->semester_id);
                        $query->whereHas('rombongan_belajar', function($query){
                            $query->where('sekolah_id', request()->sekolah_id);
                            $query->where('semester_id', request()->semester_id);
                            $query->where('jenis_rombel', 1);
                            if(auth()->user()->hasRole('wali', request()->periode_aktif)){
                                $query->where('guru_id', auth()->user()->guru_id);
                            }
                        });
                    });
                    
                    if(auth()->user()->hasRole('admin', request()->periode_aktif)) {
                        $q->orWhere(function($query) {
                            $query->where('sekolah_id', request()->sekolah_id);
                            $query->whereDoesntHave('anggota_rombel', function($sq) {
                                $sq->where('semester_id', request()->semester_id);
                            });
                            $query->whereDoesntHave('pd_keluar');
                            $query->where('active', 1);
                        });
                    }
                });
            } else {
                $query->whereHas('pd_keluar', function($query){
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('semester_id', request()->semester_id);
                });
            }
        };
    }
    public function index(){
        $data = PesertaDidik::where($this->kondisi())
        ->with([
            'agama', 
            'anggota_rombel' => function($query){
                $query->withWhereHas('rombongan_belajar', function($query){
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('semester_id', request()->semester_id);
                    $query->where('jenis_rombel', 1);
                });
            },
            'user' => function($query){
                $query->select('user_id', 'email', 'peserta_didik_id', 'password', 'default_password', 'last_login_at');
            }
        ])
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->where($this->kondisi());
            $query->orWhere('nisn', 'ILIKE', '%' . request()->q . '%');
            $query->where($this->kondisi());
            $query->orWhere('tempat_lahir', 'ILIKE', '%' . request()->q . '%');
            $query->where($this->kondisi());
            $query->orWhereHas('agama', function($query){
                $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            });
            $query->where($this->kondisi());
        })->when(request()->tingkat, function($query){
            $query->whereHas('anggota_rombel', function($query){
                $query->wherehas('rombongan_belajar', function($query){
                    $query->where('semester_id', request()->semester_id);
                    $query->where('tingkat', request()->tingkat);
                });
            });
            $query->where($this->kondisi());
        })->when(request()->jurusan_sp_id, function($query){
            $query->whereHas('anggota_rombel', function($query){
                $query->wherehas('rombongan_belajar', function($query){
                    $query->where('semester_id', request()->semester_id);
                    $query->where('jurusan_sp_id', request()->jurusan_sp_id);
                });
            });
            $query->where($this->kondisi());
        })->when(request()->rombongan_belajar_id, function($query){
            $query->whereHas('anggota_rombel', function($query){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            });
            $query->where($this->kondisi());
        })->paginate(request()->per_page);
        $jurusan_sp = [];
        $rombel = [];
        if(request()->tingkat){
            $jurusan_sp = JurusanSp::select('jurusan_sp_id', 'nama_jurusan_sp')->whereHas('rombongan_belajar', function($query){
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            })->orderBy('nama_jurusan_sp')->get();
        }
        if(request()->jurusan_sp_id){
            $rombel = RombonganBelajar::select('rombongan_belajar_id', 'nama', 'jurusan_sp_id')->where(function($query){
                $query->where('tingkat', request()->tingkat);
                $query->where('jurusan_sp_id', request()->jurusan_sp_id);
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            })->orderBy('tingkat')->orderBy('nama')->get();
        }
        return response()->json(['status' => 'success', 'data' => $data, 'jurusan_sp' => $jurusan_sp, 'rombel' => $rombel]);
    }
    public function show($id){
        $pd = PesertaDidik::with(['agama', 'pekerjaan_ayah', 'pekerjaan_ibu', 'pekerjaan_wali'])->find($id);
        $pd->diterima_raw = $pd->getRawOriginal('diterima');
        $data = [
            'pd' => $pd,
            'pekerjaan' => Pekerjaan::orderBy('pekerjaan_id')->get(),
            'agama' => Agama::orderBy('agama_id')->get(),
        ];
        return response()->json($data);
    }
    public function update(){
        request()->validate(
            [
                'nama' => 'required|string|max:255',
                'no_induk' => [
                    'required',
                    Rule::unique('peserta_didik', 'no_induk')->ignore(request()->peserta_didik_id, 'peserta_didik_id')->whereNull('deleted_at')
                ],
                'nisn' => [
                    'required',
                    'numeric',
                    'digits:10',
                    Rule::unique('peserta_didik', 'nisn')->ignore(request()->peserta_didik_id, 'peserta_didik_id')->whereNull('deleted_at')
                ],
                'nik' => [
                    'nullable',
                    'numeric',
                    'digits:16',
                    Rule::unique('peserta_didik', 'nik')->ignore(request()->peserta_didik_id, 'peserta_didik_id')->whereNull('deleted_at')
                ],
                'tanggal_lahir' => 'nullable|date_format:Y-m-d',
                'email' => [
                    'nullable', 
                    Rule::unique('peserta_didik')->ignore(request()->peserta_didik_id, 'peserta_didik_id'),
                    Rule::unique('users')->ignore(request()->peserta_didik_id, 'peserta_didik_id')
                ],
                'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            ],
            [
                'nama.required' => 'Nama lengkap wajib diisi.',
                'no_induk.required' => 'Nomor induk (NIS) wajib diisi.',
                'no_induk.unique' => 'Nomor induk (NIS) telah terdaftar.',
                'nisn.required' => 'NISN wajib diisi.',
                'nisn.numeric' => 'NISN harus berupa angka.',
                'nisn.digits' => 'NISN harus berisi 10 digit.',
                'nisn.unique' => 'NISN telah terdaftar.',
                'nik.numeric' => 'NIK harus berupa angka.',
                'nik.digits' => 'NIK harus berisi 16 digit.',
                'nik.unique' => 'NIK telah terdaftar.',
                'tanggal_lahir.date_format' => 'Tanggal lahir harus berformat YYYY-MM-DD.',
                'email.unique' => 'Email telah terdaftar',
                'photo.image' => 'Foto harus berupa berkas gambar',
                'photo.mimes' => 'Foto harus berekstensi (jpg, jpeg, png)',
                'photo.max' => 'Foto maksimal 1 MB.',
            ]
        );
        $pd = PesertaDidik::find(request()->peserta_didik_id);
        $pd->nama = request()->nama;
        $pd->no_induk = request()->no_induk;
        $pd->nisn = request()->nisn;
        $pd->nik = request()->nik ?: null;
        $pd->jenis_kelamin = request()->jenis_kelamin ?: 'L';
        $pd->tempat_lahir = request()->tempat_lahir ?: '-';
        $pd->tanggal_lahir = request()->tanggal_lahir ?: '2000-01-01';
        $pd->agama_id = request()->agama_id ?: 99;
        $pd->alamat = request()->alamat ?? '-';
        $pd->rt = request()->rt ?? 0;
        $pd->rw = request()->rw ?? 0;
        $pd->desa_kelurahan = request()->desa_kelurahan ?? '-';
        $pd->kecamatan = request()->kecamatan ?? '-';
        $pd->kode_pos = request()->kode_pos ?? 0;
        $pd->no_telp = request()->no_telp ?? 0;
        $pd->no_hp = request()->no_hp ?? 0;
        $pd->sekolah_asal = request()->sekolah_asal ?? '-';
        $pd->diterima = request()->diterima ?? now()->format('Y-m-d');
        $pd->nama_ayah = request()->nama_ayah ?? '-';
        $pd->nama_ibu = request()->nama_ibu ?? '-';
        $pd->kerja_ayah = request()->kerja_ayah ?? 1;
        $pd->kerja_ibu = request()->kerja_ibu ?? 1;
        
        $pd->status = request()->status;
        $pd->anak_ke = request()->anak_ke;
        $pd->diterima_kelas = request()->diterima_kelas;
        $pd->email = request()->email ?: null;
        $pd->nama_wali = request()->nama_wali;
        $pd->alamat_wali = request()->alamat_wali;
        $pd->telp_wali = request()->telp_wali;
        $pd->kerja_wali = request()->kerja_wali;
        $photo_user = NULL;
        if(request()->photo){
            $photo = request()->photo->store('profile-photos', 'public');
            $pd->photo = $photo_user = 'profile-photos/'.basename($photo);
        }
        if($pd->save()){
            $user = User::where('peserta_didik_id', request()->peserta_didik_id)->first();
            if($user){
                $user->email = request()->email;
                if($photo_user){
                    $user->profile_photo_path = $photo_user;
                } else {
                    $user->profile_photo_path = str_replace('/storage/', '', $pd->getOriginal('photo'));
                }
                $user->save();
            }
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data '.$pd->nama.' berhasil diperbaharui',
                'getOriginal' => $pd->getOriginal('photo'),
                'photo' => $pd->photo,
                'asli' => $pd->getOriginal(),
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data '.$pd->nama.' Gagal diperbaharui. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }

    public function referensi_add()
    {
        $agama = Agama::orderBy('agama_id')->get();
        $pekerjaan = Pekerjaan::orderBy('pekerjaan_id')->get();
        $rombel = RombonganBelajar::where('sekolah_id', request()->sekolah_id)
            ->where('semester_id', request()->semester_id)
            ->where('jenis_rombel', 1)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
        return response()->json([
            'agama' => $agama,
            'pekerjaan' => $pekerjaan,
            'rombel' => $rombel,
        ]);
    }

    public function simpan()
    {
        request()->validate(
            [
                'nama' => 'required|string|max:255',
                'no_induk' => [
                    'required',
                    Rule::unique('peserta_didik', 'no_induk')->whereNull('deleted_at')
                ],
                'nisn' => [
                    'required',
                    'numeric',
                    'digits:10',
                    Rule::unique('peserta_didik', 'nisn')->whereNull('deleted_at')
                ],
                'nik' => [
                    'nullable',
                    'numeric',
                    'digits:16',
                    Rule::unique('peserta_didik', 'nik')->whereNull('deleted_at')
                ],
                'jenis_kelamin' => 'nullable|in:L,P',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date_format:Y-m-d',
                'agama_id' => 'nullable|numeric',
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('peserta_didik', 'email')->whereNull('deleted_at'),
                    Rule::unique('users', 'email')
                ],
                'rombongan_belajar_id' => 'nullable',
            ],
            [
                'nama.required' => 'Nama lengkap wajib diisi.',
                'no_induk.required' => 'Nomor induk (NIS) wajib diisi.',
                'no_induk.unique' => 'Nomor induk (NIS) telah terdaftar.',
                'nisn.required' => 'NISN wajib diisi.',
                'nisn.numeric' => 'NISN harus berupa angka.',
                'nisn.digits' => 'NISN harus berisi 10 digit.',
                'nisn.unique' => 'NISN telah terdaftar.',
                'nik.numeric' => 'NIK harus berupa angka.',
                'nik.digits' => 'NIK harus berisi 16 digit.',
                'nik.unique' => 'NIK telah terdaftar.',
                'tanggal_lahir.date_format' => 'Tanggal lahir harus berformat YYYY-MM-DD.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email telah terdaftar.',
            ]
        );

        \DB::beginTransaction();
        try {
            $peserta_didik_id = \Illuminate\Support\Str::uuid();
            $sekolah = Sekolah::find(request()->sekolah_id);
            $kode_wilayah = $sekolah ? $sekolah->kode_wilayah : '000000';
            
            // 1. Simpan PesertaDidik
            $pd = PesertaDidik::create([
                'peserta_didik_id' => $peserta_didik_id,
                'peserta_didik_id_dapodik' => $peserta_didik_id,
                'sekolah_id' => request()->sekolah_id,
                'nama' => request()->nama,
                'no_induk' => request()->no_induk,
                'nisn' => request()->nisn,
                'nik' => request()->nik ?: null,
                'jenis_kelamin' => request()->jenis_kelamin ?: 'L',
                'tempat_lahir' => request()->tempat_lahir ?: '-',
                'tanggal_lahir' => request()->tanggal_lahir ?: '2000-01-01',
                'agama_id' => request()->agama_id ?: 99,
                'status' => request()->status ?? 'Anak Kandung',
                'anak_ke' => request()->anak_ke ?? 1,
                'alamat' => request()->alamat ?? '-',
                'rt' => request()->rt ?? 0,
                'rw' => request()->rw ?? 0,
                'desa_kelurahan' => request()->desa_kelurahan ?? '-',
                'kecamatan' => request()->kecamatan ?? '-',
                'kode_pos' => request()->kode_pos ?? 0,
                'no_telp' => request()->no_telp ?? 0,
                'no_hp' => request()->no_hp ?? 0,
                'sekolah_asal' => request()->sekolah_asal ?? '-',
                'diterima' => request()->diterima ?? now()->format('Y-m-d'),
                'diterima_kelas' => request()->diterima_kelas ?? '-',
                'kode_wilayah' => $kode_wilayah,
                'email' => request()->email ?: null,
                'nama_ayah' => request()->nama_ayah ?? '-',
                'nama_ibu' => request()->nama_ibu ?? '-',
                'kerja_ayah' => request()->kerja_ayah ?? 1,
                'kerja_ibu' => request()->kerja_ibu ?? 1,
                'nama_wali' => request()->nama_wali ?? '-',
                'alamat_wali' => request()->alamat_wali ?? '-',
                'telp_wali' => request()->telp_wali ?? 0,
                'kerja_wali' => request()->kerja_wali ?? 1,
                'active' => 1,
                'last_sync' => now(),
            ]);

            // 2. Simpan AnggotaRombel (jika kelas dipilih)
            if (request()->rombongan_belajar_id) {
                $anggota_rombel_id = \Illuminate\Support\Str::uuid();
                AnggotaRombel::create([
                    'anggota_rombel_id' => $anggota_rombel_id,
                    'sekolah_id' => request()->sekolah_id,
                    'semester_id' => request()->semester_id,
                    'rombongan_belajar_id' => request()->rombongan_belajar_id,
                    'peserta_didik_id' => $peserta_didik_id,
                    'anggota_rombel_id_dapodik' => $anggota_rombel_id,
                    'last_sync' => now(),
                ]);
            }

            // 3. Simpan User (hanya jika ada email)
            if (request()->email) {
                $new_password = strtolower(\Illuminate\Support\Str::random(8));
                $user = User::create([
                    'name' => request()->nama,
                    'email' => request()->email,
                    'nisn' => request()->nisn,
                    'password' => bcrypt($new_password),
                    'last_sync' => now(),
                    'sekolah_id' => request()->sekolah_id,
                    'password_dapo' => md5($new_password),
                    'peserta_didik_id' => $peserta_didik_id,
                    'default_password' => $new_password,
                ]);

                $role = Role::where('name', 'siswa')->first();
                if ($role) {
                    $user->addRole($role, request()->periode_aktif);
                }
            }

            \DB::commit();

            return response()->json([
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Peserta Didik '.$pd->nama.' berhasil ditambahkan secara manual',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ]);
        }
    }

    public function hapus(Request $request)
    {
        $id = $request->peserta_didik_id;
        \DB::beginTransaction();
        try {
            AnggotaRombel::where('peserta_didik_id', $id)->delete();
            User::where('peserta_didik_id', $id)->delete();
            PesertaDidik::where('peserta_didik_id', $id)->delete();
            \DB::commit();
            return response()->json([
                'title' => 'Berhasil',
                'text' => 'Data Peserta Didik berhasil dihapus',
                'color' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'title' => 'Gagal',
                'text' => 'Gagal menghapus data: ' . $e->getMessage(),
                'color' => 'error'
            ], 500);
        }
    }
}
