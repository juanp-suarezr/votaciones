<template>
  <Head title="Historial Auditoría Candidatos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Historial y Auditoría de Validaciones</h1>
          <p class="text-xs text-slate-500">
            Trazabilidad inmutable de aprobaciones y rechazos emitidos por validadores de candidatos.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            :href="route('postulaciones-candidatos.index')"
            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Volver a Postulaciones</span>
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- MÉTRICAS DE AUDITORÍA -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Acciones Registradas</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ metrics.total }}</p>
            <p class="text-[11px] text-slate-500">Eventos de auditoría en postulaciones</p>
          </div>
          <span class="p-3 bg-slate-100 text-slate-600 rounded-xl">
            <ClipboardDocumentListIcon class="w-6 h-6" />
          </span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Aprobaciones Emitidas</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ metrics.aprobadas }}</p>
            <p class="text-[11px] text-emerald-600 font-semibold">Candidaturas concedidas</p>
          </div>
          <span class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
            <CheckBadgeIcon class="w-6 h-6" />
          </span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-red-700">Rechazos Formales</span>
            <p class="text-2xl font-black text-red-700 mt-1">{{ metrics.rechazadas }}</p>
            <p class="text-[11px] text-red-600 font-semibold">Rechazos con motivos archivados</p>
          </div>
          <span class="p-3 bg-red-100 text-red-600 rounded-xl">
            <XCircleIcon class="w-6 h-6" />
          </span>
        </div>
      </div>

      <!-- FILTROS DE AUDITORÍA -->
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
          <!-- Búsqueda -->
          <div class="relative md:col-span-2">
            <input
              type="text"
              v-model="filtroSearch"
              @keyup.enter="aplicarFiltros"
              placeholder="Buscar por candidato, documento, radicado o validador..."
              class="w-full text-xs border border-slate-300 rounded-lg pl-9 pr-8 py-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            <button
              v-if="filtroSearch"
              @click="filtroSearch = ''; aplicarFiltros()"
              class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- Filtro Acción -->
          <div>
            <select
              v-model="filtroAccion"
              @change="aplicarFiltros"
              class="w-full text-xs border border-slate-300 rounded-lg py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Todas las Acciones</option>
              <option value="Aprobado">Solo Aprobaciones</option>
              <option value="Rechazado">Solo Rechazos</option>
            </select>
          </div>

          <!-- Filtro Evento -->
          <div>
            <select
              v-model="filtroEvento"
              @change="aplicarFiltros"
              class="w-full text-xs border border-slate-300 rounded-lg py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Todos los Eventos</option>
              <option v-for="ev in eventos" :key="ev.id" :value="ev.id">
                {{ ev.nombre }}
              </option>
            </select>
          </div>

          <!-- Rango Desde / Hasta -->
          <div class="flex items-center gap-1.5">
            <input
              type="date"
              v-model="filtroDesde"
              @change="aplicarFiltros"
              class="w-1/2 text-xs border border-slate-300 rounded-lg py-1.5 px-2 focus:ring-indigo-500 focus:border-indigo-500"
              title="Fecha inicial"
            />
            <span class="text-slate-400 text-xs">-</span>
            <input
              type="date"
              v-model="filtroHasta"
              @change="aplicarFiltros"
              class="w-1/2 text-xs border border-slate-300 rounded-lg py-1.5 px-2 focus:ring-indigo-500 focus:border-indigo-500"
              title="Fecha final"
            />
          </div>
        </div>
      </div>

      <!-- TABLA DE AUDITORÍA -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-extrabold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3 px-4">Fecha / Hora</th>
                <th class="py-3 px-4">Validador</th>
                <th class="py-3 px-4 text-center">Acción</th>
                <th class="py-3 px-4">Candidato y Evento</th>
                <th class="py-3 px-4">Motivos / Observaciones</th>
                <th class="py-3 px-4 text-right">Dirección IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr
                v-for="auditoria in auditorias.data"
                :key="auditoria.id"
                class="hover:bg-slate-50/80 transition"
              >
                <!-- Fecha y Hora -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="font-bold text-slate-900 text-xs">
                    {{ formatearFechaHora(auditoria.created_at) }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                    ID #{{ auditoria.id }}
                  </div>
                </td>

                <!-- Validador -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <UserCircleIcon class="w-4 h-4 text-indigo-600 shrink-0" />
                    <span>{{ auditoria.usuario?.name || 'Sistema / Desconocido' }}</span>
                  </div>
                  <div class="text-[11px] text-slate-500">
                    {{ auditoria.usuario?.email || '' }}
                  </div>
                </td>

                <!-- Acción -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1"
                    :class="auditoria.accion === 'Aprobado'
                      ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                      : 'bg-red-100 text-red-800 border border-red-300'"
                  >
                    <CheckCircleIcon v-if="auditoria.accion === 'Aprobado'" class="w-3.5 h-3.5 text-emerald-600" />
                    <XCircleIcon v-else class="w-3.5 h-3.5 text-red-600" />
                    <span>{{ auditoria.accion }}</span>
                  </span>
                </td>

                <!-- Candidato y Evento -->
                <td class="py-3 px-4">
                  <div class="font-extrabold text-slate-900 uppercase">
                    {{ auditoria.postulacion?.nombre || auditoria.votante?.nombre || 'N/A' }}
                  </div>
                  <div class="text-[11px] text-slate-500 font-mono">
                    CC: {{ auditoria.postulacion?.identificacion || auditoria.votante?.identificacion }} •
                    Rad: {{ auditoria.postulacion?.radicado }}
                  </div>
                  <div class="text-[11px] text-indigo-800 font-medium mt-0.5">
                    {{ auditoria.evento?.nombre || 'Evento no especificado' }}
                  </div>
                </td>

                <!-- Motivos / Observaciones -->
                <td class="py-3 px-4 max-w-md">
                  <!-- Chips de Motivos de Rechazo -->
                  <div v-if="auditoria.motivos_rechazo" class="flex flex-wrap gap-1 mb-1.5">
                    <span
                      v-for="(motivo, i) in parsearMotivos(auditoria.motivos_rechazo)"
                      :key="i"
                      class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-[10px] font-semibold"
                    >
                      • {{ motivo }}
                    </span>
                  </div>

                  <!-- Observaciones -->
                  <div class="text-xs text-slate-700">
                    {{ auditoria.observaciones || 'Sin observaciones adicionales.' }}
                  </div>
                </td>

                <!-- Dirección IP -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded">
                    {{ auditoria.ip_address || '127.0.0.1' }}
                  </span>
                </td>
              </tr>

              <tr v-if="auditorias.data.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  <InboxIcon class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                  <p class="font-bold text-slate-600">No hay registros de auditoría</p>
                  <p class="text-xs text-slate-400 mt-0.5">
                    No se han registrado validaciones con los filtros especificados.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50">
          <div class="text-xs text-slate-500">
            Página <span class="font-bold text-slate-800">{{ auditorias.current_page }}</span> de
            <span class="font-bold text-slate-800">{{ auditorias.last_page }}</span>
          </div>
          <Pagination :links="auditorias.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
  ArrowLeftIcon,
  ClipboardDocumentListIcon,
  CheckBadgeIcon,
  XCircleIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  UserCircleIcon,
  CheckCircleIcon,
  InboxIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  auditorias: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  metrics: {
    type: Object,
    required: true,
  },
  eventos: {
    type: Array,
    default: () => [],
  },
});

const breadcrumbLinks = [
  { name: 'Inicio', url: route('dashboard') },
  { name: 'Validación Candidatos', url: route('postulaciones-candidatos.index') },
  { name: 'Historial Auditoría', url: route('postulaciones-candidatos.historial') },
];

const filtroAccion = ref(props.filters.accion || '');
const filtroEvento = ref(props.filters.id_evento || '');
const filtroSearch = ref(props.filters.search || '');
const filtroDesde = ref(props.filters.fecha_desde || '');
const filtroHasta = ref(props.filters.fecha_hasta || '');

const aplicarFiltros = () => {
  router.get(
    route('postulaciones-candidatos.historial'),
    {
      accion: filtroAccion.value || null,
      id_evento: filtroEvento.value || null,
      search: filtroSearch.value || null,
      fecha_desde: filtroDesde.value || null,
      fecha_hasta: filtroHasta.value || null,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const formatearFechaHora = (fecha) => {
  if (!fecha) return 'N/A';
  try {
    const d = new Date(fecha);
    return d.toLocaleString('es-CO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch (e) {
    return fecha;
  }
};

const parsearMotivos = (motivos) => {
  if (!motivos) return [];
  if (Array.isArray(motivos)) return motivos;
  try {
    return JSON.parse(motivos);
  } catch (e) {
    return [motivos];
  }
};
</script>

