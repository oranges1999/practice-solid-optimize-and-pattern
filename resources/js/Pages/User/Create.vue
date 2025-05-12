<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';

const formErrors = ref({})

const form = ref({
    name:'',
    type:'',
    description:'',
    avatar:''
})

const disabledForCreate = ref(false)

const submit = async () => {
    disabledForCreate.value = true
    const formData = new FormData()
    Object.keys(form.value).forEach((key)=>{
        formData.append(key, form.value[key])
    })
    formErrors.value = {}
    try {
        const data = await axios.post(route('api.users.create-user'), formData)
        console.log(data);
        ElMessage({
            message: 'Success',
            type: 'success',
        })
        router.visit(route('users.index'));
    } catch (error) {
        if(error?.response?.data){
            formErrors.value = error.response.data.errors
        }
        disabledForCreate.value = false
    }
    disabledForCreate.value = false

} 

const avatar = ref(null);
const userAvatar = ref()
const handleFile = (file) => {
    if(file){
        form.value.avatar = file
        const reader = new FileReader()
        reader.onload = (e) => {
            avatar.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const deleteAvatar = () => {
    if(form.value.avatar){
        form.value.avatar = null
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
                Create Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-form :model="form" label-width="auto" style="max-width: 600px">
                        <el-form-item label="Avatar">
                            <div v-if="avatar" class="h-[400px] relative" :class="avatar ? 'w-auto' : 'w-[400px]'">
                                <el-image :src="avatar" class="rounded-[30px] h-full w-auto"></el-image>
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
                            <el-input v-model="form.name" />
                            <p v-if="formErrors.name" class="absolute top-[75%] text-[red]">{{ formErrors.name[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Account Type">
                            <el-radio-group v-model="form.type">
                                <el-radio :value="1">1</el-radio>
                                <el-radio :value="2">2</el-radio>
                            </el-radio-group>
                            <p v-if="formErrors.type" class="absolute top-[75%] text-[red]">{{ formErrors.type[0] }}</p>

                        </el-form-item>
                        <el-form-item label="Description">
                            <el-input v-model="form.description" />
                            <p v-if="formErrors.description" class="absolute top-[75%] text-[red]">{{ formErrors.description[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Email">
                            <el-input v-model="form.email" />
                            <p v-if="formErrors.email" class="absolute top-[75%] text-[red]">{{ formErrors.email[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Password">
                            <el-input v-model="form.password" type="password" show-password/>
                            <p v-if="formErrors.password" class="absolute top-[75%] text-[red]">{{ formErrors.password[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Confirm Password" > 
                            <el-input v-model="form.password_confirmation" type="password" show-password/>
                            <p v-if="formErrors.password_confirmation" class="absolute top-[75%] text-[red]">{{ formErrors.password_confirmation[0] }}</p>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="submit" :disabled="disabledForCreate">Update</el-button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
