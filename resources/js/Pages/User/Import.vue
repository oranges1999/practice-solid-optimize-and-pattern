<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import axios from 'axios'
import { ElMessage, ElNotification } from 'element-plus';
import { usePage } from '@inertiajs/vue3';

const page = usePage()
const user = computed(() => page.props.auth.user)
const formErrors = ref({})
const inputFile = ref()
const input = ref()
const isUploading = ref(false)
const isLoading = ref(false)
const fileData = ref(null)
const fileName = ref('')
watch(
    () => fileData.value,
    () => {
        if(fileData.value){
            isUploading.value = true
        } else {
            isUploading.value = false
        }
    } 
)

const minLength = ref(0)

const uploadFile = async (e) => {
    isLoading.value = true
    input.value = e.target
    fileName.value = e.target.files[0].name
    isLoading.value = false

    // formData.append('file', e.target.files[0])
    // try {
    //     const data = await axios.post(route('api.users.load-user'), formData)
    //     fileData.value = data.data
    //     minLength.value = fileData.value.length > 10 ? 5 : fileData.value.length
    //     fileName.value = e.target.files[0].name
    //     isLoading.value = false
    // } catch (error) {
    //     isLoading.value = false
    //     console.log(error)
    //     deleteFile()
    // }
}

const importData = async () => {
    isLoading.value = true
    try {
        let formData = new FormData()
        formData.append('user', JSON.stringify(fileData.value))
        formData.append('file', input.value.files[0])
        const response = await axios.post(route('api.users.import-user'), formData, {
            responseType: 'blob',
        })
         const contentType = response.headers['content-type']
        if (
            contentType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') ||
            contentType.includes('application/octet-stream')
            ) {
            const blob = new Blob([response.data], { type: contentType })
            const url = URL.createObjectURL(blob)
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'import_error.xlsx')
            document.body.appendChild(link)
            link.click()
            link.remove()
            URL.revokeObjectURL(url)
            ElMessage({
                message: 'There is something wrong with the data in the file, please check the result file which will be automatic downloaded to your computer in the seccond',
                type: 'error',
            })
        } else {
            const reader = new FileReader()
            reader.onload = () => {
                try {
                    const json = JSON.parse(reader.result)
                } catch (e) {
                }
            }
            reader.readAsText(response.data)
            // router.visit(route('users.index'));
        }
    } catch (error) {
        console.log(error)
    }   
}

function preventDefaults(e) {
    e.preventDefault()
}

const events = ['dragenter', 'dragover', 'dragleave', 'drop']

onMounted(() => {
    events.forEach((eventName) => {
        document.body.addEventListener(eventName, preventDefaults)
    })
    Echo.private(`App.Models.User.${user.value.id}`)
        .listen('FileReceived', (e) => fireNotification(e))
        .listen('FileEmpty', (e) => fireNotification(e))
        .listen('FileLimit', (e) => fireNotification(e))
        .listen('FileWarning', (e) => fireNotification(e))
})

onUnmounted(() => {
    events.forEach((eventName) => {
        document.body.removeEventListener(eventName, preventDefaults)
    })
    Echo.private(`App.Models.User.${user.value.id}`)
        .stopListening('FileReceived')
        .stopListening('FileEmpty')
        .stopListening('FileLimit')
        .stopListening('FileWarning')
})

const deleteFile = () => {
    inputFile.value.value = null
    fileData.value = null
    fileName.value = ''
}

const fireNotification = (e) => {
    isLoading.value = e.is_loading
    ElNotification({
        message: e.message,
        type: e.type,
        duration: 6000,
    })
}
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Import Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <div class="flex gap-2">
                        <div class="h-[32px] w-[200px] border border-dashed border-[black] rounded-[12px] flex justify-center items-center">
                            <img v-if="!fileName" src="/svgs/plus_circle.svg" class="w-[25px] cursor-pointer" alt="" @click="!isUploading ? inputFile.click() : ''">
                            <div v-else class="flex justify-between w-full mx-[10px]">
                                <p>{{ fileName }}</p>
                                <img src="/svgs/close.svg" class="w-[15px]" alt="" @click="deleteFile">
                            </div>
                        </div>
                        <input v-show="false" ref="inputFile" type="file" @change="uploadFile($event)">
                        <el-button type="primary" :disabled="!fileName" :loading="isLoading" @click="importData">Import</el-button>
                        <a :href="route('users.download-sample')">Download sample</a>
                    </div>
                    <div class="flex">
                        <i class="text-[red] mr-[5px]">*</i>
                        <p class="underline underline-offset-2">Maximum 5000 row of data at once</p>
                    </div>
                    <div v-if="fileData">
                        <table>
                            <thead>
                                <tr>
                                    <th v-for="field in fileData[0]">{{ field }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="data, index in fileData">
                                    <td v-if="index != 0 && index <= minLength" v-for="field in data">{{ field }}</td>
                                </tr>
                                <tr v-if="fileData.length > 10"><td>...</td></tr>
                                <tr v-if="fileData.length > 10">
                                    <td colspan="2" class="flex justify-center">{{ fileData.length  - 11 }} Line in between</td>
                                </tr>
                                <tr v-if="fileData.length > 10"><td>...</td></tr>
                                <tr v-if="fileData.length > 10" v-for="data, index in fileData">
                                    <td v-if="index >= fileData.length - 5" v-for="field in data">{{ field }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
