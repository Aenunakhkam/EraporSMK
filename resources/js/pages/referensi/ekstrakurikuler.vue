<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Administrator',
    title: 'Data Ekstrakurikuler',
  },
})

const options = ref({
  page: 1,
  itemsPerPage: 10,
  searchQuery: '',
  selectedRole: null,
  sortby: 'nama_ekskul',
  sortbydesc: 'ASC',
});
// Headers
const headers = [
  {
    title: 'Nama Ekstrakurikuler',
    key: 'nama_ekskul',
  },
  {
    title: 'Nama Pembina',
    key: 'guru',
    sortable: false,
  },
  {
    title: 'Prasarana',
    key: 'alamat_ekskul',
  },
  {
    title: 'Anggota Ekskul',
    key: 'anggota',
    align: 'center',
    sortable: false,
  },
  {
    title: 'Aksi',
    key: 'sinkron',
    align: 'center',
    sortable: false,
  },
]
const items = ref([])
const total = ref(0)
const loadingTable = ref(false)
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
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key
  options.value.sortbydesc = val[0]?.order
}
const fetchData = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(createUrl('/referensi/ekstrakurikuler', {
      query: {
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        q: options.value.searchQuery,
        page: options.value.page,
        per_page: options.value.itemsPerPage,
        sortby: options.value.sortby,
        sortbydesc: options.value.sortbydesc,
      },
    }));
    let getData = response.data
    items.value = getData.value.data.data
    total.value = getData.value.data.total
  } catch (error) {
    console.error(error);
  } finally {
    loadingTable.value = false;
  }
}
const showAnggota = ref(false)
const isLoading = ref(false)
const dialogTitle = ref('')
const anggotaRombel = ref([])
const rombonganBelajarId = ref()
const loadingAnggota = ref([])
const anggota = async (rombongan_belajar_id) => {
  rombonganBelajarId.value = rombongan_belajar_id
  loadingAnggota.value[rombongan_belajar_id] = true
  isLoading.value = true
  rombonganBelajarId.value = rombongan_belajar_id
  showAnggota.value = true
  await $api('/referensi/rombongan-belajar/anggota-rombel', {
    method: 'POST',
    body: {
      rombongan_belajar_id: rombonganBelajarId.value,
    },
    onResponse({ request, response, options }) {
      let getData = response._data
      dialogTitle.value = `Anggota Ekstrakurikuler Kelas ${getData.rombel.nama}`
      anggotaRombel.value = getData.data
      isLoading.value = false
      loadingAnggota.value[rombongan_belajar_id] = false
    }
  })
}
const loadingSinkron = ref([])
const sinkron = async (ekstrakurikuler_id) => {
  loadingSinkron.value[ekstrakurikuler_id] = true
  console.log(ekstrakurikuler_id);
}
const reFecthAnggota = async () => {
  anggota(rombonganBelajarId.value)
}

const isAddEkskulVisible = ref(false)
const selectedEkskul = ref(null)

const editEkskul = (item) => {
  selectedEkskul.value = item
  isAddEkskulVisible.value = true
}

const onEkskulAdded = () => {
  fetchData()
}

const isConfirmDialogVisible = ref(false)
const isAlertDialogVisible = ref(false)
const notif = ref({ color: null, title: null, text: null })

const hapusEkskul = (item) => {
  selectedEkskul.value = item
  isConfirmDialogVisible.value = true
}

const confirmDelete = async () => {
  await $api('/referensi/ekstrakurikuler/hapus', {
    method: 'POST',
    body: {
      ekstrakurikuler_id: selectedEkskul.value.ekstrakurikuler_id,
    },
    onResponse({ response }) {
      let getData = response._data
      notif.value = getData
      isAlertDialogVisible.value = true
    }
  })
}

const confirmClose = async () => {
  await fetchData()
}
</script>
<template>
  <section>
    <!-- 👉 Widgets -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Data Ekstrakurikuler</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText>
        <VRow>
          <VCol cols="12" md="4" class="d-flex align-items-center">
            <AppSelect v-model="options.itemsPerPage" :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]" />
          </VCol>
          <VCol cols="12" md="8" class="d-flex justify-end gap-4 align-center">
            <AppTextField v-model="options.searchQuery" placeholder="Cari Data" style="max-width: 250px;" />
            <VBtn prepend-icon="tabler-plus" @click="isAddEkskulVisible = true">Tambah Ekskul</VBtn>
          </VCol>
        </VRow>
      </VCardText>
      <VDivider />
      <!-- SECTION datatable -->
      <VDataTableServer class="text-no-wrap" :items="items" :server-items-length="total" :headers="headers"
        :options="options" :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
        <template #item.guru="{ item }">
          {{ item.guru?.nama_lengkap || '-' }}
        </template>
        <template #item.anggota="{ item }">
          <VBadge v-if="item.rombongan_belajar" :content="item.rombongan_belajar.anggota_rombel_count" color="success">
            <VBtn :loading="loadingAnggota[item.rombongan_belajar_id]"
              :disabled="loadingAnggota[item.rombongan_belajar_id]" @click="anggota(item.rombongan_belajar_id)"
              size="x-small">
              Detil
            </VBtn>
          </VBadge>
          <span v-else>-</span>
        </template>
        <template #item.sinkron="{ item }">
          <div class="d-flex gap-2 justify-center align-center">
            <VBtn icon variant="text" color="primary" size="small" @click="editEkskul(item)">
              <VIcon icon="tabler-edit" />
              <VTooltip activator="parent" location="top">Ubah Ekstrakurikuler</VTooltip>
            </VBtn>
            <VBtn icon variant="text" color="error" size="small" @click="hapusEkskul(item)">
              <VIcon icon="tabler-trash" />
              <VTooltip activator="parent" location="top">Hapus Ekstrakurikuler</VTooltip>
            </VBtn>
            <VBtn color="warning" @click="sinkron(item.ekstrakurikuler_id)" size="x-small"
              :loading="loadingSinkron[item.ekstrakurikuler_id]" :disabled="loadingSinkron[item.ekstrakurikuler_id]">
              <VIcon start icon="tabler-refresh" />Sinkron Anggta
            </VBtn>
          </div>
        </template>
        <!-- pagination -->
        <template #bottom>
          <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <AnggotaRombelDialog v-model:isDialogVisible="showAnggota" v-model:isLoading="isLoading" :dialog-title="dialogTitle"
      v-model:listData="anggotaRombel" :rombongan-belajar-id="rombonganBelajarId" @refresh="reFecthAnggota" />

    <AddNewEkskul v-model:is-dialog-visible="isAddEkskulVisible" :edit-data="selectedEkskul" @update:is-dialog-visible="(val) => { if(!val) selectedEkskul = null }" @close="onEkskulAdded" />
    
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isAlertDialogVisible"
      confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
      :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete"
      @close="confirmClose" />
  </section>
</template>
