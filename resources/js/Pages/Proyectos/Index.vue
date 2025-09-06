<template>

    <Head title="Proyectos" />

    <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
        <template #header>
            Proyectos
        </template>


        <div class="lg:w-1/2 md:w-2/3 overflow-x-scroll md:overflow-x-auto">
            <div class="text-right p-4">
                <PrimaryLink :href="route('proyectos.create')">
                    Agregar
                </PrimaryLink>
            </div>
        </div>

        <div class="w-full overflow-x-scroll md:overflow-x-auto rounded-lg shadow">
            <table class="w-full whitespace-no-wrap overflow-x-auto">
                <thead>
                    <tr
                        class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Nombre
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Tipo
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Subtipo
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Numero Tarjeton
                        </th>

                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Estado
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ev in proyectos.data" :key="ev.id" class="text-gray-700">
                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap">{{ ev.detalle }}</p>
                        </td>
                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap">{{ ev.tipo }}</p>
                        </td>
                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap">{{ ev.parametro_detalle?.detalle }}</p>
                        </td>
                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap">{{ ev.numero_tarjeton }}</p>
                        </td>

                        <td class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">
                            <p class="text-gray-900 whitespace-no-wrap inline-flex px-2 rounded-md bg-red-200"
                            :class="{ '!bg-green-400': ev.estado === 1 }">
                                {{ ev.estado ? "Activo" : "Bloqueado" }}
                            </p>
                        </td>
                        <td
                            class="w-full inline-block border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs">

                            <SecondaryLink type="button" :href="route('proyectos.edit', ev.id)" class="mb-3">
                                Editar
                            </SecondaryLink>

                            <DangerButton @click="confirmUserDeletion(tipo.id)">Eliminar</DangerButton>

                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between">
                <pagination :links="proyectos.links" />
            </div>
        </div>

        <!-- modal para eliminar -->
        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    ¿Estás seguro de que quieres eliminar un tipo de usuario?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Una vez que se elimine el tipo, esté deja de existir en la tabla tipos. Pero no se borra los
                    registros
                    relacionados con el usuario votante o candidato.
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
import SecondaryLink from '@/Components/SecondaryLink.vue';
const props = defineProps({
    proyectos: Object
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

const breadcrumbLinks = [
    { url: '', text: 'listado de proyectos' },

];



const closeModal = () => {
    confirmingDeletion.value = false;
    visible.value = false;
    form.reset();
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
