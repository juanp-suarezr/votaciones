<template>
  <Head title="Eventos de Votación" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Eventos de Votación
    </template>

    <div class="space-y-6">
      <!-- Barra superior: Estadísticas rápidas y acciones -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div>
          <h2 class="text-lg font-bold text-gray-800">Listado de Procesos Electorales</h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Administre los comicios, consulte las actas registradas, supervise el censo y asigne votantes entre eventos.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <SecondaryLink :href="route('eventos.migrar.view')" class="inline-flex items-center gap-1.5 shadow-xs">
            <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
            <span>Migrar / Asignar Votantes</span>
          </SecondaryLink>

          <PrimaryLink :href="route('eventos.create')" class="inline-flex items-center gap-1.5 shadow-xs">
            <PlusIcon class="w-4 h-4" />
            <span>Nuevo Evento</span>
          </PrimaryLink>
        </div>
      </div>

      <!-- Filtros y Búsqueda -->
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Input de Búsqueda -->
        <div class="relative w-full md:w-96">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <MagnifyingGlassIcon class="w-4 h-4" />
          </div>
          <input
            type="text"
            v-model="filtroSearch"
            @keyup.enter="aplicarFiltros"
            placeholder="Buscar por nombre, dependencia..."
            class="pl-9 pr-4 py-2 w-full text-xs border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-xs"
          />
        </div>

        <!-- Selector de Estado -->
        <div class="flex items-center gap-3 w-full md:w-auto">
          <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Estado:</span>
            <select
              v-model="filtroEstado"
              @change="aplicarFiltros"
              class="text-xs border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs"
            >
              <option value="">Todos los estados</option>
              <option value="Activo">Activo</option>
              <option value="Pendiente">Pendiente</option>
              <option value="Cerrado">Cerrado</option>
            </select>
          </div>

          <button
            v-if="filters.search || filters.estado"
            type="button"
            @click="limpiarFiltros"
            class="text-xs text-gray-500 hover:text-indigo-600 underline font-medium whitespace-nowrap"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Tabla Organizada de Eventos -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                <th class="py-3.5 px-4">Evento / ID</th>
                <th class="py-3.5 px-4">Dependencia / Modalidad</th>
                <th class="py-3.5 px-4">Fechas (Inicio / Fin)</th>
                <th class="py-3.5 px-4 text-center">Censo Asignado</th>
                <th class="py-3.5 px-4 text-center">Votos Emitidos</th>
                <th class="py-3.5 px-4 text-center">Actas</th>
                <th class="py-3.5 px-4 text-center">Estado</th>
                <th class="py-3.5 px-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
              <tr
                v-for="ev in eventos.data"
                :key="ev.id"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <!-- Nombre y Descripción -->
                <td class="py-3.5 px-4">
                  <div class="flex items-start gap-2.5">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono font-bold text-[11px] mt-0.5 border border-slate-200">
                      #{{ ev.id }}
                    </span>
                    <div>
                      <Link :href="route('eventos.show', ev.id)" class="font-bold text-gray-900 hover:text-indigo-600 transition block">
                        {{ ev.nombre }}
                      </Link>
                      <p class="text-gray-500 text-[11px] mt-0.5 line-clamp-1 max-w-xs" :title="ev.descripcion">
                        {{ ev.descripcion }}
                      </p>
                    </div>
                  </div>
                </td>

                <!-- Dependencia y Tipos -->
                <td class="py-3.5 px-4">
                  <span class="font-semibold text-gray-700 block">{{ ev.dependencias || 'No especificada' }}</span>
                  <div class="flex flex-wrap items-center gap-1 mt-1">
                    <span
                      v-if="ev.evento_padre"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200"
                    >
                      Evento Padre
                    </span>
                    <span
                      v-if="ev.tipos && ev.tipos !== 'NA'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200 truncate max-w-[150px]"
                      :title="ev.tipos"
                    >
                      {{ ev.tipos }}
                    </span>
                  </div>
                </td>

                <!-- Fechas -->
                <td class="py-3.5 px-4 whitespace-nowrap text-gray-600">
                  <div class="flex flex-col space-y-0.5 text-[11px]">
                    <span class="flex items-center gap-1 text-slate-700">
                      <span class="font-semibold text-gray-400">Inicio:</span> {{ formatearFecha(ev.fecha_inicio) }}
                    </span>
                    <span class="flex items-center gap-1 text-slate-700">
                      <span class="font-semibold text-gray-400">Fin:</span> {{ formatearFecha(ev.fecha_fin) }}
                    </span>
                  </div>
                </td>

                <!-- Censo Asignado (Clickeable para ver usuarios) -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <Link
                    :href="route('eventos.show', ev.id)"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-800 font-bold text-xs transition shadow-xs group"
                    title="Ver listado de usuarios asignados a este evento"
                  >
                    <UserGroupIcon class="w-3.5 h-3.5 text-blue-600 group-hover:scale-110 transition-transform" />
                    <span>{{ ev.votantes_count ?? 0 }}</span>
                  </Link>
                </td>

                <!-- Votos Registrados -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold text-xs"
                    :class="ev.votos_count > 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-gray-50 border border-gray-200 text-gray-500'">
                    <CheckCircleIcon class="w-3.5 h-3.5" :class="ev.votos_count > 0 ? 'text-emerald-600' : 'text-gray-400'" />
                    <span>{{ ev.votos_count ?? 0 }}</span>
                  </div>
                </td>

                <!-- Actas -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <span
                    class="px-2 py-1 rounded-md text-[11px] font-medium"
                    :class="ev.acta_escrutinio_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 font-semibold' : 'bg-gray-100 text-gray-500'"
                  >
                    {{ ev.acta_escrutinio_count > 0 ? `${ev.acta_escrutinio_count} escrutinio` : '0 escrutinio' }}
                  </span>
                </td>

                <!-- Estado con badges limpios -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-bold inline-block border"
                    :class="{
                      'bg-green-100 text-green-800 border-green-200': ev.estado === 'Activo',
                      'bg-amber-100 text-amber-800 border-amber-200': ev.estado === 'Pendiente',
                      'bg-gray-100 text-gray-700 border-gray-200': ev.estado === 'Cerrado',
                    }"
                  >
                    {{ ev.estado }}
                  </span>
                </td>

                <!-- Acciones Claras -->
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <!-- Ver Detalles -->
                    <Link
                      :href="route('eventos.show', ev.id)"
                      class="p-1.5 rounded-lg border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold inline-flex items-center gap-1 transition"
                      title="Ver detalles, actas y resumen del evento"
                    >
                      <EyeIcon class="w-3.5 h-3.5" />
                      <span>Detalles</span>
                    </Link>

                    <!-- Ver Votantes Asignados -->
                    <Link
                      :href="route('eventos.show', ev.id)"
                      class="p-1.5 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold inline-flex items-center gap-1 transition"
                      title="Ver listado y tabla de usuarios asignados al evento"
                    >
                      <UserGroupIcon class="w-3.5 h-3.5 text-blue-600" />
                      <span>Votantes</span>
                    </Link>

                    <!-- Editar -->
                    <Link
                      :href="route('eventos.edit', ev.id)"
                      class="p-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium inline-flex items-center gap-1 transition"
                      title="Editar evento"
                    >
                      <PencilIcon class="w-3.5 h-3.5 text-gray-500" />
                      <span>Editar</span>
                    </Link>

                    <!-- Migrar Votantes -->
                    <Link
                      :href="route('eventos.migrar.view', { origen: ev.id })"
                      class="p-1.5 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-medium inline-flex items-center gap-1 transition"
                      title="Migrar o transferir votantes de este evento"
                    >
                      <ArrowPathIcon class="w-3.5 h-3.5" />
                      <span>Migrar</span>
                    </Link>
                  </div>
                </td>
              </tr>

              <!-- Estado Vacío -->
              <tr v-if="!eventos.data || eventos.data.length === 0">
                <td colspan="8" class="text-center py-12 px-4 text-gray-500 bg-gray-50/50">
                  <ArchiveBoxXMarkIcon class="w-10 h-10 mx-auto text-gray-400 mb-2" />
                  <p class="font-semibold text-gray-700">No se encontraron eventos</p>
                  <p class="text-xs text-gray-400 mt-1">
                    Pruebe ajustando los filtros de búsqueda o registre un nuevo evento de votación.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="p-4 border-t border-gray-200 bg-gray-50/60 flex items-center justify-between">
          <span class="text-xs text-gray-500">
            Mostrando página {{ eventos.current_page }} de {{ eventos.last_page }} ({{ eventos.total }} eventos en total)
          </span>
          <pagination :links="eventos.links" />
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
import PrimaryLink from '@/Components/PrimaryLink.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  UserGroupIcon,
  CheckCircleIcon,
  EyeIcon,
  PencilIcon,
  ArchiveBoxXMarkIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  eventos: {
    type: Object,
    default: () => ({ data: [] }),
  },
  filters: {
    type: Object,
    default: () => ({ search: '', estado: '' }),
  },
});

const breadcrumbLinks = [
  { url: '', text: 'Listado de eventos' },
];

const filtroSearch = ref(props.filters.search || '');
const filtroEstado = ref(props.filters.estado || '');

const aplicarFiltros = () => {
  router.get(
    route('eventos.index'),
    {
      search: filtroSearch.value,
      estado: filtroEstado.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const limpiarFiltros = () => {
  filtroSearch.value = '';
  filtroEstado.value = '';
  aplicarFiltros();
};

const formatearFecha = (fecha) => {
  if (!fecha) return 'Sin fecha';
  try {
    const d = new Date(fecha);
    if (isNaN(d.getTime())) return fecha;
    return d.toLocaleDateString('es-CO', {
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
</script>
