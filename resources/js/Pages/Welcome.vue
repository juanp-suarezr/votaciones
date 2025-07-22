<script setup>
import { Head, Link } from '@inertiajs/vue3';


</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white flex items-center justify-center sm:px-16 px-4"
    >
        <div class="w-full">

            <div class="flex justify-start mb-8">
                <img src="/assets/img/logo_white.png" alt="Logo" class="h-24 w-auto" />
            </div>

            <!-- Nuevo grid landing -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-white dark:bg-gray-800/70 rounded-xl shadow-lg p-8 mb-12">
                <!-- Video lado izquierdo -->
                <div class="flex justify-center items-center">
                    <video
                        class="rounded-lg w-full h-auto max-h-[400px] shadow-md"
                        controls
                        poster="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80"
                    >
                        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4" />
                        Tu navegador no soporta el video.
                    </video>
                </div>
                <!-- Texto lado derecho -->
                <div class="flex flex-col justify-center items-start">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-red-600 mb-6 leading-tight">
                        ¡Vota Ya! <br />
                        <span class="text-gray-800 dark:text-white">Tu voz, tu decisión</span>
                    </h1>
                    <p class="text-lg md:text-2xl text-gray-700 dark:text-gray-300 mb-4">
                        Participa en la plataforma de votaciones más segura y moderna.
                        Elige, opina y haz parte del cambio desde cualquier lugar.
                    </p>
                    <Link
                        :href="route('register')"
                        class="mt-4 px-6 py-3 bg-red-600 text-white font-bold rounded-lg shadow hover:bg-red-700 transition"
                    >
                        ¡Regístrate -- presupuesto participativo!
                    </Link>
                </div>
            </div>


        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
