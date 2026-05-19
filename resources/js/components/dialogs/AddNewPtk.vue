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
    jenisGtk: {
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

const activeTab = ref('manual')
const listAgama = ref([])

const fetchAgama = async () => {
    try {
        const response = await useApi('/referensi/agama')
        listAgama.value = response.data.value
    } catch (e) {
        console.error(e)
    }
}

onMounted(() => {
    fetchAgama()
})

// Form manual
const manualForm = ref({
    nama: '',
    nuptk: '',
    nip: '',
    nik: '',
    jenis_kelamin: 'L',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    alamat_jalan: '',
    rt: '',
    rw: '',
    desa_kelurahan: '',
    kecamatan: '',
    kodepos: '',
    telp_hp: '',
    email: '',
})

const manualErrors = ref({
    nama: '',
    nik: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    email: '',
})

const onSubmitManual = async () => {
    saving.value = true
    manualErrors.value = {
        nama: '',
        gelar_depan: '',
        gelar_belakang: '',
        nik: '',
        email: '',
    }
    
    const payload = {
        jenis_gtk: props.jenisGtk,
        sekolah_id: $user.sekolah_id,
        nama: manualForm.value.nama,
        gelar_depan: manualForm.value.gelar_depan,
        gelar_belakang: manualForm.value.gelar_belakang,
        nuptk: manualForm.value.nuptk,
        nip: manualForm.value.nip,
        nik: manualForm.value.nik,
        jenis_kelamin: manualForm.value.jenis_kelamin,
        tempat_lahir: manualForm.value.tempat_lahir,
        tanggal_lahir: manualForm.value.tanggal_lahir,
        agama: manualForm.value.agama,
        alamat_jalan: manualForm.value.alamat_jalan,
        rt: manualForm.value.rt,
        rw: manualForm.value.rw,
        desa_kelurahan: manualForm.value.desa_kelurahan,
        kecamatan: manualForm.value.kecamatan,
        kodepos: manualForm.value.kodepos,
        telp_hp: manualForm.value.telp_hp,
        email: manualForm.value.email,
    }
    
    try {
        await $api("/referensi/ptk/simpan-manual", {
            method: "POST",
            body: payload,
            onResponse({ response }) {
                let getData = response._data;
                saving.value = false
                if (getData.errors) {
                    manualErrors.value.nama = getData.errors['nama'] ? getData.errors['nama'][0] : ''
                    manualErrors.value.nik = getData.errors['nik'] ? getData.errors['nik'][0] : ''
                    manualErrors.value.email = getData.errors['email'] ? getData.errors['email'][0] : ''
                    manualErrors.value.jenis_kelamin = getData.errors['jenis_kelamin'] ? getData.errors['jenis_kelamin'][0] : ''
                    manualErrors.value.tanggal_lahir = getData.errors['tanggal_lahir'] ? getData.errors['tanggal_lahir'][0] : ''
                    manualErrors.value.tempat_lahir = getData.errors['tempat_lahir'] ? getData.errors['tempat_lahir'][0] : ''
                    manualErrors.value.agama = getData.errors['agama'] ? getData.errors['agama'][0] : ''
                } else {
                    updateModelValue(false)
                    notif.value = getData
                    isNotifVisible.value = true
                    // Reset form
                    manualForm.value = {
                        nama: '',
                        gelar_depan: '',
                        gelar_belakang: '',
                        nuptk: '',
                        nip: '',
                        nik: '',
                        jenis_kelamin: 'L',
                        tempat_lahir: '',
                        tanggal_lahir: '',
                        agama: '',
                        alamat_jalan: '',
                        rt: '',
                        rw: '',
                        desa_kelurahan: '',
                        kecamatan: '',
                        kodepos: '',
                        telp_hp: '',
                        email: '',
                    }
                }
            }
        })
    } catch (e) {
        saving.value = false
        console.error(e)
        const errorData = e.response?._data
        if (errorData && errorData.errors) {
            manualErrors.value.nama = errorData.errors['nama'] ? errorData.errors['nama'][0] : ''
            manualErrors.value.nik = errorData.errors['nik'] ? errorData.errors['nik'][0] : ''
            manualErrors.value.email = errorData.errors['email'] ? errorData.errors['email'][0] : ''
            manualErrors.value.jenis_kelamin = errorData.errors['jenis_kelamin'] ? errorData.errors['jenis_kelamin'][0] : ''
            manualErrors.value.tanggal_lahir = errorData.errors['tanggal_lahir'] ? errorData.errors['tanggal_lahir'][0] : ''
            manualErrors.value.tempat_lahir = errorData.errors['tempat_lahir'] ? errorData.errors['tempat_lahir'][0] : ''
            manualErrors.value.agama = errorData.errors['agama'] ? errorData.errors['agama'][0] : ''
        } else {
            notif.value = {
                color: 'error',
                title: 'Gagal!',
                text: errorData?.message || 'Terjadi kesalahan pada server saat menyimpan data.'
            }
            isNotifVisible.value = true
        }
    }
}

// Excel Upload
const uploading = ref(false)
const template_excel = ref(null)
const errors = ref({
    template_excel: null,
    nama: {},
    nik: {},
    jenis_kelamin: {},
    tempat_lahir: {},
    tanggal_lahir: {},
    agama: {},
    email: {},
})
const importedData = ref([])
const onFileChange = async (val) => {
    uploading.value = true
    const data = new FormData();
    data.append('template_excel', val);
    await $api("/referensi/ptk/upload", {
        method: "POST",
        body: data,
        onResponse({ response }) {
            let getData = response._data;
            uploading.value = false
            template_excel.value = null
            importedData.value = getData.imported_data
            if (getData.imported_data.length) {
                isDialogPtk.value = true
                updateModelValue(false)
            }
        }
    })
}

const isDialogPtk = ref(false)
const saving = ref(false)
const form = ref({
    nama: {},
    nuptk: {},
    nip: {},
    nik: {},
    jenis_kelamin: {},
    tempat_lahir: {},
    tanggal_lahir: {},
    agama: {},
    alamat_jalan: {},
    rt: {},
    rw: {},
    desa_kelurahan: {},
    kecamatan: {},
    kodepos: {},
    telp_hp: {},
    email: {},
})
const isNotifVisible = ref(false)
const notif = ref({
    color: null,
    title: null,
    text: null,
})
const onSubmit = async () => {
    const defaultForm = { jenis_gtk: props.jenisGtk, sekolah_id: $user.sekolah_id }
    importedData.value.forEach(el => {
        form.value.nama[el.no] = el.nama
        form.value.nuptk[el.no] = el.nuptk
        form.value.nip[el.no] = el.nip
        form.value.nik[el.no] = el.nik
        form.value.jenis_kelamin[el.no] = el.jenis_kelamin
        form.value.tempat_lahir[el.no] = el.tempat_lahir
        form.value.tanggal_lahir[el.no] = el.tanggal_lahir
        form.value.agama[el.no] = el.agama
        form.value.alamat_jalan[el.no] = el.alamat_jalan
        form.value.rt[el.no] = el.rt
        form.value.rw[el.no] = el.rw
        form.value.desa_kelurahan[el.no] = el.desa_kelurahan
        form.value.kecamatan[el.no] = el.kecamatan
        form.value.kodepos[el.no] = el.kodepos
        form.value.telp_hp[el.no] = el.telp_hp
        form.value.email[el.no] = el.email
    })
    const mergedForm = { ...defaultForm, ...form.value };
    await $api("/referensi/ptk/simpan", {
        method: "POST",
        body: mergedForm,
        onResponse({ response }) {
            let getData = response._data;
            saving.value = false
            if (getData.errors) {
                importedData.value.forEach(el => {
                    errors.value.nama[el.no] = getData.errors['nama.' + el.no]
                    errors.value.nik[el.no] = getData.errors['nik.' + el.no]
                    errors.value.jenis_kelamin[el.no] = getData.errors['jenis_kelamin.' + el.no]
                    errors.value.tempat_lahir[el.no] = getData.errors['tempat_lahir.' + el.no]
                    errors.value.tanggal_lahir[el.no] = getData.errors['tanggal_lahir.' + el.no]
                    errors.value.agama[el.no] = getData.errors['agama.' + el.no]
                    errors.value.email[el.no] = getData.errors['email.' + el.no]
                })
            } else {
                isDialogPtk.value = false
                notif.value = getData
                isNotifVisible.value = true
                errors.value = {
                    template_excel: null,
                    nama: {},
                    nik: {},
                    jenis_kelamin: {},
                    tempat_lahir: {},
                    tanggal_lahir: {},
                    agama: {},
                    email: {},
                }
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
    <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" max-width="750">
        <DialogCloseBtn @click="updateModelValue(false)" v-if="!uploading && !saving" />
        <VCard style="max-height: 90vh; display: flex; flex-direction: column;">
            <VCardItem class="pb-3 flex-shrink-0">
                <VCardTitle>{{ cardTitle }}</VCardTitle>
            </VCardItem>
            
            <VTabs v-model="activeTab" class="px-4 flex-shrink-0">
                <VTab value="manual">Tambah Manual</VTab>
                <VTab value="excel">Unggah Template Excel</VTab>
            </VTabs>
            <VDivider class="flex-shrink-0" />

            <div style="overflow-y: auto; flex-grow: 1;">
                <VWindow v-model="activeTab">
                    <!-- TAB TAMBAH MANUAL -->
                    <VWindowItem value="manual">
                        <VForm @submit.prevent="onSubmitManual">
                            <VCardText class="pt-5">
                                <VRow>
                                    <VCol cols="12" md="12">
                                        <AppTextField v-model="manualForm.nama" label="Nama Lengkap (Tanpa Gelar)" placeholder="Nama Lengkap" :error-messages="manualErrors.nama" required />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.gelar_depan" label="Gelar Depan" placeholder="Contoh: Drs., Ir." :error-messages="manualErrors.gelar_depan" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.gelar_belakang" label="Gelar Belakang" placeholder="Contoh: S.Pd., M.T." :error-messages="manualErrors.gelar_belakang" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nuptk" label="NUPTK" placeholder="NUPTK" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nik" label="NIK" placeholder="NIK (16 digit)" :error-messages="manualErrors.nik" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.nip" label="NIP" placeholder="NIP" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.jenis_kelamin" :items="[
                                            { title: 'Laki-Laki', value: 'L' },
                                            { title: 'Perempuan', value: 'P' }
                                        ]" item-title="title" item-value="value" label="Jenis Kelamin" :error-messages="manualErrors.jenis_kelamin" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppSelect v-model="manualForm.agama" :items="listAgama.map(a => a.nama)" label="Agama" :error-messages="manualErrors.agama" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.tempat_lahir" label="Tempat Lahir" placeholder="Tempat Lahir" :error-messages="manualErrors.tempat_lahir" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.tanggal_lahir" type="date" label="Tanggal Lahir" :error-messages="manualErrors.tanggal_lahir" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.email" type="email" label="Email" placeholder="Email" :error-messages="manualErrors.email" />
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <AppTextField v-model="manualForm.telp_hp" label="No HP/Telepon" placeholder="No HP/Telepon" />
                                    </VCol>
                                    <VCol cols="12">
                                        <VDivider class="my-2" />
                                        <h4 class="text-body-1 font-weight-medium mb-3">Detail Alamat Tempat Tinggal</h4>
                                    </VCol>
                                    <VCol cols="12" md="8">
                                        <AppTextField v-model="manualForm.alamat_jalan" label="Alamat Jalan" placeholder="Alamat Jalan" />
                                    </VCol>
                                    <VCol cols="6" md="2">
                                        <AppTextField v-model="manualForm.rt" label="RT" placeholder="RT" />
                                    </VCol>
                                    <VCol cols="6" md="2">
                                        <AppTextField v-model="manualForm.rw" label="RW" placeholder="RW" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.desa_kelurahan" label="Desa/Kelurahan" placeholder="Desa/Kelurahan" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.kecamatan" label="Kecamatan" placeholder="Kecamatan" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <AppTextField v-model="manualForm.kodepos" label="Kodepos" placeholder="Kodepos" />
                                    </VCol>
                                </VRow>
                            </VCardText>
                            <VDivider />
                            <VCardText class="d-flex justify-end gap-3">
                                <VBtn color="secondary" variant="tonal" @click="updateModelValue(false)" :disabled="saving">Batal</VBtn>
                                <VBtn type="submit" :loading="saving" :disabled="saving">Simpan Data</VBtn>
                            </VCardText>
                        </VForm>
                    </VWindowItem>

                    <!-- TAB UNGGAH EXCEL -->
                    <VWindowItem value="excel">
                        <VCardText class="pt-5">
                            <VRow>
                                <VCol cols="12">
                                    <VRow no-gutters>
                                        <VCol cols="12" md="3" class="d-flex align-items-center">
                                            <label class="v-label text-body-2 text-high-emphasis">Unduh Template</label>
                                        </VCol>
                                        <VCol cols="12" md="9">
                                            <VBtn block color="warning" :href="`/excel/format_excel_${props.jenisGtk}.xlsx`"
                                                target="_blank">
                                                Unduh Template</VBtn>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol cols="12" class="mt-4">
                                    <VRow no-gutters>
                                        <VCol cols="12" md="3" class="d-flex align-items-center">
                                            <label class="v-label text-body-2 text-high-emphasis">Unggah
                                                Template</label>
                                        </VCol>
                                        <VCol cols="12" md="9">
                                            <VFileInput id="template_excel" v-model="template_excel"
                                                :error-messages="errors.template_excel" @update:model-value="onFileChange"
                                                accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                                label="Unggah Template Excel" />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCardText>
                    </VWindowItem>
                </VWindow>
            </div>

            <VOverlay v-model="uploading" contained persistent scroll-strategy="none"
                class="align-center justify-center">
                <VProgressCircular indeterminate />
            </VOverlay>
        </VCard>
    </VDialog>

    <!-- Dialog Pratinjau Excel -->
    <VDialog v-model="isDialogPtk" fullscreen :scrim="false" transition="dialog-bottom-transition">
        <VCard>
            <VToolbar color="primary" class="sticky-header">
                <VBtn icon variant="plain" @click="isDialogPtk = !isDialogPtk">
                    <VIcon color="white" icon="tabler-x" />
                </VBtn>
                <VToolbarTitle>{{ cardTitle }}</VToolbarTitle>
                <VSpacer />
                <VToolbarItems>
                    <VBtn variant="text" @click="onSubmit">
                        <VIcon icon="tabler-device-floppy" class="me-2" /> Simpan
                    </VBtn>
                </VToolbarItems>
            </VToolbar>
            <VTable class="permission-table mb-6">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">NUPTK</th>
                        <th class="text-center">NIP</th>
                        <th class="text-center">NIK</th>
                        <th class="text-center">L/P</th>
                        <th class="text-center">Tempat Lahir</th>
                        <th class="text-center">Tanggal Lahir</th>
                        <th class="text-center">Agama</th>
                        <th class="text-center">Alamat Jalan</th>
                        <th class="text-center">RT</th>
                        <th class="text-center">RW</th>
                        <th class="text-center">Desa/Kelurahan</th>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Kodepos</th>
                        <th class="text-center">Telp/HP</th>
                        <th class="text-center">Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in importedData">
                        <td class="text-center">{{ item.no }}</td>
                        <td>
                            <AppTextField v-model="item.nama" placeholder="nama" style="inline-size: 15.625rem;"
                                :error-messages="errors.nama[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.nuptk" placeholder="nuptk" style="inline-size: 11rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.nip" placeholder="nip" style="inline-size: 11rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.nik" placeholder="nik" style="inline-size: 11rem;"
                                :error-messages="errors.nik[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.jenis_kelamin" placeholder="L/P" style="inline-size: 3rem;"
                                :error-messages="errors.jenis_kelamin[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.tempat_lahir" placeholder="tempat_lahir"
                                style="inline-size: 9rem;" :error-messages="errors.tempat_lahir[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.tanggal_lahir" placeholder="tanggal_lahir"
                                style="inline-size: 7rem;" :error-messages="errors.tanggal_lahir[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.agama" placeholder="agama" style="inline-size: 5rem;"
                                :error-messages="errors.agama[item.no]" />
                        </td>
                        <td>
                            <AppTextField v-model="item.alamat_jalan" placeholder="alamat_jalan"
                                style="inline-size: 10rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.rt" placeholder="RT" style="inline-size: 3rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.rw" placeholder="RW" style="inline-size: 3rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.desa_kelurahan" placeholder="desa_kelurahan"
                                style="inline-size: 10rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.kecamatan" placeholder="kecamatan"
                                style="inline-size: 10rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.kodepos" placeholder="kodepos" style="inline-size: 5rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.telp_hp" placeholder="telp_hp" style="inline-size: 10rem;" />
                        </td>
                        <td>
                            <AppTextField v-model="item.email" placeholder="email" style="inline-size: 15rem;"
                                :error-messages="errors.email[item.no]" />
                        </td>
                    </tr>
                </tbody>
            </VTable>
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

<style lang="scss">
.permission-table {
    td {
        border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
        padding-block: 0.5rem;

        &:not(:first-child) {
            padding-inline: 0.5rem;
        }

        .v-label {
            white-space: nowrap;
        }
    }
}

.sticky-header {
    position: sticky !important;
    top: 0;
    z-index: 1;
}
</style>
