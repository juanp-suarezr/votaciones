<template>

    <Head title="Tipo de usuario" />

    <AuthenticatedLayout>
        <template #header>
            Tipo de usuario
        </template>


        <div class="lg:w-1/2 md:w-2/3 overflow-x-scroll md:overflow-x-auto">
            <div class="text-right p-4">
                <button type="button"
                    class="bg-sky-700 px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    @click="Actualizar('new')">
                    Agregar
                </button>
            </div>
        </div>

        <div class="lg:w-1/2 md:w-2/3 overflow-x-scroll md:overflow-x-auto rounded-lg shadow">
            <table class="w-full whitespace-no-wrap overflow-x-auto">
                <thead>
                    <tr
                        class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Tipo de usuario
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tipo in slicedTipos" :key="tipo.id" class="text-gray-700">
                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap">{{ tipo.nombre }}</p>
                        </td>
                        <td
                            class="inline-flex w-full border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">

                            <SecondaryButton type="button" @click="Actualizar(tipo.id)" class="mr-2">
                                Editar
                            </SecondaryButton>

                            <DangerButton @click="confirmUserDeletion(tipo.id)">Eliminar</DangerButton>

                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                class="flex items-center border-b bg-gray-50 px-5 py-2 xs:flex-row justify-between text-gray-500 text-xs font-semibold">
                <button class="hover:scale-125 transition duration-500 cursor-pointer" @click="previousPage"
                    :disabled="currentPage === 0"><i class="fa-solid fa-arrow-left"></i></button>
                <p>Pagina {{ currentPage + 1 }} de {{ totalPages }}</p>
                <button class="hover:scale-125 transition duration-500 cursor-pointer" @click="nextPage"
                    :disabled="currentPage === totalPages - 1"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- modal actu o create -->
        <Modal :show="visible" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-semibold"> 
                    {{ tipo_id == 'new' ? 'Añadir Tipo de Usuario' : 'Editar Tipo de Usuario' }}
                </h2>
                <!-- nombres -->
                <div class="mt-6">
                    <InputLabel class="sm:text-sm text-xs" for="nombre" value="Nombre del tipo de usuario" />
                    <TextInput id="nombre" type="text" class="mt-1 block w-full p-0 sm:p-2" v-model="form.nombre"
                        required autocomplete="nombre"/>
                    <InputError class="mt-1" :message="form.errors.nombre" />
                </div>
                <div class="flex items-center justify-end mt-6">
                    <Button class="bg-blue-800 text-white px-4 py-2 rounded-md hover:bg-blue-600 cursor"
                        type="button" @click="EventoTipo(tipo_id)">Guardar</Button>
                </div>
            </div>
        </Modal>

        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    ¿Estás seguro de que quieres eliminar un tipo de usuario?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Una vez que se elimine el tipo, esté deja de existir en la tabla tipos. Pero no se borra los registros relacionados con el usuario votante o candidato.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>

                    <DangerButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="deleteTipo">
                        Eliminar tipo de usuario
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue'
import PrimaryLink from '@/Components/PrimaryLink.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { ref, computed, watch, inject } from 'vue';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
const props = defineProps({
    tipos: Object
})
const swal = inject('$swal');

// Modal de eliminacion
const confirmingDeletion = ref(false);
//Modal de añadir o actualizar
const visible = ref(false);
const tipo_id = ref(0);
//form
const form = useForm({
    password: '',
    nombre: '',
});

const closeModal = () => {
    confirmingDeletion.value = false;
    visible.value = false;
    form.reset();
};
//MODAL EDIT or CREATE
const Actualizar = (id) => {
    
    form.nombre = props.tipos.find(item => item.id == id) ? props.tipos.find(item => item.id == id).nombre : '';
    visible.value = true;
    tipo_id.value = id;

};


//Añadir o actualizar
const EventoTipo = (num) => {

    if (num == 'new') {
        form.post(route('tipos.store', tipo_id.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                swal({
                    title: "Registro Creado",
                    text: "El tipo se ha registrado exitosamente",
                    icon: "success"
                })
            },
            onError: () => passwordInput.value.focus(),
            onFinish: () => form.reset(),
        });
    } else {
        form.put(route('tipos.update', tipo_id.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                swal({
                    title: "Registro Actualizado",
                    text: "El tipo se ha actualizado exitosamente",
                    icon: "success"
                })
            },
            onError: () => passwordInput.value.focus(),
            onFinish: () => form.reset(),
        });
    }


};

const confirmUserDeletion = (id) => {
    confirmingDeletion.value = true;
    tipo_id.value = id;
};
const deleteTipo = () => {
    form.delete(route('tipos.destroy', tipo_id.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            swal({
                title: "Registro Eliminado",
                text: "El rol se ha eliminado exitosamente",
                icon: "success"
            })
        },
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

//paginacion tutores
const itemsPerPage = ref(8);
const currentPage = ref(0);

const totalPages = computed(() => Math.ceil(props.tipos.length / itemsPerPage.value));



const previousPage = () => {
    if (currentPage.value > 0) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value - 1) {
        currentPage.value++;
    }
};
//FIN Paginacion

watch(itemsPerPage, () => {
    currentPage.value = 0;
});

//SEARCH
const searchTerm = ref('');

//Paginacion y filtro
const slicedTipos = computed(() => {
    const start = currentPage.value * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    const filteredUsers = props.tipos.filter(tipos =>
        tipos.nombre.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
    return filteredUsers.slice(start, end);
});

</script>
