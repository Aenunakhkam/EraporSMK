<script setup>
const props = defineProps({
    isDialogVisible: { type: Boolean, required: true },
})
const emit = defineEmits(['update:isDialogVisible', 'close'])
const updateModelValue = val => emit('update:isDialogVisible', val)

const form = ref({ mata_pelajaran_id: '', nama: '' })
const errors = ref({ mata_pelajaran_id: '', nama: '' })
const saving = ref(false)

const isNotifVisible = ref(false)
const notif = ref({ color: null, title: null, text: null })

const onSubmit = async () => {
    saving.value = true
    errors.value = { mata_pelajaran_id: '', nama: '' }

    await $api('/referensi/mata-pelajaran/simpan', {
        method: 'POST',
        body: { ...form.value },
        onResponse({ response }) {
            const getData = response._data
            saving.value = false
            if (getData.errors) {
                errors.value.mata_pelajaran_id = getData.errors['mata_pelajaran_id'] ? getData.errors['mata_pelajaran_id'][0] : ''
                errors.value.nama = getData.errors['nama'] ? getData.errors['nama'][0] : ''
            } else {
                updateModelValue(false)
                notif.value = getData
                isNotifVisible.value = true
                form.value = { mata_pelajaran_id: '', nama: '' }
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
    <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" max-width="550">
        <DialogCloseBtn @click="updateModelValue(false)" v-if="!saving" />
        <VCard>
            <VCardItem class="pb-3">
                <VCardTitle>Tambah Mata Pelajaran</VCardTitle>
            </VCardItem>
            <VDivider />

            <VForm @submit.prevent="onSubmit">
                <VCardText class="pt-5">
                    <VRow>
                        <VCol cols="12">
                            <AppTextField
                                v-model="form.mata_pelajaran_id"
                                label="ID Mata Pelajaran *"
                                placeholder="Contoh: 100001000"
                                :error-messages="errors.mata_pelajaran_id"
                                required
                            />
                            <p class="text-caption text-medium-emphasis mt-1">
                                ID unik mata pelajaran, sesuaikan dengan kode nasional Dapodik atau buat kode lokal sekolah.
                            </p>
                        </VCol>
                        <VCol cols="12">
                            <AppTextField
                                v-model="form.nama"
                                label="Nama Mata Pelajaran *"
                                placeholder="Contoh: Bahasa Indonesia"
                                :error-messages="errors.nama"
                                required
                            />
                        </VCol>
                    </VRow>
                </VCardText>
                <VDivider />
                <VCardText class="d-flex justify-end gap-3">
                    <VBtn color="secondary" variant="tonal" @click="updateModelValue(false)" :disabled="saving">Batal</VBtn>
                    <VBtn type="submit" :loading="saving" :disabled="saving">Simpan Mata Pelajaran</VBtn>
                </VCardText>
            </VForm>
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
