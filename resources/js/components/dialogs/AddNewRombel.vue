<script setup>
const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
    jenisRombel: { type: Number, required: true },
    editData: { type: Object, default: null },
})
const emit = defineEmits(['update:isDialogVisible', 'close'])
const updateModelValue = val => emit('update:isDialogVisible', val)

const listJurusan = ref([])
const listKurikulum = ref([])
const listGuru = ref([])
const loadingRefs = ref(false)

const fetchReferensi = async () => {
    loadingRefs.value = true
    try {
        const response = await useApi(createUrl('/referensi/rombongan-belajar/referensi', {
            query: {
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            }
        }))
        const data = response.data.value
        listJurusan.value = data.jurusan_sp
        listKurikulum.value = data.kurikulum
        listGuru.value = data.guru
    } catch (e) {
        console.error(e)
    } finally {
        loadingRefs.value = false
    }
}

onMounted(() => fetchReferensi())

const form = ref({
    rombongan_belajar_id: null,
    nama: '',
    tingkat: '',
    guru_id: null,
    jurusan_sp_id: null,
    kurikulum_id: null,
})

watch(() => props.isDialogVisible, (newVal) => {
    if (newVal) {
        if (props.editData) {
            form.value = {
                rombongan_belajar_id: props.editData.rombongan_belajar_id,
                nama: props.editData.nama,
                tingkat: (props.editData.tingkat === 10) ? 'X' : ((props.editData.tingkat === 11) ? 'XI' : 'XII'),
                guru_id: props.editData.wali_kelas?.guru_id || props.editData.guru_id,
                jurusan_sp_id: props.editData.jurusan_sp?.jurusan_sp_id || props.editData.jurusan_sp_id,
                kurikulum_id: props.editData.kurikulum?.kurikulum_id || props.editData.kurikulum_id,
            }
        } else {
            form.value = { rombongan_belajar_id: null, nama: '', tingkat: '', guru_id: null, jurusan_sp_id: null, kurikulum_id: null }
        }
    }
})

const errors = ref({ nama: '', tingkat: '', guru_id: '', kurikulum_id: '' })
const saving = ref(false)

const isNotifVisible = ref(false)
const notif = ref({ color: null, title: null, text: null })

const onSubmit = async () => {
    saving.value = true
    errors.value = { nama: '', tingkat: '', guru_id: '', kurikulum_id: '' }

    try {
        const url = form.value.rombongan_belajar_id ? '/referensi/rombongan-belajar/update' : '/referensi/rombongan-belajar/simpan'
        await $api(url, {
            method: 'POST',
            body: {
                ...form.value,
                jenis_rombel: props.jenisRombel,
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            },
            onResponse({ response }) {
                const getData = response._data
                saving.value = false
                if (getData.errors) {
                    errors.value.nama = getData.errors['nama'] ? getData.errors['nama'][0] : ''
                    errors.value.tingkat = getData.errors['tingkat'] ? getData.errors['tingkat'][0] : ''
                    errors.value.guru_id = getData.errors['guru_id'] ? getData.errors['guru_id'][0] : ''
                    errors.value.kurikulum_id = getData.errors['kurikulum_id'] ? getData.errors['kurikulum_id'][0] : ''
                } else {
                    updateModelValue(false)
                    notif.value = getData
                    isNotifVisible.value = true
                    form.value = { rombongan_belajar_id: null, nama: '', tingkat: '', guru_id: null, jurusan_sp_id: null, kurikulum_id: null }
                }
            }
        })
    } catch (e) {
        saving.value = false
        console.error(e)
        const errorData = e.response?._data
        if (errorData && errorData.errors) {
            errors.value.nama = errorData.errors['nama'] ? errorData.errors['nama'][0] : ''
            errors.value.tingkat = errorData.errors['tingkat'] ? errorData.errors['tingkat'][0] : ''
            errors.value.guru_id = errorData.errors['guru_id'] ? errorData.errors['guru_id'][0] : ''
            errors.value.kurikulum_id = errorData.errors['kurikulum_id'] ? errorData.errors['kurikulum_id'][0] : ''
        } else {
            notif.value = {
                color: 'error',
                title: 'Gagal!',
                text: errorData?.message || 'Terjadi kesalahan pada server saat menyimpan kelas.'
            }
            isNotifVisible.value = true
        }
    }
}

const onConfirmation = () => {
    emit('close')
    isNotifVisible.value = false
}
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" max-width="600">
        <DialogCloseBtn @click="updateModelValue(false)" v-if="!saving" />
        <VCard style="max-height: 90vh; display: flex; flex-direction: column;">
            <VCardItem class="pb-3 flex-shrink-0">
                <VCardTitle>{{ form.rombongan_belajar_id ? 'Ubah Kelas (Rombongan Belajar)' : 'Tambah Kelas (Rombongan Belajar)' }}</VCardTitle>
            </VCardItem>
            <VDivider class="flex-shrink-0" />

            <div style="overflow-y: auto; flex-grow: 1;">
                <VForm @submit.prevent="onSubmit">
                    <VCardText class="pt-5">
                        <VRow>
                            <VCol cols="12">
                                <AppTextField
                                    v-model="form.nama"
                                    label="Nama Kelas *"
                                    placeholder="Contoh: XII PPLG 1"
                                    :error-messages="errors.nama"
                                    required
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <AppSelect
                                    v-model="form.tingkat"
                                    :items="['X', 'XI', 'XII']"
                                    label="Tingkat *"
                                    placeholder="Pilih Tingkat"
                                    :error-messages="errors.tingkat"
                                    required
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <AppSelect
                                    v-model="form.guru_id"
                                    :items="listGuru"
                                    item-title="nama"
                                    item-value="guru_id"
                                    label="Wali Kelas *"
                                    placeholder="Pilih Wali Kelas"
                                    :error-messages="errors.guru_id"
                                    required
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <AppSelect
                                    v-model="form.jurusan_sp_id"
                                    :items="listJurusan"
                                    item-title="nama_jurusan_sp"
                                    item-value="jurusan_sp_id"
                                    label="Program/Kompetensi Keahlian (Opsional)"
                                    placeholder="Pilih Jurusan"
                                    clearable
                                />
                            </VCol>
                            <VCol cols="12" md="6">
                                <AppSelect
                                    v-model="form.kurikulum_id"
                                    :items="listKurikulum"
                                    item-title="nama_kurikulum"
                                    item-value="kurikulum_id"
                                    label="Kurikulum *"
                                    placeholder="Pilih Kurikulum"
                                    :error-messages="errors.kurikulum_id"
                                    required
                                />
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />
                    <VCardText class="d-flex justify-end gap-3">
                        <VBtn color="secondary" variant="tonal" @click="updateModelValue(false)" :disabled="saving">Batal</VBtn>
                        <VBtn type="submit" :loading="saving" :disabled="saving">{{ form.rombongan_belajar_id ? 'Perbaharui Kelas' : 'Simpan Kelas' }}</VBtn>
                    </VCardText>
                </VForm>
            </div>
        </VCard>
    </VDialog>

    <!-- Dialog Notifikasi -->
    <VDialog v-model="isNotifVisible" max-width="500">
        <VCard>
            <VCardText class="text-center px-10 py-6">
                <VBtn icon variant="outlined" :color="notif.color" class="my-4"
                    style="block-size: 88px; inline-size: 88px; pointer-events: none;">
                    <VIcon :icon="(notif.color == 'success') ? 'tabler-checks' : 'tabler-xbox-x'" size="38" />
                </VBtn>
                <h1 class="text-h4 mb-4">{{ notif.title }}</h1>
                <p>{{ notif.text }}</p>
                <VBtn color="success" @click="onConfirmation">Ok</VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
