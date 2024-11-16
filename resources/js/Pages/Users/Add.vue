<template>

    <Head title="Agregar usuario" />

    <AuthenticatedLayout>
        <template #header>
            Nuevo usuario
        </template>

        <div class="md:grid grid-cols-2 gap-4">
            <!-- manual -->
            <div class="flex flex-col bg-white border shadow-sm rounded-xl">
                <div class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 grid grid-cols-2 gap-4">
                    <h3 class="mt-1 text-gray-500">
                        Nuevo Usuario
                    </h3>
                    <div class="text-right">
                        <SecondaryLink :href="route('users.index')">
                            Regresar
                        </SecondaryLink>
                    </div>

                </div>
                <div class="p-4 md:p-5">
                    <form @submit.prevent="submit" class="grid sm:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div>
                            <InputLabel for="name" value="Nombre" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required
                                autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <!-- identificaciones -->
                        <div>
                            <InputLabel for="identificacion" value="Identificación" />
                            <TextInput id="identificacion" type="number" class="mt-1 block w-full"
                                v-model="form.identificacion" required autofocus autocomplete="identificacion" />
                            <InputError class="mt-2" :message="form.errors.identificacion" />
                        </div>
                        <!-- tipo -->
                        <div>
                            <InputLabel for="tipo" value="Tipo votante" />
                            <select v-if="tipos.length" v-model="form.tipo"
                                class="block w-full px-4 py-2 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option disabled value="" class="text-xs text-gray-400">Seleccione tipo votante</option>
                                <option v-for="vot in tipos" :value="vot.nombre" :key="vot.id">{{ vot.nombre }}
                                </option>
                            </select>
                            <p v-else class="text-xs text-gray-600">No hay tipos de usuarios registrados, registrar uno
                                nuevo <a :href="route('tipos.index')">Aquí</a></p>
                            <InputError class="mt-2" :message="form.errors.tipo" />
                        </div>
                        <!-- evento -->
                        <div>
                            <InputLabel for="evento" value="Evento votante" />
                            <select v-if="eventos.length" v-model="form.eventos"
                                class="block w-full px-4 py-2 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option disabled value="" class="text-xs text-gray-400">Seleccione evento a asignar
                                </option>
                                <option v-for="ev in eventos" :value="ev.id" :key="ev.id">{{ ev.nombre }}
                                </option>
                            </select>
                            <p v-else class="text-xs text-gray-600">No hay eventos registrados, registrar uno nuevo <a
                                    :href="route('eventos.index')">Aquí</a></p>
                            <InputError class="mt-2" :message="form.errors.eventos" />
                        </div>
                        <!-- email -->
                        <div>
                            <InputLabel for="email" value="Usuario" />
                            <TextInput id="email" type="text" class="mt-1 block w-full" v-model="form.email" required
                                autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <!-- contraseña -->
                        <div>
                            <InputLabel for="password" value="Contraseña" />
                            <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                                required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <!-- confirm password -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                            <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                                v-model="form.password_confirmation" required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />


                        </div>
                        <!-- roles -->
                        <div>
                            <InputLabel for="roles" value="Roles" />

                            <div class="card flex justify-content-center">
                                <MultiSelect id="roles" v-model="form.roles_user" display="chip"
                                    :options="Object.values(roles)" placeholder="Seleccione rol" :maxSelectedLabels="3"
                                    class="w-full md:w-20rem" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.roles_user" />
                        </div>
                        <!-- select empresa - campista -->
                        <div class="col-span-2 w-full">
                            <div class="card flex z-10 gap-4 mt-4">
                                <div class="card flex justify-content-center">
                                    <SelectButton v-model="tipo_user" :options="options_user" aria-labelledby="basic" />
                                </div>

                            </div>
                        </div>

                        <div class="col-span-2 w-full">
                            <div class="mt-4 flex flex-col items-center">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Registrar Usuario
                                </PrimaryButton>

                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <!-- cargue -->
            <div class="w-full bg-white border shadow-sm rounded-xl">
                <div class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 flex">
                    <h3 class="text-gray-500 m-auto">
                        CARGUE MASIVO
                    </h3>

                </div>

                <div class="w-full h-full flex px-4">
                    <div class="mt-6 w-full">

                        <p class="">
                            <b>Nota:</b> Para subir masivamente la lista de votantes, debe descargar la
                            plantilla, dando
                            clic en el
                            siguiente
                            botón.
                        </p>

                        <a :href="route('plantillaRes.excel')"
                            class="flex inline-flex text-white bg-green-800 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 mt-4">
                            <i class="fa-solid fa-file-arrow-down m-auto me-2"></i>Descargar plantilla
                        </a>

                        <p class="mt-4">
                            Luego de descargar la plantilla y cargar los datos, subir el mismo archivo excel en el
                            apartado
                            de
                            abajo.
                        </p>


                        <form @submit.prevent="cargueMasivo">
                            <!-- tipo -->
                            <div class="mt-2">
                                <InputLabel for="tipo" value="Tipo votante" />
                                <select v-if="tipos.length" v-model="form.tipo"
                                    class="block w-full px-4 py-1 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                    <option disabled value="" class="text-xs text-gray-400">Seleccione tipo votante
                                    </option>
                                    <option v-for="vot in tipos" :value="vot.nombre" :key="vot.id">{{ vot.nombre }}
                                    </option>
                                </select>
                                <p v-else class="text-xs text-gray-600">No hay tipos de usuarios registrados, registrar
                                    uno
                                    nuevo <a :href="route('tipos.index')">Aquí</a></p>
                                <InputError class="mt-2" :message="form.errors.tipo" />
                            </div>
                            <!-- evento -->
                            <div class="mt-2">
                                <InputLabel for="evento" value="Evento votante" />
                                <select v-if="eventos.length" v-model="form.eventos"
                                    class="block w-full px-4 py-1 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                    <option disabled value="" class="text-xs text-gray-400">Seleccione evento a asignar
                                    </option>
                                    <option v-for="ev in eventos" :value="ev.id" :key="ev.id">{{ ev.nombre }}
                                    </option>
                                </select>
                                <p v-else class="text-xs text-gray-600">No hay eventos registrados, registrar uno nuevo
                                    <a :href="route('eventos.index')">Aquí</a></p>
                                <InputError class="mt-2" :message="form.errors.eventos" />
                            </div>

                            <TextInput id="cargueMasivoVotantes" type="file" class="mt-4 block w-full"
                                @change="validateFile($event)" />
                            <InputError class="mt-2" :message="form.errors.votantes" />

                            <!-- boton registrar -->
                            <div>
                                <div class="mt-12 flex flex-col items-end">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing">
                                        Enviar
                                    </PrimaryButton>

                                </div>
                            </div>
                        </form>

                    </div>

                </div>
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
import MultiSelect from 'primevue/multiselect';
import { inject, ref, watch } from 'vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import SelectButton from 'primevue/selectbutton';

const swal = inject('$swal');

const props = defineProps({
    roles: {
        type: Object,
        default: () => ({}),
    },
    tipos: {
        type: Object,
        default: () => ({}),
    },
    eventos: {
        type: Object,
        default: () => ({}),
    },
    numRegistrosInsertados: {
        type: Object,
        default: () => ({}),
    },

    numRegistrosActualizados: {
        type: Object,
        default: () => ({}),
    }

});
//checked para empresas o campistas
const tipo_user = ref('Usuario');
const options_user = ref(['Candidato', 'Usuario']);


const form = useForm({
    name: '',
    email: '',
    identificacion: '',
    tipo: '',
    password: '',
    password_confirmation: '',
    terms: false,
    roles_user: '',
    eventos: 0,
    candidato: 0,
    votantes: '',

});

watch(tipo_user, (value) => {

    if (value == 'Candidato') {
        form.candidato = 1;

    } else {
        form.candidato = 0;
    }

});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: function () {
            form.reset('password', 'password_confirmation');
            swal({
                title: "Registro Guardado",
                text: "El usuario se ha almacenado exitosamente",
                icon: "success"
            })
        }
    });
};

const validateFile = (event) => {
    const file = event.target.files[0];

    // Validar si se ha seleccionado un archivo
    if (!file) {
        form.errors.votantes = "Por favor, selecciona un archivo.";
        return;
    }

    // Validar el tamaño del archivo (en bytes)
    if (file.size > 2 * 1024 * 1024) { // 2 MB en bytes
        form.errors.votantes = "El archivo no debe ser mayor a 2 MB.";
        return;
    }

    // Reiniciar errores si la validación es exitosa
    form.errors.votantes = null;
    // Asignar el archivo al modelo de datos
    form.votantes = file;
    console.log(form.votantes);
};

const cargueMasivo = () => {
    form.post(route('cargueVotantes'), {
        forceFormData: true,
        onSuccess: () => {
            // Verificar si la propiedad 'success' está presente en la respuesta
            if (props.numRegistrosInsertados !== undefined && props.numRegistrosActualizados !== undefined) {

                swal({
                    title: "Registros Cargados",
                    text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y ${props.numRegistrosActualizados} actualizados`,
                    icon: "success",
                });
            } else if (props.numRegistrosInsertados !== undefined && props.numRegistrosActualizados == undefined) {
                // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
                swal({
                    title: "Registros Cargados",
                    text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y 0 actualizados`,
                    icon: "success",
                });
            } else if (props.numRegistrosInsertados == undefined && props.numRegistrosActualizados !== undefined) {
                // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
                swal({
                    title: "Registros Cargados",
                    text: `Se han importado 0 nuevos registros correctamente y ${props.numRegistrosActualizados} actualizados`,
                    icon: "success",
                });
            } else {
                swal({
                    title: "Registros Cargados",
                    text: "Se han importado los registros de forma masiva",
                    icon: "success",
                });
            }
        },
        // Manejar el error en caso de que ocurra algún problema con la solicitud
        onError: (error) => {
            swal({
                title: "Error",
                text: "Hubo un problema al procesar la solicitud.",
                icon: "error",
            });
        }
    });
};


</script>