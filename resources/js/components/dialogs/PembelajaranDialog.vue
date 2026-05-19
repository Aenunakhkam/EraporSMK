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
  form: {
    type: Object,
    required: true,
  },
  listData: {
    type: Array,
    required: true,
  },
  listGuru: {
    type: Array,
    required: true,
  },
  listKelompok: {
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
}
const emit = defineEmits([
  'update:isDialogVisible',
  'save',
  'refresh'
])
const onSubmit = async () => {
  emit('save')
}
const loadings = ref([])
const hapus = async (pembelajaran_id) => {
  loadings.value[pembelajaran_id] = true
  await $api('/referensi/rombongan-belajar/hapus-pembelajaran', {
    method: 'POST',
    body: {
      pembelajaran_id: pembelajaran_id
    },
    onResponse() {
      loadings.value[pembelajaran_id] = false
      emit('refresh')
    }
  })
}

// Tambah Mapel Baru
const listMapel = ref([])
const loadingMapel = ref(false)
const addingMapel = ref(false)
const newPembelajaran = ref({
  mata_pelajaran_id: null,
  guru_pengajar_id: null,
  kelompok_id: null,
  no_urut: '1',
})

const isNotifVisible = ref(false)
const notif = ref({ color: '', title: '', text: '' })

const fetchMapel = async () => {
  loadingMapel.value = true
  try {
    const response = await useApi(createUrl('/referensi/rombongan-belajar/list-mapel'))
    listMapel.value = response.data.value
  } catch (e) {
    console.error(e)
  } finally {
    loadingMapel.value = false
  }
}

watch(() => props.isDialogVisible, (newVal) => {
  if (newVal) {
    fetchMapel()
    newPembelajaran.value = {
      mata_pelajaran_id: null,
      guru_pengajar_id: null,
      kelompok_id: null,
      no_urut: '1',
    }
  }
})

const tambahMapel = async () => {
  if (!newPembelajaran.value.mata_pelajaran_id || !newPembelajaran.value.guru_pengajar_id || !newPembelajaran.value.kelompok_id) return
  addingMapel.value = true
  try {
    await $api('/referensi/rombongan-belajar/tambah-pembelajaran', {
      method: 'POST',
      body: {
        rombongan_belajar_id: props.rombonganBelajarId,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        ...newPembelajaran.value,
      },
      onResponse({ response }) {
        let getData = response._data
        addingMapel.value = false
        notif.value = getData
        isNotifVisible.value = true
        newPembelajaran.value = {
          mata_pelajaran_id: null,
          guru_pengajar_id: null,
          kelompok_id: null,
          no_urut: '1',
        }
      }
    })
  } catch (e) {
    addingMapel.value = false
    console.error(e)
  }
}

const confirmClose = () => {
  isNotifVisible.value = false
  emit('refresh')
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
          <VBtn variant="text" @click="onSubmit">
            <VIcon icon="tabler-device-floppy" class="me-2"></VIcon> Simpan
          </VBtn>
        </VToolbarItems>
      </VToolbar>

      <VCardText class="d-flex align-center gap-4 py-4 flex-shrink-0 bg-light-primary border-bottom">
         <div class="text-h6 font-weight-bold text-primary">Daftar Pembelajaran / Mata Pelajaran</div>
         <VSpacer />
         <div class="d-flex align-center gap-3 flex-wrap justify-end" style="max-inline-size: 80%;">
            <AppAutocomplete
              v-model="newPembelajaran.mata_pelajaran_id"
              :items="listMapel"
              item-title="nama"
              item-value="mata_pelajaran_id"
              placeholder="Pilih Mata Pelajaran..."
              :loading="loadingMapel"
              hide-details
              style="inline-size: 16rem;"
            />
            <AppAutocomplete
              v-model="newPembelajaran.guru_pengajar_id"
              :items="listGuru"
              item-title="nama_lengkap"
              item-value="guru_id"
              placeholder="Pilih Guru..."
              hide-details
              style="inline-size: 14rem;"
            />
            <AppAutocomplete
              v-model="newPembelajaran.kelompok_id"
              :items="listKelompok"
              item-title="nama_kelompok"
              item-value="kelompok_id"
              placeholder="Kelompok..."
              hide-details
              style="inline-size: 10rem;"
            />
            <AppTextField
              v-model="newPembelajaran.no_urut"
              placeholder="No Urut"
              hide-details
              style="inline-size: 5rem;"
            />
            <VBtn color="primary" prepend-icon="tabler-plus" :loading="addingMapel" :disabled="!newPembelajaran.mata_pelajaran_id || !newPembelajaran.guru_pengajar_id || !newPembelajaran.kelompok_id" @click="tambahMapel">
               Tambah Mapel
            </VBtn>
         </div>
      </VCardText>

      <VTable class="permission-table mb-6">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th class="text-center">Mata Pelajaran</th>
            <th class="text-center">ID Mapel</th>
            <th class="text-center">Guru Mapel (Dapodik)</th>
            <th class="text-center">Guru Pengajar</th>
            <th class="text-center">Kelompok</th>
            <th class="text-center">Nomor Urut</th>
            <th class="text-center">Reset</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="isLoading">
            <tr>
              <td class="text-center" colspan="8">
                <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
              </td>
            </tr>
          </template>
          <template v-else-if="listData.length">
            <tr v-for="(item, index) in listData" :class="{ 'bg-light-warning': item.induk_pembelajaran_id }">
              <td class="text-center">{{ index + 1 }}</td>
              <td>
                <AppTextField style="inline-size: 15.5rem;" v-model="form.nama[item.pembelajaran_id]" />
              </td>
              <td>
                <AppTextField style="inline-size: 7rem;" v-model="item.mata_pelajaran_id" disabled />
              </td>
              <td>
                <AppSelect v-model="item.guru_id" :items="listGuru" item-title="nama_lengkap" item-value="guru_id"
                  disabled>
                  <template #selection="{ item }">
                    <VChip>
                      <template #prepend>
                        <VAvatar start :image="item.raw.photo" />
                      </template>

                      <span>{{ item.raw.nama_lengkap }}</span>
                    </VChip>
                  </template>
                </AppSelect>
              </td>
              <td>
                <AppAutocomplete style="inline-size: 15.5rem;" :items="listGuru" placeholder="== Pilih Guru Pengajar =="
                  item-title="nama_lengkap" item-value="guru_id" v-model="form.guru_pengajar_id[item.pembelajaran_id]"
                  clearable />
              </td>
              <td>
                <AppAutocomplete :items="listKelompok" placeholder="== Pilih Kelompok ==" item-title="nama_kelompok"
                  item-value="kelompok_id" v-model="form.kelompok_id[item.pembelajaran_id]" clearable />
              </td>
              <td class="text-center">
                <AppTextField style="inline-size: 4rem;" v-model="form.no_urut[item.pembelajaran_id]" />
              </td>
              <td class="text-center">
                <VBtn :loading="loadings[item.pembelajaran_id]" :disabled="loadings[item.pembelajaran_id]" color="error"
                  icon="tabler-trash" @click="hapus(item.pembelajaran_id)" v-if="item.kelompok_id && item.no_urut" />
              </td>
            </tr>
          </template>
          <template v-else>
            <tr>
              <td class="text-center" colspan="8">Tidak ada untuk ditampilkan</td>
            </tr>
          </template>
        </tbody>
      </VTable>
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
        <VBtn color="success" @click="confirmClose">Ok</VBtn>
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
