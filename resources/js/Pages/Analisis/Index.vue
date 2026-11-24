<template>
  <Head title="Análisis de Resultados" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold leading-tight text-gray-800">
            Análisis de Resultados Electorales
          </h2>
          <span v-if="eventoActual" class="text-xs text-gray-500 font-medium">
            Evento: <strong class="text-gray-700">{{ eventoActual.nombre }}</strong>
          </span>
        </div>

        <!-- Botones de Exportación de Reporte Oficial (PDF y Word) -->
        <div v-if="evento_selected" class="flex items-center gap-2">
          <a
            :href="route('analisis.reporte.pdf', evento_selected)"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold shadow-sm hover:shadow transition"
            title="Descargar informe oficial de resultados en PDF"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Reporte PDF</span>
          </a>

          <a
            :href="route('analisis.reporte.word', evento_selected)"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold shadow-sm hover:shadow transition"
            title="Descargar informe oficial de resultados en formato Word (.docx)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Reporte Word</span>
          </a>
        </div>
      </div>
    </template>

    <div class="py-4 space-y-6">
      <div v-if="eventos.length" class="space-y-6">
        <!-- Barra superior: Selector de Evento y Categorías/Tipos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="w-full sm:w-1/2">
              <label for="evento_selected" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                Seleccionar Evento de Votación
              </label>
              <select
                id="evento_selected"
                name="evento_selected"
                v-model="evento_selected"
                class="block w-full p-2.5 text-base text-gray-900 border border-gray-300 rounded-xl bg-gray-50 focus:ring-2 focus:ring-red-500 focus:border-red-500 font-medium transition"
              >
                <option value="" disabled>Elegir evento de votación</option>
                <option v-for="ev in eventos" :key="ev.id" :value="ev.id">
                  {{ ev.nombre }} ({{ ev.estado }})
                </option>
              </select>
            </div>

            <!-- Resumen Rápido -->
            <div class="flex items-center gap-3 self-end sm:self-auto">
              <div class="bg-red-50 border border-red-100 px-4 py-2 rounded-xl text-center">
                <span class="text-[11px] uppercase tracking-wider text-red-600 font-bold block">Total Votos</span>
                <span class="text-lg sm:text-xl font-black text-red-700">{{ totalVotosTipo }}</span>
              </div>
              <div class="bg-gray-50 border border-gray-200 px-4 py-2 rounded-xl text-center">
                <span class="text-[11px] uppercase tracking-wider text-gray-500 font-bold block">Candidatos</span>
                <span class="text-lg sm:text-xl font-black text-gray-800">{{ candidatosOrdenados.length }}</span>
              </div>
            </div>
          </div>

          <!-- Selector de Tipos / Categorías -->
          <div v-if="tiposDisponibles.length > 0" class="pt-3 border-t border-gray-100">
            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
              Filtrar por Tipo de Votante / Categoría:
            </span>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tipoItem in tiposDisponibles"
                :key="tipoItem"
                type="button"
                @click="showGraphics(tipoItem)"
                class="px-4 py-1.5 rounded-xl text-sm font-semibold transition-all duration-200"
                :class="
                  tipoItem === tipos
                    ? 'bg-red-600 text-white shadow-sm ring-2 ring-red-300 scale-105'
                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                "
              >
                {{ tipoItem }}
              </button>
            </div>
          </div>
        </div>

        <!-- Fila de Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Gráfico 1: Resultado de Votaciones (Barras) -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <div class="p-2 bg-red-100 text-red-600 rounded-xl">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-base sm:text-lg">Resultado de Votaciones</h3>
                  <p class="text-xs text-gray-500">Ordenado de mayor a menor (Voto en blanco al final)</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="toggleOrientacion"
                  title="Cambiar orientación de barras"
                  class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium transition flex items-center gap-1"
                >
                  <span v-if="isHorizontal">Vertical</span>
                  <span v-else>Horizontal</span>
                </button>
                <button
                  type="button"
                  @click="expandirGrafico('bar')"
                  title="Expandir gráfico en pantalla completa"
                  class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition flex items-center gap-1"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                  </svg>
                  <span>Expandir</span>
                </button>
              </div>
            </div>

            <!-- Contenedor del gráfico con altura fija y responsiva -->
            <div class="h-80 w-full mt-4">
              <Chart
                v-if="chartData && chartData.labels.length"
                type="bar"
                :data="chartData"
                :options="chartOptions"
                class="h-full w-full"
              />
              <div v-else class="h-full flex items-center justify-center text-gray-400 text-sm">
                Sin candidatos registrados para esta categoría
              </div>
            </div>
          </div>

          <!-- Gráfico 2: Participación Electoral (Dona) -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-base sm:text-lg">Participación Electoral</h3>
                  <p class="text-xs text-gray-500">Votos emitidos vs. Abstención</p>
                </div>
              </div>
              <button
                type="button"
                @click="expandirGrafico('doughnut')"
                title="Expandir gráfico en pantalla completa"
                class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition flex items-center gap-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Expandir</span>
              </button>
            </div>

            <div class="h-80 w-full mt-4 flex items-center justify-center">
              <Chart
                v-if="chartData1"
                type="doughnut"
                :data="chartData1"
                :options="chartOptions1"
                class="h-full w-full"
              />
            </div>

            <!-- Métricas del censo -->
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 text-center">
              <div class="bg-gray-50 p-2 rounded-xl">
                <span class="text-[11px] text-gray-500 uppercase font-semibold block">Censo Habilitado</span>
                <span class="text-sm sm:text-base font-bold text-gray-800">{{ metricasCenso.total }}</span>
              </div>
              <div class="bg-emerald-50 p-2 rounded-xl">
                <span class="text-[11px] text-emerald-700 uppercase font-semibold block">Votos Emitidos</span>
                <span class="text-sm sm:text-base font-bold text-emerald-700">
                  {{ metricasCenso.votaron }} ({{ metricasCenso.porcentaje }}%)
                </span>
              </div>
              <div class="bg-slate-50 p-2 rounded-xl">
                <span class="text-[11px] text-slate-500 uppercase font-semibold block">Abstención</span>
                <span class="text-sm sm:text-base font-bold text-slate-700">{{ metricasCenso.abstencion }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla Detallada de Candidatos y Resultados -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <h3 class="font-bold text-gray-800 text-base sm:text-lg">
                Tabla de Posiciones y Escrutinio
              </h3>
              <p class="text-xs text-gray-500">
                Resultados consolidados de mayor a menor votación
              </p>
            </div>
            <span class="text-xs font-medium text-gray-500">
              Mostrando {{ paginatedCandidatos.length }} de {{ candidatosOrdenados.length }} opciones
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
              <thead class="bg-gray-100 text-xs uppercase font-bold text-gray-700 border-b border-gray-200">
                <tr>
                  <th scope="col" class="px-4 py-3 text-center w-16">Posición</th>
                  <th scope="col" class="px-4 py-3">Candidato / Opción</th>
                  <th scope="col" class="px-4 py-3">Identificación</th>
                  <th scope="col" class="px-4 py-3 text-center">Tipo</th>
                  <th scope="col" class="px-4 py-3 text-center w-48">Porcentaje</th>
                  <th scope="col" class="px-4 py-3 text-right">Total Votos</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="(user, index) in paginatedCandidatos"
                  :key="user.id"
                  class="hover:bg-gray-50 transition-colors"
                  :class="user.esVotoBlanco ? 'bg-slate-50/70 italic' : ''"
                >
                  <!-- Posición -->
                  <td class="px-4 py-3.5 text-center">
                    <span
                      v-if="user.esVotoBlanco"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700"
                    >
                      Blanco
                    </span>
                    <span
                      v-else-if="getAbsoluteRank(index) === 1 && user.cantidadVotos > 0"
                      class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-black text-xs shadow-sm ring-2 ring-amber-300"
                      title="1° Lugar"
                    >
                      🥇 1
                    </span>
                    <span
                      v-else-if="getAbsoluteRank(index) === 2 && user.cantidadVotos > 0"
                      class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-800 font-black text-xs shadow-sm ring-2 ring-slate-300"
                      title="2° Lugar"
                    >
                      🥈 2
                    </span>
                    <span
                      v-else-if="getAbsoluteRank(index) === 3 && user.cantidadVotos > 0"
                      class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700/20 text-amber-900 font-black text-xs shadow-sm ring-2 ring-amber-400"
                      title="3° Lugar"
                    >
                      🥉 3
                    </span>
                    <span v-else class="text-xs font-semibold text-gray-500">
                      #{{ getAbsoluteRank(index) }}
                    </span>
                  </td>

                  <!-- Foto y Nombre -->
                  <td class="px-4 py-3.5">
                    <div class="flex items-center gap-3">
                      <Avatar
                        v-if="user.votante.imagen === 'user.png' || !user.votante.imagen"
                        :label="getInitials(user.votante.nombre)"
                        class="bg-indigo-100 text-indigo-700 font-bold shrink-0"
                        size="large"
                        shape="circle"
                      />
                      <Avatar
                        v-else
                        :image="getImageUrl(user.votante.imagen)"
                        class="bg-indigo-100 shrink-0"
                        size="large"
                        shape="circle"
                      />
                      <div>
                        <p class="font-bold text-gray-900 text-sm leading-tight">
                          {{ user.votante.nombre }}
                        </p>
                        <p v-if="user.esVotoBlanco" class="text-xs text-gray-500 font-normal">
                          Opción institucional de voto en blanco
                        </p>
                      </div>
                    </div>
                  </td>

                  <!-- Identificación -->
                  <td class="px-4 py-3.5 font-medium text-gray-700">
                    {{ user.votante.identificacion || 'N/A' }}
                  </td>

                  <!-- Tipo -->
                  <td class="px-4 py-3.5 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ user.tipo }}
                    </span>
                  </td>

                  <!-- Barra de Porcentaje -->
                  <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2">
                      <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div
                          class="h-2.5 rounded-full transition-all duration-500"
                          :class="user.esVotoBlanco ? 'bg-slate-400' : 'bg-red-600'"
                          :style="{ width: `${getPorcentaje(user)}%` }"
                        ></div>
                      </div>
                      <span class="text-xs font-semibold text-gray-700 w-12 text-right">
                        {{ getPorcentaje(user) }}%
                      </span>
                    </div>
                  </td>

                  <!-- Total Votos -->
                  <td class="px-4 py-3.5 text-right font-black text-gray-900 text-base">
                    {{ user.cantidadVotos }}
                  </td>
                </tr>

                <tr v-if="candidatosOrdenados.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                    No hay candidatos registrados para la categoría seleccionada
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div
            v-if="totalPages > 1"
            class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs font-semibold text-gray-600"
          >
            <button
              class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
              @click="previousPage"
              :disabled="currentPage === 0"
            >
              ← Anterior
            </button>
            <p>Página {{ currentPage + 1 }} de {{ totalPages }}</p>
            <button
              class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
              @click="nextPage"
              :disabled="currentPage === totalPages - 1"
            >
              Siguiente →
            </button>
          </div>
        </div>
      </div>

      <div v-else class="flex justify-center py-12">
        <Message severity="info" class="text-center max-w-lg">
          No hay eventos de votación disponibles para análisis en este momento.
        </Message>
      </div>
    </div>

    <!-- MODAL DE EXPANSIÓN DE GRÁFICOS (PANTALLA COMPLETA) -->
    <Teleport to="body">
      <div
        v-if="expandedChart"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/60 backdrop-blur-sm transition-opacity"
        @click.self="cerrarExpansion"
      >
        <div class="bg-white w-full max-w-6xl max-h-[94vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
          <!-- Encabezado Modal -->
          <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div>
              <h2 class="text-lg sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span v-if="expandedChart === 'bar'">📊 Vista Ampliada: Resultado de Votaciones</span>
                <span v-else>🍩 Vista Ampliada: Participación Electoral</span>
              </h2>
              <p class="text-xs sm:text-sm text-gray-500">
                Evento: <strong class="text-gray-700">{{ eventoActual?.nombre }}</strong> |
                Categoría: <strong class="text-red-600">{{ tipos }}</strong>
              </p>
            </div>
            <div class="flex items-center gap-3">
              <button
                v-if="expandedChart === 'bar'"
                type="button"
                @click="toggleOrientacion"
                class="px-3 py-1.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold transition"
              >
                Orientación: {{ isHorizontal ? 'Horizontal' : 'Vertical' }}
              </button>
              <button
                type="button"
                @click="cerrarExpansion"
                class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-200 transition"
                title="Cerrar vista ampliada"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Cuerpo Modal -->
          <div class="p-6 overflow-y-auto flex-1 flex flex-col items-center justify-center bg-white">
            <div v-if="expandedChart === 'bar'" class="w-full h-[520px]">
              <Chart
                type="bar"
                :data="chartData"
                :options="expandedBarOptions"
                class="w-full h-full"
              />
            </div>
            <div v-else class="w-full flex flex-col items-center justify-center py-4">
              <div class="h-80 w-80 max-w-full">
                <Chart
                  type="doughnut"
                  :data="chartData1"
                  :options="chartOptions1"
                  class="w-full h-full"
                />
              </div>

              <!-- Métricas expandidas -->
              <div class="grid grid-cols-3 gap-4 mt-6 max-w-xl w-full text-center">
                <div class="bg-gray-50 border border-gray-200 p-3 rounded-2xl">
                  <span class="text-xs text-gray-500 uppercase font-semibold block">Censo Habilitado</span>
                  <p class="text-2xl font-black text-gray-800">{{ metricasCenso.total }}</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-2xl">
                  <span class="text-xs text-emerald-700 uppercase font-semibold block">Votos Emitidos</span>
                  <p class="text-2xl font-black text-emerald-700">{{ metricasCenso.votaron }}</p>
                  <span class="text-xs text-emerald-600 font-bold">({{ metricasCenso.porcentaje }}% participación)</span>
                </div>
                <div class="bg-slate-50 border border-slate-200 p-3 rounded-2xl">
                  <span class="text-xs text-slate-500 uppercase font-semibold block">Abstención</span>
                  <p class="text-2xl font-black text-slate-700">{{ metricasCenso.abstencion }}</p>
                  <span class="text-xs text-slate-500 font-bold">
                    ({{ (100 - parseFloat(metricasCenso.porcentaje || 0)).toFixed(1) }}%)
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import Message from "primevue/message";
import Avatar from "primevue/avatar";
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

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  votantes: {
    type: Array,
    default: () => [],
  },
});

// Breadcrumbs
const breadcrumbLinks = [{ url: "/", text: "Análisis" }];

// Estados de selección
const evento_selected = ref(props.eventos.length ? props.eventos[0].id : "");
const tipos = ref("NA");
const isHorizontal = ref(false);
const expandedChart = ref(null); // 'bar' | 'doughnut' | null

// Evento actual
const eventoActual = computed(() => {
  return props.eventos.find((item) => item.id == evento_selected.value) || null;
});

// Tipos disponibles para el evento seleccionado (excluyendo flags de sistema)
const tiposDisponibles = computed(() => {
  if (!eventoActual.value || !eventoActual.value.tipos) return [];
  const ignoredFlags = ["withBanner", "Proyecto", "Delegados"];
  return eventoActual.value.tipos
    .split("|")
    .map((t) => t.trim())
    .filter((t) => t !== "" && !ignoredFlags.includes(t));
});

// Función para detectar voto en blanco
const isVotoBlanco = (nombre) => {
  const n = (nombre || "").toLowerCase().trim();
  return n.includes("blanco");
};

// 1. CANDIDATOS ORDENADOS: Mayor a menor por votación, Voto en blanco de último
const candidatosOrdenados = computed(() => {
  if (!eventoActual.value) return [];

  const evId = evento_selected.value;
  const currentTipo = tipos.value;

  // Filtrar candidatos pertenecientes al evento y al tipo seleccionado
  const filtrados = props.candidatos.filter((item) => {
    if (item.id_evento != evId) return false;
    if (!currentTipo || currentTipo === "NA") return true;
    if (!item.tipo) return false;
    const tokens = item.tipo.split("|").map((t) => t.trim());
    return tokens.includes(currentTipo);
  });

  // Mapear con su conteo de votos
  const conVotos = filtrados.map((cand) => {
    const votosCount = (eventoActual.value.votos || []).filter(
      (v) => v.id_candidato == cand.id_votante
    ).length;
    const nombre = cand.votante?.nombre || "";
    return {
      ...cand,
      cantidadVotos: votosCount,
      esVotoBlanco: isVotoBlanco(nombre),
    };
  });

  // Separar regulares y voto en blanco
  const regulares = conVotos.filter((c) => !c.esVotoBlanco);
  const blancos = conVotos.filter((c) => c.esVotoBlanco);

  // Ordenar regulares de mayor a menor votación
  regulares.sort((a, b) => {
    if (b.cantidadVotos !== a.cantidadVotos) {
      return b.cantidadVotos - a.cantidadVotos;
    }
    return (a.votante?.nombre || "").localeCompare(b.votante?.nombre || "");
  });

  // Si hay más de un voto en blanco, ordenar entre ellos de mayor a menor
  blancos.sort((a, b) => b.cantidadVotos - a.cantidadVotos);

  // Voto en blanco SIEMPRE al final
  return [...regulares, ...blancos];
});

// Total de votos en la categoría seleccionada
const totalVotosTipo = computed(() => {
  return candidatosOrdenados.value.reduce((acc, c) => acc + c.cantidadVotos, 0);
});

// Cálculo de porcentaje
const getPorcentaje = (candidato) => {
  if (!totalVotosTipo.value || totalVotosTipo.value === 0) return "0.0";
  return ((candidato.cantidadVotos / totalVotosTipo.value) * 100).toFixed(1);
};

// Métricas de censo y participación
const metricasCenso = computed(() => {
  if (!eventoActual.value) {
    return { total: 0, votaron: 0, abstencion: 0, porcentaje: "0.0" };
  }

  const currentTipo = tipos.value;
  const totalCenso = props.votantes.filter((item) => {
    if (item.id_evento != eventoActual.value.id) return false;
    if (!currentTipo || currentTipo === "NA") return true;
    const tokens = (item.tipo || "").split("|").map((t) => t.trim());
    return tokens.includes(currentTipo);
  }).length;

  const votaron = totalVotosTipo.value;
  const abstencion = Math.max(0, totalCenso - votaron);
  const porcentaje = totalCenso > 0 ? ((votaron / totalCenso) * 100).toFixed(1) : "0.0";

  return {
    total: totalCenso,
    votaron,
    abstencion,
    porcentaje,
  };
});

// Paginación de la tabla
const itemsPerPage = ref(8);
const currentPage = ref(0);

const totalPages = computed(() => {
  return Math.ceil(candidatosOrdenados.value.length / itemsPerPage.value) || 1;
});

const paginatedCandidatos = computed(() => {
  const start = currentPage.value * itemsPerPage.value;
  return candidatosOrdenados.value.slice(start, start + itemsPerPage.value);
});

const getAbsoluteRank = (pageIndex) => {
  return currentPage.value * itemsPerPage.value + pageIndex + 1;
};

const previousPage = () => {
  if (currentPage.value > 0) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value - 1) currentPage.value++;
};

// Variables para Chart.js
const chartData = ref(null);
const chartOptions = ref(null);
const chartData1 = ref(null);
const chartOptions1 = ref(null);
const expandedBarOptions = ref(null);

// Alternar orientación de barras (Vertical / Horizontal)
const toggleOrientacion = () => {
  isHorizontal.value = !isHorizontal.value;
  actualizarGraficos();
};

// Expansión
const expandirGrafico = (tipo) => {
  expandedChart.value = tipo;
};

const cerrarExpansion = () => {
  expandedChart.value = null;
};

// Manejo de tecla Escape para cerrar modal
const handleKeyDown = (e) => {
  if (e.key === "Escape" && expandedChart.value) {
    cerrarExpansion();
  }
};

// Construcción de datos del gráfico de barras (mayor a menor y blanco al final)
const setChartData = () => {
  const list = candidatosOrdenados.value;
  const labels = list.map((c) => c.votante?.nombre || "Candidato");
  const data = list.map((c) => c.cantidadVotos);

  // Paleta de colores atractiva:
  // - Voto en blanco: Slate neutral (#94a3b8)
  // - Top 1: Verde esmeralda (#10b981) si tiene votos
  // - Top 2: Azul (#3b82f6)
  // - Top 3: Indigo (#6366f1)
  // - Demás: Rojo institucional (#E20613)
  const bgColors = list.map((c, idx) => {
    if (c.esVotoBlanco) return "#94a3b8";
    if (idx === 0 && c.cantidadVotos > 0) return "#10b981";
    if (idx === 1 && c.cantidadVotos > 0) return "#3b82f6";
    if (idx === 2 && c.cantidadVotos > 0) return "#6366f1";
    return "#E20613";
  });

  return {
    labels,
    datasets: [
      {
        label: "Votos",
        data,
        backgroundColor: bgColors,
        borderRadius: 8,
        borderWidth: 1,
        borderColor: "#ffffff",
      },
    ],
  };
};

// Opciones del gráfico de barras
const setChartOptions = (expanded = false) => {
  return {
    indexAxis: isHorizontal.value ? "y" : "x",
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        backgroundColor: "#1e293b",
        titleFont: { size: 13, weight: "bold" },
        bodyFont: { size: 12 },
        padding: 10,
        cornerRadius: 8,
        callbacks: {
          label: (context) => {
            const val = context.raw || 0;
            const pct = totalVotosTipo.value > 0
              ? ((val / totalVotosTipo.value) * 100).toFixed(1)
              : "0.0";
            return ` ${val} votos (${pct}%)`;
          },
        },
      },
      datalabels: {
        color: "#1e293b",
        anchor: isHorizontal.value ? "end" : "end",
        align: isHorizontal.value ? "right" : "top",
        offset: 4,
        formatter: (value) => (value > 0 ? value : ""),
        font: {
          weight: "bold",
          size: expanded ? 13 : 11,
        },
      },
    },
    scales: {
      x: {
        ticks: {
          color: "#475569",
          font: { weight: 600, size: expanded ? 12 : 10 },
          maxRotation: isHorizontal.value ? 0 : 45,
          minRotation: isHorizontal.value ? 0 : 25,
        },
        grid: {
          display: isHorizontal.value,
          color: "#f1f5f9",
        },
      },
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
          color: "#475569",
          font: { weight: 600, size: expanded ? 12 : 10 },
        },
        grid: {
          color: "#f1f5f9",
        },
      },
    },
  };
};

// Gráfico de Dona (Participación)
const setChartData1 = () => {
  const m = metricasCenso.value;
  return {
    labels: ["Votos Realizados", "Abstención"],
    datasets: [
      {
        data: [m.votaron, m.abstencion],
        backgroundColor: ["#10b981", "#64748b"],
        hoverBackgroundColor: ["#059669", "#475569"],
        borderWidth: 2,
        borderColor: "#ffffff",
      },
    ],
  };
};

const setChartOptions1 = () => {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom",
        labels: {
          color: "#334155",
          font: { weight: 600, size: 12 },
          boxWidth: 14,
          padding: 12,
        },
      },
      tooltip: {
        backgroundColor: "#1e293b",
        padding: 10,
        cornerRadius: 8,
        callbacks: {
          label: (context) => {
            const val = context.raw || 0;
            const total = metricasCenso.value.total;
            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : "0.0";
            return ` ${context.label}: ${val} (${pct}%)`;
          },
        },
      },
      datalabels: {
        color: "#ffffff",
        formatter: (value, ctx) => {
          const total = metricasCenso.value.total;
          if (!value || total === 0) return "";
          return `${((value / total) * 100).toFixed(0)}%`;
        },
        font: {
          weight: "bold",
          size: 13,
        },
      },
    },
  };
};

// Actualizar todos los gráficos
const actualizarGraficos = () => {
  chartData.value = setChartData();
  chartOptions.value = setChartOptions(false);
  expandedBarOptions.value = setChartOptions(true);
  chartData1.value = setChartData1();
  chartOptions1.value = setChartOptions1();
};

// Cambiar tipo/categoría
const showGraphics = (tipo) => {
  tipos.value = tipo;
  currentPage.value = 0;
  actualizarGraficos();
};

// Reaccionar a cambios en evento seleccionado
watch(evento_selected, (newVal) => {
  currentPage.value = 0;
  if (tiposDisponibles.value.length > 0) {
    tipos.value = tiposDisponibles.value[0];
  } else {
    tipos.value = "NA";
  }
  actualizarGraficos();
});

// Inicialización de tipos
const inicializarTipo = () => {
  if (tiposDisponibles.value.length > 0) {
    tipos.value = tiposDisponibles.value[0];
  } else {
    tipos.value = "NA";
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
  inicializarTipo();
  actualizarGraficos();
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
});

// Funciones utilitarias de imagen e iniciales
const getImageUrl = (imageName) => {
  if (!imageName || imageName === "user.png") return "";
  if (imageName.startsWith("foto_")) {
    return `/storage/uploads/candidatos/${imageName}`;
  }
  return `/storage/uploads/usuarios/${imageName}`;
};

const getInitials = (name) => {
  if (!name) return "NN";
  const parts = name.trim().split(" ");
  let initials = "";
  for (let i = 0; i < parts.length && initials.length < 2; i++) {
    if (parts[i].length > 0) {
      initials += parts[i][0];
    }
  }
  return initials.toUpperCase();
};
</script>
