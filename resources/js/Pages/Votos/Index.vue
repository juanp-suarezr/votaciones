<template>

    <Head title="Votaciones" />

    <AuthenticatedLayout>
        <template #header>
            Votaciones
        </template>

        <div class="items-center flex justify-center mx-2">

            <div class="sm:flex w-full sm:grid md:grid-cols-3 grid-cols-2 gap-4">
                <div v-for="candi in candidatos" :key="candi.id"
                    class="flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800">
                    <div class="mx-auto sm:w-3/6 lg:2/6">
                        <Avatar v-if="candi.votante.imagen == 'user.png'" :label="getInitials(candi.votante.nombre)"
                            class="w-full h-[60pt] sm:h-[100pt] bg-indigo-200 text-indigo-800 p-4 text-4xl"
                            shape="circle" />
                        <Avatar v-else :image="getImageUrl(candi.votante.imagen)"
                            class="w-full h-[60pt] sm:h-[100pt] bg-indigo-200 text-indigo-800 text-4xl"
                            shape="circle" />
                    </div>
                    <!-- Candidato -->
                    <div class="mt-4 mx-auto">
                        <h2 class="text-xl font-bold capitalize">{{ candi.votante.nombre }}</h2>
                        <h3 v-if="candi.votante.nombre == 'Voto En Blanco'" class="text-base text-gray-800"> CC {{ candi.votante.identificacion }}</h3>
                        <p class="text-sm text-gray-600"> Tipo: {{ candi.tipo }}</p>
                    </div>
                    <!-- votacion -->
                    <div class="mt-4 mx-auto">
                        <PrimaryButton type="button" @click="votar(candi)" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            Seleccionar
                        </PrimaryButton>

                    </div>
                </div>
            </div>



        </div>


    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Avatar from 'primevue/avatar';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from "@/Components/SecondaryLink.vue";
import InputError from '@/Components/InputError.vue';
import { inject } from 'vue';
const swal = inject('$swal');

const props = defineProps({
    candidatos: Object,
    votante: Object,
});

console.log(props);


const form = useForm({
    id_votante: props.votante[0].id,
    id_candidato: 0,
    id_eventos: 0,
    tipo: '',
    estado: 'Activo',

});


//funtion para avatar letter
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

//MOSTRAR IMAGEN EN TABLA 
//IMAGEN 
const getImageUrl = (imageName) => {
    // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
    return `/storage/uploads/usuarios/${imageName}`;
};

const votar = candidato => {
    console.log(candidato);
    

    form.id_candidato = candidato.id_votante;
    form.id_eventos = candidato.id_evento;
    form.tipo = candidato.tipo;

    form.post(route('votos.store'), {
        onSuccess: () => swal({
            title: "Voto registrado exitosamente",
            text: "Su voto es por " + candidato.votante.nombre,
            icon: "success"
        }).then((result) => {
            router.get('dashboard');
        }), onError: () => swal({
            title: "Error en el registro",
            text: "Su voto no fue asignado, vuelve a intentar nuevamente",
            icon: "error"
        })
    });
};

</script>