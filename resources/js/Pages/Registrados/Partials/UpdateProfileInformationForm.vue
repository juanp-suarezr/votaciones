<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MultiSelect from 'primevue/multiselect';

import { Link, useForm, usePage } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const swal = inject('$swal');
const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },


});

const user = ref(props.user);


const form = useForm({
    nombres: props.user.nombres,
    apellidos: props.user.apellidos,
    edad: props.user.edad,
    tipo_documento: props.user.tipo_documento,
    identificacion: props.user.identificacion,
    direccion: props.user.direccion,
    sector: props.user.sector,
    telefono: props.user.telefono,
    email: props.user.email,
    genero: props.user.genero,
    condicion: props.user.condicion,
    etnia: props.user.etnia,
    nivel_estudio: props.user.nivel_estudio,

});



//ITEMS DEL TIPO DE IDENTIFICACION
const tipoIden = ref([
    { id: '0', nombre: "Tarjeta de Identidad", code: 'TI' },
    { id: '1', nombre: "Registro Civil", code: 'R' },
    { id: '2', nombre: "Cédula Ciudadanía", code: 'CC' },
    { id: '3', nombre: "Cédula Extranjería", code: 'CE' },
    { id: '4', nombre: "Pasaporte", code: 'P' },
    { id: '5', nombre: "Otro", code: 'O' },
]);
//ITEMS DE CONDICIONES
const condiciones = ref([
    { id: '0', nombre: 'Sin condición', code: 'NA' },
    { id: '1', nombre: 'Discapacitado', code: 'discapacitado' },
    { id: '2', nombre: 'Desplazados', code: 'desplazados' },
    { id: '3', nombre: 'Victimas conflicto armado interno', code: 'victimasConfArm' },
    { id: '4', nombre: 'Mujer cabeza de hogar', code: 'mujerCabHogar' },
    { id: '5', nombre: 'Diversidad sexual', code: 'diversidadSex' },
    { id: '6', nombre: 'Habitante de calle', code: 'habitanteCalle' },
    { id: '7', nombre: 'Migrante', code: 'migrante' },
]);

//ITEMS DE ETNIAS
const etnias = ref([
    { id: '0', nombre: 'No aplica', code: 'NA' },
    { id: '1', nombre: 'Mestizo', code: 'mestizo' },
    { id: '2', nombre: 'Afrocolombiano', code: 'afro' },
    { id: '3', nombre: 'Indígena', code: 'indigena' },
    { id: '4', nombre: 'Palanquero', code: 'palanquero' },
    { id: '5', nombre: 'Razal', code: 'razal' },
    { id: '6', nombre: 'ROM', code: 'rom' },
]);

//ITEMS DE nivel_estudios
const nivel_estudios = ref([
    { id: '0', nombre: 'Ninguno', code: 'NA' },
    { id: '1', nombre: 'Primaria', code: 'primaria' },
    { id: '2', nombre: 'Secundaria', code: 'secundaria' },
    { id: '3', nombre: 'Tecnico', code: 'tecnico' },
    { id: '4', nombre: 'Tecnologico', code: 'tecnologico' },
    { id: '5', nombre: 'Universitario', code: 'universitario' },
    { id: '6', nombre: 'Postgrado', code: 'postgrado' },
]);


const submit = () => {
    console.log(user.value.id);
    form.patch(route('registrados.update', { id: user.value.id }), {
        onSuccess: () => swal({
            title: "Registro Actualizado",
            text: "El usuario se ha actualizado exitosamente",
            icon: "success"
        })
    });
};


</script>

<template>
    <section>
        <header>
            <div class="grid grid-cols-6">
                <div class="col-span-4">
                    <h2 class="text-lg font-medium text-gray-900">Información registrado</h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Actualice la información especifica del registro
                    </p>
                </div>
                <div class="col-span-2 text-right">
                    <SecondaryButton :href="route('registrados.index')">
                        Regresar
                    </SecondaryButton>
                </div>
            </div>

        </header>
        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="sm:grid grid-cols-2 gap-4">

                <!-- nombres -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="nombres" value="Nombres" />
                    <TextInput id="nombres" type="text" class="mt-1 block w-full p-0 sm:p-2" v-model="form.nombres"
                        required autofocus autocomplete="nombre" />
                    <InputError class="mt-1" :message="form.errors.nombres" />
                </div>
                <!-- apellidos -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="apellidos" value="Apellidos" />
                    <TextInput id="apellidos" type="text" class="mt-1 block w-full p-0 sm:p-2" v-model="form.apellidos"
                        required autocomplete="apellidos" />
                    <InputError class="mt-1" :message="form.errors.apellidos" />
                </div>

                <!-- edad e identificacion -->
                <div class="col-span-2 mb-4 sm:flex">

                    <!-- edad -->
                    <div class="w-3/12 sm:pe-6">
                        <InputLabel class="sm:text-sm text-xs" for="edad" value="Edad" />
                        <TextInput id="edad" type="number" class="mt-1 p-0 sm:p-2 block w-full" v-model="form.edad"
                            required autocomplete="edad" />
                        <InputError class="mt-1" :message="form.errors.edad" />
                    </div>

                    <!-- identificacion -->
                    <div class="w-full sm:w-9/12 grid grid-cols-4 gap-4 mt-4 sm:mt-0">
                        <!-- tipo de identificacion -->
                        <div class="col-span-1">
                            <InputLabel for="tipo" value="Tipo" class="text-neutral-500 sm:text-sm text-xs" />
                            <select id="tipo" name="tipo" v-model="form.tipo_documento"
                                class="block w-full sm:px-4 sm:py-2 p-0 mt-1 text-base text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500">

                                <option class="text-black text-[9px] sm:text-xs" v-for="tipo in tipoIden" :key="tipo.id"
                                    :value="tipo.nombre">
                                    {{ tipo.nombre }}
                                </option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.tipo_documento" />
                        </div>
                        <!-- Identificacion -->
                        <div class="col-span-3">
                            <InputLabel for="identificacion" value="identificacion" />
                            <TextInput id="identificacion" type="number" class="mt-1 block w-full p-0 sm:p-2"
                                v-model="form.identificacion" required autocomplete="identificacion" />
                            <InputError class="mt-1" :message="form.errors.edad" />
                        </div>

                    </div>



                </div>

                <!-- direccion -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="direccion" value="Dirección de residencia" />
                    <TextInput id="direccion" type="text" class="mt-1 block w-full p-0 sm:p-2" v-model="form.direccion"
                        required autofocus autocomplete="direccion" />
                    <InputError class="mt-1" :message="form.errors.direccion" />
                </div>
                <!-- sector -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="sector" value="Barrio o vereda" />
                    <TextInput id="sector" type="text" class="mt-1 block w-full p-0 sm:p-2" v-model="form.sector"
                        required autofocus autocomplete="sector" />
                    <InputError class="mt-1" :message="form.errors.sector" />
                </div>
                <!-- telefono -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="telefono" value="Telefono" />
                    <TextInput id="telefono" type="number" class="mt-1 block w-full p-0 sm:p-2" v-model="form.telefono"
                        required autofocus autocomplete="telefono" />
                    <InputError class="mt-1" :message="form.errors.telefono" />
                </div>
                <!-- email -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="email" value="Usuario" />
                    <TextInput id="email" type="email" class="mt-1 block w-full p-0 sm:p-2" v-model="form.email"
                        required autofocus autocomplete="username" />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- genero -->
                <div class="mb-4">
                    <InputLabel class="sm:text-sm text-xs" for="genero" value="Genero" />
                    <div class="mt-2 block sm:flex">
                        <div class="">
                            <input type="radio" id="masculino" value="Masculino" v-model="form.genero" />
                            <label class="ps-2 text-sm sm:text-base" for="masculino">Masculino</label>
                        </div>
                        <div class="sm:ms-6">
                            <input type="radio" id="femenino" value="Femenino" v-model="form.genero" />
                            <label class="ps-2 text-sm sm:text-base" for="femenino">Femenino</label>
                        </div>
                        <!-- otros generos -->
                        <div v-if="IsNewGenero" class="sm:ms-6">
                            <InputLabel class="sm:text-sm text-xs" for="genero" value="¿Cual?" />
                            <TextInput id="genero" type="text" class="mt-1 block w-full p-0 sm:p-1"
                                v-model="form.genero" autofocus autocomplete="genero" />
                            <InputError class="mt-1" :message="form.errors.genero" />
                        </div>
                        <div v-else class="sm:ms-6">
                            <input type="radio" id="two" value="true" v-model="IsNewGenero" />
                            <label class="ps-2 text-sm sm:text-base" for="two">Otro</label>
                        </div>




                    </div>

                </div>
                <!-- condicion -->
                <div class="mb-4">
                    <InputLabel for="condicion" value="Condición" class="text-neutral-500 sm:text-sm text-xs" />
                    <select id="condicion" name="condicion" v-model="form.condicion"
                        class="block w-full sm:px-4 sm:py-2 p-0 mt-1 text-base text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500">

                        <option class="text-black" v-for="con in condiciones" :key="con.id" :value="con.code">
                            {{ con.nombre }}
                        </option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.condicion" />
                </div>
                <!-- etnia -->
                <div class="mb-4">
                    <InputLabel for="etnia" value="Etnia" class="text-neutral-500 sm:text-sm text-xs" />
                    <select id="etnia" name="etnia" v-model="form.etnia"
                        class="block w-full sm:px-4 sm:py-2 p-0 mt-1 text-base text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500">

                        <option class="text-black" v-for="et in etnias" :key="et.id" :value="et.code">
                            {{ et.nombre }}
                        </option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.etnia" />
                </div>
                <!-- nivel estudio -->
                <div class="mb-4">
                    <InputLabel for="nivel_estudio" value="Nivel de estudio"
                        class="text-neutral-500 sm:text-sm text-xs" />
                    <select id="nivel_estudio" name="nivel_estudio" v-model="form.nivel_estudio"
                        class="block w-full sm:px-4 sm:py-2 p-0 mt-1 text-base text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500">

                        <option class="text-black" v-for="niv in nivel_estudios" :key="niv.id" :value="niv.code">
                            {{ niv.nombre }}
                        </option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.nivel_estudio" />
                </div>

            </div>
            <div class="flex items-center gap-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Guardar
                </PrimaryButton>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Actualizado.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
