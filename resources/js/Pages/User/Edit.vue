<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'
import { router } from '@inertiajs/vue3';
import axios from 'axios'

const formData = ref({})

const formErrors = ref({})

const disabledForUpdate = ref(false)

const submit = async () => {
    try {
        disabledForUpdate.value = true
        formErrors.value = {}
        formData.value['_method'] = 'PATCH'
        const data = await axios.post(route('api.users.mass-update'), formData.value)
        if(data.status == 200){
            router.visit(route('users.index'))
        }
    } catch (error) {
        disabledForUpdate.value = false
        formErrors.value = error.response.data.errors
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
                Edit Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-form :model="formData" label-width="auto" style="max-width: 600px">
                        <el-form-item label="Account Type" class="relative">
                            <el-radio-group v-model="formData.account_type" :disabled="disabledForUpdate">
                                <el-radio :value="1">1</el-radio>
                                <el-radio :value="2">2</el-radio>
                            </el-radio-group>
                            <br/>
                            <p v-if="formErrors.account_type" class="absolute top-[75%] text-[red] font-normal">{{ formErrors.account_type[0] }}</p>
                        </el-form-item>
                        <el-form-item label="Description" class="relative">
                            <el-input v-model="formData.description" :disabled="disabledForUpdate"/>
                            <br/>
                            <p v-if="formErrors.description" class="absolute top-[75%] text-[red] font-normal">{{ formErrors.description[0]}}</p>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="submit" :disabled="disabledForUpdate">Update</el-button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
