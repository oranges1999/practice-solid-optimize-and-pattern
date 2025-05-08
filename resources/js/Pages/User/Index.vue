<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { ElMessageBox, ElMessage } from 'element-plus';

const users = ref(0)
const userIds = ref([])
const scrollPosition = ref(null)
const getUser = async (link) => {
    link = link??route('api.users.index')
    try {
        const data = await axios.get(link);
        users.value = data.data
        userIds.value = users.value.data.map((user)=>{
            return user.id
        })
        userCheck.value[users.value.current_page]=userCheck.value[users.value.current_page]??[]
        scrollToSection()
    } catch (error) {
        console.log(error);
    }
};

const scrollToSection = () => {
    scrollPosition.value.scrollIntoView({ behavior: 'smooth' });
};

onMounted(()=>{
    getUser()
})

const toEdit = () => {
    router.visit(route('users.edit'))
}
const userCheck = ref([])

const checkAll = ref(false)

const isIndeterminate = ref(false)

const handleCheckAll = () => {
    isIndeterminate.value = false
    if(userCheck.value[users.value.current_page].length == 20 && userCheck.value[users.value.current_page]){
        userCheck.value[users.value.current_page] = []
    } else {
        userCheck.value[users.value.current_page] = userIds.value
    }
}

const compareArray = (array1, array2) => {
    return JSON.stringify(array1.sort()) == JSON.stringify(array2.sort())
}

watch(
    () => userCheck.value[users.value.current_page],
    (newVal) => {
        if(newVal.length == 0 || !newVal){
            isIndeterminate.value = false
            checkAll.value = false
        } else if (newVal.length > 0 && newVal.length < 20) {
            isIndeterminate.value = true
            checkAll.value = false
        } else if(compareArray(userIds.value, newVal)){
            isIndeterminate.value = false
            checkAll.value = true
        }
    }
)

const openPopup = () => {
  ElMessageBox.confirm(
    'These user will be permanent remove. Continue?',
    'Warning',
    {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(() => {
        try { 
            axios.post(route('api.users.mass-delete'), {userCheck: userCheck.value})
            getUser()
            ElMessage({
                type: 'success',
                message: 'Delete completed',
            })
        } catch (error) {
            console.log(error)
        }
    })
    .catch(() => {
      ElMessage({
        type: 'info',
        message: 'Delete canceled',
      })
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
                Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    <el-button type="primary" @click="toEdit()">Bulk Edit</el-button>
                    <el-button type="danger" @click="openPopup()">Bulk Delete</el-button>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th colspan="2">Action</th>
                                <th>
                                    <el-checkbox
                                        v-model="checkAll"
                                        :indeterminate="isIndeterminate"
                                        @change="handleCheckAll()"
                                    />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                <tr v-for="user in users.data">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.type }}</td>
                                    <td>{{ user.description }}</td>
                                    <td>
                                        <el-button type="primary">Edit</el-button>
                                    </td>
                                    <td>
                                        <el-button type="danger">Delete</el-button>
                                    </td>
                                    <td>
                                        <el-checkbox-group v-model="userCheck[users.current_page]">
                                            <el-checkbox :value="user.id" />
                                        </el-checkbox-group>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                    <el-pagination
                        :current-page="users.current_page"
                        :page-size="users.per_page"
                        layout="prev, pager, next"
                        :total="users.total"
                        @current-change="(page) => {getUser(route('api.users.index', { page: page }))}"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
