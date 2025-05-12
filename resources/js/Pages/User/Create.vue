<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'
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
const handleFile = (e) => {
    if(e.target.files){
        form.value.avatar = e.target.files[0]
        const reader = new FileReader()
        reader.onload = (e) => {
            avatar.value = e.target.result
        }
        reader.readAsDataURL(e.target.files[0])
    }
}
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
                            <el-image v-if="avatar" :src="avatar"></el-image>
                            <input ref="userAvatar" type="file" @change="handleFile($event)">
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
