<template>

    <Head title="Votaciones" />

    <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
        <template #header>
            Votaciones
        </template>

        <!-- Accesibilidad: Lector de voz global -->
            <div class="w-full max-w-4xl mt-2 flex flex-col sm:flex-row items-end sm:items-center justify-end gap-3">
                <button
                    @click="leerTodasLasOpciones"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base shadow transition"
                    aria-label="Escuchar en voz alta todas las opciones de candidatos">
                    🔊 Leer todas las opciones
                </button>

                <!-- Controles de reproducción de voz -->
                <div class="flex items-center gap-2 bg-white/90 border border-gray-300 rounded-xl px-2 py-1 shadow-sm">
                    <button
                        @click="toggleReproducir"
                        :disabled="!lastSpokenText"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="paused ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-800 hover:bg-gray-900 text-white'"
                        :aria-label="paused ? 'Continuar lectura' : 'Reproducir última lectura'">
                        <span v-if="paused">▶️ Continuar</span>
                        <span v-else>▶️ Reproducir</span>
                    </button>

                    <button
                        @click="pausarLectura"
                        :disabled="!speaking"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed"
                        aria-label="Pausar lectura">
                        ⏸️ Pausar
                    </button>

                    <button
                        @click="detenerLectura"
                        :disabled="!speaking && !paused"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed"
                        aria-label="Detener lectura completamente">
                        ⏹️ Detener
                    </button>
                </div>
            </div>

        <div class="items-center flex  justify-center mx-2">

            <div class="sm:flex w-full sm:grid md:grid-cols-3 grid-cols-2 gap-4">
                <div v-for="candi in candidatos" :key="candi.id"
                    class="flex flex-col w-full h-full p-3 inline-block m-auto mb-4 rounded-2xl shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800 focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                    
                    <!-- Foto / Avatar cuadrado con estilo -->
                    <div class="relative w-full mx-auto overflow-hidden rounded-2xl shadow-md ring-1 ring-gray-300 bg-gray-100 aspect-[4/3] sm:aspect-square">
                        <div v-if="candi.votante.imagen == 'user.png'"
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-200 via-indigo-300 to-violet-200 text-indigo-900">
                            <span class="text-[3.2rem] sm:text-[4.5rem] font-black tracking-[0.08em] select-none drop-shadow">
                                {{ getInitials(candi.votante.nombre) }}
                            </span>
                        </div>
                        <img v-else
                            :src="getImageUrl(candi.votante.imagen)"
                            :alt="`Foto de ${candi.votante.nombre}`"
                            class="w-full h-full object-cover" />

                        <!-- Botón accesibilidad por candidato -->
                        <button
                            @click.stop="leerCandidato(candi)"
                            class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 hover:bg-white shadow text-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :aria-label="`Escuchar información de ${candi.votante.nombre}`"
                            title="Escuchar información del candidato">
                            🔊
                        </button>
                    </div>

                    <!-- Información del candidato -->
                    <div class="mt-4 mx-auto text-center px-1">
                        <h2 class="text-xl sm:text-2xl font-bold capitalize leading-tight">{{ candi.votante.nombre }}</h2>
                        <h3 v-if="candi.votante.nombre !== 'Voto En Blanco'" class="text-base text-gray-700 mt-0.5">CC {{ candi.votante.identificacion }}</h3>
                        <p class="text-sm text-gray-600 mt-1 font-medium">Tipo: {{ candi.tipo }}</p>
                    </div>

                    <!-- Botón votar -->
                    <div class="mt-auto pt-4 mx-auto">
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
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from "@/Components/SecondaryLink.vue";
import { inject, ref } from 'vue';
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

const breadcrumbLinks = [
    { url: '', text: 'Votaciones' },
];

/* ===========================================
   🔊 Estado y controles de lector de voz (pause/resume/stop)
   =========================================== */
const speaking = ref(false);
const paused = ref(false);
const lastSpokenText = ref('');

const leerTexto = (texto) => {
    // Guardar el último texto solicitado
    lastSpokenText.value = texto;

    // Detener cualquier reproducción anterior
    window.speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(texto);
    speech.lang = "es-CO";
    speech.rate = 0.95;
    speech.pitch = 1.05;

    speech.onend = () => {
        speaking.value = false;
        paused.value = false;
    };
    speech.onerror = () => {
        speaking.value = false;
        paused.value = false;
    };

    window.speechSynthesis.speak(speech);
    speaking.value = true;
    paused.value = false;
};

const toggleReproducir = () => {
    if (paused.value) {
        // Reanudar desde donde se pausó
        window.speechSynthesis.resume();
        paused.value = false;
        speaking.value = true;
    } else if (lastSpokenText.value) {
        // Reproducir desde el inicio el último texto (después de stop o nuevo)
        leerTexto(lastSpokenText.value);
    }
};

const pausarLectura = () => {
    if (window.speechSynthesis.speaking && !paused.value) {
        window.speechSynthesis.pause();
        paused.value = true;
        speaking.value = false;
    }
};

const detenerLectura = () => {
    window.speechSynthesis.cancel();
    speaking.value = false;
    paused.value = false;
    // NO limpiamos lastSpokenText: el botón Reproducir lo usará desde el inicio
};


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

const leerCandidato = (candi) => {
    let texto = `Opción de votación. Candidato: ${candi.votante.nombre}. `;
    if (candi.votante.nombre !== 'Voto En Blanco' && candi.votante.identificacion) {
        texto += `Identificación: ${candi.votante.identificacion}. `;
    }
    texto += `Tipo: ${candi.tipo}. `;
    leerTexto(texto);
};

const leerTodasLasOpciones = () => {
    let texto = "Opciones disponibles para votar. ";
    if (props.candidatos && props.candidatos.length) {
        props.candidatos.forEach((c, i) => {
            texto += `Opción ${i + 1}: ${c.votante.nombre}. Tipo ${c.tipo}. `;
        });
    }
    leerTexto(texto);
};

</script>
