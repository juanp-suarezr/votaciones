<template>

    <Head title="Registro" />

    <GuestLayout :isRegister=true>
        <Link href="/" class="flex items-center justify-center">
        <ApplicationLogo class="fill-current text-gray-500" />
        </Link>

        <form @submit.prevent="submit">
            <div class="sm:grid grid-cols-2 gap-4 mt-4">
                <!-- nombre -->
                <div class="mb-2">
                    <InputLabel for="nombre" value="Nombre" />
                    <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                        autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <!-- email -->
                <div class="mb-2">
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                        autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <!-- contraseña -->
                <div class="mb-2">
                    <InputLabel for="password" value="Password" />
                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                        autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <!-- confirm password -->
                <div class="mb-2">
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                        v-model="form.password_confirmation" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

            </div>
            <!-- botones y already exist? -->
            <div class="mt-6 flex flex-col items-center">
                <PrimaryButton class="flex justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>

                <Link :href="route('login')" class="mt-4 text-sm text-gray-600 underline hover:text-gray-900">
                ¿Ya está registrado?
                </Link>
            </div>

        </form>

    </GuestLayout>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import logoViveD from '../../../../public/assets/img/puntoViveDigital.png'
import Steps from 'primevue/steps';
import { ref, watch, inject } from 'vue'
import Dialog from 'primevue/dialog';
import VueQrcode from 'vue-qrcode';
import { useToast } from 'vue-toast-notification';


const $toast = useToast();


const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});



const submit = () => {



    form.post(route('register'), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },

        onError: () => {
            let instance = $toast.open({
                message: "Algo salio mal en el registro, revise los campos o vuelva a intentarlo por favor",
                type: 'warning',
                position: 'top-right',
                duration: 6000,
                pauseOnHover: true,

            });
        }


    });
};
</script>
