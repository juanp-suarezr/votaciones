<template>
  <Head :title="'Detalle del Evento: ' + evento.nombre" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Detalle del Evento
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Encabezado con datos principales y acciones -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-mono font-bold text-xs border border-slate-200">
              ID #{{ evento.id }}
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
              :class="{
                'bg-green-100 text-green-800 border-green-200': evento.estado === 'Activo',
                'bg-amber-100 text-amber-800 border-amber-200': evento.estado === 'Pendiente',
                'bg-gray-100 text-gray-700 border-gray-200': evento.estado === 'Cerrado',
              }"
            >
              {{ evento.estado }}
            </span>
            <span
              v-if="evento.evento_padre"
              class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200"
            >
              Evento Padre
            </span>
          </div>

          <h1 class="text-2xl font-bold text-gray-900">{{ evento.nombre }}</h1>
          <p class="text-xs text-gray-500 flex items-center gap-1.5">
            <BuildingOfficeIcon class="w-4 h-4 text-gray-400" />
            <span>Dependencia: <b>{{ evento.dependencias || 'Sin dependencia especificada' }}</b></span>
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <SecondaryLink :href="route('eventos.migrar.view', { destino: evento.id })" class="inline-flex items-center gap-1.5 shadow-xs">
            <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
            <span>Asignar Votantes</span>
          </SecondaryLink>

          <SecondaryLink :href="route('eventos.edit', evento.id)" class="inline-flex items-center gap-1.5 shadow-xs">
            <PencilIcon class="w-4 h-4 text-gray-600" />
            <span>Editar Evento</span>
          </SecondaryLink>

          <SecondaryLink :href="route('eventos.index')" class="inline-flex items-center gap-1.5 shadow-xs">
            <ArrowLeftIcon class="w-4 h-4 text-gray-500" />
            <span>Volver</span>
          </SecondaryLink>
        </div>
      </div>

      <!-- Métricas Clave (KPIs) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Votantes Asignados -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
            <UserGroupIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Censo Asignado</span>
            <span class="text-2xl font-black text-gray-900">{{ conteo_votantes.total }}</span>
            <span class="block text-[11px] text-green-700 font-medium mt-0.5">
              {{ conteo_votantes.activos }} activos • {{ conteo_votantes.pendientes }} pendientes
            </span>
          </div>
        </div>

        <!-- Votos Emitidos -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
            <CheckCircleIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Votos Emitidos</span>
            <span class="text-2xl font-black text-gray-900">{{ conteo_votos.total }}</span>
            <span class="block text-[11px] text-gray-500 font-medium mt-0.5">
              {{ conteo_votos.virtuales }} virtuales • {{ conteo_votos.presenciales }} presenciales
            </span>
          </div>
        </div>

        <!-- Acta de Inicio -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 rounded-lg" :class="actas_inicio.length > 0 ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-400'">
            <DocumentTextIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Acta de Inicio</span>
            <span class="text-lg font-bold" :class="actas_inicio.length > 0 ? 'text-purple-700' : 'text-gray-400'">
              {{ actas_inicio.length > 0 ? 'Registrada' : 'Sin Registrar' }}
            </span>
            <span class="block text-[11px] text-gray-500 mt-0.5">
              {{ actas_inicio.length > 0 ? actas_inicio.length + ' acta(s)' : 'Pendiente apertura' }}
            </span>
          </div>
        </div>

        <!-- Acta de Fin / Cierre -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 rounded-lg" :class="actas_fin.length > 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-400'">
            <DocumentCheckIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Acta de Cierre</span>
            <span class="text-lg font-bold" :class="actas_fin.length > 0 ? 'text-indigo-700' : 'text-gray-400'">
              {{ actas_fin.length > 0 ? 'Registrada' : 'Sin Registrar' }}
            </span>
            <span class="block text-[11px] text-gray-500 mt-0.5">
              {{ actas_fin.length > 0 ? actas_fin.length + ' acta(s)' : 'Pendiente cierre' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Fila: Información Detallada y Censo -->
      <div class="grid md:grid-cols-3 gap-6">
        <!-- Columna 1 y 2: Información General del Evento -->
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
          <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider pb-3 border-b border-gray-100 flex items-center gap-2">
            <InformationCircleIcon class="w-5 h-5 text-indigo-600" />
            Información General del Proceso
          </h2>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <span class="block text-xs font-semibold text-gray-400">Fecha y Hora de Inicio</span>
              <span class="text-sm font-bold text-gray-800 block mt-0.5">
                {{ formatearFechaLarga(evento.fecha_inicio) }}
              </span>
            </div>

            <div>
              <span class="block text-xs font-semibold text-gray-400">Fecha y Hora de Cierre</span>
              <span class="text-sm font-bold text-gray-800 block mt-0.5">
                {{ formatearFechaLarga(evento.fecha_fin) }}
              </span>
            </div>
          </div>

          <div>
            <span class="block text-xs font-semibold text-gray-400">Descripción del Evento</span>
            <p class="text-xs text-gray-700 mt-1 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-200">
              {{ evento.descripcion || 'Sin descripción registrada.' }}
            </p>
          </div>

          <div class="grid sm:grid-cols-2 gap-4 pt-2">
            <div>
              <span class="block text-xs font-semibold text-gray-400">Tipos / Estamentos Habilitados</span>
              <div class="flex flex-wrap gap-1 mt-1">
                <span
                  v-for="t in desglosarTipos(evento.tipos)"
                  :key="t"
                  class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200"
                >
                  {{ t }}
                </span>
                <span v-if="!evento.tipos || evento.tipos === 'NA'" class="text-xs text-gray-400">
                  Sin estamentos específicos (Abierto a todos)
                </span>
              </div>
            </div>

            <div>
              <span class="block text-xs font-semibold text-gray-400">Estructura Jerárquica</span>
              <div class="mt-1 text-xs">
                <span v-if="evento.evento_padre" class="text-purple-700 font-semibold">
                  ★ Es un Evento Padre (agrupa sub-eventos)
                </span>
                <span v-else-if="evento.padre" class="text-gray-700">
                  Sub-evento perteneciente al padre: <b>{{ evento.padre.nombre }}</b>
                </span>
                <span v-else class="text-gray-500">
                  Evento independiente
                </span>
              </div>
            </div>
          </div>

          <!-- Eventos Hijos si los tiene -->
          <div v-if="eventos_hijos && eventos_hijos.length > 0" class="pt-3 border-t border-gray-100">
            <span class="block text-xs font-bold text-gray-700 uppercase mb-2">Eventos Hijos Vinculados ({{ eventos_hijos.length }}):</span>
            <div class="grid sm:grid-cols-2 gap-2">
              <Link
                v-for="hijo in eventos_hijos"
                :key="'hijo-' + hijo.id"
                :href="route('eventos.show', hijo.id)"
                class="p-2.5 rounded-lg border border-purple-200 bg-purple-50/40 hover:bg-purple-100/60 transition flex items-center justify-between text-xs"
              >
                <span class="font-semibold text-purple-950">#{{ hijo.id }} - {{ hijo.nombre }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-white text-purple-700 border border-purple-200">
                  {{ hijo.estado }}
                </span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Columna 3: Desglose del Censo Asignado -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5 flex flex-col justify-between">
          <div>
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider pb-3 border-b border-gray-100 flex items-center gap-2">
              <UserGroupIcon class="w-5 h-5 text-blue-600" />
              Desglose de Usuarios
            </h2>

            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                <span class="text-gray-600">Votantes Regulares:</span>
                <span class="font-bold text-gray-800">{{ conteo_votantes.solo_votantes }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                <span class="text-gray-600">Candidatos Inscritos:</span>
                <span class="font-bold text-indigo-700">{{ conteo_votantes.candidatos }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs">
                <span class="text-emerald-800 font-medium">Usuarios 'Activos' (Habilitados):</span>
                <span class="font-bold text-emerald-800">{{ conteo_votantes.activos }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs">
                <span class="text-amber-800 font-medium">Usuarios 'Pendientes':</span>
                <span class="font-bold text-amber-800">{{ conteo_votantes.pendientes }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-red-50 border border-red-200 text-xs">
                <span class="text-red-800 font-medium">Usuarios 'Rechazados':</span>
                <span class="font-bold text-red-800">{{ conteo_votantes.rechazados }}</span>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100">
            <SecondaryLink
              :href="route('eventos.migrar.view', { destino: evento.id })"
              class="w-full justify-center inline-flex items-center gap-1.5 text-xs py-2.5"
            >
              <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
              <span>Migrar más votantes a este evento</span>
            </SecondaryLink>
          </div>
        </div>
      </div>

      <!-- Sección de Actas Registradas -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
          <div class="flex items-center gap-2">
            <DocumentDuplicateIcon class="w-5 h-5 text-indigo-600" />
            <h2 class="text-base font-bold text-gray-800">Historial y Registro de Actas</h2>
          </div>
          <span class="text-xs text-gray-500">
            Apertura, Cierre y Escrutinio electoral
          </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <!-- Actas de Inicio -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-purple-600"></span>
              Acta(s) de Inicio (Apertura)
            </h3>

            <div v-if="actas_inicio && actas_inicio.length > 0" class="space-y-2.5">
              <div
                v-for="acta in actas_inicio"
                :key="'inicio-' + acta.id"
                class="p-4 rounded-xl border border-purple-200 bg-purple-50/30 text-xs space-y-1.5"
              >
                <div class="flex items-center justify-between font-bold text-purple-900">
                  <span>Acta #{{ acta.id }} ({{ acta.modalidad || 'virtual' }})</span>
                  <span class="text-gray-500 font-normal">{{ formatearFechaLarga(acta.fecha_inicio) }}</span>
                </div>
                <div class="text-gray-600">
                  <b>Jurado/Responsable:</b> {{ acta.jurado?.nombre || 'Automática / Sistema' }}
                </div>
                <div v-if="acta.puesto_votacion" class="text-gray-600">
                  <b>Puesto de votación:</b> {{ acta.puesto_votacion }}
                </div>
                <div v-if="acta.comunas" class="text-gray-500 text-[11px]">
                  <b>Comunas:</b> {{ acta.comunas }}
                </div>
              </div>
            </div>
            <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
              No se ha registrado acta de inicio para este evento.
            </div>
          </div>

          <!-- Actas de Cierre -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
              Acta(s) de Fin (Cierre)
            </h3>

            <div v-if="actas_fin && actas_fin.length > 0" class="space-y-2.5">
              <div
                v-for="acta in actas_fin"
                :key="'fin-' + acta.id"
                class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 text-xs space-y-1.5"
              >
                <div class="flex items-center justify-between font-bold text-indigo-900">
                  <span>Acta #{{ acta.id }} ({{ acta.modalidad || 'virtual' }})</span>
                  <span class="text-gray-500 font-normal">{{ formatearFechaLarga(acta.fecha_cierre) }}</span>
                </div>
                <div class="text-gray-600">
                  <b>Jurado/Responsable:</b> {{ acta.jurado?.nombre || 'Automática / Sistema' }}
                </div>
                <div v-if="acta.puesto_votacion" class="text-gray-600">
                  <b>Puesto de votación:</b> {{ acta.puesto_votacion }}
                </div>
                <div v-if="acta.comunas" class="text-gray-500 text-[11px]">
                  <b>Comunas:</b> {{ acta.comunas }}
                </div>
              </div>
            </div>
            <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
              No se ha registrado acta de cierre para este evento.
            </div>
          </div>
        </div>

        <!-- Actas de Escrutinio -->
        <div class="pt-4 border-t border-gray-200 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
              Actas de Escrutinio ({{ actas_escrutinio ? actas_escrutinio.length : 0 }})
            </h3>
          </div>

          <div v-if="actas_escrutinio && actas_escrutinio.length > 0" class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-600 border-b border-gray-200">
                <tr>
                  <th class="py-2.5 px-3">ID</th>
                  <th class="py-2.5 px-3">Puesto / Comuna</th>
                  <th class="py-2.5 px-3">Fecha</th>
                  <th class="py-2.5 px-3 text-center">Total Votos</th>
                  <th class="py-2.5 px-3 text-center">Nulos</th>
                  <th class="py-2.5 px-3 text-center">Blanco</th>
                  <th class="py-2.5 px-3">Jurado / Coordinador</th>
                  <th class="py-2.5 px-3">Observaciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="acta in actas_escrutinio" :key="'escrutinio-' + acta.id" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-mono font-bold text-gray-700">#{{ acta.id }}</td>
                  <td class="py-2.5 px-3">
                    <span class="font-semibold block">{{ acta.puesto_votacion || 'General' }}</span>
                    <span class="text-[11px] text-gray-500">Comuna: {{ acta.comuna || 'N/A' }}</span>
                  </td>
                  <td class="py-2.5 px-3 whitespace-nowrap text-gray-600">
                    {{ formatearFechaLarga(acta.created_at || acta.fecha_evento) }}
                  </td>
                  <td class="py-2.5 px-3 text-center font-bold text-gray-900">
                    {{ acta.total_votantes ?? acta.total_ciudadanos ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-center text-red-600 font-semibold">
                    {{ acta.votos_nulos ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-center text-gray-600">
                    {{ acta.votos_blanco ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-gray-700">
                    {{ acta.jurado?.nombre || acta.cordinador?.name || 'N/A' }}
                  </td>
                  <td class="py-2.5 px-3 text-gray-500 max-w-xs truncate" :title="acta.observaciones">
                    {{ acta.observaciones || 'Sin observaciones' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
            No se han registrado actas de escrutinio para este evento de votación.
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import {
  UserGroupIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  DocumentCheckIcon,
  InformationCircleIcon,
  BuildingOfficeIcon,
  PencilIcon,
  ArrowPathIcon,
  ArrowLeftIcon,
  DocumentDuplicateIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  evento: {
    type: Object,
    required: true,
  },
  conteo_votantes: {
    type: Object,
    default: () => ({
      total: 0,
      activos: 0,
      pendientes: 0,
      rechazados: 0,
      candidatos: 0,
      solo_votantes: 0,
    }),
  },
  conteo_votos: {
    type: Object,
    default: () => ({
      total: 0,
      virtuales: 0,
      presenciales: 0,
    }),
  },
  actas_inicio: {
    type: Array,
    default: () => [],
  },
  actas_fin: {
    type: Array,
    default: () => [],
  },
  actas_escrutinio: {
    type: Array,
    default: () => [],
  },
  eventos_hijos: {
    type: Array,
    default: () => [],
  },
});

const breadcrumbLinks = [
  { url: '/eventos', text: 'Listado de eventos' },
  { url: '', text: 'Detalle: ' + props.evento.nombre },
];

const desglosarTipos = (tipos) => {
  if (!tipos || tipos === 'NA') return [];
  return tipos.split('|').map((t) => t.trim()).filter(Boolean);
};

const formatearFechaLarga = (fecha) => {
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

