<script setup>
import onlineUpdateTypes from '@/Consts/OnlineUpdateTypes';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const baseContainerClass = 'bg-[#f3f4f6] rounded-[8px] p-[10px]'
const baseOnlineUsers = 'h-full min-w-[50px] w-[50px] flex flex-col justify-center items-center text-center'
const onlineUsers = ref([])
const waitTimeout = ref()
const updateOnlineUsers = (data) => {
    switch (data.type) {
        case onlineUpdateTypes.join:
            if(waitTimeout.value){
                clearTimeout(waitTimeout.value)
            }
            if(onlineUsers.value.filter((u) => u.id === data.user.id).length === 0){
                onlineUsers.value.push(data.user)
            }
            break;
        case onlineUpdateTypes.left:
            if(onlineUsers.value.filter((u) => u.id === data.user.id).length > 0){
                waitTimeout.value = setTimeout(() => {
                    onlineUsers.value = onlineUsers.value.filter((u) => u.id !== data.user.id)             
                }, 5000);
            }
            break;
        default:
            console.log('Something wrong :(')
            break;
    }
}
const getCurrentOnlineUser = (users) => {
    onlineUsers.value = users.users
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout @update-online-user="updateOnlineUsers" @current-online-users="getCurrentOnlineUser">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-[20px] rounded-[16px] shadow grid grid-cols-4 gap-[20px]">
                    <div class="">
                        <div class="mb-[20px] h-[90px] w-full flex " :class="baseContainerClass">
                            <div :class="baseOnlineUsers">
                                <img src="/svgs/plus_circle_white.svg" class="w-[50px]" alt="">
                                <p class="text-[12px] truncate w-full">Add</p>
                            </div>
                            <el-scrollbar class="!h-full">
                                <div class="scrollbar-flex-content">
                                    <div v-for="user in onlineUsers" :class="baseOnlineUsers" class="mx-[3px]">
                                        <img :src="user.avatar" class="rounded-full" alt="">
                                        <p class="text-[12px] truncate w-full">{{ user.name }}</p>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                        <div class="min-h-[280px]" :class="baseContainerClass">

                        </div>
                    </div>
                    <div class="col-span-3">
                        <div :class="baseContainerClass" class="h-[70px]"></div>
                        <div :class="baseContainerClass" class="my-[20px] min-h-[210px]"></div>
                        <div :class="baseContainerClass" class="h-[70px]"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-flex-content {
  display: flex;
  width: fit-content;
  height: 100%;
}
.scrollbar-demo-item {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 50px;
  margin: 10px;
  text-align: center;
  border-radius: 4px;
  background: var(--el-color-danger-light-9);
  color: var(--el-color-danger);
}
:deep(.el-scrollbar__view){
    height: 100%;
}
</style>
