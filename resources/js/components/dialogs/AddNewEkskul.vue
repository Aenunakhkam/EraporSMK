<script setup>
const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
    editData: { type: Object, default: null },
})
const emit = defineEmits(['update:isDialogVisible', 'close'])
const updateModelValue = val => emit('update:isDialogVisible', val)

const listGuru = ref([])
const loadingRefs = ref(false)

const fetchReferensi = async () => {
    loadingRefs.value = true
    try {
        const response = await useApi(createUrl('/referensi/ekstrakurikuler/referensi', {
            query: {
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            }
        }))
        const data = response.data.value
        listGuru.value = data.guru
    } catch (e) {
        console.error(e)
    } finally {
        loadingRefs.value = false
    }
}

onMounted(() => fetchReferensi())

const form = ref({
    ekstrakurikuler_id: null,
    nama_ekskul: '',
    guru_id: null,
    alamat_ekskul: '',
})

watch(() => props.isDialogVisible, (newVal) => {
    if (newVal) {
        fetchReferensi()
        if (props.editData) {
            form.value = {
                ekstrakurikuler_id: props.editData.ekstrakurikuler_id,
                nama_ekskul: props.editData.nama_ekskul,
                guru_id: props.editData.guru?.guru_id || props.editData.guru_id,
                alamat_ekskul: props.editData.alamat_ekskul,
            }
        } else {
            form.value = { ekstrakurikuler_id: null, nama_ekskul: '', guru_id: null, alamat_ekskul: '' }
        }
    }
})

const errors = ref({ nama_ekskul: '', guru_id: '' })
const saving = ref(false)

const isNotifVisible = ref(false)
const notif = ref({ color: null, title: null, text: null })

const onSubmit = async () => {
    saving.value = true
    errors.value = { nama_ekskul: '', guru_id: '' }

    try {
        const url = form.value.ekstrakurikuler_id ? '/referensi/ekstrakurikuler/update' : '/referensi/ekstrakurikuler/simpan'
        await $api(url, {
            method: 'POST',
            body: {
                ...form.value,
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            },
            onResponse({ response }) {
                const getData = response._data
                saving.value = false
                if (getData.errors) {
                    errors.value.nama_ekskul = getData.errors['nama_ekskul'] ? getData.errors['nama_ekskul'][0] : ''
                    errors.value.guru_id = getData.errors['guru_id'] ? getData.errors['guru_id'][0] : ''
                } else {
                    updateModelValue(false)
                    notif.value = getData
                    isNotifVisible.value = true
                    form.value = { ekstrakurikuler_id: null, nama_ekskul: '', guru_id: null, alamat_ekskul: '' }
                }
            }
        })
    } catch (e) {
        saving.value = false
        console.error(e)
        const errorData = e.response?._data
        if (errorData && errorData.errors) {
            errors.value.nama_ekskul = errorData.errors['nama_ekskul'] ? errorData.errors['nama_ekskul'][0] : ''
            errors.value.guru_id = errorData.errors['guru_id'] ? errorData.errors['guru_id'][0] : ''
        } else {
            notif.value = {
                color: 'error',
                title: 'Gagal!',
                text: errorData?.message || 'Terjadi kesalahan pada server saat menyimpan ekstrakurikuler.'
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
                <VCardTitle>{{ form.ekstrakurikuler_id ? 'Ubah Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}</VCardTitle>
            </VCardItem>
            <VDivider class="flex-shrink-0" />

            <div style="overflow-y: auto; flex-grow: 1;">
                <VForm @submit.prevent="onSubmit">
                    <VCardText class="pt-5">
                        <VRow>
                            <VCol cols="12">
                                <AppTextField
                                    v-model="form.nama_ekskul"
                                    label="Nama Ekstrakurikuler *"
                                    placeholder="Contoh: Pramuka, Marching Band"
                                    :error-messages="errors.nama_ekskul"
                                    required
                                />
                            </VCol>
                            <VCol cols="12">
                                <AppSelect
                                    v-model="form.guru_id"
                                    :items="listGuru"
                                    item-title="nama_lengkap"
                                    item-value="guru_id"
                                    label="Pembina Ekstrakurikuler *"
                                    placeholder="Pilih Pembina/Guru"
                                    :error-messages="errors.guru_id"
                                    required
                                />
                            </VCol>
                            <VCol cols="12">
                                <AppTextField
                                    v-model="form.alamat_ekskul"
                                    label="Prasarana / Tempat Latihan"
                                    placeholder="Contoh: Lapangan Sekolah, Aula"
                                />
                            </VCol>
                        </VRow>
                    </VCardText>
                    <VDivider />
                    <VCardText class="d-flex justify-end gap-3">
                        <VBtn color="secondary" variant="tonal" @click="updateModelValue(false)" :disabled="saving">Batal</VBtn>
                        <VBtn type="submit" :loading="saving" :disabled="saving">{{ form.ekstrakurikuler_id ? 'Perbaharui' : 'Simpan' }}</VBtn>
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
