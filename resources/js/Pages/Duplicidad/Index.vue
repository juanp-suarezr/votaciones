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

    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-6">
                Validación para {{ comuna.detalle }}
            </h1>

            <form @submit.prevent="validar">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
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