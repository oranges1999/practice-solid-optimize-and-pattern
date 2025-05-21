<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const baseContainerClass = 'bg-[#f3f4f6] rounded-[8px] p-[10px]'
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
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
                        <div class="mb-[20px] h-[70px] w-full" :class="baseContainerClass">

                        </div>
                        <div class="min-h-[300px]" :class="baseContainerClass">

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
