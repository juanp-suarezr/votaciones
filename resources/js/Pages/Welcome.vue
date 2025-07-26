<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";
import Pagination from "@/Components/Pagination.vue";
import ProgressSpinner from "primevue/progressspinner";

import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
const swal = inject("$swal");

const IsSecondTime = ref(false);
//loading
const loading = ref(false);
//info
const registro = ref([]);

// pass filters in search
let identificacion = ref(null);

//busqueda
const buscarCertificados = async () => {
  loading.value = true;
  try {
    const response = await axios.get(
      `/validar-certificado/${identificacion.value}`
    );
    registro.value = response.data.registro; // Aquí recibes el paginado
  } catch (error) {
    registro.value = [];
    swal.fire({
      icon: "error",
      title: "Error",
      text: "Error en el proceso de búsqueda.",
    });
    // Puedes mostrar un swal de error aquí si lo deseas
  } finally {
    console.log(registro.value);

    loading.value = false;
    IsSecondTime.value = true;
  }
};

//Liampiar filtros
const clearAll = () => {
  identificacion.value = "";
  registro.value = [];
};

//dercargar certificado
const descargarCertificado = (ev, idVotante) => {
  window.open(
    route("certificados.descargar", { id: ev, idVotante: idVotante }),
    "_blank"
  );
};
</script>

<template>
  <Head title="Welcome" />

  <div
    class="relative min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white flex items-center justify-center sm:px-16 px-4"
  >
    <div class="w-full">
      <div class="sm:flex justify-start mb-8 mt-2">
        <img src="/assets/img/logo_white.png" alt="Logo" class="h-24 w-auto" />
        <div class="w-full flex justify-end items-center">
          <a class="text-white text-xl" href="#certificados_info">
            Consultar certificado
          </a>
        </div>
      </div>

      <!-- seccion 1 -->
      <div
        class="grid grid-cols-1 md:grid-cols-5 gap-8 items-center bg-white dark:bg-gray-800/70 rounded-xl shadow-lg p-8 mb-12"
      >
        <!-- Texto lado izquierdo -->
        <div
          class="flex flex-col justify-center items-start md:col-span-3 lg:px-16"
        >
          <h1
            class="text-4xl md:text-5xl font-extrabold text-red-600 mb-6 leading-tight"
          >
            ¡Vota Ya! <br />
            <span class="text-gray-800 dark:text-white"
              >Tu voz, tu decisión</span
            >
          </h1>
          <p class="text-lg md:text-2xl text-gray-700 dark:text-gray-300 mb-4">
            Plataforma de votaciones más segura y fácil para votar de forma
            digital.
            <br />
            Elige, opina y haz parte del cambio desde cualquier lugar.
          </p>
        </div>
        <!-- imagen lado derecho -->
        <div class="flex flex-col justify-center items-start md:col-span-2">
          <div class="flex justify-start mb-8">
            <img
              src="/assets/img/votaYa.webp"
              alt="Logo"
              class="h-full w-auto"
            />
          </div>
        </div>
      </div>

      <!-- seccion 2 paso a paso presupuesto participativo -->
      <div
        class="md:grid md:grid-cols-3 gap-8 items-stretch bg-white dark:bg-gray-800/70 rounded-xl shadow-lg p-8 mb-12"
      >
        <!-- info general -->
        <div class="flex justify-center items-center col-span-3 mb-4">
          <h2 class="text-4xl text-center font-bold text-white">
            Pasos para participar en las votaciones del presupuesto
            participativo
          </h2>
        </div>
        <!-- Paso 1 -->
        <div
          class="flex flex-col justify-between items-start bg-gray-50 dark:bg-gray-900/40 rounded-lg p-6 shadow"
        >
          <h2 class="text-2xl font-bold text-red-600 mb-4">1. Regístrate</h2>
          <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 mb-4">
            Ingresa tus datos personales, sube foto de tu documento y realiza el
            registro biométrico. Recibirás una notificación por correo si tu
            registro fue aprobado. Si es rechazado, podrás corregir la
            información.
          </p>
          <Link
            href="#registro_info"
            class="mt-auto px-4 py-2 bg-red-600 text-white font-semibold rounded shadow hover:bg-red-700 transition"
          >
            Instructivo
          </Link>
        </div>
        <!-- Paso 2 -->
        <div
          class="flex flex-col justify-between items-start bg-gray-50 dark:bg-gray-900/40 rounded-lg p-6 shadow"
        >
          <h2 class="text-2xl font-bold text-red-600 mb-4">
            2. Vota por tu proyecto
          </h2>
          <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 mb-4">
            En las fechas estipuladas, ingresa al software y vota por el
            proyecto de tu preferencia.
          </p>
          <Link
            :href="route('login')"
            class="mt-auto px-4 py-2 bg-indigo-600 text-white font-semibold rounded shadow hover:bg-indigo-700 transition"
          >
            Ir a votación
          </Link>
        </div>
        <!-- Paso 3 -->
        <div
          class="flex flex-col justify-between items-start bg-gray-50 dark:bg-gray-900/40 rounded-lg p-6 shadow"
        >
          <h2 class="text-2xl font-bold text-red-600 mb-4">
            3. Descarga tu certificado
          </h2>
          <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 mb-4">
            Descarga tu certificado de participación en el software o accede a
            la sección específica para obtenerlo.
          </p>
          <Link
            href="#certificados_info"
            class="mt-auto px-4 py-2 bg-green-600 text-white font-semibold rounded shadow hover:bg-green-700 transition flex items-center gap-2"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
            Descargar certificado
          </Link>
        </div>
      </div>

      <!-- Nuevo grid landing registro info-->
      <div
        id="registro_info"
        class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-white dark:bg-gray-800/70 rounded-xl shadow-lg p-8 mb-12"
      >
        <!-- Video lado izquierdo -->
        <div class="flex justify-center items-center">
          <video
            class="rounded-lg w-full h-auto max-h-[400px] shadow-md"
            controls
            poster="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80"
          >
            <source
              src="https://www.w3schools.com/html/mov_bbb.mp4"
              type="video/mp4"
            />
            Tu navegador no soporta el video.
          </video>
        </div>
        <!-- Texto lado derecho -->
        <div class="flex flex-col justify-center items-start">
          <h1
            class="text-4xl md:text-5xl font-extrabold text-red-600 mb-6 leading-tight"
          >
            ¡Registro! <br />
            <span class="text-gray-800 dark:text-white"
              >Presupuesto participativo</span
            >
          </h1>
          <p class="text-lg md:text-2xl text-gray-700 dark:text-gray-300 mb-4">
            Participa y vota por los proyectos que más beneficien a tu comunidad
            de forma más segura y moderna.
            <br />
            <i>Tienes un plazo para inscribirte del 15 al 25 de octubre</i>
          </p>
          <Link
            :href="route('register')"
            class="mt-4 px-6 py-3 bg-red-600 text-white font-bold rounded-lg shadow hover:bg-red-700 transition"
          >
            ¡Regístrate -- presupuesto participativo!
          </Link>
        </div>
      </div>
      <!-- consultar certificados -->
      <div
        id="certificados_info"
        class="w-full bg-white dark:bg-gray-800/70 rounded-xl shadow-lg p-8 mb-12"
      >
        <!-- Filtro de búsqueda -->
        <div class="w-full">
          <div class="bg-gray-50 dark:bg-gray-900/40 shadow-md rounded-lg p-4">
            <span class="text-2xl text-center font-bold text-white mb-6">
              Ingresar número de identificación para consultar los certificados
              registrados a su nombre
            </span>
            <div class="sm:flex items-center gap-4 mt-2 h-full w-auto">
              <TextInput
                id="identificacion"
                v-model="identificacion"
                placeholder="Ingrese el número de identificación"
                class="sm:w-1/2 flex inline-flex mb-2 text-white bg-gray-900/40"
                @keydown.enter="buscarCertificados"
              />
              <SecondaryButton
                class="bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br font-medium text-white"
                @click="buscarCertificados"
                >Buscar</SecondaryButton
              >
              <SecondaryButton @click="clearAll" class="hover:underline">
                Limpiar
              </SecondaryButton>
            </div>
          </div>
        </div>
        <!-- Tabla de resultados - right side -->
        <div class="mt-6">
          <div class="bg-gray-50 dark:bg-gray-900/40 shadow-md rounded-lg p-4">
            <div
              v-if="registro.data && registro.data.length > 0"
              class="w-full overflow-x-auto"
            >
              <table class="table-medium w-full border-collapse">
                <thead class="bg-gray-900/40 text-red-600 text-bold">
                  <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Nombre del Usuario</th>
                    <th class="px-4 py-2 text-left">Identificación</th>
                    <th class="px-4 py-2 text-left">Evento</th>

                    <th class="px-4 py-2 text-left">Descargar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(info, index) in registro.data" :key="index">
                    <td class="px-4 py-2 text-white">
                      {{ index + 1 }}
                    </td>
                    <td class="px-4 py-2 text-white break-words">
                      {{ info.votante.nombre || "Sin información" }}
                    </td>
                    <td class="px-4 py-2 text-white">
                      {{ info.votante.tipo_documento }} -
                      {{ info.votante.identificacion }}
                    </td>
                    <td class="px-4 py-2 text-white">
                      {{ info.evento.nombre }}
                    </td>

                    <td class="px-4 py-2 text-white">
                      <PrimaryButton
                        @click="
                          descargarCertificado(info.evento.id, info.votante.id)
                        "
                      >
                        Descargar certificado
                      </PrimaryButton>
                    </td>
                  </tr>
                </tbody>
              </table>
              <!-- Diseño vertical para pantallas pequeñas -->
              <div class="table-responsive grid grid-cols-1 gap-4 sm:hidden">
                <div
                  v-for="(info, index) in registro.data"
                  :key="index"
                  class="rounded-lg p-4 shadow-md text-white"
                >
                  <p>
                    <strong class="text-red-600">#:</strong> {{ index + 1 }}
                  </p>
                  <p>
                    <strong class="text-red-600">Nombre del Usuario:</strong>
                    {{ info.votante.nombre || "Sin información" }}
                  </p>
                  <p>
                    <strong class="text-red-600">Identificación:</strong>
                    {{ info.votante.tipo_documento }} --
                    {{ info.votante.identificacion }}
                  </p>
                  <p>
                    <strong class="text-red-600">Evento:</strong>
                    {{ info.evento.nombre }}
                  </p>

                  <div class="px-4 py-2 text-white">
                    <PrimaryButton
                      @click="
                        descargarCertificado(info.evento.id, info.votante.id)
                      "
                    >
                      Descargar certificado
                    </PrimaryButton>
                  </div>
                </div>
              </div>
            </div>
            <div
              class="w-full flex justify-center items-center"
              v-else-if="loading"
            >
              <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="8"
                fill="transparent"
                animationDuration=".5s"
                aria-label="Custom ProgressSpinner"
              />
            </div>
            <div class="w-full flex justify-center items-center" v-else>
              <em
                v-if="identificacion && IsSecondTime"
                class="text-xl text-white"
                >No hay votaciones registradas al usuario con numero de
                identificación --> {{ identificacion }}.
              </em>
              <div class="w-1/2 flex justify-center items-center" v-else>
                <div class="bg-azul rounded-md shadow-lg p-4">
                  <h3 class="text-white sm:text-2xl text-xl text-center">
                    certificado votaciones
                  </h3>
                </div>
              </div>
            </div>

            <!-- Paginación -->
            <Pagination
              v-if="registro.data && registro.data.length > 0"
              :links="registro.links"
              class="mt-4"
            />
          </div>
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

@media screen and (max-width: 900px) {
  .table-medium {
    display: none;
  }

  .table-responsive {
    display: block;
  }
}
</style>
