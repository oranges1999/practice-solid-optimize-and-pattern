<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, onUnmounted, computed } from 'vue'
import axios from 'axios'
import { ElMessageBox, ElMessage, ElNotification } from 'element-plus';

</script>

<template>
    <Head title="Chat" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Chat
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div ref="scrollPosition" class="example-pagination-block">
                    
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
