<template>

    <Head title="Agregar usuario" />

    <AuthenticatedLayout>
        <template #header>
            Nuevo usuario
        </template>

        <div class="flex flex-col bg-white border shadow-sm rounded-xl lg:w-1/2 md:w-2/3">
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
                            <option disabled value="" class="text-xs text-gray-400">Seleccione evento a asignar</option>
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
</script>