<template>
  <Head title="Inscripción Candidato - Comisión de Personal" />

  <div class="min-h-screen bg-slate-100 py-8 px-4 sm:px-6 lg:px-8 text-slate-800">
    <!-- Contenedor Principal con estilo de Documento Institucional -->
    <div class="max-w-4xl mx-auto bg-white border-2 border-slate-300 rounded-2xl shadow-xl overflow-hidden print:border-none print:shadow-none">
      
      <!-- ENCABEZADO INSTITUCIONAL SEGÚN LA IMAGEN -->
      <div class="border-b-2 border-slate-300 p-6 sm:p-8 bg-slate-50/80 text-center relative">
        <div class="flex items-center justify-between mb-4">
          <Link
            href="/"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition print:hidden"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Volver al portal principal</span>
          </Link>
          <span class="text-[11px] font-mono font-bold px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-full border border-indigo-200">
            Proceso Oficial 2025 - 2026
          </span>
        </div>

        <div class="space-y-1">
          <h2 class="text-sm sm:text-base font-extrabold uppercase tracking-wide text-slate-900">
            {{ titulo_encabezado }}
          </h2>
          <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700">
            {{ subtitulo_encabezado }}
          </h3>
          <h1 class="text-base sm:text-lg font-black uppercase tracking-tight text-indigo-900 pt-1">
            {{ nombre_formulario }}
          </h1>
        </div>

        <p class="text-xs text-slate-500 mt-2 max-w-xl mx-auto">
          Formulario de postulación para empleados de carrera administrativa. Ingrese su número de documento para validar su registro y cargar la documentación exigida.
        </p>
      </div>

      <!-- VISTA DE ÉXITO CON RADICADO (SI YA SE POSTULÓ EXITOSAMENTE) -->
      <div v-if="postulacionExitosa" class="p-8 sm:p-12 text-center space-y-6">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
          <CheckCircleIcon class="w-10 h-10" />
        </div>

        <div class="space-y-2">
          <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 inline-block uppercase tracking-wider">
            ¡Inscripción Radicada Exitosamente!
          </span>
          <h2 class="text-2xl font-black text-slate-900">{{ postulacionExitosa.nombre }}</h2>
          <p class="text-xs text-slate-500">
            Su postulación a la Comisión de Personal ha sido registrada y enviada a revisión formal.
          </p>
        </div>

        <!-- Ficha de Constancia de Radicación -->
        <div class="max-w-md mx-auto bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-5 text-left text-xs space-y-2.5 shadow-sm">
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-semibold uppercase">Número de Radicado:</span>
            <span class="font-mono text-sm font-black text-indigo-700">{{ postulacionExitosa.radicado }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Identificación:</span>
            <span class="font-mono font-bold text-slate-800">{{ postulacionExitosa.identificacion }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Estamento / Planta:</span>
            <span class="font-bold text-slate-800">{{ postulacionExitosa.tipo_planta }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Fecha de Radicación:</span>
            <span class="font-semibold text-slate-700">{{ postulacionExitosa.fecha }}</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-amber-700">
            <span class="font-semibold">Estado Actual:</span>
            <span class="font-bold uppercase bg-amber-100 px-2 py-0.5 rounded text-[11px] border border-amber-300">Pendiente de Verificación</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-4 print:hidden">
          <button
            type="button"
            @click="imprimirConstancia"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-2 shadow-sm"
          >
            <PrinterIcon class="w-4 h-4" />
            <span>Imprimir Comprobante</span>
          </button>

          <button
            type="button"
            @click="reiniciarFormulario"
            class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition shadow-xs"
          >
            Nueva Consulta / Registro
          </button>
        </div>
      </div>

      <!-- FORMULARIO DE POSTULACIÓN -->
      <div v-else class="p-6 sm:p-8 space-y-8">

        <!-- PASO 1: BÚSQUEDA Y VALIDACIÓN DE FUNCIONARIO -->
        <div class="bg-indigo-50/60 border-2 border-indigo-200 rounded-xl p-5 space-y-4">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-indigo-600 text-white rounded-lg shadow-sm">
              <IdentificationIcon class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">
                Paso 1: Validación de Documento de Funcionario
              </h3>
              <p class="text-[11px] text-indigo-700">
                Ingrese su número de documento. El sistema verificará que se encuentre registrado como funcionario activo (is_funcionario = 1).
              </p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
            <div class="w-full sm:w-1/3">
              <select
                v-model="tipoDocConsulta"
                :disabled="funcionarioValidado"
                class="w-full text-xs border border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-slate-100"
              >
                <option value="CC">Cédula de Ciudadanía (CC)</option>
                <option value="CE">Cédula de Extranjería (CE)</option>
                <option value="TI">Tarjeta de Identidad (TI)</option>
              </select>
            </div>

            <div class="relative w-full sm:w-2/3">
              <input
                type="text"
                v-model="identificacionConsulta"
                @keyup.enter="consultarFuncionario"
                :disabled="funcionarioValidado"
                placeholder="Ingrese número de identificación..."
                class="w-full text-xs font-mono font-bold border border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 pr-28 disabled:bg-slate-100"
              />

              <button
                v-if="!funcionarioValidado"
                type="button"
                @click="consultarFuncionario"
                :disabled="cargandoConsulta || !identificacionConsulta"
                class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white rounded-md text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
              >
                <ArrowPathIcon v-if="cargandoConsulta" class="w-3.5 h-3.5 animate-spin" />
                <MagnifyingGlassIcon v-else class="w-3.5 h-3.5" />
                <span>{{ cargandoConsulta ? 'Validando...' : 'Consultar' }}</span>
              </button>

              <button
                v-else
                type="button"
                @click="cambiarFuncionario"
                class="absolute right-1.5 top-1.5 bottom-1.5 px-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-md text-[11px] font-semibold transition"
              >
                Cambiar
              </button>
            </div>
          </div>

          <!-- Mensaje de Error de Funcionario -->
          <div v-if="errorConsulta" class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800 flex items-start gap-2">
            <ExclamationTriangleIcon class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="font-bold">No habilitado para postulación</p>
              <p class="text-[11px] text-red-700 mt-0.5">{{ errorConsulta }}</p>
            </div>
          </div>

          <!-- Alerta si ya está postulado -->
          <div v-if="avisoYaPostulado" class="p-3 bg-amber-50 border border-amber-300 rounded-lg text-xs text-amber-900 flex items-start gap-2">
            <InformationCircleIcon class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="font-bold">Postulación ya registrada</p>
              <p class="text-[11px] text-amber-800 mt-0.5">{{ avisoYaPostulado }}</p>
            </div>
          </div>

          <!-- Confirmación de Funcionario Validado -->
          <div v-if="funcionarioValidado" class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-900 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
              <div>
                <span class="font-bold">Funcionario Activo Verificado: </span>
                <span class="uppercase font-extrabold text-slate-800">{{ form.nombre }}</span>
              </div>
            </div>
            <span class="px-2 py-0.5 bg-emerald-200 text-emerald-900 rounded font-bold text-[10px]">Habilitado</span>
          </div>
        </div>

        <!-- PASO 2: FORMULARIO OFICIAL (ESTRUCTURA IDÉNTICA A LA IMAGEN) -->
        <form @submit.prevent="enviarInscripcion" class="space-y-6" :class="{ 'opacity-50 pointer-events-none': !funcionarioValidado }">
          
          <div class="border-2 border-slate-800 rounded-xl p-5 sm:p-6 bg-white space-y-5 shadow-sm">
            
            <!-- FILA 1: NOMBRES Y APELLIDOS + SELECCIÓN PLANTA CENTRAL / SGP -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
              <div class="w-full md:w-3/5 space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-900">
                  Nombres y Apellidos Candidato:
                </label>
                <input
                  type="text"
                  v-model="form.nombre"
                  readonly
                  class="w-full text-xs font-bold uppercase bg-slate-50 border-2 border-slate-400 rounded-md px-3 py-2 text-slate-900 focus:outline-none"
                  placeholder="Auto-llenado con el documento verificado"
                />
              </div>

              <!-- CHECKBOXES PLANTA CENTRAL / SGP (SEGÚN LA IMAGEN) -->
              <div class="w-full md:w-2/5 flex items-center justify-end gap-5 pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none group">
                  <span class="text-xs font-black uppercase tracking-wide text-slate-900">Planta Central</span>
                  <input
                    type="radio"
                    name="tipo_planta"
                    value="PLANTA CENTRAL"
                    v-model="form.tipo_planta"
                    class="w-5 h-5 text-indigo-600 border-2 border-slate-600 rounded focus:ring-indigo-500 cursor-pointer"
                  />
                </label>

                <label class="flex items-center gap-2 cursor-pointer select-none group">
                  <span class="text-xs font-black uppercase tracking-wide text-slate-900">SGP</span>
                  <input
                    type="radio"
                    name="tipo_planta"
                    value="SGP"
                    v-model="form.tipo_planta"
                    class="w-5 h-5 text-indigo-600 border-2 border-slate-600 rounded focus:ring-indigo-500 cursor-pointer"
                  />
                </label>
              </div>
            </div>

            <!-- FILA 2: TIPO Y NÚMERO DE IDENTIFICACIÓN -->
            <div class="space-y-1">
              <label class="block text-xs font-black uppercase tracking-wider text-slate-900">
                Tipo y Número de Identificación:
              </label>
              <input
                type="text"
                :value="form.tipo_documento + ' - ' + form.identificacion"
                readonly
                class="w-full text-xs font-mono font-bold bg-slate-50 border-2 border-slate-400 rounded-md px-3 py-2 text-slate-900 focus:outline-none"
              />
            </div>

            <!-- FILA 3: DATOS DE CONTACTO ADICIONALES (EMAIL Y CELULAR) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                  Correo Electrónico de Notificación:
                </label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="ejemplo@alcaldia.gov.co"
                  class="w-full text-xs border border-slate-300 rounded-md px-3 py-2 shadow-2xs focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                  Teléfono Móvil / Celular:
                </label>
                <input
                  type="text"
                  v-model="form.celular"
                  required
                  placeholder="3001234567"
                  class="w-full text-xs border border-slate-300 rounded-md px-3 py-2 shadow-2xs focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
            </div>

            <!-- DECLARACIÓN JURADA (TEXTO EXACTO DE LA IMAGEN) -->
            <div class="p-4 bg-slate-50 border border-slate-300 rounded-lg space-y-3">
              <p class="text-xs font-semibold text-slate-800 leading-relaxed">
                Declaro que cumplo con las calidades determinadas en la normatividad vigente, siendo estas:
              </p>
              <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                <li>No haber sido sancionados disciplinariamente durante el año anterior a la fecha de la inscripción de la candidatura; y</li>
                <li>Ser empleados de carrera administrativa.</li>
              </ul>

              <div class="pt-2 border-t border-slate-200">
                <label class="flex items-start gap-2.5 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    v-model="form.declaracion"
                    required
                    class="mt-0.5 rounded border-2 border-slate-600 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                  />
                  <span class="text-xs font-bold text-slate-900 leading-snug">
                    Acepto y declaro bajo gravedad de juramento el cumplimiento cabal de los requisitos anteriores.
                  </span>
                </label>
              </div>
            </div>

            <!-- FILA 4: FECHA POSTULACIÓN (SEGÚN LA IMAGEN EN TRES CASILLAS) -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
              <label class="text-xs font-black uppercase tracking-wider text-slate-900">
                Fecha Postulación:
              </label>

              <div class="flex items-center gap-1.5 font-mono">
                <!-- Casilla Día -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.dia"
                    readonly
                    class="w-12 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Día</span>
                </div>

                <span class="font-bold text-slate-400">/</span>

                <!-- Casilla Mes -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.mes"
                    readonly
                    class="w-12 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Mes</span>
                </div>

                <span class="font-bold text-slate-400">/</span>

                <!-- Casilla Año -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.anio"
                    readonly
                    class="w-16 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Año</span>
                </div>
              </div>
            </div>
          </div>

          <!-- PASO 3: CARGA OBLIGATORIA DE DOCUMENTOS -->
          <div class="border-2 border-indigo-200 rounded-xl p-5 sm:p-6 bg-slate-50/70 space-y-5 shadow-sm">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-200">
              <DocumentArrowUpIcon class="w-5 h-5 text-indigo-700" />
              <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                Paso 3: Carga de Documentación Obligatoria
              </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              
              <!-- 1. CERTIFICADO DE PROCURADURÍA -->
              <div class="p-4 bg-white border border-slate-300 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-black text-slate-900 uppercase">
                    1. Certificado de Procuraduría *
                  </label>
                  <span class="text-[10px] text-indigo-700 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    Máx 3 meses
                  </span>
                </div>

                <p class="text-[11px] text-slate-500">
                  Certificado vigente no mayor a tres meses expedido por la Procuraduría General de la Nación. Formato PDF o imagen.
                </p>

                <div class="space-y-2">
                  <input
                    type="file"
                    ref="inputProcuraduria"
                    @change="handleFileProcuraduria"
                    accept=".pdf,.png,.jpg,.jpeg"
                    required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                  />
                  <div v-if="archivoProcNombre" class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1">
                    <CheckCircleIcon class="w-3.5 h-3.5" />
                    <span>{{ archivoProcNombre }}</span>
                  </div>
                </div>
              </div>

              <!-- 2. FOTOCOPIA DE CÉDULA LEGIBLE -->
              <div class="p-4 bg-white border border-slate-300 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-black text-slate-900 uppercase">
                    2. Fotocopia de Cédula de Ciudadanía *
                  </label>
                  <span class="text-[10px] text-indigo-700 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    Legible
                  </span>
                </div>

                <p class="text-[11px] text-slate-500">
                  Documento de identidad legible por ambas caras o al 150%. Formato PDF o imagen (PNG/JPG).
                </p>

                <div class="space-y-2">
                  <input
                    type="file"
                    ref="inputCedula"
                    @change="handleFileCedula"
                    accept=".pdf,.png,.jpg,.jpeg"
                    required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                  />
                  <div v-if="archivoCedulaNombre" class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1">
                    <CheckCircleIcon class="w-3.5 h-3.5" />
                    <span>{{ archivoCedulaNombre }}</span>
                  </div>
                </div>
              </div>

              <!-- 3. FOTOGRAFÍA OPCIONAL PARA EL TARJETÓN -->
              <div class="p-4 bg-white border border-slate-300 rounded-xl space-y-3 md:col-span-2">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-bold text-slate-800 uppercase">
                    3. Fotografía Reciente del Candidato (Opcional para el Tarjetón)
                  </label>
                  <span class="text-[10px] text-slate-500 font-medium">Recomendado</span>
                </div>

                <p class="text-[11px] text-slate-500">
                  Fotografía reciente tipo documento (fondo blanco) que será visible en la plataforma de votación durante el sufragio.
                </p>

                <div class="flex items-center gap-4">
                  <input
                    type="file"
                    ref="inputFoto"
                    @change="handleFileFoto"
                    accept=".png,.jpg,.jpeg"
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                  />
                  <div v-if="archivoFotoNombre" class="text-[11px] font-mono text-emerald-700 font-bold whitespace-nowrap">
                    {{ archivoFotoNombre }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- BOTÓN DE ENVÍO Y RADICACIÓN -->
          <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500">
              * Todos los campos y documentos marcados son estrictamente obligatorios según normatividad.
            </span>

            <button
              type="submit"
              :disabled="enviando || !funcionarioValidado"
              class="w-full sm:w-auto px-8 py-3.5 bg-indigo-700 hover:bg-indigo-800 disabled:opacity-50 text-white font-extrabold rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2"
            >
              <ArrowPathIcon v-if="enviando" class="w-4 h-4 animate-spin" />
              <PaperAirplaneIcon v-else class="w-4 h-4" />
              <span>{{ enviando ? 'Procesando Radicación...' : 'Radicar Formulario de Inscripción' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- PIE DE PÁGINA INSTITUCIONAL -->
      <div class="p-4 bg-slate-50 border-t border-slate-200 text-center text-[11px] text-slate-500">
        Plataforma Electoral Institucional • Dirección Administrativa de Talento Humano • Comisión de Personal
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import {
  ArrowLeftIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ArrowPathIcon,
  DocumentArrowUpIcon,
  PaperAirplaneIcon,
  IdentificationIcon,
  PrinterIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  evento: {
    type: Object,
    default: null,
  },
  fecha_actual: {
    type: Object,
    required: true,
  },
  titulo_encabezado: {
    type: String,
    default: 'SECRETARÍA DE GESTIÓN ADMINISTRATIVA',
  },
  subtitulo_encabezado: {
    type: String,
    default: 'DIRECCIÓN ADMINISTRATIVA DE TALENTO HUMANO',
  },
  nombre_formulario: {
    type: String,
    default: 'FORMULARIO DE INSCRIPCIÓN – COMISIÓN DE PERSONAL 2025-2026',
  },
});

const page = usePage();

// Estado de la consulta inicial
const tipoDocConsulta = ref('CC');
const identificacionConsulta = ref('');
const cargandoConsulta = ref(false);
const errorConsulta = ref('');
const avisoYaPostulado = ref('');
const funcionarioValidado = ref(false);

// Archivos seleccionados (nombres para feedback)
const archivoProcNombre = ref('');
const archivoCedulaNombre = ref('');
const archivoFotoNombre = ref('');

// Formulario de Inertia
const form = useForm({
  identificacion: '',
  tipo_documento: 'CC',
  nombre: '',
  tipo_planta: 'PLANTA CENTRAL',
  email: '',
  celular: '',
  fecha_postulacion: props.fecha_actual.completa,
  declaracion: false,
  archivo_procuraduria: null,
  archivo_cedula: null,
  foto: null,
  evento_id: props.evento ? props.evento.id : null,
});

const enviando = ref(false);
const postulacionExitosa = computed(() => page.props.flash?.success_postulacion || null);

// Consultar si es funcionario y traer datos automáticamente
const consultarFuncionario = async () => {
  if (!identificacionConsulta.value) return;

  cargandoConsulta.value = true;
  errorConsulta.value = '';
  avisoYaPostulado.value = '';
  funcionarioValidado.value = false;

  try {
    const response = await axios.post(route('candidatos.inscripcion.consultar'), {
      identificacion: identificacionConsulta.value,
    });

    if (response.data.success) {
      if (response.data.ya_postulado) {
        avisoYaPostulado.value = response.data.mensaje;
        return;
      }

      // Auto-llenar campos del formulario
      form.identificacion = response.data.identificacion;
      form.tipo_documento = response.data.tipo_documento || tipoDocConsulta.value;
      form.nombre = response.data.nombre;
      form.email = response.data.email || '';
      form.celular = response.data.celular || '';
      form.tipo_planta = response.data.tipo_planta || 'PLANTA CENTRAL';
      form.fecha_postulacion = props.fecha_actual.completa;

      funcionarioValidado.value = true;
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.mensaje) {
      errorConsulta.value = error.response.data.mensaje;
    } else {
      errorConsulta.value = 'Ocurrió un error al verificar la identificación. Por favor intente nuevamente.';
    }
  } finally {
    cargandoConsulta.value = false;
  }
};

const cambiarFuncionario = () => {
  funcionarioValidado.value = false;
  identificacionConsulta.value = '';
  errorConsulta.value = '';
  avisoYaPostulado.value = '';
  form.reset();
  archivoProcNombre.value = '';
  archivoCedulaNombre.value = '';
  archivoFotoNombre.value = '';
};

// Manejo de archivos
const handleFileProcuraduria = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.archivo_procuraduria = file;
    archivoProcNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

const handleFileCedula = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.archivo_cedula = file;
    archivoCedulaNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

const handleFileFoto = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.foto = file;
    archivoFotoNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

// Envío del formulario
const enviarInscripcion = () => {
  if (!form.declaracion) {
    Swal.fire({
      icon: 'warning',
      title: 'Declaración obligatoria',
      text: 'Debe marcar la casilla de aceptación y declaración juramentada de cumplimiento normativo.',
    });
    return;
  }

  if (!form.archivo_procuraduria) {
    Swal.fire({
      icon: 'warning',
      title: 'Certificado de Procuraduría requerido',
      text: 'Debe adjuntar el certificado vigente no mayor a 3 meses de la Procuraduría.',
    });
    return;
  }

  if (!form.archivo_cedula) {
    Swal.fire({
      icon: 'warning',
      title: 'Fotocopia de Cédula requerida',
      text: 'Debe adjuntar la fotocopia legible de la cédula de ciudadanía.',
    });
    return;
  }

  Swal.fire({
    title: '¿Confirmar radicación de candidatura?',
    text: `Se registrará la postulación de ${form.nombre} (${form.identificacion}) para ${form.tipo_planta}.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, radicar inscripción',
    cancelButtonText: 'Revisar datos',
    confirmButtonColor: '#4338ca',
  }).then((result) => {
    if (result.isConfirmed) {
      enviando.value = true;
      form.post(route('candidatos.inscripcion.store'), {
        forceFormData: true,
        onFinish: () => {
          enviando.value = false;
        },
        onError: (errores) => {
          const mensaje = Object.values(errores).join('<br>• ');
          Swal.fire({
            icon: 'error',
            title: 'Inconsistencias en el formulario',
            html: `• ${mensaje}`,
          });
        },
      });
    }
  });
};

const imprimirConstancia = () => {
  window.print();
};

const reiniciarFormulario = () => {
  window.location.href = route('candidatos.inscripcion.publica');
};
</script>

