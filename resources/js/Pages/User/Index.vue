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
const params = ref({
    advance_search: false
})
const getUser = async (link) => {
    link = link??route('api.users.index')
    try {
        const data = await axios.get(link, {
            params:params.value
        });
        users.value = data.data
        userIds.value = users.value.data.map((user)=>{
            return user.id
        })
        userCheck.value = userCheck.value ?? []
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
    let filterUserIds = userCheck.value.filter((id) => {
        return userIds.value.includes(id)
    })
    if(filterUserIds.length == 0){
        userCheck.value = [
            ...userCheck.value,
            ...userIds.value
        ]
    }
    if(filterUserIds.length > 0 && filterUserIds.length < userIds.value.length){
        let pushUserIds = userIds.value.filter((id) => {
            return !filterUserIds.includes(id)
        })
        userCheck.value = [
            ...userCheck.value,
            ...pushUserIds
        ]
    }
    if(filterUserIds.length == userIds.value.length){
        filterUserIds.forEach((id) => {
            userCheck.value = userCheck.value.filter((item) => {
                return item !== id
            })
        })
    }
}

const compareArray = (array1, array2) => {
    return JSON.stringify(array1.sort()) == JSON.stringify(array2.sort())
}

watch(
    () => [userCheck.value, userIds.value],
    () => {
        let filterUserCheckIds = userIds.value.filter((id) => {
            return userCheck.value.includes(id)
        })
        let filterUserIds = userCheck.value.filter((id) => {
            return userIds.value.includes(id)
        })
        if(filterUserCheckIds.length >= 0 && filterUserIds.length == 0){
            isIndeterminate.value = false;
            checkAll.value = false;
        }
        if(filterUserCheckIds.length > 0 && filterUserIds.length > 0 && filterUserIds.length < userIds.value.length ){
            isIndeterminate.value = true;
            checkAll.value = false;
        }
        if(filterUserCheckIds.length > 0 && filterUserIds.length == userIds.value.length){
            isIndeterminate.value = false;
            checkAll.value = true;
        }
    }
)

const openPopup1 = () => {
  ElMessageBox.confirm(
    'These user will be permanent remove. Continue?',
    'Warning',
    {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(async () => {
        try { 
            await axios.post(route('api.users.mass-delete'), {
                userCheck: userCheck.value,
                _method: 'delete'
            })
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

const openPopup2 = (name, id) => {
  ElMessageBox.confirm(
    'User'+' '+name+' '+'will be permanent remove. Continue?',
    'Warning',
    {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then( async () => {
        try { 
            await axios.post(route('api.users.delete-specific', {user: id}), {
                _method: 'delete'
            })
            getUser()
            ElMessage({
                type: 'success',
                message: 'Delete completed',
            })
        } catch (error) {
            ElMessage({
                type: 'error',
                message: 'Delete failed',
            })
        }
    })
    .catch(() => {
      ElMessage({
        type: 'info',
        message: 'Delete canceled',
      })
    })
}

const toEditUser = (id) => {
    router.visit(route('users.show', {user:id}))
}
const toggleAdvanceSearch = () => {
    if(params.value.advance_search){
        params.value.advance_search = false
        params.value.created_from = null
        params.value.created_to = null
    } else {
        params.value.advance_search = true
    }
}

const toCreateUser = () => {
    router.visit(route('users.create'));
}

const toImport = () => {
    router.visit(route('users.import'));
}

const toExport = (url) => {
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'import_error.xlsx')
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
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
                    <div>
                        <div class="flex justify-between mb-[20px]">
                            <div>
                                <el-button type="primary" @click="toEdit()">Bulk Edit</el-button>
                                <el-button type="danger" @click="openPopup1()">Bulk Delete</el-button>
                                <el-button type="success" @click="toCreateUser()">Create User</el-button>
                                <el-button type="info" @click="toImport()">Import User</el-button>
                                <el-button type="warning" @click="toExport(route(''))">Export User</el-button>
                            </div>
                            <div>
                                <div class="flex gap-3">
                                    <el-input v-model="params.key_word"/>
                                    <el-button type="primary" @click="getUser()">Search</el-button>
                                </div>
                                <div class="flex justify-end items-center" @click="toggleAdvanceSearch()">
                                    <p class="text-right underline underline-offset-2 cursor-pointer">
                                        Advance Search 
                                    </p>
                                    <img 
                                        src="/svgs/arrow_left.svg" 
                                        class="h-[30px] transition duration-100 ease-linear" 
                                        :class="{'rotate-90-counter-clock':params.advance_search}" 
                                        alt=""
                                    >    
                                </div>
                            </div>
                        </div>
                        <div 
                            class="flex gap-2 justify-center items-center overflow-y-hidden transition-height-300" 
                            :style="params.advance_search ? 'height: 50px' : 'height: 0px'"
                        >
                            <el-form-item label="Created From" class="!m-0">
                                <el-date-picker
                                    v-model="params.created_from"
                                    type="date"
                                    placeholder="Pick a day"
                                    format="YYYY-MM-DD"
                                    value-format="YYYY-MM-DD"
                                />
                            </el-form-item>
                            <el-form-item label="Created To" class="!m-0">
                                <el-date-picker
                                    v-model="params.created_to"
                                    type="date"
                                    placeholder="Pick a day"
                                    format="YYYY-MM-DD"
                                    value-format="YYYY-MM-DD"
                                />
                            </el-form-item>
                        </div>
                    </div>
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
                                        <el-button type="primary" @click="toEditUser(user.id)">Edit</el-button>
                                    </td>
                                    <td>
                                        <el-button type="danger" @click="openPopup2(user.name, user.id)">Delete</el-button>
                                    </td>
                                    <td>
                                        <el-checkbox-group v-model="userCheck">
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
<style scoped>
.close-nav-btn{
    transition: linear 0.1s;
}

.rotate-90-counter-clock{
    transform: rotateZ(-90deg);
}

.transition-height-300{
    transition: height 0.3s;
}
</style>
