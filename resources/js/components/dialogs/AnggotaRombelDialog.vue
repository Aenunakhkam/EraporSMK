<script setup>
const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  isLoading: {
    type: Boolean,
    required: true,
  },
  dialogTitle: {
    type: String,
    required: true,
  },
  listData: {
    type: Array,
    required: true,
  },
  rombonganBelajarId: {
    type: String,
    required: false,
  },
})
const updateModelValue = val => {
  emit('update:isDialogVisible', val)
  emit('close')
}
const emit = defineEmits([
  'update:isDialogVisible',
  'refresh',
  'close'
])
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: '',
  title: '',
  text: '',
})
const loadings = ref([])
const anggotaRombelId = ref()
const deleteData = ref()
const hapus = async (anggota_rombel_id, data) => {
  anggotaRombelId.value = anggota_rombel_id
  deleteData.value = data
  isConfirmDialogVisible.value = true
}
const confirmDialog = async () => {
  loadings.value[anggotaRombelId.value] = true
  await $api('/referensi/rombongan-belajar/hapus-anggota-rombel', {
    method: 'POST',
    body: {
      anggota_rombel_id: anggotaRombelId.value,
      data: deleteData.value,
    },
    onResponse({ request, response, options }) {
      let getData = response._data
      loadings.value[anggotaRombelId.value] = false
      notif.value = getData
      isNotifVisible.value = true
    }
  })
}
const confirmClose = async () => {
  isNotifVisible.value = false
  setTimeout(() => {
    notif.value = {
      color: '',
      title: '',
      text: '',
    }
  }, 300)
  emit('refresh')
}

// Tambah Siswa Baru
const listSiswa = ref([])
const loadingSiswa = ref(false)
const selectedPdId = ref(null)
const addingSiswa = ref(false)

const fetchSiswa = async () => {
  loadingSiswa.value = true
  try {
    const response = await useApi(createUrl('/referensi/rombongan-belajar/list-siswa', {
      query: {
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
      }
    }))
    listSiswa.value = response.data.value.map(item => {
      let currentRombel = item.anggota_rombel?.rombongan_belajar?.nama
      return {
        peserta_didik_id: item.peserta_didik_id,
        nama: item.nama + (currentRombel ? ` (Aktif di Kelas: ${currentRombel})` : ' (Belum ada kelas)'),
      }
    })
  } catch (e) {
    console.error(e)
  } finally {
    loadingSiswa.value = false
  }
}

watch(() => props.isDialogVisible, (newVal) => {
  if (newVal) {
    fetchSiswa()
    selectedPdId.value = null
  }
})

const tambahSiswa = async () => {
  if (!selectedPdId.value) return
  addingSiswa.value = true
  try {
    await $api('/referensi/rombongan-belajar/tambah-anggota', {
      method: 'POST',
      body: {
        rombongan_belajar_id: props.rombonganBelajarId,
        peserta_didik_id: selectedPdId.value,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
      },
      onResponse({ response }) {
        let getData = response._data
        addingSiswa.value = false
        notif.value = getData
        isNotifVisible.value = true
        selectedPdId.value = null
      }
    })
  } catch (e) {
    addingSiswa.value = false
    console.error(e)
  }
}
</script>

<template>
  <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" fullscreen :scrim="false"
    transition="dialog-bottom-transition">
    <VCard>
      <VToolbar color="primary" class="sticky-header">
        <VBtn icon variant="plain" @click="updateModelValue(false)">
          <VIcon color="white" icon="tabler-x" />
        </VBtn>
        <VToolbarTitle>{{ dialogTitle }}</VToolbarTitle>
        <VSpacer />
        <VToolbarItems>
          <VBtn variant="text" @click="updateModelValue(false)">
            <VIcon icon="tabler-x" class="me-2"></VIcon> Tutup
          </VBtn>
        </VToolbarItems>
      </VToolbar>
      
      <VCardText class="d-flex align-center gap-4 py-4 flex-shrink-0 bg-light-primary border-bottom">
         <div class="text-h6 font-weight-bold text-primary">Daftar Anggota Kelas</div>
         <VSpacer />
         <div class="d-flex align-center gap-3" style="inline-size: 32rem;">
            <AppAutocomplete
              v-model="selectedPdId"
              :items="listSiswa"
              item-title="nama"
              item-value="peserta_didik_id"
              placeholder="Cari & Pilih Siswa..."
              :loading="loadingSiswa"
              hide-details
              style="flex-grow: 1;"
            />
            <VBtn color="primary" prepend-icon="tabler-plus" :loading="addingSiswa" :disabled="!selectedPdId" @click="tambahSiswa">
               Tambah Siswa
            </VBtn>
         </div>
      </VCardText>

      <VTable class="permission-table mb-6">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Nama</th>
            <th class="text-center">NISN</th>
            <th class="text-center">L/P</th>
            <th>Tempat, Tanggal Lahir</th>
            <th>Agama</th>
            <th class="text-center">Keluarkan</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="isLoading">
            <tr>
              <td class="text-center" colspan="7">
                <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
              </td>
            </tr>
          </template>
          <template v-else-if="listData.length">
            <tr v-for="(item, index) in listData">
              <td class="text-center">{{ index + 1 }}</td>
              <td>{{ item.nama }}</td>
              <td class="text-center">{{ item.nisn }}</td>
              <td class="text-center">{{ item.jenis_kelamin }}</td>
              <td>{{ item.tempat_tanggal_lahir }}</td>
              <td>{{ item.agama?.nama }}</td>
              <td class="text-center">
                <template v-if="item.anggota_akt_pd">
                  <VBtn :loading="loadings[item.anggota_akt_pd.anggota_akt_pd_id]"
                    :disabled="loadings[item.anggota_akt_pd.anggota_akt_pd_id]" color="error" icon="tabler-trash"
                    @click="hapus(item.anggota_akt_pd.anggota_akt_pd_id, 'prakerin')" />
                </template>
                <template v-else>
                  <VBtn :loading="loadings[item.anggota_rombel.anggota_rombel_id]"
                    :disabled="loadings[item.anggota_rombel.anggota_rombel_id]" color="error" icon="tabler-trash"
                    @click="hapus(item.anggota_rombel.anggota_rombel_id, 'rombel')" />
                </template>
              </td>
            </tr>
          </template>
          <template v-else>
            <tr>
              <td class="text-center" colspan="7">Tidak ada untuk ditampilkan</td>
            </tr>
          </template>
        </tbody>
      </VTable>
    </VCard>
  </VDialog>
  <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
    confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
    :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDialog"
    @close="confirmClose" />
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
