<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    cardTitle: {
        type: String,
        required: true,
    },
})
const updateModelValue = val => {
    emit('update:isDialogVisible', val)
}
const emit = defineEmits([
    'update:isDialogVisible',
    'close'
])

const activeTab = ref('siswa')
const listAgama = ref([])
const listPekerjaan = ref([])
const listRombel = ref([])
const loadingRefs = ref(false)

const fetchReferences = async () => {
    loadingRefs.value = true
    try {
        const response = await useApi(createUrl('/referensi/pd/referensi-add', {
            query: {
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            }
        }))
        const data = response.data.value
        listAgama.value = data.agama
        listPekerjaan.value = data.pekerjaan
        listRombel.value = data.rombel
    } catch (e) {
        console.error(e)
    } finally {
        loadingRefs.value = false
    }
}

onMounted(() => {
    fetchReferences()
})

const manualForm = ref({
    nama: '',
    no_induk: '',
    nisn: '',
    nik: '',
    jenis_kelamin: 'L',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama_id: '',
    status: 'Anak Kandung',
    anak_ke: 1,
    alamat: '',
    rt: 0,
    rw: 0,
    desa_kelurahan: '',
    kecamatan: '',
    kode_pos: '',
    no_telp: '',
    no_hp: '',
    sekolah_asal: '',
    diterima_kelas: '',
    email: '',
    nama_ayah: '',
    nama_ibu: '',
    kerja_ayah: 1,
    kerja_ibu: 1,
    nama_wali: '',
    alamat_wali: '',
    telp_wali: '',
    kerja_wali: 1,
    rombongan_belajar_id: '',
})

const manualErrors = ref({
    nama: '',
    no_induk: '',
    nisn: '',
    nik: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama_id: '',
    email: '',
    rombongan_belajar_id: '',
})

const saving = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
    color: null,
    title: null,
    text: null,
})

const onSubmit = async () => {
    saving.value = true
    manualErrors.value = {
        nama: '',
        no_induk: '',
        nisn: '',
        nik: '',
        jenis_kelamin: '',
        tempat_lahir: '',
        tanggal_lahir: '',
        agama_id: '',
        email: '',
        rombongan_belajar_id: '',
    }
    
    const payload = {
        ...manualForm.value,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        periode_aktif: $semester.nama,
    }
    
    await $api("/referensi/pd/simpan", {
        method: "POST",
        body: payload,
        onResponse({ response }) {
            let getData = response._data;
            saving.value = false
            if (getData.errors) {
                manualErrors.value.nama = getData.errors['nama'] ? getData.errors['nama'][0] : ''
                manualErrors.value.no_induk = getData.errors['no_induk'] ? getData.errors['no_induk'][0] : ''
                manualErrors.value.nisn = getData.errors['nisn'] ? getData.errors['nisn'][0] : ''
                manualErrors.value.nik = getData.errors['nik'] ? getData.errors['nik'][0] : ''
                manualErrors.value.jenis_kelamin = getData.errors['jenis_kelamin'] ? getData.errors['jenis_kelamin'][0] : ''
                manualErrors.value.tempat_lahir = getData.errors['tempat_lahir'] ? getData.errors['tempat_lahir'][0] : ''
                manualErrors.value.tanggal_lahir = getData.errors['tanggal_lahir'] ? getData.errors['tanggal_lahir'][0] : ''
                manualErrors.value.agama_id = getData.errors['agama_id'] ? getData.errors['agama_id'][0] : ''
                manualErrors.value.email = getData.errors['email'] ? getData.errors['email'][0] : ''
                manualErrors.value.rombongan_belajar_id = getData.errors['rombongan_belajar_id'] ? getData.errors['rombongan_belajar_id'][0] : ''
                
                // Pindahkan ke tab dengan error jika relevan
                if (manualErrors.value.rombongan_belajar_id) {
                    activeTab.value = 'rombel'
                } else if (manualErrors.value.nama || manualErrors.value.no_induk || manualErrors.value.nisn || manualErrors.value.nik || manualErrors.value.jenis_kelamin || manualErrors.value.tempat_lahir || manualErrors.value.tanggal_lahir || manualErrors.value.agama_id || manualErrors.value.email) {
                    activeTab.value = 'siswa'
                }
            } else {
                updateModelValue(false)
                notif.value = getData
                isNotifVisible.value = true
                // Reset form
                manualForm.value = {
                    nama: '',
                    no_induk: '',
                    nisn: '',
                    nik: '',
                    jenis_kelamin: 'L',
                    tempat_lahir: '',
                    tanggal_lahir: '',
                    agama_id: '',
                    status: 'Anak Kandung',
                    anak_ke: 1,
                    alamat: '',
                    rt: 0,
                    rw: 0,
                    desa_kelurahan: '',
                    kecamatan: '',
                    kode_pos: '',
                    no_telp: '',
                    no_hp: '',
                    sekolah_asal: '',
                    diterima_kelas: '',
                    email: '',
                    nama_ayah: '',
                    nama_ibu: '',
                    kerja_ayah: 1,
                    kerja_ibu: 1,
                    nama_wali: '',
                    alamat_wali: '',
                    telp_wali: '',
                    kerja_wali: 1,
                    rombongan_belajar_id: '',
                }
                activeTab.value = 'siswa'
            }
        }
    })
}

const onConfirmation = () => {
    emit('close')
    isNotifVisible.value = false
}
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" max-width="800">
        <DialogCloseBtn @click="updateModelValue(false)" v-if="!saving" />
        <VCard style="max-height: 90vh; display: flex; flex-direction: column;">
            <VCardItem class="pb-3 flex-shrink-0">
                <VCardTitle>{{ cardTitle }}</VCardTitle>
            </VCardItem>
            
            <VTabs v-model="activeTab" class="px-4 flex-shrink-0">
                <VTab value="siswa">Data Siswa</VTab>
                <VTab value="keluarga">Data Orang Tua & Wali</VTab>
                <VTab value="rombel">Kelas & Rombel</VTab>
            </VTabs>
            <VDivider class="flex-shrink-0" />

            <div style="overflow-y: auto; flex-grow: 1;">
                <VWindow v-model="activeTab">
                    <!-- TAB DATA SISWA -->
                    <VWindowItem value="siswa">
                        <VForm @submit.prevent="activeTab = 'keluarga'">
                            <VCardText class="pt-5">
                                <VRow>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nama" label="Nama Lengkap" placeholder="Nama Lengkap" :error-messages="manualErrors.nama" required />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.no_induk" label="Nomor Induk (NIS)" placeholder="NIS / No Induk" :error-messages="manualErrors.no_induk" required />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nisn" label="NISN" placeholder="NISN (10 digit)" :error-messages="manualErrors.nisn" required />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nik" label="NIK" placeholder="NIK (16 digit)" :error-messages="manualErrors.nik" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.jenis_kelamin" :items="[
                                            { title: 'Laki-Laki', value: 'L' },
                                            { title: 'Perempuan', value: 'P' }
                                        ]" item-title="title" item-value="value" label="Jenis Kelamin" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.agama_id" :items="listAgama" item-title="nama" item-value="agama_id" label="Agama" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.tempat_lahir" label="Tempat Lahir" placeholder="Tempat Lahir" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.tanggal_lahir" type="date" label="Tanggal Lahir" :error-messages="manualErrors.tanggal_lahir" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.status" :items="['Anak Kandung', 'Anak Tiri', 'Anak Angkat']" label="Status Dalam Keluarga" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.anak_ke" type="number" min="1" label="Anak Ke" placeholder="Anak Ke" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.email" type="email" label="Email" placeholder="Email" :error-messages="manualErrors.email" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.no_hp" label="No HP" placeholder="No HP" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.sekolah_asal" label="Sekolah Asal" placeholder="Sekolah Asal" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.diterima_kelas" label="Diterima Kelas" placeholder="Diterima Kelas (misal: X, XI, XII)" />
                                    </VCol>
                                    <VCol cols="12">
                                        <VDivider class="my-2" />
                                        <h4 class="text-body-1 font-weight-medium mb-3">Detail Alamat Tempat Tinggal</h4>
                                    </VCol>
                                    <VCol cols="12" md="8">
                                        <AppTextField v-model="manualForm.alamat" label="Alamat Jalan" placeholder="Alamat Jalan" />
                                    </VCol>
                                    <VCol cols="6" md="2">
                                        <AppTextField v-model="manualForm.rt" type="number" label="RT" placeholder="RT" />
                                    </VCol>
                                    <VCol cols="6" md="2">
                                        <AppTextField v-model="manualForm.rw" type="number" label="RW" placeholder="RW" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.desa_kelurahan" label="Desa/Kelurahan" placeholder="Desa/Kelurahan" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.kecamatan" label="Kecamatan" placeholder="Kecamatan" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.kode_pos" label="Kodepos" placeholder="Kodepos" />
                                    </VCol>
                                </VRow>
                            </VCardText>
                            <VDivider />
                            <VCardText class="d-flex justify-end gap-3">
                                <VBtn color="secondary" variant="tonal" @click="updateModelValue(false)">Batal</VBtn>
                                <VBtn type="submit">Lanjut ke Data Keluarga</VBtn>
                            </VCardText>
                        </VForm>
                    </VWindowItem>

                    <!-- TAB DATA KELUARGA -->
                    <VWindowItem value="keluarga">
                        <VForm @submit.prevent="activeTab = 'rombel'">
                            <VCardText class="pt-5">
                                <VRow>
                                    <VCol cols="12">
                                        <h4 class="text-body-1 font-weight-medium mb-1">Data Ayah Kandung</h4>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nama_ayah" label="Nama Ayah" placeholder="Nama Lengkap Ayah" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.kerja_ayah" :items="listPekerjaan" item-title="nama" item-value="pekerjaan_id" label="Pekerjaan Ayah" />
                                    </VCol>
                                    
                                    <VCol cols="12">
                                        <VDivider class="my-2" />
                                        <h4 class="text-body-1 font-weight-medium mb-1">Data Ibu Kandung</h4>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nama_ibu" label="Nama Ibu" placeholder="Nama Lengkap Ibu" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.kerja_ibu" :items="listPekerjaan" item-title="nama" item-value="pekerjaan_id" label="Pekerjaan Ibu" />
                                    </VCol>
                                    
                                    <VCol cols="12">
                                        <VDivider class="my-2" />
                                        <h4 class="text-body-1 font-weight-medium mb-1">Data Wali (Opsional)</h4>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nama_wali" label="Nama Wali" placeholder="Nama Lengkap Wali" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.kerja_wali" :items="listPekerjaan" item-title="nama" item-value="pekerjaan_id" label="Pekerjaan Wali" />
                                    </VCol>
                                    <VCol cols="12" md="8">
                                        <AppTextField v-model="manualForm.alamat_wali" label="Alamat Wali" placeholder="Alamat Lengkap Wali" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.telp_wali" label="Telp Wali" placeholder="Nomor Telepon Wali" />
                                    </VCol>
                                </VRow>
                            </VCardText>
                            <VDivider />
                            <VCardText class="d-flex justify-end gap-3">
                                <VBtn color="secondary" variant="tonal" @click="activeTab = 'siswa'">Kembali</VBtn>
                                <VBtn type="submit">Lanjut ke Kelas & Rombel</VBtn>
                            </VCardText>
                        </VForm>
                    </VWindowItem>

                    <!-- TAB ROMBONGAN BELAJAR -->
                    <VWindowItem value="rombel">
                        <VForm @submit.prevent="onSubmit">
                            <VCardText class="pt-5">
                                <VRow class="justify-center">
                                    <VCol cols="12" md="8" class="text-center">
                                        <VIcon icon="tabler-school" size="80" color="primary" class="mb-4" />
                                        <h3 class="text-h4 mb-2">Penempatan Kelas Peserta Didik</h3>
                                        <p class="text-body-1 mb-6 text-secondary">
                                            Pilih rombongan belajar (kelas) aktif untuk peserta didik baru ini. Siswa akan otomatis didaftarkan ke rombel dan semester aktif ini.
                                        </p>
                                        
                                        <AppSelect v-model="manualForm.rombongan_belajar_id" :items="listRombel" item-title="nama" item-value="rombongan_belajar_id" label="Pilih Rombongan Belajar (Kelas)" placeholder="== Pilih Kelas aktif ==" :error-messages="manualErrors.rombongan_belajar_id" />
                                    </VCol>
                                </VRow>
                            </VCardText>
                            <VDivider />
                            <VCardText class="d-flex justify-end gap-3">
                                <VBtn color="secondary" variant="tonal" @click="activeTab = 'keluarga'" :disabled="saving">Kembali</VBtn>
                                <VBtn type="submit" color="primary" :loading="saving" :disabled="saving">Simpan & Daftarkan Siswa</VBtn>
                            </VCardText>
                        </VForm>
                    </VWindowItem>
                </VWindow>
            </div>
        </VCard>
    </VDialog>

    <!-- Dialog Notifikasi Sukses/Gagal -->
    <VDialog v-model="isNotifVisible" max-width="500">
        <VCard>
            <VCardText class="text-center px-10 py-6">
                <VBtn icon variant="outlined" :color="notif.color" class="my-4"
                    style=" block-size: 88px;inline-size: 88px; pointer-events: none;">
                    <VIcon :icon="(notif.color == 'success') ? 'tabler-checks' : 'tabler-xbox-x'" size="38" />
                </VBtn>
                <h1 class="text-h4 mb-4">
                    {{ notif.title }}
                </h1>
                <p>{{ notif.text }}</p>
                <VBtn color="success" @click="onConfirmation">
                    Ok
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
