<script setup>
const props = defineProps({
    titleCard: {
        type: String,
        required: true,
    },
    addBtn: {
        type: Boolean,
        default: false,
    },
    data: {
        type: String,
        required: true,
    },
})
const headers = [
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
        title: 'Aksi',
        key: 'aksi',
        align: 'center',
        sortable: false,
    },
]
const options = ref({
    page: 1,
    itemsPerPage: 10,
    searchQuery: '',
    sortby: 'nama',
    sortbydesc: 'ASC',
});
const loadingTable = ref(false)
const items = ref([])
const total = ref(0)
const updateSortBy = (val) => {
    options.value.sortby = val[0]?.key
    options.value.sortbydesc = val[0]?.order
}
const fetchData = async () => {
    loadingTable.value = true;
    try {
        const response = await useApi(createUrl('/referensi/ptk', {
            query: {
                jenis_gtk: props.data,
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
                periode_aktif: $semester.nama,
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
const isConfirmDialogVisible = ref(false)
const notif = ref({
    color: null,
    title: null,
    text: null,
})
const loadings = ref([])
const cardTitle = ref('')
const detilData = ref({})
const listAgama = ref([])
const fetchAgama = async () => {
    try {
        const response = await useApi(createUrl('/referensi/agama'))
        listAgama.value = response.data.value
    } catch (e) {
        console.error(e)
    }
}
onMounted(() => {
    fetchAgama()
})
const form = ref({
    guru_id: null,
    sekolah_id: $user.sekolah_id,
    nama: null,
    gelar_depan: null,
    gelar_belakang: null,
    nuptk: null,
    nip: null,
    nik: null,
    jenis_kelamin: null,
    tempat_lahir: null,
    tanggal_lahir: null,
    agama: null,
    alamat: null,
    rt: null,
    rw: null,
    desa_kelurahan: null,
    kecamatan: null,
    kode_pos: null,
    no_hp: null,
    email: null,
    dudi_id: null,
    asesor: props.data == 'asesor' ? true : false,
})
const dataDudi = ref([])
const detilUser = async (guru_id) => {
    form.value.guru_id = guru_id
    loadings.value[guru_id] = true
    await $api('/referensi/ptk/detil', {
        method: 'POST',
        body: {
            guru_id: guru_id,
            sekolah_id: $user.sekolah_id,
            asesor: props.data == 'asesor' ? true : false
        },
        onResponse({ response }) {
            let getData = response._data
            cardTitle.value = `Detil PTK (${getData.ptk.nama_lengkap})`
            detilData.value = getData.ptk
            dataDudi.value = getData.dudi
            
            form.value.nama = getData.ptk.nama
            form.value.gelar_depan = getData.ptk.gelar_depan
            form.value.gelar_belakang = getData.ptk.gelar_belakang
            form.value.nuptk = getData.ptk.nuptk
            form.value.nip = getData.ptk.nip
            form.value.nik = getData.ptk.nik
            form.value.jenis_kelamin = getData.ptk.jenis_kelamin
            form.value.tempat_lahir = getData.ptk.tempat_lahir
            form.value.tanggal_lahir = getData.ptk.tanggal_lahir
            form.value.agama = getData.ptk.agama ? getData.ptk.agama.nama : null
            form.value.alamat = getData.ptk.alamat
            form.value.rt = getData.ptk.rt
            form.value.rw = getData.ptk.rw
            form.value.desa_kelurahan = getData.ptk.desa_kelurahan
            form.value.kecamatan = getData.ptk.kecamatan
            form.value.kode_pos = getData.ptk.kode_pos
            form.value.no_hp = getData.ptk.no_hp
            form.value.email = getData.ptk.email
            form.value.dudi_id = getData.dudi_id
            
            loadings.value[guru_id] = false
            isDialogVisible.value = true
        }
    })
}
const onFormSubmit = async () => {
    await $api('/referensi/ptk/update', {
        method: 'POST',
        body: form.value,
        onResponse({ response }) {
            let getData = response._data
            isDialogVisible.value = false
            notif.value = getData
            isAlertDialogVisible.value = true
        }
    })
}
const confirmClose = async () => {
    await fetchData()
}
const isDialogAddNew = ref(false)
const deletedId = ref()
const hapus = async (id) => {
    deletedId.value = id
    isConfirmDialogVisible.value = true
}
const confirmDelete = async () => {
    await $api('/referensi/ptk/hapus', {
        method: 'POST',
        body: {
            guru_id: deletedId.value,
            data: props.data,
        },
        onResponse({ response }) {
            let getData = response._data
            notif.value = getData
            isDialogVisible.value = false
            isAlertDialogVisible.value = true
        }
    })
}
</script>
<template>
    <VCard class="mb-6">
        <template v-slot:title class="pb-4">{{ titleCard }}</template>
        <template #append v-if="addBtn">
            <div class="d-flex align-center">
                <VBtn prepend-icon="tabler-plus" @click="isDialogAddNew = true">Tambah Data</VBtn>
            </div>
        </template>
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
                        <h6 class="text-base">{{ item.nama_lengkap }}</h6>
                        <div class="text-sm">
                            {{ item.email }}
                        </div>
                    </div>
                </div>
            </template>
            <template #item.aksi="{ item }">
                <div class="d-flex gap-2 justify-center">
                    <VBtn :loading="loadings[item.guru_id]" :disabled="loadings[item.guru_id]" color="warning"
                        icon="tabler-edit" size="small" @click="detilUser(item.guru_id)" />
                    <VBtn color="error" icon="tabler-trash" size="small" @click="hapus(item.guru_id)" />
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
                        <VRow>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nama">Nama Lengkap (Tanpa Gelar)</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nama" v-model="form.nama" placeholder="Nama Lengkap" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="gelar_depan">Gelar Depan</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="gelar_depan" v-model="form.gelar_depan" placeholder="Gelar Depan" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="gelar_belakang">Gelar Belakang</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="gelar_belakang" v-model="form.gelar_belakang" placeholder="Gelar Belakang" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nuptk">NUPTK</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nuptk" v-model="form.nuptk" placeholder="NUPTK" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nip">NIP</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nip" v-model="form.nip" placeholder="NIP" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="nik">NIK</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="nik" v-model="form.nik" placeholder="NIK" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="jenis_kelamin">Jenis Kelamin</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect id="jenis_kelamin" v-model="form.jenis_kelamin" :items="[
                                            { title: 'Laki-Laki', value: 'L' },
                                            { title: 'Perempuan', value: 'P' }
                                        ]" item-title="title" item-value="value" placeholder="Pilih Jenis Kelamin" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="tempat_lahir">Tempat Lahir</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="tempat_lahir" v-model="form.tempat_lahir" placeholder="Tempat Lahir" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="tanggal_lahir">Tanggal Lahir</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="tanggal_lahir" v-model="form.tanggal_lahir" type="date" placeholder="Tanggal Lahir" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="agama">Agama</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppSelect id="agama" v-model="form.agama" :items="listAgama.map(a => a.nama)" placeholder="Pilih Agama" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="alamat">Alamat</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="alamat" v-model="form.alamat" placeholder="Alamat" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="rt">RT</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="rt" v-model="form.rt" placeholder="RT" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="rw">RW</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="rw" v-model="form.rw" placeholder="RW" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="desa_kelurahan">Desa/Kelurahan</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="desa_kelurahan" v-model="form.desa_kelurahan" placeholder="Desa/Kelurahan" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="kecamatan">Kecamatan</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="kecamatan" v-model="form.kecamatan" placeholder="Kecamatan" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="kode_pos">Kodepos</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="kode_pos" v-model="form.kode_pos" placeholder="Kodepos" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="no_hp">Telp/HP</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="no_hp" v-model="form.no_hp" placeholder="Telp/HP" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol cols="12">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="email">Email</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppTextField id="email" v-model="form.email" placeholder="Email" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>

                            <VCol cols="12" v-if="data == 'asesor'">
                                <VRow no-gutters>
                                    <VCol cols="12" md="3" class="d-flex align-items-center">
                                        <label class="v-label text-body-2 text-high-emphasis" for="dudi_id">DUDI</label>
                                    </VCol>
                                    <VCol cols="12" md="9">
                                        <AppAutocomplete v-model="form.dudi_id" placeholder="== Pilih DUDI =="
                                            :items="dataDudi" clearable clear-icon="tabler-x" item-value="dudi_id"
                                            item-title="nama" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />
                    <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible">
                        <VBtn color="error" @click="hapus(detilData?.guru_id)"
                            v-if="detilData?.jenis_ptk_id === 97 || detilData?.jenis_ptk_id === 98">
                            Hapus
                        </VBtn>
                        <v-spacer />
                        <VBtn color="secondary" variant="tonal" @click="isDialogVisible = false">
                            Tutup
                        </VBtn>
                        <VBtn type="submit">
                            Perbaharui
                        </VBtn>
                    </VCardText>
                </VCard>
            </VForm>
        </VDialog>
        <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isAlertDialogVisible"
            confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
            :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete"
            @close="confirmClose" />
        <AddNewPtk :cardTitle="`Tambah ${titleCard}`" :jenisGtk="props.data" v-model:isDialogVisible="isDialogAddNew"
            @close="confirmClose" />
    </VCard>
</template>
<style lang="scss">
.scrollable-dialog {
    overflow: visible !important;
}
</style>
