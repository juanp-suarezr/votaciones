<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import { inject } from "vue";
const swal = inject("$swal");

const props = defineProps({
    comuna: Object,
});

const form = useForm({
    numero_identificacion: "",
});

const loading = ref(false);

const validar = async () => {
    loading.value = true;
    try {
        const response = await axios.post(`/duplicidad/${props.comuna.id}/validar`, {
            numero_identificacion: form.numero_identificacion,
        });

        if (response.data.valid) {
            swal.fire({
                icon: "success",
                title: "Validación exitosa",
                text: response.data.message,
            });
            // Aquí puedes redirigir a la página de votación si es necesario
        } else {
            swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message,
            });
        }
    } catch (error) {
        swal.fire({
            icon: "error",
            title: "Error",
            text: "Ocurrió un error al validar.",
        });
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head :title="`Validación - ${comuna.detalle}`" />

    <!-- Header con logos -->
    <div class="w-full bg-white shadow-md py-4 px-4 sm:px-8 md:px-16">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-center">
            <img src="/assets/img/logo1.png" alt="Logo Alcaldía" class="h-16 sm:h-20 md:h-24 w-auto" />
            <img src="/assets/img/voto_electronico.png" alt="Logo Voto Electrónico" class="h-16 sm:h-20 md:h-24 w-auto" />
        </div>
    </div>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 flex items-center justify-center px-4 py-8">
        <div class="bg-white p-4 sm:p-6 md:p-8 rounded-lg shadow-lg w-full max-w-sm sm:max-w-md md:max-w-lg lg:max-w-xl">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-center mb-4 sm:mb-6">
                Validación para {{ comuna.detalle }}
            </h1>

            <form @submit.prevent="validar">
                <div class="mb-3 sm:mb-4">
                    <label class="block text-gray-700 text-xs sm:text-sm font-bold mb-2">
                        Número de Identificación
                    </label>
                    <TextInput
                        v-model="form.numero_identificacion"
                        type="text"
                        class="w-full"
                        required
                    />
                </div>

                <PrimaryButton
                    type="submit"
                    :disabled="loading"
                    class="w-full"
                >
                    <span v-if="loading">Validando...</span>
                    <span v-else>Validar</span>
                </PrimaryButton>
            </form>
        </div>
    </div>
</template>