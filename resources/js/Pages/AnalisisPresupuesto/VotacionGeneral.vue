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

});

console.log(props);

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
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ res.puesto_nombre }}
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">N/A</p>
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
      </div>
    </div>
  </div>
</template>

<style>
</style>
