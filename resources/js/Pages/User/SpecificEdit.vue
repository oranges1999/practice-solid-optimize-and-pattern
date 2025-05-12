<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, onUnmounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
})

const formErrors = ref({})

const submit = async () => {
    const formData = new FormData()
    formData.append('name', props.user.name)
    formData.append('type', props.user.type)
    formData.append('description', props.user.description)
    if(avatar.value){
        formData.append('avatar', avatar.value)
    }
    formData.append('_method', 'PUT')
    formErrors.value = {}
    try {
        await axios.post(route('api.users.update-specific', {user:props.user.id}), formData)
        ElMessage({
            message: 'Success',
            type: 'success',
        })
    } catch (error) {
        formErrors.value = error.response.data.errors
    }
} 

const avatar = ref(null);
const userAvatar = ref()
const handleFile = (file) => {
    if(file){
        avatar.value = file
        const reader = new FileReader()
        reader.onload = (e) => {
            props.user.avatar = e.target.result
        }
        reader.readAsDataURL(file)
    }
}
const dropFile = (file) => {
    console.log(file)
}

const deleteAvatar = () => {
    if(props.user.avatar){
        props.user.avatar = null
        avatar.value = null
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
})

onUnmounted(() => {
    events.forEach((eventName) => {
        document.body.removeEventListener(eventName, preventDefaults)
    })
})
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Edit Specific Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-form label-width="auto" style="max-width: 600px">
                        <el-form-item label="Avatar">
                            <div v-if="user.avatar" class="h-[400px] relative" :class="user.avatar ? 'w-auto' : 'w-[400px]'">
                                <el-image :src="user.avatar" class="rounded-[30px] h-full w-auto"></el-image>
                                <img 
                                    src="/svgs/close.svg" 
                                    alt="" 
                                    class="absolute top-[20px] right-[20px] h-[20px] cursor-pointer" 
                                    @click="deleteAvatar"
                                >
                            </div>
                            <div 
                                v-else 
                                class="h-[400px] w-[400px] flex justify-center items-center border border-dashed border-[black] rounded-[30px] cursor-pointer" 
                                @click="userAvatar.click()"
                                @drop.prevent="handleFile($event.dataTransfer.files[0])"
                            >
                                <img src="/svgs/plus_circle.svg" alt="" class="h-[50px]">
                            </div>
                            <input v-show="false" ref="userAvatar" type="file" @change="handleFile($event.target.files[0])">
                        </el-form-item>
                        <el-form-item label="Name" class="relative">
                            <el-input v-model="user.name" />
                            <p v-if="formErrors.name" class="absolute top-[75%] text-[red]">{{ formErrors.name[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Account Type">
                            <el-radio-group v-model="user.type">
                                <el-radio :value="1">1</el-radio>
                                <el-radio :value="2">2</el-radio>
                            </el-radio-group>
                            <p v-if="formErrors.type" class="absolute top-[75%] text-[red]">{{ formErrors.type[0] }}</p>

                        </el-form-item>
                        <el-form-item label="Description">
                            <el-input v-model="user.description" />
                            <p v-if="formErrors.description" class="absolute top-[75%] text-[red]">{{ formErrors.description[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Email">
                            <el-input v-model="user.email" disabled />
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="submit">Update</el-button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
