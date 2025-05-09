<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue'
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
const handleFile = (e) => {
    if(e.target.files){
        avatar.value = e.target.files[0]
        const reader = new FileReader()
        reader.onload = (e) => {
            props.user.avatar = e.target.result
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
                Edit Specific Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-form label-width="auto" style="max-width: 600px">
                        <el-form-item label="Avatar">
                            <el-image :src="user.avatar"></el-image>
                            <input ref="userAvatar" type="file" @change="handleFile($event)">
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
