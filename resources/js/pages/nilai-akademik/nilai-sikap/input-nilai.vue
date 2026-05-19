<script setup>
import { Indonesian } from "flatpickr/dist/l10n/id.js";
definePage({
    meta: {
        action: 'read',
        subject: 'Guru',
        title: 'Input Nilai Sikap',
        navActiveLink: 'nilai-akademik-nilai-sikap',
    },
})
const router = useRouter();
const refSikap = ref([])
const confirmed = ref(false)
onMounted(async () => {
    await fetchData();
});
const loadingBody = ref(false)
const fetchData = async () => {
    loadingBody.value = true;
    try {
        const response = await useApi(createUrl('/referensi/sikap', {
            query: {
                sekolah_id: $user.sekolah_id,
                semester_id: $semester.semester_id,
            },
        }));
        let getData = response.data.value
        refSikap.value = getData.data
        console.log(getData);

    } catch (error) {
        console.error(error);
    } finally {
        loadingBody.value = false;
    }
}
const removeDuplicates = (arr, key) => {
    const seen = {};
    const uniqueArray = arr.filter((obj) => {
        const value = obj[key];
        if (seen[value]) {
            return false;
        }
        seen[value] = true;
        return true;
    });
    return uniqueArray;
}
const form = ref({
    tingkat: null,
    rombongan_belajar_id: null,
    peserta_didik_id: null,
    anggota_rombel_id: null,
})
const errors = ref({
    tingkat: null,
    rombongan_belajar_id: null,
    peserta_didik_id: null,
    tanggal: null,
    budaya_kerja_id: null,
    elemen_id: null,
    opsi_sikap: null,
    uraian_sikap: null,
})
const arrayData = ref({
    rombel: [],
    siswa: [],
    elemen: [],
})
const loading = ref({
    rombel: false,
    siswa: false,
    elemen: false,
})
const defaultForm = ref({
    opsi: 'nilai-sikap',
    nilai_budaya_kerja_id: null,
    semester_id: $semester.semester_id,
    user_id: $user.user_id,
    guru_id: $user.guru_id,
    sekolah_id: $user.sekolah_id,
    tanggal: null,
    budaya_kerja_id: null,
    elemen_id: null,
    opsi_sikap: null,
    uraian_sikap: null,
})
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const confirmationText = ref('')
const notif = ref({
    color: "",
    title: "",
    text: "",
})
const resetForm = ref(false)
const formReset = () => {
    resetForm.value = true
    form.value = {
        tingkat: null,
        rombongan_belajar_id: null,
        peserta_didik_id: null,
        anggota_rombel_id: null,
    }
    defaultForm.value = {
        opsi: 'nilai-sikap',
        semester_id: $semester.semester_id,
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        tanggal: null,
        budaya_kerja_id: null,
        elemen_id: null,
        opsi_sikap: null,
        uraian_sikap: null,
    }
    errors.value = {
        tingkat: null,
        rombongan_belajar_id: null,
        peserta_didik_id: null,
        tanggal: null,
        budaya_kerja_id: null,
        elemen_id: null,
        opsi_sikap: null,
        uraian_sikap: null,
    }
    arrayData.value = {
        rombel: [],
        siswa: [],
        elemen: [],
    }
    loading.value = {
        rombel: false,
        siswa: false,
        elemen: false,
    }
    confirmed.value = false;
    isNotifVisible.value = false;
    notif.value = {
        color: "",
        title: "",
        text: "",
    }
}
const getData = async (postData) => {
    const mergedForm = { ...postData, ...defaultForm.value };
    await $api("/referensi/get-data", {
        method: "POST",
        body: mergedForm,
        onResponse({ response }) {
            let getData = response._data;
            if (postData.data == "rombel") {
                arrayData.value.rombel = getData;
            }
            if (postData.data == "siswa") {
                arrayData.value.siswa = getData.data_siswa;
            }
            if (postData.data == "elemen") {
                arrayData.value.elemen = getData
            }
        },
    });
}
const changeTingkat = async (val) => {
    form.value.rombongan_belajar_id = null;
    form.value.pembelajaran_id = null;
    form.value.mata_pelajaran_id = null
    if (val) {
        loading.value.rombel = true;
        await getData({
            data: "rombel",
            nilai: 1,
            tingkat: val,
        }).then(() => {
            loading.value.rombel = false;
        });
    }
};
const changeRombel = async (val) => {
    form.value.peserta_didik_id = null
    form.value.anggota_rombel_id = null
    if (val) {
        loading.value.siswa = true;
        await getData({
            data: "siswa",
            rombongan_belajar_id: val,
        }).then(() => {
            loading.value.siswa = false;
        });
    }
}
const changeDimensi = async (val) => {
    defaultForm.value.elemen_id = null
    if (val) {
        loading.value.elemen = true
        await getData({
            data: "elemen",
            budaya_kerja_id: val,
        }).then(() => {
            loading.value.elemen = false;
        });
    }
    console.log('changeDimensi');
}
const confirmClose = async () => {
    formReset();
    await nextTick(() => {
        router.replace('/nilai-akademik/nilai-sikap')
    });
}
const onSubmit = async () => {
    confirmed.value = true;
    const mergedForm = { ...defaultForm.value, ...form.value };
    await $api("/penilaian/simpan", {
        method: "POST",
        body: mergedForm,
        onResponse({ response }) {
            let getData = response._data;
            confirmed.value = false;
            if (getData.errors) {
                errors.value = getData.errors;
            } else {
                isNotifVisible.value = true;
                notif.value = getData;
            }
        },
    })
}
const dateConfig = ref({
    locale: Indonesian,
    altFormat: "j F Y",
    altInput: true,
});
</script>
<template>
    <VCard class="mb-6">
        <VCardItem class="pb-4">
            <VCardTitle>Input Nilai Sikap</VCardTitle>
        </VCardItem>
        <VDivider />
        <template v-if="loadingBody">
            <VCardText class="text-center">
                <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
            </VCardText>
        </template>
        <template v-else>
            <VTable>
                <thead>
                    <tr>
                        <th class="text-center" v-for="sikap in refSikap">{{ sikap.aspek }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td v-for="sikap in refSikap" style="vertical-align: top;">
                            <ul class="py-2 px-2">
                                <li v-for="elemen in removeDuplicates(sikap.elemen_budaya_kerja, 'elemen')">{{
                                    elemen.elemen }}</li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </VTable>
            <VForm @submit.prevent="onSubmit">
                <VCardText>
                    <VRow>
                        <DefaultForm v-model:form="form" v-model:errors="errors" v-model:arrayData="arrayData"
                            v-model:loading="loading" v-model:resetForm="resetForm" :showMapel="false" :showPd="true"
                            @tingkat="changeTingkat" @rombongan_belajar_id="changeRombel"></DefaultForm>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3" class="d-flex align-items-center">
                                    <label class="v-label text-body-2 text-high-emphasis" for="tanggal">Tanggal</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <AppDateTimePicker v-model="defaultForm.tanggal" placeholder="== Pilih Tanggal =="
                                        :config="dateConfig" :error-messages="errors.tanggal" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3" class="d-flex align-items-center">
                                    <label class="v-label text-body-2 text-high-emphasis"
                                        for="budaya_kerja_id">Dimensi/Elemen Sikap</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VRow>
                                        <VCol cols="12" md="4">
                                            <AppSelect v-model="defaultForm.budaya_kerja_id"
                                                placeholder="== Pilih Dimensi Sikap ==" :items="refSikap" clearable
                                                clear-icon="tabler-x" item-value="budaya_kerja_id" item-title="aspek"
                                                :error-messages="errors.budaya_kerja_id"
                                                @update:model-value="changeDimensi" />
                                        </VCol>
                                        <VCol cols="12" md="4">
                                            <AppSelect v-model="defaultForm.elemen_id"
                                                placeholder="== Pilih Elemen Sikap ==" :items="arrayData.elemen"
                                                clearable clear-icon="tabler-x" item-value="elemen_id"
                                                item-title="elemen" :error-messages="errors.elemen_id"
                                                :loading="loading.elemen" :disabled="loading.elemen" />
                                        </VCol>
                                        <VCol cols="12" md="4">
                                            <AppSelect v-model="defaultForm.opsi_sikap"
                                                placeholder="== Pilih Opsi Sikap =="
                                                :items="[{ value: 1, title: 'Positif' }, { value: 2, title: 'Negatif' }]"
                                                clearable clear-icon="tabler-x" :error-messages="errors.opsi_sikap"
                                                :loading="loading.elemen" :disabled="loading.elemen" />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3" class="d-flex align-items-center">
                                    <label class="v-label text-body-2 text-high-emphasis" for="uraian_sikap">Uraian
                                        Sikap</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <AppTextarea v-model="defaultForm.uraian_sikap"
                                        :error-messages="errors.uraian_sikap" placeholder="Uraian Sikap" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardText>
                <VDivider />
                <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible">
                    <VBtn variant="tonal" color="secondary" @click="router.replace('/nilai-akademik/nilai-sikap')">
                        Kembali
                    </VBtn>
                    <VBtn variant="elevated" type="submit" :loading="confirmed" :disabled="confirmed">
                        Simpan
                    </VBtn>
                </VCardText>
            </VForm>
        </template>
        <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
            confirmation-question="Apakah Anda yakin?" :confirmation-text="confirmationText"
            :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @close="confirmClose" />
    </VCard>
</template>
