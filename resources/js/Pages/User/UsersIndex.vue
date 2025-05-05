<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue'
import axios from 'axios'

const users = ref(0)
const scrollPosition = ref(null)
const getUser = async (link) => {
    link = link??route('api.users.index')
    try {
        const data = await axios.get(link);
        users.value = data.data
        scrollToSection()
    } catch (error) {
        console.log(error);
    }
};

const scrollToSection = () => {
    scrollPosition.value.scrollIntoView({ behavior: 'smooth' }); // Cuộn mượt
};

onMounted(
    getUser()
)
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
                {{ users.last_page }}
                <div ref="scrollPosition" class="example-pagination-block">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.description }}</td>
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
