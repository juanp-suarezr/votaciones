<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";
import Pagination from "@/Components/Pagination.vue";
import ProgressSpinner from "primevue/progressspinner";
import {
  XCircleIcon,
  InformationCircleIcon,
  ChatBubbleLeftRightIcon,
} from "@heroicons/vue/24/solid";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
const swal = inject("$swal");
import AOS from "aos";
import "aos/dist/aos.css";
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON
import barrios from "@/shared/barrios_comuna.json"; // Importa el JSON
import barrios_por_comuna from "@/shared/barrios.json"; // Importa el JSON
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Select, Textarea } from "primevue";

AOS.init();

const props = defineProps({
  isActive: Boolean,
});

console.log(props);

const form = useForm({
  nombre: "",
  identificacion: "",
  celular: "",
  descripcion: "",
  campo_obligatorio: "",
});

const STORAGE_KEY = "panel_visible";

// Inicializar el panel desde localStorage o por tamaño de pantalla
const mostrarPanel = ref(false);

const tipoBusqueda = ref("");
//loading
const loading = ref(false);
//info
const comunaSelected = ref(null);
const resultados = ref([]);

const barrioSelected = ref(null);

// para verificar si cambia el tipo de busqueda
watch(tipoBusqueda, (nuevoValor) => {
  if (nuevoValor === "comuna") {
    barrioSelected.value = null; // Limpiar selección de barrio
  } else if (nuevoValor === "barrio") {
    comunaSelected.value = null; // Limpiar selección de comuna
  }
});

//Liampiar filtros
const clearAll = () => {
  identificacion.value = "";
  registro.value = [];
};

//buscar resultados x comuna
const buscar = () => {
  resultados.value = [];

  console.log(barrioSelected.value);

  if (tipoBusqueda.value === "comuna" && comunaSelected.value) {
    loading.value = true;

    // Filtrar barrios por comuna seleccionada
    let barriosSeleccionados = barrios_por_comuna.find(
      (barrio) => barrio.id === parseInt(comunaSelected.value.value)
    );

    if (barriosSeleccionados) {
      resultados.value = barriosSeleccionados.barrios.map((bar, index) => ({
        id: index,
        nombre: bar,
      }));
    }

    loading.value = false;
  } else if (tipoBusqueda.value === "barrio" && barrioSelected.value) {
    loading.value = true;
    console.log(barrioSelected.value.idComuna);

    // Filtrar comunas por barrio seleccionada
    let comunaActiva = comunas.find(
      (comuna) => parseInt(comuna.value) === barrioSelected.value.idComuna
    );
    console.log(comunaActiva);

    if (comunaActiva) {
      resultados.value = [
        {
          id: comunaActiva.id,
          nombre: comunaActiva.label,
        },
      ];
    }
    console.log(resultados.value);

    loading.value = false;
  } else {
    swal({
      title: "Error",
      text: "Por favor, selecciona una comuna o barrio.",
      icon: "error",
    });
  }
};

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY);

  if (saved !== null) {
    mostrarPanel.value = saved === "true";
  } else {
    // Default para pantallas grandes
    mostrarPanel.value = window.innerWidth >= 768;
  }
});

// Guardar el estado cada vez que cambia
watch(mostrarPanel, (nuevoValor) => {
  localStorage.setItem(STORAGE_KEY, nuevoValor);
});

const cambiarVista = () => {
  mostrarPanel.value = !mostrarPanel.value;
};

//enviar msj
const enviarSolicitud = () => {
  form.post(route("enviarSolicitud"), {
    onSuccess: function () {
      swal({
        title: "Solicitud enviada con éxito",
        text: "La solicitud ha sido aprobado exitosamente, muy pronto recibirá respuesta",
        icon: "success",
      }).then(() => {
        mostrarPanel.value = false;
      });
    },
    onError: function () {
      swal({
        title: "Error al enviar la solicitud",
        text: "Error al intentar enviar este registro, por favor vuelve a intentar",
        icon: "error",
      });
    },
  });
};
</script>

<template>
  <Head title="Consulta de Comunas y Barrios" />

  <div
    class="relative min-h-screen bg-dots-darker bg-option2 bg-center selection:bg-red-500 selection:text-white flex justify-center body-landing"
  >
    <!-- Botón flotante para abrir/cerrar panel -->
    <button
      @click="cambiarVista"
      class="lg:flex fixed right-4 bottom-6 z-20 p-2 bg-blue-700 hover:bg-blue-800 text-white rounded-full shadow-lg"
      title="Mostrar/Ocultar información"
    >
      <component
        :is="mostrarPanel ? XCircleIcon : InformationCircleIcon"
        class="w-6 h-6 lg:w-8 lg:h-8"
      />
      <p class="my-auto ps-2 text-lg sm:block hidden">
        {{ mostrarPanel ? "" : " Soporte" }}
      </p>
    </button>
    <!-- Panel fijo lateral -->
    <div
      v-show="mostrarPanel"
      class="lg:block fixed right-4 top-[10%] lg:w-72 md:w-64 w-56 space-y-4 z-10 transition-all duration-300"
    >
      <!-- Tarjeta de soporte -->
      <div class="bg-white shadow-md rounded-xl p-4 border border-red-200">
        <h4
          class="text-sm md:text-base font-semibold text-red-700 mb-2 flex items-center gap-1"
        >
          <InformationCircleIcon class="h-4 w-4" />
          ¿Necesitas soporte?
        </h4>
        <!-- Formulario de solicitud de soporte -->
        <div class="bg-white shadow-md rounded-xl p-4 border border-blue-200">
          <h4 class="text-sm md:text-base font-semibold text-blue-700 mb-2">
            Enviar solicitud
          </h4>
          <form @submit.prevent="enviarSolicitud">
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700"
                >Identificación</label
              >
              <input
                v-model="form.identificacion"
                type="number"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700">Celular</label>
              <input
                v-model="form.celular"
                type="tel"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700"
                >Descripción</label
              >
              <Textarea
                v-model="form.descripcion"
                variant="filled"
                autoResize
                rows="3"
                class="mt-1 block w-full"
                required
                autocomplete="descripcion"
              />
            </div>
            <div class="mb-2 hidden">
              <label class="text-xs md:text-sm text-gray-700"
                >campo obligatorio</label
              >
              <input
                v-model="form.campo_obligatorio"
                rows="3"
                class="w-full p-2 rounded border border-gray-300 text-sm"
              />
            </div>
            <PrimaryButton
              class="mt-2"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Enviar
            </PrimaryButton>
          </form>
        </div>
      </div>
    </div>
    <div class="w-full">
      <!-- menu -->
      <div
        class="sm:flex justify-start mt-2 sm:px-16 px-4 py-4 overflow-x-hidden"
      >
        <div
          class="flex flex-col sm:flex-row gap-4 items-center hover:scale-105"
        >
          <img
            src="/assets/img/logo1.png"
            alt="Logo"
            class="sm:h-32 h-24 w-auto sm:border-r border-black"
          />
          <img
            src="/assets/img/voto_electronico.png"
            alt="Logo"
            class="sm:h-32 h-24 w-auto"
          />
        </div>
        <!-- <div class="w-full flex justify-end items-center">
          <a class="text-white text-xl" href="#certificados_info">
            Consultar certificado
          </a>
        </div> -->
      </div>
      <!-- seccion 1 -->
      <div
        class="w-full md:grid md:grid-cols-5 gap-8 justify-center items-start mt-12 px-6"
      >
        <!-- Sección de filtros (lado derecho) -->
        <div
          class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 w-full h-full flex flex-col px-4 md:col-span-2"
        >
          <h2 class="text-xl font-bold text-primary mb-2">Buscar por</h2>
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-gray-700">Tipo de búsqueda</label>
            <select v-model="tipoBusqueda" class="border rounded-lg px-3 py-2">
              <option value="comuna">Comuna</option>
              <option value="barrio">Barrio</option>
            </select>
          </div>
          <div class="flex flex-col gap-2 mt-4" v-if="tipoBusqueda !== ''">
            <label class="font-semibold text-gray-700"
              >{{
                tipoBusqueda === "comuna"
                  ? "Selecciona una comuna"
                  : "Selecciona un barrio"
              }}
            </label>
            <Select
              v-if="tipoBusqueda === 'comuna'"
              id="comuna"
              v-model="comunaSelected"
              :options="comunas"
              filter
              optionLabel="label"
              placeholder="Seleccione comuna/corregimiento"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
            <Select
              v-else
              id="barrio"
              v-model="barrioSelected"
              :options="barrios"
              :virtualScrollerOptions="{
                itemSize: 38,
                showLoader: true,
              }"
              showClear
              filter
              optionLabel="nombre"
              placeholder="Seleccione barrio/vereda"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
          </div>
          <PrimaryButton class="mt-6" @click="buscar"> Buscar </PrimaryButton>
        </div>

        <!-- Sección de resultados (lado izquierdo) -->
        <div
          class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 w-full h-full flex flex-col px-4 md:col-span-3"
        >
          <h2 class="text-xl font-bold text-primary mb-2">Resultados</h2>
          <div v-if="resultados.length > 0" class="flex flex-wrap gap-4">
            <div
              v-for="res in resultados"
              :key="res.id"
              class="border border-blue-300 rounded-lg p-4 bg-blue-50 shadow-md min-w-[180px] flex flex-col items-center"
            >
              <span class="text-sm text-gray-600">
                {{ tipoBusqueda === "comuna" ? "Barrio/vereda" : "Comuna" }}:
                {{ res.nombre }}
              </span>
            </div>
          </div>
          <div v-else class="text-gray-500 text-center py-8">
            No hay resultados para la búsqueda seleccionada.
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

.bg-dots-lighter {
  background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
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
