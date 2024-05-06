<template>

    <Head title="Candidatos" />

    <AuthenticatedLayout>
        <template #header>
            Candidatos
        </template>

        <div class="inline-block min-w-full overflow-hidden mb-3 grid md:grid-cols-3 gap-4">
            <div>
                <select id="candidato" name="candidato" v-model="candidato" @change="handleEnterKey"
                    class="block w-full px-4 py-3 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                    <option selected value="" disabled>Candidatos/todos</option>
                    <option value="1">Candidato</option>
                    <option value="0">Usuarios</option>
                </select>
            </div>
            <div class="...">
                <label for="default-search"
                    class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" @keydown.enter="handleEnterKey" v-model="search" id="default-search"
                        class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-0 focus:border-transparent"
                        placeholder="Busqueda de usuarios" required>
                    <button type="submit" @click="handleEnterKey"
                        class="text-white absolute end-2.5 bottom-2.5 bg-sky-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-sky-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Buscar</button>
                </div>
            </div>


        </div>
        <div class="flex flex-col overflow-x-auto">
            <div class="inline-block rounded-lg shadow">
                <div class="inline-block min-w-full py-2">
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-no-wrap ">
                            <thead>
                                <tr
                                    class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <th colspan="2"
                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Nombre
                                    </th>
                                    <th
                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Tiene imagen
                                    </th>
                                    <th
                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Identificación
                                    </th>
                                    <th
                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Tipo usuario
                                    </th>

                                    <th
                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="text-gray-700">
                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <Avatar v-if="user.imagen == 'user.png'" :label="getInitials(user.nombre)"
                                            class="bg-indigo-200 text-indigo-800 text-xl" size="xlarge"
                                            shape="circle" />
                                        <Avatar v-else :image="getImageUrl(user.imagen)"
                                            class="bg-indigo-200 text-indigo-800" size="xlarge" shape="circle" />
                                    </td>
                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ user.nombre }}</p>
                                    </td>
                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ user.imagen == 'user.png' ? 'No'
                                            :
                                            'Si' }}
                                        </p>
                                    </td>
                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ user.identificacion }}</p>
                                    </td>
                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ user.tipo }}</p>
                                    </td>

                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                        <SecondaryButton type="button" @click="Actualizar(user)">
                                            Subir imagen
                                        </SecondaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between">
            <pagination :links="users.links" />
        </div>


        <!-- modal actu o create -->
        <Modal :show="visible" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-semibold">
                    Añadir/Actualizar Imagen del candidato
                </h2>
                <div class="border-2 border-gray-300 rounded-md p-2">
                    <div class="" v-show="isChange">
                        <input type="file" class="mt-1 !border-0" accept="image/*"
                            @input="onAdvancedUpload($event.target.files[0])" :maxFileSize="2e+6" />

                    </div>

                    <button type="button" @click="isChange = true"
                        class="bg-blue-800 text-white p-2 rounded-md shadow-xl hover:scale-125" v-show="!isChange">
                        Cambiar imagen
                    </button>


                    <div class="flex justify-center">

                        <img v-if="imageUrl" :src="imageUrl" :alt="form.nombre" class="w-2/6 h-auto" />

                    </div>
                    <InputError class="mt-2" :message="form.errors.imagen" />
                </div>
                <div class="flex items-center justify-end mt-6">
                    <Button class="bg-blue-800 text-white px-4 py-2 rounded-md hover:bg-blue-600 cursor" type="button"
                        @click="submit(form.user_id)">Guardar</Button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, inject } from "vue";
import { watch } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryLink from '@/Components/PrimaryLink.vue';
import Pagination from '@/Components/Pagination.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import Avatar from 'primevue/avatar';
import SecondaryLink from "@/Components/SecondaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from '@/Components/Modal.vue';

//alert cool
const swal = inject('$swal');

const props = defineProps({
    users: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

//form
const form = useForm({
    user_id: '',
    nombre: '',
    imagen: '',
});

//Modal de añadir o actualizar
const visible = ref(false);

//variable de imagen
const imageUrl = ref('/storage/uploads/eventos/' + form.imagen);
//VALIDA SI HAY Q CAMBIAR DE IMG
const isChange = ref(false);

// pass filters in search
let search = ref(props.filters.search);
let candidato = ref(props.filters.candidato ?? "");
//evento de busqueda
const handleEnterKey = () => {
    router.get(
        "/candidatos",
        { search: search.value, candidato: candidato.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};
//avatar letters
const getInitials = function (name) {
    let parts = name.split(' ');
    let initials = '';
    let count = 0;

    for (var i = 0; i < parts.length && count < 2; i++) {
        if (parts[i].length > 0 && parts[i] !== '') {
            initials += parts[i][0];
            count++;
        }
    }
    return initials;
};
watch(search, (value) => {
    console.log("Valor de búsqueda actualizado:", value)
});

const closeModal = () => {
    visible.value = false;
    form.reset();
};
//MODAL EDIT or CREATE
const Actualizar = (user) => {
    form.user_id = user.id;
    form.nombre = user.nombre;
    form.imagen = user.imagen;

    visible.value = true;
    imageUrl.value = '/storage/uploads/usuarios/' + form.imagen;

};

//IMAGEN

//cambio de img
watch(isChange, () => {
    imageUrl.value = '';
});


//cuando suba la img
const onAdvancedUpload = (ev) => {

    form.imagen = ev;
    console.log(form);

    if (!form.imagen) return;

    //reader para mostrar el doc cargado
    const reader = new FileReader();
    reader.onload = () => {
        imageUrl.value = reader.result;
    };
    reader.readAsDataURL(form.imagen);

};

//MOSTRAR IMAGEN EN TABLA 
//IMAGEN 
const getImageUrl = (imageName) => {
    // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
    return `/storage/uploads/usuarios/${imageName}`;
};

const submit = () => {
    form.post(route('candidatos.store'), {
        onSuccess: function () {

            swal({
                title: "Registro Guardado",
                text: "la imagen del candidato se ha almacenado exitosamente",
                icon: "success"
            })
        },
        onFinish: () => {
            visible.value = false;
        },
        onError: () => {
            visible.value = true;
        }
    });
};



</script>
