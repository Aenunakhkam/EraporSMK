<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/js/main.js'])
</head>

<body>
    <div id="app">
        <div id="loading-bg">
            <div class="loading-logo">
                <img src="{{ asset('images/logo/logo-small.png') }}" alt="Logo" />
                </svg>
            </div>
            <div class=" loading">
                <div class="effect-1 effects"></div>
                <div class="effect-2 effects"></div>
                <div class="effect-3 effects"></div>
            </div>
        </div>
    </div>

    <script>
        const loaderColor = localStorage.getItem('vuexy-initial-loader-bg') || '#FFFFFF'
        const primaryColor = localStorage.getItem('vuexy-initial-loader-color') || '#7367F0'

        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

        if (primaryColor)
            document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
        var app_name = '{{ config('app.name') }}'
        var app_url = '{{ config('app.url') }}'
        var app_version = '{{ get_setting('app_version') }}';
        var app_registration = '{{ config('app.registration') }}';

        // Script Injection for Sinkronisasi Configuration
        (function() {
            function injectConfig() {
                const existingCard = document.getElementById('manual-config-card');
                
                // Jika tidak di halaman sinkronisasi, hapus form jika ada
                if (!window.location.pathname.includes('/sinkronisasi/dapodik')) {
                    if (existingCard) existingCard.remove();
                    return;
                }

                // Jika sudah ada, jangan tambah lagi
                if (existingCard) return;

                const container = document.querySelector('.v-container') || document.querySelector('.layout-page-content');
                if (!container) return;

                const userData = JSON.parse(localStorage.getItem('userData') || '{}');
                const semester = JSON.parse(localStorage.getItem('semester') || '{}');
                const token = (localStorage.getItem('accessToken') || '').replace(/"/g, '');

                const card = document.createElement('div');
                card.id = 'manual-config-card';
                card.innerHTML = `
                    <div class="v-card v-theme--light v-card--density-default v-card--variant-elevated mb-6" style="padding: 20px; border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));">
                        <div class="v-card-item" style="padding: 0 0 15px 0;">
                            <div class="v-card-title" style="font-size: 1.25rem; font-weight: 600;">Konfigurasi Web Service Dapodik</div>
                        </div>
                        <div class="v-card-text" style="padding: 0;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div>
                                    <label style="font-size: 0.8rem; color: #666;">Nama Aplikasi</label>
                                    <input type="text" id="cfg_nama" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-top: 5px;" value="eRaporSMK">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; color: #666;">IP e-Rapor</label>
                                    <input type="text" id="cfg_ip_erapor" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-top: 5px;" value="localhost">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; color: #666;">IP Dapodik</label>
                                    <input type="text" id="cfg_ip_dapodik" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-top: 5px;" value="localhost:5774">
                                </div>
                                <div>
                                    <label style="font-size: 0.8rem; color: #666;">Token Web Service</label>
                                    <input type="text" id="cfg_token" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-top: 5px;" placeholder="Masukkan Token">
                                </div>
                            </div>
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button id="btn-save-cfg" style="background-color: #7367F0; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Simpan & Test Koneksi</button>
                                <button id="btn-tarik-profil" style="background-color: #00CFE8; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Tarik Profil Sekolah</button>
                            </div>
                        </div>
                    </div>
                `;
                container.prepend(card);

                // Fetch current settings to populate form
                fetch('/api/setting?sekolah_id=' + userData.sekolah_id, {
                    headers: { 'Authorization': 'Bearer ' + token }
                })
                .then(res => res.json())
                .then(res => {
                    const settings = res.setting || {};
                    if (settings.nama_aplikasi_dapodik) document.getElementById('cfg_nama').value = settings.nama_aplikasi_dapodik;
                    if (settings.ip_erapor) document.getElementById('cfg_ip_erapor').value = settings.ip_erapor;
                    if (settings.ip_dapodik) document.getElementById('cfg_ip_dapodik').value = settings.ip_dapodik;
                    if (settings.token_dapodik) document.getElementById('cfg_token').value = settings.token_dapodik;
                });

                document.getElementById('btn-save-cfg').onclick = function() {
                    const btn = this;
                    const originalText = btn.innerText;
                    btn.innerText = 'Processing...';
                    btn.disabled = true;

                    const payload = {
                        nama_aplikasi_dapodik: document.getElementById('cfg_nama').value,
                        ip_erapor: document.getElementById('cfg_ip_erapor').value,
                        ip_dapodik: document.getElementById('cfg_ip_dapodik').value,
                        token_dapodik: document.getElementById('cfg_token').value,
                        sekolah_id: userData.sekolah_id,
                        semester_id: semester.semester_id,
                        npsn: userData.sekolah ? userData.sekolah.npsn : ''
                    };

                    fetch('/api/sinkronisasi/cek-koneksi', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Server error');
                        return response.json();
                    })
                    .then(res => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        Swal.fire({
                            title: res.color === 'success' ? 'Koneksi Berhasil' : 'Koneksi Gagal',
                            text: res.color === 'success' ? 'Konfigurasi Web Service Dapodik telah berhasil disimpan dan terhubung.' : (res.text || 'Gagal menghubungkan ke Web Service Dapodik.'),
                            icon: res.color === 'success' ? 'success' : 'error',
                            confirmButtonColor: '#7367F0',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            if (res.color === 'success') window.location.reload();
                        });
                    })
                    .catch(err => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        Swal.fire({
                            title: 'Gagal Terhubung',
                            text: 'Terjadi kesalahan saat menghubungi server aplikasi. Pastikan alamat IP dan Web Service Dapodik sudah aktif.',
                            icon: 'error',
                            confirmButtonColor: '#7367F0',
                        });
                    });
                };

                document.getElementById('btn-tarik-profil').onclick = function() {
                    const btn = this;
                    const originalText = btn.innerText;
                    btn.innerText = 'Mengambil Data...';
                    btn.disabled = true;

                    fetch('/api/sinkronisasi/tarik-profil', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify({
                            sekolah_id: userData.sekolah_id,
                            semester_id: semester.semester_id,
                            url_dapodik: document.getElementById('cfg_ip_dapodik').value.startsWith('http') ? document.getElementById('cfg_ip_dapodik').value : 'http://' + document.getElementById('cfg_ip_dapodik').value,
                            token_dapodik: document.getElementById('cfg_token').value,
                            npsn: userData.sekolah ? userData.sekolah.npsn : ''
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Server error');
                        return response.json();
                    })
                    .then(res => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        Swal.fire({
                            title: res.color === 'success' ? 'Pembaruan Berhasil' : 'Pembaruan Gagal',
                            text: res.color === 'success' ? 'Data profil sekolah telah berhasil diperbarui dari Dapodik lokal.' : (res.text || 'Gagal mengambil data profil dari Dapodik.'),
                            icon: res.color === 'success' ? 'success' : 'error',
                            confirmButtonColor: '#7367F0',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(err => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        Swal.fire({
                            title: 'Gagal Memperbarui',
                            text: 'Terjadi kesalahan saat mencoba mengambil data profil sekolah. Pastikan koneksi ke Dapodik lokal tersedia.',
                            icon: 'error',
                            confirmButtonColor: '#7367F0',
                        });
                    });
                };
            }

            function injectRegisterButton() {
                if (window.location.pathname !== '/login' && window.location.pathname !== '/auth/login') return;
                
                const registerBtnId = 'manual-register-btn';
                if (document.getElementById(registerBtnId)) return;

                const forms = document.querySelectorAll('form');
                if (forms.length === 0) return;

                const buttons = document.querySelectorAll('.v-btn');
                let loginBtn = null;
                for (const btn of buttons) {
                    if (btn.innerText.trim().toLowerCase() === 'login') {
                        loginBtn = btn;
                        break;
                    }
                }

                if (!loginBtn) return;

                // Cek apakah registrasi diperbolehkan
                if (app_registration !== '1' && app_registration !== 'true' && app_registration !== true) {
                    // Jika di settings db juga ada (app_registration di atas dari config, 
                    // tapi kita bisa cek global var yang mungkin diupdate)
                    // Untuk saat ini kita asumsikan true karena kita sudah set di AuthController
                }

                const registerBtn = document.createElement('a');
                registerBtn.id = registerBtnId;
                registerBtn.href = '/register';
                registerBtn.className = 'v-btn v-btn--block v-theme--light v-btn--density-default v-btn--size-default v-btn--variant-tonal mt-4';
                registerBtn.style.textDecoration = 'none';
                registerBtn.innerHTML = '<span class="v-btn__content">Registrasi Pengguna Baru</span>';
                
                loginBtn.parentNode.appendChild(registerBtn);
                
                // Sembunyikan teks lama
                const spans = document.querySelectorAll('span, a');
                spans.forEach(el => {
                    if (el.innerText.includes('Pengguna Baru?') || el.innerText.includes('Register Disini')) {
                        el.closest('.v-col-12')?.style.setProperty('display', 'none', 'important');
                    }
                });
            }

            // Polling for container because Vue might take time to render
            setInterval(() => {
                injectConfig();
                injectRegisterButton();
            }, 1000);
        })();
    </script>
</body>

</html>
