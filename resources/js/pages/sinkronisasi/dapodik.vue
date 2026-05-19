<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Administrator',
    title: 'Tarik Dapodik',
  },
})
const loadingBody = ref(true)
const jam_sinkron = ref(false)
const data_sinkron = ref([])
const error = ref()
const isAlertDialogVisible = ref(false)
const notif = ref({
  color: '',
  title: '',
  text: '',
})

const form = ref({
  nama_aplikasi_dapodik: '',
  ip_erapor: '',
  ip_dapodik: '',
  token_dapodik: '',
  url_dapodik: '',
})

const isEdit = ref(false)
const isConnected = ref(false)

const fetchData = async () => {
  loadingBody.value = true
  try {
    const response = await useApi(createUrl('/sinkronisasi', {
      query: {
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        user_id: $user.user_id,
      },
    }))
    let getData = response.data.value
    jam_sinkron.value = getData.jam_sinkron
    data_sinkron.value = getData.data_sinkron
    error.value = getData.error
    
    form.value.nama_aplikasi_dapodik = getData.nama_aplikasi_dapodik
    form.value.ip_erapor = getData.ip_erapor
    form.value.ip_dapodik = getData.ip_dapodik
    form.value.token_dapodik = getData.token_dapodik
    form.value.url_dapodik = getData.url_dapodik

    if (form.value.token_dapodik && form.value.ip_dapodik) {
      isConnected.value = true
      isEdit.value = false
    } else {
      isEdit.value = true
    }
  } catch (error) {
    console.error(error);
  } finally {
    loadingBody.value = false;
  }
}

const loadingTest = ref(false)
const cekKoneksi = async () => {
  loadingTest.value = true
  try {
    const res = await $api('/sinkronisasi/cek-koneksi', {
      method: 'POST',
      body: {
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        npsn: $user.sekolah.npsn,
        ip_dapodik: form.value.ip_dapodik,
        ip_erapor: form.value.ip_erapor,
        nama_aplikasi_dapodik: form.value.nama_aplikasi_dapodik,
        token_dapodik: form.value.token_dapodik,
        url_dapodik: (form.value.ip_dapodik.includes('http')) ? form.value.ip_dapodik : `http://${form.value.ip_dapodik}:5774`
      }
    })
    
    isAlertDialogVisible.value = true
    notif.value = {
      color: res.color,
      title: res.title,
      text: res.text,
    }

    if (res.color === 'success') {
      isConnected.value = true
      isEdit.value = false
      fetchData()
    }
  } catch (err) {
    toast.error('Gagal menghubungi server lokal.')
  } finally {
    loadingTest.value = false
  }
}

const loadingProfil = ref(false)
const tarikProfil = async () => {
  loadingProfil.value = true
  try {
    const response = await $api('/sinkronisasi/tarik-profil', {
      method: 'POST',
      body: {
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        url_dapodik: form.value.url_dapodik,
        token_dapodik: form.value.token_dapodik,
        npsn: $user.sekolah.npsn,
      }
    })
    if (response.error) {
      toast.error(response.message)
    } else {
      toast.success(response.message)
      fetchData()
    }
  } catch (error) {
    toast.error('Gagal mengambil data profil.')
  } finally {
    loadingProfil.value = false
  }
}

const kurang = (item) => {
  if (item.dapodik > item.erapor)
    return true
  return false
}
const loading = ref(false)
const show = ref(false)
const syncText = ref()
const myTimer = async () => {
  await $api(`/sinkronisasi/hitung/${$user.sekolah_id}`, {
    method: 'GET',
    onResponse({ request, response, options }) {
      let getData = response._data
      if (getData.output) {
        if (getData.output.jumlah) {
          if (getData.output.jumlah === 1 && getData.output.inserted === 1) {
            syncText.value = 'Proses sinkronisasi selesai'
          } else {
            syncText.value = `${getData.output.table} (${getData.output.inserted}/${getData.output.jumlah})`
          }
        } else {
          syncText.value = getData.output.table
        }
      }
    }
  })
}
const syncSatuan = async (server, aksi) => {
  if (server && aksi) {
    show.value = true
    syncText.value = 'Menyiapkan proses sinkronisasi'
    loading.value = true
    var myInterval;
    myInterval = setInterval(myTimer, 500);
    await $api('/sinkronisasi/dapodik', {
      method: 'POST',
      body: {
        email: $user.email,
        satuan: aksi,
        tujuan: server,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        user_id: $user.user_id,
      },
      onResponse({ request, response, options }) {
        let getData = response._data
        loading.value = false
        show.value = false
        clearInterval(myInterval);
        syncText.value = 'Proses sinkronisasi selesai'
        isAlertDialogVisible.value = true
        notif.value = {
          color: 'success',
          title: 'Berhasil',
          text: 'Sinkronisasi Dapodik Berhasil',
        }
      }
    })
  }
}
onMounted(async () => {
  await fetchData();
});
const confirmAlert = () => {
  fetchData()
}
</script>

<template>
  <div>
    <VCard class="mb-6">
      <VCardItem>
        <VCardTitle>Konfigurasi Web Service Dapodik</VCardTitle>
        <template #append>
          <VBtn v-if="!isEdit" color="warning" variant="tonal" size="small" @click="isEdit = true">
            <VIcon icon="tabler-edit" class="me-1" /> Edit
          </VBtn>
        </template>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <AppTextField v-model="form.nama_aplikasi_dapodik" label="Nama Aplikasi" :disabled="!isEdit" placeholder="eRaporSMK" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="form.ip_erapor" label="IP e-Rapor" :disabled="!isEdit" placeholder="192.168.1.100" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="form.ip_dapodik" label="IP Dapodik" :disabled="!isEdit" placeholder="192.168.1.10" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="form.token_dapodik" label="Token Web Service" :disabled="!isEdit" placeholder="Masukkan Token" />
          </VCol>
          <VCol cols="12" class="d-flex gap-4">
            <VBtn v-if="isEdit" color="primary" :loading="loadingTest" @click="cekKoneksi">
              Simpan & Test Koneksi
            </VBtn>
            <VBtn v-if="isConnected && !isEdit" color="info" variant="tonal" :loading="loadingProfil" @click="tarikProfil">
              Tarik Profil Sekolah
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard class="text-center mb-10" v-if="loadingBody">
      <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
    </VCard>
    
    <VCard v-else-if="isConnected">
      <VCardText v-if="show">
        <VAlert color="secondary" class="text-center">
          {{ syncText }}
        </VAlert>
      </VCardText>
      <VCardText>
        <template v-if="jam_sinkron">
          <VAlert color="error" class="text-center" variant="tonal">
            <h2 class="mt-4 mb-4">Penyesuaian Waktu Layanan Sinkronisasi Dapodik</h2>
            <p>Dikarenakan adanya proses rutin sinkronisasi data Dapodik antara Server PUSDATIN dan Server Direktorat
              SMK, <br>maka layanan sinkronisasi hanya dapat diakses antara pukul <span class="text-danger"><b>03.00 s/d
                  24.00 (WIB)</b></span></p>
          </VAlert>
        </template>
        <template v-else-if="error">
          <VAlert title="Pengambilan Dapodik Gagal" type="error" variant="tonal">
            Status Server: {{ error.message }}
          </VAlert>
        </template>
        <template v-else>
          <VTable class="text-no-wrap">
            <thead>
              <tr>
                <th class="text-center">Data</th>
                <th class="text-center">Jml Dapodik</th>
                <th class="text-center">Jml e-Rapor</th>
                <th class="text-center">Jml Sinkron</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in data_sinkron" :key="item.nama">
                <td>{{ item.nama }}</td>
                <td class="text-center">{{ item.dapodik }}</td>
                <td class="text-center">{{ item.erapor }}</td>
                <td class="text-center">{{ item.sinkron }}</td>
                <td class="text-center">
                  <template v-if="item.sinkron">
                    <VChip color="warning" v-if="kurang(item)">Kurang</VChip>
                    <VChip color="success" v-else>Lengkap</VChip>
                  </template>
                  <template v-else>
                    <VChip color="error">Belum</VChip>
                  </template>
                </td>
                <td class="text-center">
                  <template v-if="item.dapodik">
                    <VBtn :loading="loading" :disabled="loading" size="small" color="success"
                      @click="syncSatuan(item.server, item.aksi)">
                      Sinkronisasi
                    </VBtn>
                  </template>
                  <template v-else>
                    -
                  </template>
                </td>
              </tr>
            </tbody>
          </VTable>
        </template>
      </VCardText>
    </VCard>

    <VCard v-else class="text-center p-10">
      <VCardText>
        <VAlert color="warning" variant="tonal" icon="tabler-alert-triangle">
          Silahkan lengkapi konfigurasi Web Service Dapodik di atas dan lakukan Test Koneksi terlebih dahulu untuk menampilkan data sinkronisasi.
        </VAlert>
      </VCardText>
    </VCard>

    <AlertDialog v-model:isDialogVisible="isAlertDialogVisible" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmAlert"></AlertDialog>
  </div>
</template>
