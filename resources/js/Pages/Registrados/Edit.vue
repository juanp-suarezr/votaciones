<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({}),
    },
    
});

</script>

<template>
    <Head title="Editar Registro" />

    <AuthenticatedLayout>
        <div class="py-2">
            <div class="overflow-hidden shadow-sm sm:rounded-lg space-y-6 w-full">
                <div class="p-6 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateProfileInformationForm :user="usuario" class="px-4" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>