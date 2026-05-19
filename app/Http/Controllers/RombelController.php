<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RombonganBelajar;
use App\Models\Pembelajaran;
use App\Models\PesertaDidik;
use App\Models\AnggotaRombel;
use App\Models\Kelompok;
use App\Models\Ptk;
use App\Models\AnggotaAktPd;

class RombelController extends Controller
{
    public function index(){
        $data = RombonganBelajar::where($this->kondisi())->with([
            'wali_kelas' => function($query){
                $query->select('guru_id', 'nama', 'email', 'gelar_depan', 'gelar_belakang', 'photo');
            },
            'jurusan_sp' => function($query){
                $query->select('jurusan_sp_id', 'nama_jurusan_sp');
            },
            'kurikulum' => function($query){
                $query->select('kurikulum_id', 'nama_kurikulum');
            },
        ])
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->orderBy('nama')
        ->when(request()->q, function($query){
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->where($this->kondisi());
            $query->orWhereHas('wali_kelas', function($query){
                $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            });
            $query->where($this->kondisi());
            $query->orWhereHas('jurusan_sp', function($query){
                $query->where('nama_jurusan_sp', 'ILIKE', '%' . request()->q . '%');
            });
            $query->where($this->kondisi());
            $query->orWhereHas('kurikulum', function($query){
                $query->where('nama_kurikulum', 'ILIKE', '%' . request()->q . '%');
            });
            $query->where($this->kondisi());
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    private function kondisi(){
        return function($query){
            $query->where('jenis_rombel', request()->jenis_rombel);
            $query->where('semester_id', request()->semester_id);
            $query->where('sekolah_id', request()->sekolah_id);
        };
    }
    public function referensi(){
        $jurusan_sp = \App\Models\JurusanSp::select('jurusan_sp_id', 'nama_jurusan_sp')
            ->whereHas('rombongan_belajar', function($query){
                $query->where('sekolah_id', request()->sekolah_id);
            })
            ->orWhereDoesntHave('rombongan_belajar')
            ->orderBy('nama_jurusan_sp')
            ->get();
        $kurikulum = \App\Models\Kurikulum::select('kurikulum_id', 'nama_kurikulum')
            ->orderBy('nama_kurikulum')
            ->get();
        $guru = \App\Models\Ptk::select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang')
            ->where('sekolah_id', request()->sekolah_id)
            ->whereDoesntHave('ptk_keluar', function($query){
                $query->where('semester_id', request()->semester_id);
            })
            ->orderBy('nama')
            ->get();
        return response()->json([
            'jurusan_sp' => $jurusan_sp,
            'kurikulum' => $kurikulum,
            'guru' => $guru,
        ]);
    }
    public function simpan(){
        request()->validate(
            [
                'nama' => 'required|string|max:255',
                'tingkat' => 'required|in:X,XI,XII',
                'guru_id' => 'required',
                'kurikulum_id' => 'required',
            ],
            [
                'nama.required' => 'Nama kelas wajib diisi.',
                'tingkat.required' => 'Tingkat kelas wajib diisi.',
                'tingkat.in' => 'Tingkat kelas harus X, XI, atau XII.',
                'guru_id.required' => 'Wali Kelas wajib dipilih.',
                'kurikulum_id.required' => 'Kurikulum wajib dipilih.',
            ]
        );

        $tingkat = request()->tingkat;
        $tingkat_int = ($tingkat === 'X') ? 10 : (($tingkat === 'XI') ? 11 : 12);

        $rombongan_belajar_id = \Illuminate\Support\Str::uuid();
        RombonganBelajar::create([
            'rombongan_belajar_id' => $rombongan_belajar_id,
            'rombel_id_dapodik' => $rombongan_belajar_id,
            'sekolah_id' => request()->sekolah_id,
            'semester_id' => request()->semester_id,
            'nama' => request()->nama,
            'tingkat' => $tingkat_int,
            'jenis_rombel' => request()->jenis_rombel ?: 1,
            'guru_id' => request()->guru_id,
            'jurusan_sp_id' => request()->jurusan_sp_id ?: null,
            'kurikulum_id' => request()->kurikulum_id,
            'last_sync' => now(),
        ]);

        return response()->json([
            'color' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Kelas '.(request()->nama).' berhasil ditambahkan.',
        ]);
    }
    public function update(){
        request()->validate(
            [
                'rombongan_belajar_id' => 'required',
                'nama' => 'required|string|max:255',
                'tingkat' => 'required|in:X,XI,XII',
                'guru_id' => 'required',
                'kurikulum_id' => 'required',
            ],
            [
                'nama.required' => 'Nama kelas wajib diisi.',
                'tingkat.required' => 'Tingkat kelas wajib diisi.',
                'tingkat.in' => 'Tingkat kelas harus X, XI, atau XII.',
                'guru_id.required' => 'Wali Kelas wajib dipilih.',
                'kurikulum_id.required' => 'Kurikulum wajib dipilih.',
            ]
        );

        $tingkat = request()->tingkat;
        $tingkat_int = ($tingkat === 'X') ? 10 : (($tingkat === 'XI') ? 11 : 12);

        $rombel = RombonganBelajar::find(request()->rombongan_belajar_id);
        if ($rombel) {
            $rombel->update([
                'nama' => request()->nama,
                'tingkat' => $tingkat_int,
                'guru_id' => request()->guru_id,
                'jurusan_sp_id' => request()->jurusan_sp_id ?: null,
                'kurikulum_id' => request()->kurikulum_id,
                'last_sync' => now(),
            ]);

            return response()->json([
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Kelas '.(request()->nama).' berhasil diperbaharui.',
            ]);
        }

        return response()->json([
            'color' => 'error',
            'title' => 'Gagal!',
            'text' => 'Kelas tidak ditemukan.',
        ], 404);
    }
    public function hapus(){
        $find = RombonganBelajar::find(request()->rombongan_belajar_id);
        if($find){
            if($find->delete()){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data Kelas berhasil dihapus',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Data Kelas gagal dihapus',
                ];
            }
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data Kelas tidak ditemukan',
            ];
        }
        return response()->json($data);
    }
    public function pembelajaran(){
        $rombel = RombonganBelajar::find(request()->rombongan_belajar_id);
        $merdeka = Str::of($rombel->kurikulum->nama_kurikulum)->contains('Merdeka');
        if($merdeka){
            $kurikulum = 2022;
        } else {
            $kurikulum = 2017;
        }
        $data = Pembelajaran::with(['guru', 'pengajar'])->where(function($query){
            $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
        })->orderBy('kelompok_id')->orderBy('no_urut')->orderBy('mata_pelajaran_id')->get();
        return response()->json([
            'data' => $data,
            'guru' => Ptk::where(function($query){
                $query->where('sekolah_id', request()->sekolah_id);
                $query->whereDoesntHave('ptk_keluar', function($query){
                    $query->where('semester_id', request()->semester_id);
                });
                $query->select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang', 'photo');
            })->orderBy('nama')->get(),
            'kelompok' => Kelompok::where(function($query) use ($kurikulum){
                $query->where('kurikulum', $kurikulum);
                //if($kurikulum != 2022){
                    $query->orWhere('kurikulum', 0);
                //}
            })->orderBy('kelompok_id')->get(),
            'rombel' => $rombel,
        ]);
    }
    public function simpan_pembelajaran(){
        /*foreach(request()->all() as $item){
            Pembelajaran::where('pembelajaran_id', $item['pembelajaran_id'])->update([
                'nama_mata_pelajaran' => $item['nama_mata_pelajaran'],
                'guru_pengajar_id' => $item['guru_pengajar_id'],
                'kelompok_id' => $item['kelompok_id'],
                'no_urut' => $item['no_urut']
            ]);
        }*/
        foreach(request()->pembelajaran_id as $pembelajaran_id){
            Pembelajaran::where('pembelajaran_id', $pembelajaran_id)->update([
                'nama_mata_pelajaran' => request()->nama[$pembelajaran_id],
                'guru_pengajar_id' => request()->guru_pengajar_id[$pembelajaran_id],
                'kelompok_id' => request()->kelompok_id[$pembelajaran_id],
                'no_urut' => request()->no_urut[$pembelajaran_id],
            ]);
        }
        $data = [
            'request' => request()->all(),
        ];
        return response()->json($data);
    }
    public function hapus_pembelajaran(){
        Pembelajaran::where('pembelajaran_id', request()->pembelajaran_id)->update([
            'guru_pengajar_id' => NULL,
            'kelompok_id' => NULL,
            'no_urut' => NULL
        ]);
    }
    public function anggota_rombel(){
        return response()->json([
            'data' => PesertaDidik::with(['agama'])->withWhereHas('anggota_rombel', function($query){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            })->orderByRaw('LOWER(nama) ASC')->get(),
            'rombel' => RombonganBelajar::find(request()->rombongan_belajar_id),
        ]);
    }
    public function hapus_anggota_rombel(){
        $data = [
            'color' => 'error',
            'title' => 'Gagal!',
            'text' => 'Permintaan tidak ditemukan!',
        ];
        if(request()->data == 'rombel'){
            $find = AnggotaRombel::find(request()->anggota_rombel_id);
            if($find){
                if($find->delete()){
                    $data = [
                        'color' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Anggota Rombel berhasil dikeluarkan',
                    ];
                } else {
                    $data = [
                        'color' => 'error',
                        'title' => 'Gagal!',
                        'text' => 'Anggota Rombel gagal dikeluarkan. Silahkan coba beberapa saat lagi!',
                    ];
                }
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Anggota Rombel tidak ditemukan. Silahkan muat ulang aplikasi!',
                ];
            }
        }
        if(request()->data == 'prakerin'){
            $find = AnggotaAktPd::find(request()->anggota_rombel_id);
            if($find){
                if($find->delete()){
                    $data = [
                        'color' => 'success',
                        'title' => 'Berhasil!',
                        'text' => 'Data Peserta Prakerin berhasil di hapus',
                    ];
                } else {
                    $data = [
                        'color' => 'error',
                        'title' => 'Gagal!',
                        'text' => 'Data Peserta Prakerin tidak ditemukan. Silahkan coba beberapa saat lagi!',
                    ];
                }
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Data Peserta Prakerin di hapus. Silahkan coba beberapa saat lagi!',
                ];
            }
        }
        return response()->json($data);
    }
    public function list_siswa(){
        $sekolah_id = request()->sekolah_id;
        $semester_id = request()->semester_id;
        
        $data = PesertaDidik::where('sekolah_id', $sekolah_id)
            ->where('active', 1)
            ->whereDoesntHave('pd_keluar')
            ->with(['anggota_rombel' => function($q) use ($semester_id) {
                $q->where('semester_id', $semester_id)->with('rombongan_belajar');
            }])
            ->orderBy('nama')
            ->get();
            
        return response()->json($data);
    }
    public function tambah_anggota(){
        request()->validate([
            'rombongan_belajar_id' => 'required',
            'peserta_didik_id' => 'required',
        ]);
        
        $rombongan_belajar_id = request()->rombongan_belajar_id;
        $peserta_didik_id = request()->peserta_didik_id;
        $sekolah_id = request()->sekolah_id;
        $semester_id = request()->semester_id;
        
        $exists = AnggotaRombel::where('rombongan_belajar_id', $rombongan_belajar_id)
            ->where('peserta_didik_id', $peserta_didik_id)
            ->first();
            
        if ($exists) {
            return response()->json([
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Siswa sudah terdaftar di kelas ini.',
            ]);
        }
        
        $rombel = RombonganBelajar::find($rombongan_belajar_id);
        if ($rombel && $rombel->jenis_rombel == 1) {
            AnggotaRombel::where('semester_id', $semester_id)
                ->where('peserta_didik_id', $peserta_didik_id)
                ->whereHas('rombongan_belajar', function($q) {
                    $q->where('jenis_rombel', 1);
                })
                ->delete();
        }
            
        $anggota_rombel_id = \Illuminate\Support\Str::uuid();
        AnggotaRombel::create([
            'anggota_rombel_id' => $anggota_rombel_id,
            'sekolah_id' => $sekolah_id,
            'semester_id' => $semester_id,
            'rombongan_belajar_id' => $rombongan_belajar_id,
            'peserta_didik_id' => $peserta_didik_id,
            'anggota_rombel_id_dapodik' => $anggota_rombel_id,
            'last_sync' => now(),
        ]);
        
        return response()->json([
            'color' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Siswa berhasil dimasukkan ke kelas.',
        ]);
    }
    public function list_mapel(){
        $data = \App\Models\MataPelajaran::orderBy('nama')->get();
        return response()->json($data);
    }
    public function tambah_pembelajaran(){
        request()->validate([
            'rombongan_belajar_id' => 'required',
            'mata_pelajaran_id' => 'required',
            'guru_pengajar_id' => 'required',
            'kelompok_id' => 'required',
        ]);
        
        $pembelajaran_id = \Illuminate\Support\Str::uuid();
        $mapel = \App\Models\MataPelajaran::find(request()->mata_pelajaran_id);
        
        Pembelajaran::create([
            'pembelajaran_id' => $pembelajaran_id,
            'pembelajaran_id_dapodik' => $pembelajaran_id,
            'sekolah_id' => request()->sekolah_id,
            'semester_id' => request()->semester_id,
            'rombongan_belajar_id' => request()->rombongan_belajar_id,
            'guru_id' => request()->guru_pengajar_id,
            'guru_pengajar_id' => request()->guru_pengajar_id,
            'mata_pelajaran_id' => request()->mata_pelajaran_id,
            'nama_mata_pelajaran' => $mapel ? $mapel->nama : 'Mata Pelajaran Baru',
            'kelompok_id' => request()->kelompok_id,
            'no_urut' => request()->no_urut ?: 1,
            'is_dapodik' => 0,
            'last_sync' => now(),
        ]);
        
        return response()->json([
            'color' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Mata pelajaran berhasil ditambahkan ke kelas.',
        ]);
    }
}
