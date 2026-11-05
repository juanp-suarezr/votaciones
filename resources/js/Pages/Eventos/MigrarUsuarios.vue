<template>
  <Head title="Migrar / Asignar Votantes entre Eventos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Migrar o Asignar Usuarios entre Eventos
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Banner explicativo -->
      <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-md">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-white/10 rounded-lg">
            <ArrowPathIcon class="w-8 h-8 text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold">Asignación y Migración de Censo Electoral</h2>
            <p class="text-blue-100 text-sm mt-1 leading-relaxed">
              Esta herramienta le permite vincular masivamente los votantes de un evento anterior a un nuevo evento de votación. 
              Los datos personales, cuentas de usuario, biometría y asignaciones de comuna se preservan sin requerir una nueva carga masiva por Excel.
            </p>
          </div>
        </div>
      </div>

      <!-- Alerta de resumen post-ejecución -->
      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-50 border border-green-300 rounded-xl p-5 shadow-sm">
        <div class="flex items-start gap-3">
          <CheckCircleIcon class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <h3 class="font-semibold text-green-900">Operación exitosa</h3>
            <p class="text-green-800 text-sm mt-1">{{ $page.props.flash.success }}</p>
            <div v-if="$page.props.flash.resumen" class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-gray-500 uppercase font-medium">Procesados</span>
                <span class="text-lg font-bold text-gray-800">{{ $page.props.flash.resumen.total }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-green-600 uppercase font-medium">Asignados</span>
                <span class="text-lg font-bold text-green-700">{{ $page.props.flash.resumen.insertados }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-amber-600 uppercase font-medium">Omitidos</span>
                <span class="text-lg font-bold text-amber-700">{{ $page.props.flash.resumen.omitidos }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-blue-600 uppercase font-medium">Actualizados</span>
                <span class="text-lg font-bold text-blue-700">{{ $page.props.flash.resumen.actualizados }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alerta de error -->
      <div v-if="$page.props.flash && $page.props.flash.error" class="bg-red-50 border border-red-300 rounded-xl p-4 shadow-sm flex items-start gap-3">
        <ExclamationTriangleIcon class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" />
        <div>
          <h3 class="font-semibold text-red-900">Error en el proceso</h3>
          <p class="text-red-700 text-sm mt-0.5">{{ $page.props.flash.error }}</p>
        </div>
      </div>

      <form @submit.prevent="confirmarEnvio">
        <!-- Grid de Selección de Eventos -->
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Tarjeta Evento Origen -->
          <div class="bg-white border rounded-xl shadow-sm p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between pb-3 border-b mb-4">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm">1</span>
                  <h3 class="text-base font-bold text-gray-800">Evento de Origen</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">Desde donde se copian</span>
              </div>

              <div>
                <InputLabel for="evento_origen_id" value="Selecciona el evento con los usuarios actuales *" />
                <select
                  id="evento_origen_id"
                  v-model="form.evento_origen_id"
                  @change="cargarEstadisticas"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                  <option value="">-- Seleccionar evento origen --</option>
                  <option v-for="ev in eventos" :key="'orig-' + ev.id" :value="ev.id">
                    #{{ ev.id }} - {{ ev.nombre }} ({{ ev.estado }})
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.evento_origen_id" />
              </div>

              <!-- Estadísticas del evento de origen -->
              <div v-if="cargandoStats" class="mt-4 p-4 text-center text-gray-500 bg-gray-50 rounded-lg border">
                <ArrowPathIcon class="w-5 h-5 mx-auto animate-spin text-blue-600 mb-1" />
                <span class="text-xs">Cargando censo del evento...</span>
              </div>

              <div v-else-if="statsOrigen" class="mt-4 bg-slate-50 border border-slate-200 rounded-lg p-4">
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                  <UserGroupIcon class="w-4 h-4 text-slate-500" />
                  Censo detectado en origen
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-gray-500">Total</span>
                    <span class="text-base font-bold text-gray-800">{{ statsOrigen.total }}</span>
                  </div>
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-green-600">Activos</span>
                    <span class="text-base font-bold text-green-700">{{ statsOrigen.activos }}</span>
                  </div>
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-amber-600">Pendientes</span>
                    <span class="text-base font-bold text-amber-700">{{ statsOrigen.pendientes }}</span>
                  </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500 px-1">
                  <span>Solo Votantes: <b>{{ statsOrigen.solo_votantes }}</b></span>
                  <span>Candidatos: <b>{{ statsOrigen.candidatos }}</b></span>
                  <span>Rechazados: <b>{{ statsOrigen.rechazados }}</b></span>
                </div>
              </div>
            </div>

            <div v-if="eventoOrigenSeleccionado" class="mt-4 pt-3 border-t text-xs text-gray-500 space-y-1">
              <div><b>Fechas:</b> {{ eventoOrigenSeleccionado.fecha_inicio }} &rarr; {{ eventoOrigenSeleccionado.fecha_fin }}</div>
              <div><b>Tipos:</b> {{ eventoOrigenSeleccionado.tipos }}</div>
            </div>
          </div>

          <!-- Tarjeta Evento Destino -->
          <div class="bg-white border rounded-xl shadow-sm p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between pb-3 border-b mb-4">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm">2</span>
                  <h3 class="text-base font-bold text-gray-800">Evento de Destino</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full">Hacia donde se asignan</span>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <InputLabel for="evento_destino_id" value="Selecciona el nuevo evento *" />
                  <Link :href="route('eventos.create')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    + Crear nuevo evento
                  </Link>
                </div>
                <select
                  id="evento_destino_id"
                  v-model="form.evento_destino_id"
                  @change="cargarEstadisticas"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                  <option value="">-- Seleccionar evento destino --</option>
                  <option
                    v-for="ev in eventos"
                    :key="'dest-' + ev.id"
                    :value="ev.id"
                    :disabled="ev.id === form.evento_origen_id"
                  >
                    #{{ ev.id }} - {{ ev.nombre }} ({{ ev.estado }})
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.evento_destino_id" />
              </div>

              <!-- Comparación en vivo destino -->
              <div v-if="statsOrigen && statsOrigen.ya_en_destino !== null && form.evento_destino_id" class="mt-4 bg-indigo-50/60 border border-indigo-200 rounded-lg p-4">
                <p class="text-xs font-bold text-indigo-900 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                  <DocumentDuplicateIcon class="w-4 h-4 text-indigo-600" />
                  Previsualización de impacto
                </p>
                <div class="grid grid-cols-2 gap-2 text-center">
                  <div class="bg-white p-2.5 rounded border border-indigo-200">
                    <span class="block text-xs text-amber-600 font-medium">Ya registrados en destino</span>
                    <span class="text-base font-bold text-amber-700">{{ statsOrigen.ya_en_destino }}</span>
                  </div>
                  <div class="bg-white p-2.5 rounded border border-indigo-200">
                    <span class="block text-xs text-indigo-700 font-medium">Nuevos a asignar</span>
                    <span class="text-base font-bold text-indigo-800">{{ statsOrigen.nuevos }}</span>
                  </div>
                </div>
                <p class="text-[11px] text-indigo-700 mt-2 text-center">
                  {{ statsOrigen.nuevos > 0 ? `Se crearán ${statsOrigen.nuevos} nuevas vinculaciones de votante.` : 'Todos los votantes del origen ya se encuentran en el destino.' }}
                </p>
              </div>

              <div v-else-if="form.evento_origen_id && !form.evento_destino_id" class="mt-4 p-4 text-center text-gray-400 bg-gray-50 rounded-lg border border-dashed text-xs">
                Selecciona un evento de destino para calcular los votantes nuevos y duplicados.
              </div>
            </div>

            <div v-if="eventoDestinoSeleccionado" class="mt-4 pt-3 border-t text-xs text-gray-500 space-y-1">
              <div><b>Fechas:</b> {{ eventoDestinoSeleccionado.fecha_inicio }} &rarr; {{ eventoDestinoSeleccionado.fecha_fin }}</div>
              <div><b>Tipos:</b> {{ eventoDestinoSeleccionado.tipos }}</div>
            </div>
          </div>
        </div>

        <!-- Tarjeta de Configuración de la Migración -->
        <div class="bg-white border rounded-xl shadow-sm p-6 mt-6">
          <div class="flex items-center gap-2 pb-3 border-b mb-6">
            <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-sm">3</span>
            <div>
              <h3 class="text-base font-bold text-gray-800">Reglas y Filtros de Asignación</h3>
              <p class="text-xs text-gray-500">Personalice exactamente cómo desea que se transfieran los usuarios</p>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Modo de Asignación -->
            <div class="space-y-3">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                Modo de Operación
              </label>

              <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition"
                :class="form.modo === 'copiar' ? 'border-indigo-500 bg-indigo-50/40 ring-1 ring-indigo-400' : 'border-gray-200 hover:bg-gray-50'">
                <input type="radio" value="copiar" v-model="form.modo" class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                <div>
                  <span class="text-sm font-semibold text-gray-900 block">Copiar / Asignar (Recomendado)</span>
                  <span class="text-xs text-gray-500">Conserva el registro en el evento original y vincula a los usuarios al nuevo evento para que puedan votar en ambos.</span>
                </div>
              </label>

              <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition"
                :class="form.modo === 'mover' ? 'border-amber-500 bg-amber-50/40 ring-1 ring-amber-400' : 'border-gray-200 hover:bg-gray-50'">
                <input type="radio" value="mover" v-model="form.modo" class="mt-1 text-amber-600 focus:ring-amber-500" />
                <div>
                  <span class="text-sm font-semibold text-gray-900 block">Mover / Desvincular del Origen</span>
                  <span class="text-xs text-gray-500">Transfiere al nuevo evento y los quita del origen (si un votante ya sufragó en el origen, se conservará por auditoría).</span>
                </div>
              </label>
            </div>

            <!-- Filtros de Selección de Votantes -->
            <div class="space-y-4">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                Filtros de Votantes en Origen
              </label>

              <div>
                <InputLabel for="filtro_estado" value="Filtrar por estado en origen" class="text-xs" />
                <select
                  id="filtro_estado"
                  v-model="form.filtro_estado"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="todos">Todos los estados (Activos, Pendientes, Rechazados)</option>
                  <option value="Activo">Solo Activos (Votantes habilitados)</option>
                  <option value="Pendiente">Solo Pendientes</option>
                  <option value="Rechazado">Solo Rechazados</option>
                </select>
              </div>

              <div>
                <InputLabel for="filtro_comuna" value="Filtrar por comuna / sector (opcional)" class="text-xs" />
                <select
                  id="filtro_comuna"
                  v-model="form.filtro_comuna"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Todas las comunas / sectores</option>
                  <option v-for="c in comunas" :key="'com-' + c.id" :value="c.id">
                    {{ c.detalle }}
                  </option>
                </select>
              </div>

              <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.incluir_candidatos"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span class="text-xs text-gray-700 font-medium">Incluir usuarios registrados como candidatos</span>
                </label>
              </div>
            </div>

            <!-- Condiciones en el Nuevo Evento -->
            <div class="space-y-4">
              <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                Condiciones en el Nuevo Evento
              </label>

              <div>
                <InputLabel for="estado_destino" value="Estado que tendrán en el nuevo evento" class="text-xs" />
                <select
                  id="estado_destino"
                  v-model="form.estado_destino"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="Activo">Activo (Habilitados para votar de inmediato)</option>
                  <option value="mantener">Mantener el mismo estado que tenían en el origen</option>
                  <option value="Pendiente">Pendiente (Requieren validación manual posterior)</option>
                </select>
              </div>

              <div>
                <InputLabel for="accion_duplicados" value="Si el usuario ya está en el evento destino" class="text-xs" />
                <select
                  id="accion_duplicados"
                  v-model="form.accion_duplicados"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="omitir">Omitir (Conservar registro previo sin cambios)</option>
                  <option value="actualizar">Actualizar (Sobrescribir estado y tipo con el nuevo)</option>
                </select>
              </div>

              <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.resetear_intentos"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span class="text-xs text-gray-700 font-medium">Reiniciar contador de intentos de voto a 0</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white border rounded-xl p-4 shadow-sm">
          <SecondaryLink :href="route('eventos.index')">
            Regresar al Listado de Eventos
          </SecondaryLink>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing || !form.evento_origen_id || !form.evento_destino_id || form.evento_origen_id === form.evento_destino_id"
              class="w-full sm:w-auto justify-center"
            >
              <ArrowPathIcon v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
              <span>{{ form.modo === 'mover' ? 'Ejecutar Migración (Mover)' : 'Asignar Votantes al Nuevo Evento' }}</span>
            </PrimaryButton>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import {
  ArrowPathIcon,
  UserGroupIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  DocumentDuplicateIcon,
} from '@heroicons/vue/24/solid';

const swal = inject('$swal');

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  comunas: {
    type: Array,
    default: () => [],
  },
  tipos: {
    type: Object,
    default: () => ({}),
  },
  origen_inicial: {
    type: Number,
    default: null,
  },
  destino_inicial: {
    type: Number,
    default: null,
  },
});

const breadcrumbLinks = [
  { url: '/eventos', text: 'Listado de eventos' },
  { url: '', text: 'Migrar / Asignar Votantes' },
];

const form = useForm({
  evento_origen_id: props.origen_inicial || '',
  evento_destino_id: props.destino_inicial || '',
  modo: 'copiar',
  filtro_estado: 'Activo',
  incluir_candidatos: false,
  filtro_comuna: '',
  estado_destino: 'Activo',
  accion_duplicados: 'omitir',
  resetear_intentos: true,
});

const cargandoStats = ref(false);
const statsOrigen = ref(null);

const eventoOrigenSeleccionado = computed(() => {
  return props.eventos.find((e) => e.id === Number(form.evento_origen_id));
});

const eventoDestinoSeleccionado = computed(() => {
  return props.eventos.find((e) => e.id === Number(form.evento_destino_id));
});

const cargarEstadisticas = async () => {
  if (!form.evento_origen_id) {
    statsOrigen.value = null;
    return;
  }

  cargandoStats.value = true;
  try {
    const params = {};
    if (form.evento_destino_id && form.evento_destino_id !== form.evento_origen_id) {
      params.destino_id = form.evento_destino_id;
    }
    const response = await axios.get(route('eventos.estadisticas.votantes', form.evento_origen_id), { params });
    statsOrigen.value = response.data;
  } catch (error) {
    console.error('Error cargando estadísticas del evento', error);
  } finally {
    cargandoStats.value = false;
  }
};

onMounted(() => {
  if (form.evento_origen_id) {
    cargarEstadisticas();
  }
});

const confirmarEnvio = () => {
  if (!form.evento_origen_id || !form.evento_destino_id) {
    swal({
      title: 'Selección requerida',
      text: 'Debes seleccionar tanto el evento de origen como el evento de destino.',
      icon: 'warning',
    });
    return;
  }

  if (form.evento_origen_id === form.evento_destino_id) {
    swal({
      title: 'Eventos idénticos',
      text: 'El evento de origen y de destino no pueden ser el mismo.',
      icon: 'error',
    });
    return;
  }

  const nombreOrigen = eventoOrigenSeleccionado.value ? eventoOrigenSeleccionado.value.nombre : `ID ${form.evento_origen_id}`;
  const nombreDestino = eventoDestinoSeleccionado.value ? eventoDestinoSeleccionado.value.nombre : `ID ${form.evento_destino_id}`;
  const verboModo = form.modo === 'mover' ? 'mover / desvincular' : 'copiar / asignar';

  swal({
    title: '¿Confirmar migración de votantes?',
    html: `
      <div class="text-left text-sm space-y-2">
        <p>Estás a punto de <b>${verboModo}</b> los usuarios del evento:</p>
        <div class="bg-gray-100 p-2.5 rounded font-medium text-gray-800">
          <b>Origen:</b> ${nombreOrigen}<br>
          <b>Destino:</b> ${nombreDestino}
        </div>
        <ul class="list-disc pl-5 text-xs text-gray-600 space-y-1 mt-2">
          <li>Filtro: <b>${form.filtro_estado}</b></li>
          <li>Estado asignado en destino: <b>${form.estado_destino}</b></li>
          <li>Duplicados: <b>${form.accion_duplicados === 'omitir' ? 'Omitir existentes' : 'Actualizar existentes'}</b></li>
          <li>Reiniciar intentos: <b>${form.resetear_intentos ? 'Sí (0 intentos)' : 'No'}</b></li>
        </ul>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, continuar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#4338ca',
  }).then((result) => {
    if (result.isConfirmed) {
      form.post(route('eventos.migrar.ejecutar'), {
        preserveScroll: true,
        onSuccess: () => {
          cargarEstadisticas();
        },
      });
    }
  });
};
</script>

