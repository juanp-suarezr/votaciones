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
import Chart from "primevue/chart";

import SecondaryLink from "@/Components/SecondaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SecondaryButtonReturn from "@/Components/SecondaryButtonReturn.vue";
const swal = inject("$swal");

const props = defineProps({
  proyectos: {
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
  comuna: {
    type: String,
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
// Asegura nombres válidos
const proyectosLabels = props.proyectos.map((p) => p.nombre ?? "Sin nombre");

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
  labels: props.proyectos.map((p) => p.nombre),
  datasets: [
    {
      data: props.proyectos.map((p) => p.total),
      backgroundColor: getPieColors(props.proyectos.length),
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
  },
});

// Bar chart data (puedes usar los mismos datos, pero puedes personalizar si lo deseas)
const chartDataBar = ref({
  labels: proyectosLabels,
  datasets: [
    {
      label: "Total de votos",
      data: props.proyectos.map((p) => p.total),
      backgroundColor: getPieColors(props.proyectos.length),
    },
  ],
});

const chartOptionsBar = ref({
  indexAxis: "x",
  plugins: {
    legend: {
      display: false,
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
  <Head title="Votaciones por comuna - proyectos" />

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

        <div class="md:grid md:grid-cols-5 gap-8 mt-6">
          <!-- pie chart -->
          <div class="w-full items-center mb-4 md:col-span-2">
            <Chart
              type="doughnut"
              :data="chartData"
              :options="chartOptions"
              class="h-full w-full mt-4"
            />
          </div>

          <!-- bar chart -->
          <div class="w-full mb-4 md:col-span-3">
            <Chart
              type="bar"
              :data="chartDataBar"
              :options="chartOptionsBar"
              class="h-full w-full mt-4"
            />
          </div>
        </div>
        <div class="w-full mb-4">
          <!-- table -->
          <div
            class="flex flex-col overflow-x-auto mt-6"
            v-if="proyectos.length > 0"
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
                          Proyectos
                        </th>

                        <th
                          class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                        >
                          Votos
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="res in proyectos"
                        :key="res.id"
                        class="text-gray-700"
                      >
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ res.nombre }}
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ res.total }}
                          </p>
                        </td>
                      </tr>
                      <!-- voto nulo -->
                      <tr class="text-gray-700">
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            Votos nulos
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ votos_nulos }}
                          </p>
                        </td>
                      </tr>
                      <!-- votos no marcados -->
                      <tr class="text-gray-700">
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            Votos no marcados
                          </p>
                        </td>
                        <td
                          class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                        >
                          <p class="text-gray-900 whitespace-no-wrap">
                            {{ votos_no_marcados }}
                          </p>
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
