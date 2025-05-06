<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue'
import axios from 'axios'

const formData = ref({})

const submit = async () => {
    try {
        const data = await axios.post(route('api.users.mass-update'), formData.value)
    } catch (error) {
        console.log(error)
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
                Edits Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-form :model="formData" label-width="auto" style="max-width: 600px">
                        <el-form-item label="Account Type">
                            <el-radio-group v-model="formData.account_type">
                                <el-radio value="1">1</el-radio>
                                <el-radio value="2">2</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="Description">
                            <el-input v-model="formData.description" />
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="submit">Update</el-button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
