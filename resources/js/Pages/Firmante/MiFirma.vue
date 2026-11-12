<template>
  <AuthenticatedLayout :breadcrumb-links="breadcrumbLinks">
    <div class="space-y-6 pb-20">

      <!-- ENCABEZADO INSTITUCIONAL PEREIRA / VOTA YA -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#221718] rounded-2xl shadow-xl text-white relative overflow-hidden border border-slate-700/60">
        <!-- Franja Bicolor Superior Oficial Pereira -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#C20E1A] from-50% to-[#FFB800] to-50%"></div>

        <div class="p-6 sm:p-8 relative">
          <!-- Marca de agua Escudo Pereira -->
          <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
            <img src="/assets/img/escudo.webp" onerror="this.onerror=null; this.src='/assets/img/logo.png';" alt="Escudo de Pereira" class="w-64 h-64 object-contain" />
          </div>

          <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-bold text-[#FFB800]">
                <CheckBadgeIcon class="w-4 h-4 text-emerald-400" />
                ALCALDÍA DE PEREIRA · CERTIFICACIÓN ELECTORAL
              </div>
              <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                Mi Firma Digital y Asignación de Eventos
              </h1>
              <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                Autogestione su firma institucional digitalizada y seleccione las elecciones en las que certificará las actas oficiales de apertura, cierre y escrutinio como secretario/a firmante.
              </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
              <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/15 text-center">
                <span class="block text-xs uppercase tracking-wider text-slate-300 font-bold">Eventos Asignados</span>
                <span class="text-2xl font-black text-[#FFB800]">{{ form.eventos_seleccionados.length }}</span>
              </div>

              <button
                @click="guardarTodo"
                :disabled="procesando"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#C20E1A] hover:bg-[#991B1B] text-white font-bold text-sm shadow-lg hover:shadow-[#C20E1A]/30 transition-all duration-200 disabled:opacity-50 cursor-pointer"
              >
                <ArrowPathIcon v-if="procesando" class="w-5 h-5 animate-spin" />
                <CheckIcon v-else class="w-5 h-5" />
                {{ procesando ? 'Guardando...' : 'Guardar Firma' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ALERTA INFORMATIVA -->
      <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl shadow-sm text-amber-900 text-xs sm:text-sm flex items-start gap-3">
        <InformationCircleIcon class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
        <div>
          <span class="font-bold">Información de Validez Jurídica:</span>
          La firma aquí registrada será estampada automáticamente en los encabezados y pies de certificación de las actas oficiales generadas por el sistema (en formato digital y PDF para exportación). Asegúrese de mantener su cargo y trazado actualizados.
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- COLUMNA IZQUIERDA: DATOS Y FIRMA (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

          <!-- TARJETA 1: DATOS INSTITUCIONALES -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
              <UserCircleIcon class="w-5 h-5 text-[#C20E1A]" />
              <h2 class="font-bold text-gray-800 text-base">Datos del Firmante</h2>
            </div>

            <div class="space-y-3">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nombre Completo *</label>
                <input
                  type="text"
                  v-model="form.nombre"
                  placeholder="Ej: Pedro Pérez Gómez"
                  class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:border-[#C20E1A] focus:ring-[#C20E1A] py-2.5"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Documento de Identificación *</label>
                <input
                  type="text"
                  v-model="form.identificacion"
                  placeholder="Cédula de ciudadanía"
                  class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:border-[#C20E1A] focus:ring-[#C20E1A] py-2.5"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  Cargo Institucional *
                </label>
                <input
                  type="text"
                  v-model="form.cargo"
                  placeholder="Ej: Secretario de las TIC / Directora Talento Humano"
                  class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:border-[#C20E1A] focus:ring-[#C20E1A] py-2.5"
                />
                <p class="text-[11px] text-gray-500 mt-1">
                  Este texto exacto aparecerá debajo de su nombre en las actas oficiales.
                </p>
              </div>
            </div>
          </div>

          <!-- TARJETA 2: GESTIÓN DE FIRMA DIGITAL -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
              <div class="flex items-center gap-2">
                <PencilSquareIcon class="w-5 h-5 text-[#C20E1A]" />
                <h2 class="font-bold text-gray-800 text-base">Firma Digital</h2>
              </div>
              <span v-if="firmaGuardadaUrl" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                <CheckCircleIcon class="w-3.5 h-3.5 text-emerald-500" /> Registrada
              </span>
              <span v-else class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                Sin firma
              </span>
            </div>

            <!-- FIRMA ACTUAL (SI EXISTE) -->
            <div v-if="firmaGuardadaUrl && !firmaModificada" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
              <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Firma Vigente en el Sistema</span>
              <div class="bg-white border border-dashed border-gray-300 rounded-lg p-3 max-w-[260px] mx-auto shadow-sm">
                <img :src="firmaGuardadaUrl" alt="Firma Registrada" class="max-h-24 mx-auto object-contain" />
              </div>
              <p class="text-[11px] text-gray-500 mt-2">
                Para cambiarla, trace una nueva en el lienzo inferior o suba un archivo nuevo.
              </p>
            </div>

            <!-- SELECTOR DE MÉTODO -->
            <div>
              <div class="flex gap-2 p-1 bg-gray-100 rounded-xl mb-3 text-xs font-bold">
                <button
                  type="button"
                  @click="modoFirma = 'canvas'"
                  :class="modoFirma === 'canvas' ? 'bg-[#C20E1A] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                  class="flex-1 py-1.5 rounded-lg transition-all text-center cursor-pointer"
                >
                  Trazar en Pantalla
                </button>
                <button
                  type="button"
                  @click="modoFirma = 'archivo'"
                  :class="modoFirma === 'archivo' ? 'bg-[#C20E1A] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                  class="flex-1 py-1.5 rounded-lg transition-all text-center cursor-pointer"
                >
                  Subir Imagen
                </button>
              </div>

              <!-- MÉTODO 1: CANVAS INTERACTIVO -->
              <div v-show="modoFirma === 'canvas'" class="space-y-2">
                <div class="border-2 border-dashed border-rose-300 rounded-xl bg-white p-2 relative shadow-inner">
                  <canvas
                    ref="canvasRef"
                    width="420"
                    height="170"
                    class="w-full h-36 bg-white cursor-crosshair touch-none rounded-lg"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @mouseleave="stopDrawing"
                    @touchstart="startDrawingTouch"
                    @touchmove="drawTouch"
                    @touchend="stopDrawing"
                  ></canvas>
                  <span class="absolute bottom-2 right-3 text-[10px] text-gray-400 select-none pointer-events-none">
                    Lienzo Digital
                  </span>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                  <button
                    type="button"
                    @click="limpiarCanvas"
                    class="text-gray-600 hover:text-rose-600 font-semibold px-2 py-1 rounded hover:bg-gray-100 transition-colors cursor-pointer"
                  >
                    Borrar Lienzo
                  </button>
                  <button
                    type="button"
                    @click="aplicarFirmaCanvas"
                    class="bg-[#C20E1A] hover:bg-[#991B1B] text-white font-bold px-3 py-1 rounded-lg shadow-sm transition-all cursor-pointer"
                  >
                    Confirmar Trazo
                  </button>
                </div>
              </div>

              <!-- MÉTODO 2: SUBIR ARCHIVO -->
              <div v-show="modoFirma === 'archivo'" class="space-y-2">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-[#C20E1A] transition-colors">
                  <input
                    type="file"
                    accept="image/png, image/jpeg"
                    @change="onFileFirmaChange"
                    class="hidden"
                    id="archivo-firma-firmante"
                  />
                  <label for="archivo-firma-firmante" class="cursor-pointer space-y-1 block">
                    <ArrowDownTrayIcon class="w-8 h-8 text-gray-400 mx-auto" />
                    <span class="block text-xs font-bold text-[#C20E1A]">Haga clic para seleccionar archivo</span>
                    <span class="block text-[11px] text-gray-500">PNG o JPG con fondo blanco o transparente</span>
                  </label>
                </div>
              </div>

              <!-- VISTA PREVIA DE FIRMA MODIFICADA -->
              <div v-if="firmaPreview" class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                <span class="block text-[11px] font-bold text-emerald-800 mb-1">Nueva Firma a Guardar:</span>
                <img :src="firmaPreview" alt="Nueva firma" class="max-h-20 mx-auto object-contain bg-white rounded p-1 border border-emerald-300" />
              </div>
            </div>
          </div>
        </div>

        <!-- COLUMNA DERECHA: SELECCIÓN DE EVENTOS (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
              <div>
                <h2 class="font-bold text-gray-800 text-base flex items-center gap-2">
                  <CheckBadgeIcon class="w-5 h-5 text-[#C20E1A]" />
                  Asignación de Firma a Eventos Electorales
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                  Marque las elecciones institucionales en cuyas actas oficiales debe figurar su firma.
                </p>
              </div>

              <!-- FILTRO / BUSCADOR -->
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="toggleTodosEventos"
                  class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-300 px-3 py-1.5 rounded-lg transition-colors cursor-pointer whitespace-nowrap"
                >
                  {{ todosSeleccionados ? 'Deseleccionar Todos' : 'Seleccionar Todos' }}
                </button>
              </div>
            </div>

            <!-- CAMPO DE BÚSQUEDA RÁPIDA -->
            <div class="relative">
              <input
                type="text"
                v-model="filtroEvento"
                placeholder="Buscar evento por nombre o dependencia..."
                class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:border-[#C20E1A] focus:ring-[#C20E1A] pl-9 py-2"
              />
              <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" />
            </div>

            <!-- LISTADO DE EVENTOS INTERACTIVO -->
            <div class="space-y-2.5 max-h-[560px] overflow-y-auto pr-1">
              <div
                v-for="ev in eventosFiltrados"
                :key="ev.id"
                @click="toggleEvento(ev.id)"
                :class="form.eventos_seleccionados.includes(ev.id)
                  ? 'border-[#C20E1A] bg-rose-50/40 shadow-sm ring-1 ring-[#C20E1A]/40'
                  : 'border-gray-200 hover:border-gray-300 bg-white'"
                class="border rounded-xl p-3.5 transition-all cursor-pointer flex items-start gap-3.5"
              >
                <!-- CHECKBOX ESTILIZADO -->
                <div class="pt-0.5 shrink-0">
                  <div
                    :class="form.eventos_seleccionados.includes(ev.id) ? 'bg-[#C20E1A] border-[#C20E1A] text-white' : 'border-gray-300 bg-white'"
                    class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors"
                  >
                    <CheckIcon v-if="form.eventos_seleccionados.includes(ev.id)" class="w-3.5 h-3.5" />
                  </div>
                </div>

                <!-- CONTENIDO DEL EVENTO -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
                    <span class="text-xs font-bold text-gray-900 truncate">
                      [ID {{ ev.id }}] {{ ev.nombre }}
                    </span>
                    <span
                      v-if="form.eventos_seleccionados.includes(ev.id)"
                      class="text-[10px] font-bold uppercase tracking-wider text-[#C20E1A] bg-rose-100 px-2 py-0.5 rounded-full"
                    >
                      Firma Activa
                    </span>
                    <span
                      v-else
                      class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"
                    >
                      No Asignado
                    </span>
                  </div>

                  <div class="text-[11px] text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="text-gray-500">
                      <strong>Dependencia:</strong> {{ ev.dependencias || 'Alcaldía de Pereira' }}
                    </span>
                    <span v-if="ev.fecha_inicio" class="text-gray-500">
                      <strong>Inicio:</strong> {{ ev.fecha_inicio }}
                    </span>
                    <span v-if="ev.fecha_fin" class="text-gray-500">
                      <strong>Cierre:</strong> {{ ev.fecha_fin }}
                    </span>
                  </div>
                </div>

                <!-- BOTÓN IR AL ACTA (SI APLICA) -->
                <div class="shrink-0 self-center" @click.stop>
                  <a
                    :href="ev.id === 25 ? route('actas-comision.index') : route('actas-generales.index', { id_evento: ev.id })"
                    target="_blank"
                    class="p-1.5 text-gray-400 hover:text-[#C20E1A] hover:bg-rose-50 rounded-lg transition-colors block"
                    title="Ver Actas de este Evento"
                  >
                    <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                  </a>
                </div>
              </div>

              <!-- SIN RESULTADOS -->
              <div v-if="eventosFiltrados.length === 0" class="text-center py-8 text-gray-500 text-xs">
                No se encontraron eventos coincidentes con la búsqueda.
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- BARRA DE ACCIÓN INFERIOR FLOTANTE -->
      <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-gray-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs text-gray-600">
          <CheckBadgeIcon class="w-5 h-5 text-emerald-600 shrink-0" />
          <span>
            Tiene <strong>{{ form.eventos_seleccionados.length }}</strong> evento(s) seleccionados para estampar su firma digital.
          </span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
          <button
            type="button"
            @click="guardarTodo"
            :disabled="procesando"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#C20E1A] hover:bg-[#991B1B] text-white font-bold text-xs shadow-md transition-all cursor-pointer disabled:opacity-50"
          >
            <ArrowPathIcon v-if="procesando" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" />
            {{ procesando ? 'Guardando...' : 'Guardar Firma y Asignaciones' }}
          </button>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import {
  PencilSquareIcon,
  CheckBadgeIcon,
  UserCircleIcon,
  CheckCircleIcon,
  InformationCircleIcon,
  ArrowDownTrayIcon,
  CheckIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  perfil: Object,
  eventos_disponibles: Array,
  eventos_asignados: Array
});

const breadcrumbLinks = [
  { url: '/dashboard', text: 'Inicio' },
  { url: '', text: 'Mi Firma y Asignación de Eventos' }
];

const form = ref({
  nombre: props.perfil?.nombre || '',
  identificacion: props.perfil?.identificacion || '',
  cargo: props.perfil?.cargo || '',
  firma: null,
  eventos_seleccionados: [...(props.eventos_asignados || [])]
});

const firmaGuardadaUrl = ref(props.perfil?.firma_url || null);
const firmaPreview = ref(null);
const firmaModificada = ref(false);
const modoFirma = ref('canvas');
const canvasRef = ref(null);
const filtroEvento = ref('');
const procesando = ref(false);

let isDrawing = false;
let ctx = null;

const initCanvas = () => {
  if (canvasRef.value) {
    ctx = canvasRef.value.getContext('2d');
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }
};

const getCanvasCoordinates = (e) => {
  const rect = canvasRef.value.getBoundingClientRect();
  const scaleX = canvasRef.value.width / rect.width;
  const scaleY = canvasRef.value.height / rect.height;
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY
  };
};

const startDrawing = (e) => {
  initCanvas();
  isDrawing = true;
  const pos = getCanvasCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
  if (!isDrawing || !ctx) return;
  const pos = getCanvasCoordinates(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.stroke();
};

const stopDrawing = () => {
  isDrawing = false;
};

const startDrawingTouch = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  startDrawing(touch);
};

const drawTouch = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  draw(touch);
};

const limpiarCanvas = () => {
  if (ctx && canvasRef.value) {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  }
  firmaPreview.value = null;
  form.value.firma = null;
  firmaModificada.value = false;
};

const aplicarFirmaCanvas = () => {
  if (canvasRef.value) {
    const dataUrl = canvasRef.value.toDataURL('image/png');
    firmaPreview.value = dataUrl;
    form.value.firma = dataUrl;
    firmaModificada.value = true;
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Trazo de firma listo para guardar',
      showConfirmButton: false,
      timer: 2000
    });
  }
};

const onFileFirmaChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.firma = file;
    firmaModificada.value = true;
    const reader = new FileReader();
    reader.onload = (ev) => {
      firmaPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const eventosFiltrados = computed(() => {
  if (!filtroEvento.value) return props.eventos_disponibles || [];
  const q = filtroEvento.value.toLowerCase();
  return (props.eventos_disponibles || []).filter(ev =>
    ev.nombre?.toLowerCase().includes(q) ||
    ev.dependencias?.toLowerCase().includes(q) ||
    String(ev.id).includes(q)
  );
});

const todosSeleccionados = computed(() => {
  if (!props.eventos_disponibles || props.eventos_disponibles.length === 0) return false;
  return props.eventos_disponibles.every(ev => form.value.eventos_seleccionados.includes(ev.id));
});

const toggleEvento = (id) => {
  const index = form.value.eventos_seleccionados.indexOf(id);
  if (index > -1) {
    form.value.eventos_seleccionados.splice(index, 1);
  } else {
    form.value.eventos_seleccionados.push(id);
  }
};

const toggleTodosEventos = () => {
  if (todosSeleccionados.value) {
    form.value.eventos_seleccionados = [];
  } else {
    form.value.eventos_seleccionados = props.eventos_disponibles.map(ev => ev.id);
  }
};

const guardarTodo = () => {
  if (!form.value.nombre.trim()) {
    Swal.fire('Atención', 'Por favor ingrese su nombre completo.', 'warning');
    return;
  }
  if (!form.value.identificacion.trim()) {
    Swal.fire('Atención', 'Por favor ingrese su número de identificación.', 'warning');
    return;
  }
  if (!form.value.cargo.trim()) {
    Swal.fire('Atención', 'Por favor ingrese su cargo institucional.', 'warning');
    return;
  }
  if (!firmaGuardadaUrl.value && !form.value.firma) {
    Swal.fire('Atención', 'Debe trazar su firma en el lienzo o subir una imagen de su firma antes de continuar.', 'warning');
    return;
  }

  procesando.value = true;

  router.post(route('firmante.mi-firma.guardar'), form.value, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      procesando.value = false;
      firmaModificada.value = false;
      Swal.fire({
        icon: 'success',
        title: '¡Firma y Eventos Actualizados!',
        text: 'Su firma digital y asignaciones a eventos han sido guardadas y quedarán estampadas en las actas correspondientes.',
        confirmButtonColor: '#0369a1'
      });
    },
    onError: (errors) => {
      procesando.value = false;
      const msg = Object.values(errors).flat().join('<br>');
      Swal.fire({
        icon: 'error',
        title: 'Error al guardar',
        html: msg || 'Ocurrió un error al procesar la información.',
        confirmButtonColor: '#b91c1c'
      });
    }
  });
};

onMounted(() => {
  initCanvas();
});
</script>

