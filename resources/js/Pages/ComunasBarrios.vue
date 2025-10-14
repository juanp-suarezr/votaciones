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
import comunas from "@/shared/comunas.json"; // Importa el JSON
import barrios from "@/shared/barrios.json"; // Importa el JSON

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

const IsSecondTime = ref(false);
//loading
const loading = ref(false);
//info
const resultados = ref([]);

//is disabledsegun fecha
const eventoActivo = ref(props.isActive);

// pass filters in search
let identificacion = ref(null);

//Liampiar filtros
const clearAll = () => {
  identificacion.value = "";
  registro.value = [];
};

//dercargar certificado
const descargarCertificado = (ev, idVotante, id_padre) => {
  window.open(
    route("certificados.descargar", {
      id: ev,
      idVotante: idVotante,
      id_padre: id_padre,
    }),
    "_blank"
  );
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

  <AuthenticatedLayout>
    <div
      class="relative min-h-screen bg-dots-darker bg-option2 bg-center selection:bg-red-500 selection:text-white flex items-center justify-center body-landing"
    ></div>

    <!-- seccion 1 -->
    <div class="flex flex-wrap gap-8 justify-center items-start mt-12">
      <!-- Sección de filtros (lado derecho) -->
      <div
        class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 w-full md:w-1/3 flex flex-col gap-4"
      >
        <h2 class="text-xl font-bold text-primary mb-2">Buscar por</h2>
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-gray-700">Tipo de búsqueda</label>
          <select v-model="tipoBusqueda" class="border rounded px-3 py-2">
            <option value="comuna">Comuna</option>
            <option value="barrio">Barrio</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-gray-700"
            >{{
              tipoBusqueda === "comuna"
                ? "Selecciona una comuna"
                : "Selecciona un barrio"
            }}
          </label>
          <select v-model="seleccionado" class="border rounded px-3 py-2">
            <option v-for="item in opciones" :key="item.id" :value="item.id">
              {{ item.nombre }}
            </option>
          </select>
        </div>
        <PrimaryButton class="mt-4" @click="buscar"> Buscar </PrimaryButton>
      </div>

      <!-- Sección de resultados (lado izquierdo) -->
      <div
        class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 w-full md:w-2/3 flex flex-col gap-4"
      >
        <h2 class="text-xl font-bold text-primary mb-2">Resultados</h2>
        <div v-if="resultados.length > 0" class="flex flex-wrap gap-4">
          <div
            v-for="res in resultados"
            :key="res.id"
            class="border border-blue-300 rounded-lg p-4 bg-blue-50 shadow-md min-w-[180px] flex flex-col items-center"
          >
            <span class="font-bold text-lg text-blue-700">
              {{ res.nombre }}
            </span>
            <span class="text-sm text-gray-600">
              {{ tipoBusqueda === "comuna" ? "Barrio" : "Comuna" }}:
              {{ res.relacion }}
            </span>
          </div>
        </div>
        <div v-else class="text-gray-500 text-center py-8">
          No hay resultados para la búsqueda seleccionada.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
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
