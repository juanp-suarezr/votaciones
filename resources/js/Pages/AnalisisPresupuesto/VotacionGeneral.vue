<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

import { EyeIcon } from "@heroicons/vue/24/solid";
import Select from "primevue/select";

import SecondaryLink from "@/Components/SecondaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SecondaryButtonReturn from "@/Components/SecondaryButtonReturn.vue";

import Chart from "primevue/chart";
import ChartDataLabels from "chartjs-plugin-datalabels";
import {
  Chart as ChartJS,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from "chart.js";

ChartJS.register(
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  ChartDataLabels
);

const swal = inject("$swal");

const props = defineProps({
  resumen_puestos: {
    type: Object,
    default: () => [],
  },
  votos_nulos: {
    type: Number,
    default: () => [],
  },
  votos_no_marcados: {
    type: Number,
    default: () => [],
  },
  votos_blanco: {
    type: Number,
    default: () => [],
  },
  votos_validados: {
    type: Number,
    default: () => [],
  },
  votos_virtuales: {
    type: Number,
    default: () => [],
  },
  votos_virtuales_tic: {
    type: Number,
    default: () => [],
  },
  votos_fisicos: {
    type: Number,
    default: () => [],
  },

  comuna: {
    type: String,
    default: () => "",
  },

  total_votos_virtuales: {
    type: Number,
    default: () => [],
  },
  total_votos_fisicos: {
    type: Number,
    default: () => "",
  },

  actas: {
    type: Array,
    default: () => [],
  },

});

console.log(props);

// Estado para el modal de reporte de actas
const showReporteModal = ref(false);
const selectedPuesto = ref(null);
const actasDelPuesto = ref([]);

function abrirReporteActas(puesto) {
  selectedPuesto.value = puesto;
  actasDelPuesto.value = (props.actas || []).filter(
    (a) => Number(a.puesto_votacion) === Number(puesto.puesto_id)
  );
  showReporteModal.value = true;
}

const baseColors = [
  "#C20E1A", // primary
  "#004884", // azul
  "#E20613", // secondary
  "#003070", // azul
  "#585857", // parrafo
];

//VARS PARA CHART

// Función para asignar colores a cada segmento del pie
function getPieColors(length) {
  const colors = [];
  for (let i = 0; i < length; i++) {
    colors.push(baseColors[i % baseColors.length]);
  }
  return colors;
}

// Calcular datos para el gráfico de pie
const chartData = ref({
  labels: ["Voto virtual", "Voto presencial tic", "Voto físico"],
  datasets: [
    {
      data: [
        props.votos_virtuales,
        props.votos_virtuales_tic,
        props.votos_fisicos,
      ],
      backgroundColor: ["#5cb85c", "#004884", "#C20E1A"],
    },
  ],
});

const chartOptions = ref({
  plugins: {
    legend: {
      display: true,
      position: "bottom",
      labels: {
        color: "#333",
        font: { size: 14 },
      },
    },
    datalabels: {
      color: "white",
      formatter: (value) => value,
      font: {
        weight: "bold",
        size: 16
      },
    },
  },
});

// Bar chart data (puedes usar los mismos datos, pero puedes personalizar si lo deseas)
const chartDataBar = ref({
  labels: [
    "Voto validado",
    "Voto nulos",
    "Votos no marcados",
    "Voto en blanco",
  ],
  datasets: [
    {
      label: [
        "Voto validado",
        "Voto nulos",
        "Votos no marcados",
        "Voto en blanco",
      ],
      data: [
        props.votos_validados,
        props.votos_nulos,
        props.votos_no_marcados,
        props.votos_blanco,
      ],
      backgroundColor: getPieColors(4),
    },
  ],
});

const chartOptionsBar = ref({
  indexAxis: "x",
  plugins: {
    legend: {
      display: false,
    },
    datalabels: {
      color: "white",
      formatter: (value) => value,
      font: {
        weight: "bold",
        size: 16
      },
    },
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: { color: "#333" },
    },
    y: {
      ticks: { color: "#333" },
    },
  },
});
</script>

<template>
  <Head title="Votaciones por comuna - general" />

  <div
    class="relative min-h-screen bg-center bg-option2 selection:bg-red-500 selection:text-white flex flex-col justify-center"
  >
    <div class="w-full flex justify-between sm:px-16 px-4">
      <a class="sm:flex justify-start mt-2" href="/welcome">
        <img src="/assets/img/logo.png" alt="Logo" class="h-24 w-auto" />
      </a>
      <SecondaryButtonReturn
        class="h-full flex inline-flex mt-8 !text-base"
      >
        Regresar
      </SecondaryButtonReturn>
    </div>
    <!-- contenido -->
    <div class="w-full flex justify-center my-auto sm:px-16 px-4">
      <div
        class="w-full border border-gray-200 bg-white shadow-md rounded-lg p-4"
      >
        <h2 class="font-bold mt-2 text-2xl">Resultados de Votación</h2>
        <p class="mt-2 text-base">Comuna/corregimiento -- {{ comuna }}</p>
        <!-- total registros voto virtual -->
        <p class="flex gap-2 mt-2">
          <b>Total registrados habilitados para votar virtualmente o electronico: </b>
          {{ total_votos_virtuales }}
        </p>
        <!-- total registros voto fisico -->
        <p class="flex gap-2 mt-2">
          <b>Total registrados habilitados para votar presencial físico: </b>
          {{ total_votos_fisicos }}
        </p>

        
        <div class="w-full mb-4">
          <!-- table -->
          <!-- table -->
          <div
            class="flex flex-col overflow-x-auto mt-6"
            v-if="resumen_puestos.length > 0"
          >
            <div class="inline-block rounded-lg shadow">
              <div class="inline-block min-w-full py-2">
                <div class="overflow-x-auto">
                  <table class="min-w-full whitespace-no-wrap">
                    <thead>
                      <tr
                        class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                      >
                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Votación por punto
                        </th>

                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Votos virtuales
                        </th>
                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Votos electronicos
                        </th>
                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Votos físicos
                        </th>
                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Total
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="res in resumen_puestos"
                        :key="res.id"
                        class="text-gray-700"
                      >
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm cursor-pointer hover:bg-blue-50 transition-colors"
                          @click="abrirReporteActas(res)"
                          title="Clic para ver reporte de actas"
                        >
                          <p class="text-gray-900 whitespace-no-wrap font-medium text-blue-700 flex items-center gap-1">
                            {{ res.puesto_nombre }}
                            <EyeIcon class="w-4 h-4 text-blue-600" />
                          </p>
                          <span class="text-[10px] text-blue-500">Ver actas →</span>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">0</p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ res.votos_tic }}
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ res.votos_fisicos }}
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-blue-100/80 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ res.votos_tic + res.votos_fisicos }}
                          </b>
                        </td>
                      </tr>
                      <!-- Voto virtual -->
                      <tr class="text-gray-900">
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            Votos virtuales
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_virtuales }}
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            0
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            0
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_virtuales }}
                          </b>
                        </td>
                      </tr>
                      <!-- totales -->
                      <tr class="text-gray-700 bg-blue-100/80">
                        <td
                          class="border-b border-gray-200 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            Total
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_virtuales }}
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_virtuales_tic }}
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_fisicos }}
                          </b>
                        </td>
                        <td
                          class="border-b border-gray-200 px-5 py-5 text-sm"
                        >
                          <b class="text-gray-900 whitespace-no-wrap">
                            {{ votos_virtuales+votos_virtuales_tic+votos_fisicos }}
                          </b>
                        </td>
                      </tr>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!-- no hay datos -->
          <div
            v-else
            class="flex flex-col overflow-x-auto justify-center items-center"
          >
            <em> No hay datos con votaciones realizadas </em>
          </div>
        </div>
        
        <div class="md:grid md:grid-cols-5 md:gap-16 mt-6">
          <!-- bar chart -->
          <div class="w-full flex mb-4 md:col-span-3">
            <Chart
              type="bar"
              :data="chartDataBar"
              :options="chartOptionsBar"
              class="h-full w-full mt-4"
            />
          </div>
          <!-- pie chart -->
          <div class="w-full items-center mb-4 md:col-span-2">
            <Chart
              type="doughnut"
              :data="chartData"
              :options="chartOptions"
              class="h-full w-full mt-4"
            />
          </div>
        </div>
      </div>
     </div>

    <!-- Modal: Reporte de Actas por Puesto de Votación -->
    <Modal :show="showReporteModal" @close="showReporteModal = false" maxWidth="2xl">
      <div class="p-6 max-h-[80vh] overflow-auto">
        <div class="flex justify-between items-start mb-4 border-b pb-3">
          <div>
            <h3 class="text-2xl font-bold text-gray-800">Reporte de Actas</h3>
            <p class="text-base text-gray-600">{{ selectedPuesto?.puesto_nombre }}</p>
          </div>
          <button
            @click="showReporteModal = false"
            class="text-gray-400 hover:text-gray-600 text-3xl leading-none font-light"
          >
            &times;
          </button>
        </div>

        <p class="text-sm mb-4 text-gray-500">
          Se encontraron <b>{{ actasDelPuesto.length }}</b> acta(s) para este punto de votación.
        </p>

        <div v-if="actasDelPuesto.length" class="space-y-4">
          <div
            v-for="acta in actasDelPuesto"
            :key="acta.id"
            class="border border-gray-200 bg-white rounded-lg p-4 shadow-sm"
          >
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div>
                <div class="font-semibold text-gray-800">Acta #{{ acta.id }}</div>
                <div class="text-sm text-gray-500">
                  {{ acta.fecha_evento ? new Date(acta.fecha_evento).toLocaleDateString('es-CO', { dateStyle: 'medium' }) : 'Sin fecha' }}
                </div>
              </div>
              <div class="text-sm text-right">
                <div v-if="acta.jurado">
                  <span class="text-gray-600">Jurado:</span> <b>{{ acta.jurado.nombre }}</b><br />
                  <span class="text-xs text-gray-500">CC: {{ acta.jurado.identificacion }}</span>
                </div>
                <div v-else-if="acta.cordinador">
                  <span class="text-gray-600">Coordinador:</span> <b>{{ acta.cordinador.name }}</b>
                </div>
                <div v-else class="text-gray-400 text-xs">Sin responsable asignado</div>
              </div>
            </div>

            <!-- Resumen de votos -->
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
              <div class="bg-gray-50 border rounded p-2">
                Ciudadanos: <span class="font-bold">{{ acta.total_ciudadanos ?? 0 }}</span>
              </div>
              <div class="bg-red-50 border border-red-100 rounded p-2">
                Nulos: <span class="font-bold text-red-700">{{ acta.votos_nulos ?? 0 }}</span>
              </div>
              <div class="bg-yellow-50 border border-yellow-100 rounded p-2">
                No marcados: <span class="font-bold">{{ acta.votos_no_marcados ?? 0 }}</span>
              </div>
              <div class="bg-blue-50 border border-blue-100 rounded p-2">
                En blanco: <span class="font-bold">{{ acta.votos_blanco ?? 0 }}</span>
              </div>
            </div>

            <!-- Votos físicos por proyecto -->
            <div v-if="acta.votos_fisico?.length" class="mt-3">
              <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Votos por proyecto</div>
              <div class="text-sm bg-gray-50 rounded p-2 max-h-32 overflow-auto border">
                <div v-for="vf in acta.votos_fisico" :key="vf.id" class="flex justify-between py-0.5">
                  <span class="truncate pr-2">{{ vf.proyecto?.nombre || ('Proyecto #' + vf.id_proyecto) }}</span>
                  <span class="font-mono font-semibold">{{ vf.cantidad }}</span>
                </div>
              </div>
            </div>

            <div v-if="acta.imagen" class="mt-3">
              <a
                :href="`/storage/uploads/actas/${acta.imagen}`"
                target="_blank"
                class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 hover:underline"
              >
                📷 Ver imagen del acta de escrutinio
              </a>
            </div>

            <div v-if="acta.observaciones" class="mt-2 text-xs text-gray-600 italic">
              Observaciones: {{ acta.observaciones }}
            </div>
          </div>
        </div>

        <div v-else class="py-8 text-center text-gray-500 border border-dashed rounded">
          <p>No hay actas de escrutinio físico registradas para este puesto.</p>
          <p class="text-xs mt-1">(Solo votos virtuales / TIC)</p>
        </div>

        <div class="flex justify-end mt-6">
          <SecondaryButton @click="showReporteModal = false">Cerrar reporte</SecondaryButton>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style>
</style>
