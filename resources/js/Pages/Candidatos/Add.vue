<template>

    <Head title="Agregar candidato" />

    <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
        <template #header>
            Nuevo Candidato
        </template>

        <div class="flex flex-col bg-white border shadow-sm rounded-xl w-full">
            <div class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 grid grid-cols-2 gap-4">
                <h3 class="mt-1 text-gray-500">
                    Nuevo Candidato
                </h3>
                <div class="text-right">
                    <SecondaryLink :href="route('candidatos.index')">
                        Regresar
                    </SecondaryLink>
                </div>

            </div>
            <div class="p-4 md:p-5">
                <form @submit.prevent="submit" class="grid sm:grid-cols-2 gap-4">

                    <!-- eventos -->
                    <div class="col-span-2 flex gap-4">
                        <InputLabel for="eventos" value="Eventos" />

                        <div class="card flex justify-content-center">
                            <select id="eventos" name="eventoSelect" v-model="form.eventos" @change="eventoSelect"
                                class="block w-full p-2 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option selected value="" disabled>Elegir evento de votación</option>
                                <option class="flex cursor mb-2 sm:mb-auto" v-for="eventos in eventos" :key="eventos.id"
                                    :value="eventos.id">
                                    {{ eventos.nombre }}
                                </option>
                            </select>
                        </div>
                        <InputError class="mt-2" :message="form.errors.tipos" />
                    </div>
                    <!-- Selección de Candidatos -->
                    <div class="col-span-2">
                        <InputLabel for="candidatos" value="Seleccionar Candidatos" />
                        <MultiSelect v-model="form.candidatos" :options="candidatosOptions" filter
                            :virtualScrollerOptions="{ itemSize: 44 }" optionLabel="nombre" optionValue="id"
                            placeholder="Seleccione candidatos" class="w-full" :disabled="!form.eventos" />
                        <InputError class="mt-2" :message="form.errors.candidatos" />
                    </div>



                    <div>
                        <div class="mt-4 flex flex-col items-end">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Asignar Candidato(s)
                            </PrimaryButton>

                        </div>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import MultiSelect from 'primevue/multiselect';
import { inject, ref, computed, watch } from 'vue';
import { PhotoIcon } from "@heroicons/vue/24/solid";
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import SecondaryLink from '@/Components/SecondaryLink.vue';





const swal = inject('$swal');


const props = defineProps({
    eventos: {
        type: Object,
        default: () => ({}),
    },
    tipos: {
        type: Object,
        default: () => ({}),
    },
});

console.log(props);


const candidatosOptions = ref([]);  // Se actualizará según el evento


const form = useForm({
    eventos: '',
    candidatos: [],


});

// Breadcrumb
const breadcrumbLinks = [
    { url: '/candidatos', text: 'Listado de candidatos' },
    { url: '', text: 'Nuevo candidato' },
];


// Lógica al seleccionar un evento
const eventoSelect = () => {
    if (form.eventos) {
        form.candidatos = [];
        candidatosOptions.value = [];
        console.log(form.eventos);

        const eventoSelect = props.eventos.find(item => item.id === form.eventos)
        console.log(eventoSelect);

        // Filtramos los candidatos según el evento seleccionado
        eventoSelect.votantes.forEach(element => {
            candidatosOptions.value.push({
                id: element.id_votante,
                nombre: element.votante.nombre
            })
        });;
        console.log(candidatosOptions.value);

    } else {
        candidatosOptions.value = [];
    }
};


const submit = () => {

    console.log(form);


    form.patch(route('candidatos.update', form.eventos), {
        onSuccess: function () {

            swal({
                title: "Registros Guardado",
                text: "los candidatos han sido asignados al evento",
                icon: "success"
            }).then((result) => {
                window.location.reload();
            })
        }, onError: function () {
            swal({
                title: "Registros fallados",
                text: "los candidatos no han sido asignados al evento",
                icon: "error"
            })
        }
    });
};




</script>
