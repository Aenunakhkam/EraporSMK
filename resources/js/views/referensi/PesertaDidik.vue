<script setup>
const props = defineProps({
    titleCard: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        required: true,
    },
})
const headers = ref([])
if (props.status == 'password') {
    headers.value = [
        {
            title: 'Nama',
            key: 'nama',
            sortable: true,
        },
        {
            title: 'NIK',
            key: 'nik',
            align: 'center',
            sortable: true,
        },
        {
            title: 'L/P',
            key: 'jenis_kelamin',
            align: 'center',
            sortable: true,
        },
        {
            title: 'Tempat, Tanggal Lahir',
            key: 'tempat_tanggal_lahir',
            sortable: false,
        },
        {
            title: 'email',
            key: 'email',
            sortable: false,
        },
        {
            title: 'password',
            key: 'password',
            align: 'center',
            sortable: false,
        },
    ]
} else {
    headers.value = [
        {
            title: 'Nama',
            key: 'nama',
            sortable: true,
        },
        {
            title: 'NIK',
            key: 'nik',
            align: 'center',
            sortable: true,
        },
        {
            title: 'L/P',
            key: 'jenis_kelamin',
            align: 'center',
            sortable: true,
        },
        {
            title: 'Tempat, Tanggal Lahir',
            key: 'tempat_tanggal_lahir',
            sortable: false,
        },
        {
            title: 'Agama',
            key: 'agama',
            sortable: false,
        },
        {
            title: 'Kelas',
            key: 'kelas',
            align: 'center',
            sortable: false,
        },
        {
            title: 'detil',
            key: 'detil',
            align: 'center',
            sortable: false,
        },
    ]
}
const options = ref({
    tingkat: null,
    jurusanSpId: null,
    rombonganBelajarId: null,
    page: 1,
    itemsPerPage: 10,
    searchQuery: '',
    sortby: 'nama',
    sortbydesc: 'ASC',
});
const loadingTable = ref(false)
const items = ref([])
const total = ref(0)
const dataJurusan = ref([])
const dataRombel = ref([])
const updateSortBy = (val) => {
    options.value.sortby = val[0]?.key
    options.value.sortbydesc = val[0]?.order
}
const fetchData = async () => {
    loadingTable.value = true;
    try {
        const response = await useApi(createUrl('/referensi/pd', {
            query: {
                status: props.status,
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
                periode_aktif: $semester.nama,
                tingkat: options.value.tingkat,
                jurusan_sp_id: options.value.jurusanSpId,
                rombongan_belajar_id: options.value.rombonganBelajarId,
                q: options.value.searchQuery,
                page: options.value.page,
                per_page: options.value.itemsPerPage,
                sortby: options.value.sortby,
                sortbydesc: options.value.sortbydesc,
            },
        }));
        let getData = response.data.value
        items.value = getData.data.data
        total.value = getData.data.total
        dataJurusan.value = getData.jurusan_sp
        dataRombel.value = getData.rombel
    } catch (error) {
        console.error(error);
    } finally {
        loadingTable.value = false;
    }
};
onMounted(async () => {
    await fetchData();
});
watch(options, async () => {
    await fetchData();
}, { deep: true });
watch(
    () => options.value.searchQuery,
    () => {
        options.value.page = 1
    }
)
const isDialogVisible = ref(false)
const isAlertDialogVisible = ref(false)
const notif = ref({
    color: null,
    title: null,
    text: null,
})
const loadings = ref([])
const cardTitle = ref('')
const detilData = ref({})
const form = ref({
    peserta_didik_id: null,
    nama: null,
    no_induk: null,
    nisn: null,
    nik: null,
    jenis_kelamin: null,
    tempat_lahir: null,
    tanggal_lahir: null,
    agama_id: null,
    alamat: null,
    rt: null,
    rw: null,
    desa_kelurahan: null,
    kecamatan: null,
    kode_pos: null,
    no_telp: null,
    no_hp: null,
    sekolah_asal: null,
    diterima: null,
    nama_ayah: null,
    kerja_ayah: null,
    nama_ibu: null,
    kerja_ibu: null,
    status: null,
    anak_ke: null,
    diterima_kelas: null,
    email: null,
    nama_wali: null,
    alamat_wali: null,
    telp_wali: null,
    kerja_wali: null,
    photo: null,
})
const errors = ref({
    nama: undefined,
    no_induk: undefined,
    nisn: undefined,
    nik: undefined,
    tanggal_lahir: undefined,
    email: undefined,
    photo: undefined,
})
const pekerjaan = ref([])
const dataAgama = ref([])
const detilUser = async (peserta_didik_id) => {
    form.value.peserta_didik_id = peserta_didik_id
    loadings.value[peserta_didik_id] = true
    try {
        const response = await useApi(createUrl(`/referensi/pd/detil/${peserta_didik_id}`));
        let getData = response.data.value
        cardTitle.value = `Detil Peserta Didik (${getData.pd.nama})`
        detilData.value = getData.pd
        pekerjaan.value = getData.pekerjaan
        dataAgama.value = getData.agama
        form.value.nama = getData.pd.nama
        form.value.no_induk = getData.pd.no_induk
        form.value.nisn = getData.pd.nisn
        form.value.nik = getData.pd.nik
        form.value.jenis_kelamin = getData.pd.jenis_kelamin
        form.value.tempat_lahir = getData.pd.tempat_lahir
        form.value.tanggal_lahir = getData.pd.tanggal_lahir
        form.value.agama_id = getData.pd.agama_id
        form.value.alamat = getData.pd.alamat
        form.value.rt = getData.pd.rt
        form.value.rw = getData.pd.rw
        form.value.desa_kelurahan = getData.pd.desa_kelurahan
        form.value.kecamatan = getData.pd.kecamatan
        form.value.kode_pos = getData.pd.kode_pos
        form.value.no_telp = getData.pd.no_telp
        form.value.no_hp = getData.pd.no_hp
        form.value.sekolah_asal = getData.pd.sekolah_asal
        form.value.diterima = getData.pd.diterima_raw
        form.value.nama_ayah = getData.pd.nama_ayah
        form.value.kerja_ayah = getData.pd.kerja_ayah
        form.value.nama_ibu = getData.pd.nama_ibu
        form.value.kerja_ibu = getData.pd.kerja_ibu
        form.value.status = getData.pd.status
        form.value.anak_ke = getData.pd.anak_ke
        form.value.diterima_kelas = getData.pd.diterima_kelas
        form.value.email = getData.pd.email
        form.value.nama_wali = getData.pd.nama_wali
        form.value.alamat_wali = getData.pd.alamat_wali
        form.value.telp_wali = getData.pd.telp_wali
        form.value.kerja_wali = getData.pd.kerja_wali
    } catch (error) {
        console.error(error);
    } finally {
        loadings.value[peserta_didik_id] = false
        isDialogVisible.value = true
    }
}
const hapusPd = async (peserta_didik_id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data siswa ini? Data terkait juga akan dihapus.')) {
        loadings.value[peserta_didik_id] = true
        try {
            const response = await $api('/referensi/pd/hapus', {
                method: 'POST',
                body: { peserta_didik_id }
            });
            notif.value = response;
            isAlertDialogVisible.value = true;
            await fetchData();
        } catch (error) {
            console.error(error);
        } finally {
            loadings.value[peserta_didik_id] = false
        }
    }
}
const onFormSubmit = async () => {
    const dataForm = new FormData();
    dataForm.append('peserta_didik_id', form.value.peserta_didik_id)
    dataForm.append('nama', (form.value.nama) ? form.value.nama : '')
    dataForm.append('no_induk', (form.value.no_induk) ? form.value.no_induk : '')
    dataForm.append('nisn', (form.value.nisn) ? form.value.nisn : '')
    dataForm.append('nik', (form.value.nik) ? form.value.nik : '')
    dataForm.append('jenis_kelamin', (form.value.jenis_kelamin) ? form.value.jenis_kelamin : '')
    dataForm.append('tempat_lahir', (form.value.tempat_lahir) ? form.value.tempat_lahir : '')
    dataForm.append('tanggal_lahir', (form.value.tanggal_lahir) ? form.value.tanggal_lahir : '')
    dataForm.append('agama_id', (form.value.agama_id) ? form.value.agama_id : '')
    dataForm.append('alamat', (form.value.alamat) ? form.value.alamat : '')
    dataForm.append('rt', (form.value.rt) ? form.value.rt : '')
    dataForm.append('rw', (form.value.rw) ? form.value.rw : '')
    dataForm.append('desa_kelurahan', (form.value.desa_kelurahan) ? form.value.desa_kelurahan : '')
    dataForm.append('kecamatan', (form.value.kecamatan) ? form.value.kecamatan : '')
    dataForm.append('kode_pos', (form.value.kode_pos) ? form.value.kode_pos : '')
    dataForm.append('no_telp', (form.value.no_telp) ? form.value.no_telp : '')
    dataForm.append('no_hp', (form.value.no_hp) ? form.value.no_hp : '')
    dataForm.append('sekolah_asal', (form.value.sekolah_asal) ? form.value.sekolah_asal : '')
    dataForm.append('diterima', (form.value.diterima) ? form.value.diterima : '')
    dataForm.append('nama_ayah', (form.value.nama_ayah) ? form.value.nama_ayah : '')
    dataForm.append('kerja_ayah', (form.value.kerja_ayah) ? form.value.kerja_ayah : '')
    dataForm.append('nama_ibu', (form.value.nama_ibu) ? form.value.nama_ibu : '')
    dataForm.append('kerja_ibu', (form.value.kerja_ibu) ? form.value.kerja_ibu : '')
    dataForm.append('status', (form.value.status) ? form.value.status : '')
    dataForm.append('anak_ke', (form.value.anak_ke) ? form.value.anak_ke : '')
    dataForm.append('diterima_kelas', (form.value.diterima_kelas) ? form.value.diterima_kelas : '')
    dataForm.append('email', (form.value.email) ? form.value.email : '')
    dataForm.append('nama_wali', (form.value.nama_wali) ? form.value.nama_wali : '')
    dataForm.append('alamat_wali', (form.value.alamat_wali) ? form.value.alamat_wali : '')
    dataForm.append('telp_wali', (form.value.telp_wali) ? form.value.telp_wali : '')
    dataForm.append('kerja_wali', (form.value.kerja_wali) ? form.value.kerja_wali : '')
    dataForm.append('photo', (form.value.photo) ? form.value.photo : '')
    try {
        await $api('/referensi/pd/update', {
            method: 'POST',
            body: dataForm,
            onResponse({ request, response, options }) {
                let getData = response._data
                if (getData.errors) {
                    errors.value = getData.errors
                } else {
                    errors.value = {
                        nama: undefined,
                        no_induk: undefined,
                        nisn: undefined,
                        nik: undefined,
                        tanggal_lahir: undefined,
                        email: undefined,
                        photo: undefined,
                    }
                    form.value.photo = null
                    isDialogVisible.value = false
                    notif.value = getData
                    isAlertDialogVisible.value = true
                }
            }
        })
    } catch (err) {
        console.error(err)
        if (err.response && err.response._data && err.response._data.errors) {
            errors.value = err.response._data.errors
        } else {
            notif.value = {
                color: 'error',
                title: 'Gagal!',
                text: (err.response && err.response._data && err.response._data.message) ? err.response._data.message : 'Terjadi kesalahan sistem'
            }
            isAlertDialogVisible.value = true
        }
    }
}
const confirmDialog = async () => {
    await fetchData()
}
const dataStatus = ['Anak Kandung', 'Anak Tiri', 'Anak Angkat'];
import bcrypt from "bcryptjs";
const cekPass = (pass, defaultPassword) => {
    if (defaultPassword) {
        return bcrypt.compareSync(defaultPassword, pass)
    }
    return false
}
import AddNewPd from '@/components/dialogs/AddNewPd.vue';
const isDialogAddNew = ref(false)
</script>
<template>
    <VCard class="mb-6">
        <VCardItem class="pb-4">
            <VCardTitle>{{ titleCard }}</VCardTitle>
        </VCardItem>
        <VDivider />
        <VCardText>
            <VRow>
                <VCol cols="12" sm="4">
                    <AppSelect v-model="options.tingkat" placeholder="== Filter Tingkat Kelas" :items="tingkatKelas"
                        clearable clear-icon="tabler-x" />
                </VCol>
                <VCol cols="12" sm="4">
                    <AppSelect v-model="options.jurusanSpId" placeholder="== Filter Jurusan ==" :items="dataJurusan"
                        item-title="nama_jurusan_sp" item-value="jurusan_sp_id" clearable clear-icon="tabler-x" />
                </VCol>
                <VCol cols="12" sm="4">
                    <AppSelect v-model="options.rombonganBelajarId" placeholder="== Filter Rombel" :items="dataRombel"
                        item-title="nama" item-value="rombongan_belajar_id" clearable clear-icon="tabler-x" />
                </VCol>
            </VRow>
        </VCardText>
        <VDivider />
        <VCardText class="d-flex flex-wrap gap-4">
            <div class="d-flex gap-2 align-center">
                <AppSelect v-model="options.itemsPerPage" :items="[
                    { value: 10, title: '10' },
                    { value: 25, title: '25' },
                    { value: 50, title: '50' },
                    { value: 100, title: '100' },
                ]" style="inline-size: 15.5rem;" />
            </div>
            <VSpacer />
            <div class="d-flex align-center flex-wrap gap-4">
                <VBtn v-if="props.status == 'aktif' && $can('read', 'Administrator')" color="primary" prepend-icon="tabler-plus" @click="isDialogAddNew = true">
                    Tambah Data
                </VBtn>
                <!-- 👉 Search  -->
                <AppTextField v-model="options.searchQuery" placeholder="Cari data" style="inline-size: 15.625rem;" />
            </div>
        </VCardText>
        <VDivider />
        <VDataTableServer v-model:page="options.page" :items-per-page="options.itemsPerPage" :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 20, title: '20' },
            { value: 50, title: '50' },
        ]" :items="items" :server-items-length="total" :items-length="total" :headers="headers"
            items-per-page-text="Item" class="text-no-wrap" :options="options" :loading="loadingTable"
            loading-text="Loading..." @update:sortBy="updateSortBy">
            <template #item.nama="{ item }">
                <div class="d-flex align-center gap-x-4">
                    <VAvatar size="34" :variant="!item.photo ? 'tonal' : undefined"
                        :color="!item.photo ? 'success' : undefined">
                        <VImg :src="item.photo" />
                    </VAvatar>
                    <div class="d-flex flex-column">
                        <h6 class="text-base">{{ item.nama }}</h6>
                        <div class="text-sm">
                            {{ item.nisn }}
                        </div>
                    </div>
                </div>
            </template>
            <template #item.agama="{ item }">
                {{ item.agama?.nama }}
            </template>
            <template #item.kelas="{ item }">
                {{ item.anggota_rombel?.rombongan_belajar?.nama }}
            </template>
            <template #item.email="{ item }">
                {{ item.user?.email }}
            </template>
            <template #item.password="{ item }">
                <template v-if="item.user">
                    <template v-if="cekPass(item.user.password, item.user.default_password)">
                        {{ item.user.default_password }}
                    </template>
                    <template v-else>
                        <VChip size="x-small" color="error">
                            Custom
                        </VChip>
                    </template>
                </template>
            </template>
            <template #item.detil="{ item }">
                <div class="d-flex justify-center gap-1">
                    <VBtn :loading="loadings[item.peserta_didik_id]" :disabled="loadings[item.peserta_didik_id]"
                        color="info" icon="tabler-eye" size="small" @click="detilUser(item.peserta_didik_id)" title="Lihat" />
                    <VBtn v-if="$can('read', 'Administrator')" :loading="loadings[item.peserta_didik_id]" :disabled="loadings[item.peserta_didik_id]"
                        color="warning" icon="tabler-edit" size="small" @click="detilUser(item.peserta_didik_id)" title="Edit" />
                    <VBtn v-if="$can('read', 'Administrator')" :loading="loadings[item.peserta_didik_id]" :disabled="loadings[item.peserta_didik_id]"
                        color="error" icon="tabler-trash" size="small" @click="hapusPd(item.peserta_didik_id)" title="Hapus" />
                </div>
            </template>
            <!-- pagination -->
            <template #bottom>
                <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage"
                    :total-items="total" />
            </template>
        </VDataTableServer>
        <VDialog v-model="isDialogVisible" width="700" scrollable content-class="scrollable-dialog">
            <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />
            <VForm @submit.prevent="onFormSubmit">
                <VCard>
                    <VCardItem class="pb-5">
                        <VCardTitle>{{ cardTitle }}</VCardTitle>
                    </VCardItem>
                    <VDivider />
                    <VCardText>
                        <fieldset :disabled="!$can('read', 'Administrator')" style="border: 0; padding: 0; margin: 0; min-width: 0;">
                            <VRow>
                            <VCol cols="12" class="text-center">
                                <VAvatar rounded :size="150" color="primary" variant="tonal">
                                    <VImg :src="detilData?.photo" />
                                </VAvatar>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nama">Nama
                                            Lengkap</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.nama" placeholder="Nama Lengkap" persistent-placeholder :error-messages="errors.nama" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="no_induk">NIS</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="no_induk" v-model="form.no_induk" placeholder="NIS" persistent-placeholder :error-messages="errors.no_induk" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nisn">NISN</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nisn" v-model="form.nisn" placeholder="NISN" persistent-placeholder :error-messages="errors.nisn" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nik">NIK</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.nik" placeholder="NIK" persistent-placeholder :error-messages="errors.nik" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="jenis_kelamin">Jenis
                                            Kelamin</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.jenis_kelamin" placeholder="== Pilih Jenis Kelamin ==" :items="[{title: 'Laki-Laki', value: 'L'}, {title: 'Perempuan', value: 'P'}]" clearable clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="tempat_lahir">Tempat
                                            Lahir</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.tempat_lahir"
                                            placeholder="Tempat Lahir" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="tanggal_lahir_indo">Tanggal
                                            Lahir</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.tanggal_lahir"
                                            placeholder="Tanggal Lahir" persistent-placeholder :error-messages="errors.tanggal_lahir" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="agama_id">Agama</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.agama_id" placeholder="== Pilih Agama ==" :items="dataAgama" item-title="nama" item-value="agama_id" clearable clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="status">Status dalam
                                            Keluarga</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.status" placeholder="== Pilih Status dalam
                                            Keluarga ==" :items="dataStatus" clearable clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="anak_ke">Anak ke
                                            berapa?</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="anak_ke" v-model="form.anak_ke" placeholder="Anak ke berapa?"
                                            persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="alamat">Alamat</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.alamat" placeholder="Alamat" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="rt">RT</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.rt" placeholder="RT" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="rw">RW</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.rw" placeholder="RW" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="desa_kelurahan">Desa/Kelurahan</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.desa_kelurahan"
                                            placeholder="Desa/Kelurahan" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="kecamatan">Kecamatan</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.kecamatan" placeholder="Kecamatan" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="kode_pos">Kodepos</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.kode_pos" placeholder="Kodepos" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="no_telp">Telp/HP</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.no_telp" placeholder="Telp/HP" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="sekolah_asal">Asal
                                            Sekolah</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="sekolah_asal" v-model="form.sekolah_asal"
                                            placeholder="Asal Sekolah" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis"
                                            for="diterima_kelas">Diterima
                                            dikelas</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="diterima_kelas" v-model="form.diterima_kelas"
                                            placeholder="Diterima dikelas" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="diterima">Diterima
                                            pada
                                            tanggal</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="diterima" v-model="form.diterima"
                                            placeholder="Diterima pada tanggal" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="email">Email</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.email" placeholder="Email"
                                            persistent-placeholder :error-messages="errors.email" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nama_ayah">Nama
                                            Ayah</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama_ayah" v-model="form.nama_ayah"
                                            placeholder="Nama Ayah" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="kerja_ayah">Pekerjaan
                                            Ayah</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.kerja_ayah" placeholder="== Pilih Pekerjaan ==" :items="pekerjaan" item-title="nama" item-value="pekerjaan_id" clearable clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nama_ibu">Nama
                                            Ibu</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama_ibu" v-model="form.nama_ibu" placeholder="Nama Ibu" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="kerja_ibu">Pekerjaan
                                            Ibu</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.kerja_ibu" placeholder="== Pilih Pekerjaan ==" :items="pekerjaan" item-title="nama" item-value="pekerjaan_id" clearable clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nama_wali">Nama
                                            Wali</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama_wali" v-model="form.nama_wali" placeholder="Nama Wali"
                                            persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="alamat_wali">Alamat
                                            Wali</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="alamat_wali" v-model="form.alamat_wali"
                                            placeholder="Alamat Wali" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="telp_wali">Telp/HP
                                            Wali</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="telp_wali" v-model="form.telp_wali" placeholder="Telp/HP Wali"
                                            persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="kerja_wali">Pekerjaan
                                            Wali</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect v-model="form.kerja_wali" placeholder="== Pilih Pekerjaan Wali =="
                                            :items="pekerjaan" item-title="nama" item-value="pekerjaan_id" clearable
                                            clear-icon="tabler-x" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="photo">Unggah
                                            Foto</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <VFileInput v-model="form.photo" :error-messages="errors.photo" accept="image/*"
                                            label="Unggah Foto Peserta Didik" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        </fieldset>
                    </VCardText>
                    <VDivider />
                    <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible">
                        <VBtn color="secondary" variant="tonal" @click="isDialogVisible = false">
                            Tutup
                        </VBtn>
                        <VBtn type="submit" v-if="$can('read', 'Administrator')">
                            Perbaharui
                        </VBtn>
                    </VCardText>
                </VCard>
            </VForm>
        </VDialog>
        <AlertDialog v-model:isDialogVisible="isAlertDialogVisible" :confirm-color="notif.color"
            :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDialog" />
        <AddNewPd v-model:isDialogVisible="isDialogAddNew" cardTitle="Tambah Peserta Didik Manual" @close="fetchData" />
    </VCard>
</template>
<style lang="scss">
.scrollable-dialog {
    overflow: visible !important;
}
</style>
