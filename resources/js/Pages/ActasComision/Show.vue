<template>
  <Head :title="`${config.codigo} - ${config.titulo}`" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
          <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <DocumentTextIcon class="h-6 w-6 text-[#C20E1A]" />
            {{ config.titulo }}
          </h1>
          <p class="text-xs text-gray-500 mt-0.5">
            Documento Oficial Institucional - Alcaldía de Pereira
          </p>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-2.5 print:hidden">
          <a
            :href="route('actas-comision.index', { id_evento: evento.id })"
            class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm inline-flex items-center gap-1.5"
          >
            <ArrowLeftIcon class="h-4 w-4" />
            Volver a Actas
          </a>

          <button
            @click="imprimirActa"
            class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm inline-flex items-center gap-1.5"
          >
            <PrinterIcon class="h-4 w-4" />
            Imprimir
          </button>

          <a
            :href="route('actas-comision.pdf', { tipo: tipo, id_evento: evento.id })"
            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-md inline-flex items-center gap-1.5"
          >
            <ArrowDownTrayIcon class="h-4 w-4" />
            Exportar PDF Oficial
          </a>
        </div>
      </div>
    </template>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6">

      <!-- HOJA INSTITUCIONAL OFICIAL (ESTILO DOCUMENTAL) -->
      <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 sm:p-14 relative overflow-hidden print:p-0 print:shadow-none print:border-none">

        <!-- Marca de agua institucional sutil -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none z-0">
          <img src="/assets/img/logo.png" alt="" class="w-[500px] h-[500px] object-contain" />
        </div>

        <div class="relative z-10 space-y-6">

          <!-- ENCABEZADO INSTITUCIONAL -->
          <div class="border-b-2 border-[#C20E1A] pb-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <img src="/assets/img/logo.png" alt="Alcaldía de Pereira" class="h-16 w-auto object-contain" />
              <div>
                <div class="text-lg font-black tracking-tight text-gray-900 uppercase">
                  Alcaldía de Pereira
                </div>
                <div class="text-xs text-gray-500 font-semibold uppercase">
                  {{ dependencia_evento || 'Secretaría / Dependencia Responsable' }}
                </div>
                <div class="text-[11px] text-[#C20E1A] font-bold">
                  Plataforma Digital VOTA YA
                </div>
              </div>
            </div>

            <div class="text-right">
              <span class="inline-block px-3 py-1 bg-[#C20E1A] text-white text-xs font-mono font-bold rounded-md shadow-sm">
                {{ config.codigo }}
              </span>
              <div class="text-[11px] text-gray-500 font-medium mt-1">
                Generado: {{ fecha_generacion }}
              </div>
            </div>
          </div>

          <!-- TÍTULO DEL DOCUMENTO -->
          <div class="bg-slate-50 border border-slate-200 border-l-4 border-l-[#C20E1A] p-5 rounded-xl text-center">
            <h2 class="text-base sm:text-lg font-black text-gray-900 uppercase tracking-wide">
              {{ config.titulo }}
            </h2>
            <div class="text-xs font-bold text-[#C20E1A] uppercase mt-1 tracking-wider">
              {{ config.subtitulo }}
            </div>
          </div>

          <!-- TABLA DE METADATOS OFICIALES -->
          <div class="overflow-hidden border border-gray-200 rounded-xl text-xs">
            <table class="w-full border-collapse">
              <tbody class="divide-y divide-gray-200">
                <tr class="bg-gray-50/60">
                  <td class="py-2.5 px-4 font-bold text-gray-700 w-1/3 bg-gray-100/70 border-r border-gray-200">
                    Entidad Oficial:
                  </td>
                  <td class="py-2.5 px-4 font-semibold text-gray-900">
                    ALCALDÍA DE PEREIRA - Risaralda, Colombia
                  </td>
                </tr>
                <tr>
                  <td class="py-2.5 px-4 font-bold text-gray-700 bg-gray-100/70 border-r border-gray-200">
                    Evento Electoral:
                  </td>
                  <td class="py-2.5 px-4 font-semibold text-gray-900">
                    [ID: {{ evento?.id }}] {{ evento?.nombre }}
                  </td>
                </tr>
                <tr class="bg-gray-50/60">
                  <td class="py-2.5 px-4 font-bold text-gray-700 bg-gray-100/70 border-r border-gray-200">
                    Dependencia Responsable:
                  </td>
                  <td class="py-2.5 px-4 font-semibold text-gray-800">
                    {{ dependencia_evento }}
                  </td>
                </tr>
                <tr>
                  <td class="py-2.5 px-4 font-bold text-gray-700 bg-gray-100/70 border-r border-gray-200">
                    Fecha y Hora Oficial:
                  </td>
                  <td class="py-2.5 px-4 font-bold text-sky-900">
                    {{ formatearFecha(config.fecha_acta) }}
                  </td>
                </tr>
                <tr class="bg-gray-50/60">
                  <td class="py-2.5 px-4 font-bold text-gray-700 bg-gray-100/70 border-r border-gray-200">
                    Fuente de Detección:
                  </td>
                  <td class="py-2.5 px-4 text-gray-700">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-semibold text-[11px]">
                      {{ config.origen_fecha }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="py-2.5 px-4 font-bold text-gray-700 bg-gray-100/70 border-r border-gray-200">
                    Plataforma Tecnológica:
                  </td>
                  <td class="py-2.5 px-4 text-gray-700">
                    Sistema de Votación Virtual <strong>VOTA YA</strong> (Auditoría Criptográfica)
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- SECCIÓN 1: OBJETO Y FUNDAMENTO LEGAL -->
          <div class="space-y-2 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5">
              <span class="text-[#C20E1A]">1.</span> OBJETO Y CONSIDERACIONES LEGALES
            </h3>
            <p class="text-xs sm:text-sm text-gray-700 leading-relaxed text-justify">
              {{ config.objeto }}
            </p>
            <p class="text-xs text-gray-600 leading-relaxed text-justify">
              El presente proceso electoral se fundamenta en las disposiciones institucionales y legales vigentes, garantizando el principio de legalidad, la participación democrática, la transparencia del sufragio y la custodia de la información electoral.
            </p>
          </div>

          <!-- ======================================================== -->
          <!-- CONTENIDOS ESPECÍFICOS SEGÚN TIPO DE ACTA                -->
          <!-- ======================================================== -->

          <!-- CASO 1: APERTURA DE INSCRIPCIÓN (COMISIÓN DE PERSONAL) -->
          <div v-if="tipo === 'inscripcion_apertura'" class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5">
              <span class="text-[#C20E1A]">2.</span> CONDICIONES Y PARÁMETROS DE APERTURA
            </h3>

            <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">
              En la fecha y hora reglamentarias, se habilitó formalmente el acceso público al formulario de postulación digital, quedando disponible para todos los funcionarios habilitados en el censo laboral oficial.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-gray-500 font-medium">Fecha y Hora de Apertura:</div>
                <div class="font-bold text-gray-900 mt-1">{{ formatearFecha(fechas.inscripcion_inicio) }}</div>
              </div>
              <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-gray-500 font-medium">Fecha y Hora Límite Prevista:</div>
                <div class="font-bold text-gray-900 mt-1">{{ formatearFecha(fechas.inscripcion_fin) }}</div>
              </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-800 space-y-1.5">
              <div class="font-bold uppercase tracking-wide text-[#C20E1A]">Requisitos Oficiales de Postulación:</div>
              <ul class="list-disc list-inside space-y-0.5 text-slate-700">
                <li>Ser empleado con derechos de carrera administrativa inscrito en el censo.</li>
                <li>Presentar documento de identidad y certificado de antecedentes disciplinarios de la Procuraduría.</li>
                <li>Foto digital formal para el tarjetón de votación.</li>
              </ul>
            </div>
          </div>

          <!-- CASO 2: CIERRE DE INSCRIPCIÓN -->
          <div v-else-if="tipo === 'inscripcion_cierre'" class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5">
              <span class="text-[#C20E1A]">2.</span> CONSOLIDADO OFICIAL DE POSTULACIONES
            </h3>

            <!-- KPIs de Postulaciones -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
              <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-2xl font-black text-gray-900">{{ totales.total_postulaciones }}</div>
                <div class="text-[11px] text-gray-500 uppercase font-semibold">Total Radicadas</div>
              </div>
              <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                <div class="text-2xl font-black text-emerald-700">{{ totales.total_aprobados }}</div>
                <div class="text-[11px] text-emerald-800 uppercase font-semibold">Aprobadas (Tarjetón)</div>
              </div>
              <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
                <div class="text-2xl font-black text-rose-700">{{ totales.total_rechazados }}</div>
                <div class="text-[11px] text-rose-800 uppercase font-semibold">Rechazadas</div>
              </div>
              <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                <div class="text-2xl font-black text-amber-700">{{ totales.total_pendientes }}</div>
                <div class="text-[11px] text-amber-800 uppercase font-semibold">En Validación</div>
              </div>
            </div>

            <div class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-200">
              * Distribución por Planta: <strong>Planta Central: {{ totales.aprobados_planta_central }}</strong> | <strong>SGP: {{ totales.aprobados_sgp }}</strong>.
            </div>

            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5 pt-2">
              <span class="text-[#C20E1A]">3.</span> LISTADO DETALLADO DE ASPIRANTES Y CANDIDATOS HABILITADOS
            </h3>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
              <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-800 text-white font-bold uppercase text-[10px]">
                  <tr>
                    <th class="py-2.5 px-3">Radicado</th>
                    <th class="py-2.5 px-3">Funcionario</th>
                    <th class="py-2.5 px-3">Identificación</th>
                    <th class="py-2.5 px-3">Planta</th>
                    <th class="py-2.5 px-3">Dependencia</th>
                    <th class="py-2.5 px-3 text-center">Estado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="p in postulaciones" :key="p.id" class="hover:bg-gray-50">
                    <td class="py-2 px-3 font-mono font-bold text-slate-800">
                      {{ p.radicado || ('RAD-CP-' + String(p.id).padStart(4, '0')) }}
                    </td>
                    <td class="py-2 px-3 font-semibold text-gray-900">{{ p.nombre }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ p.identificacion }}</td>
                    <td class="py-2 px-3">{{ p.tipo_planta || 'N/A' }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ p.dependencia || 'N/A' }}</td>
                    <td class="py-2 px-3 text-center">
                      <span
                        :class="[
                          'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                          esEstadoAprobado(p.estado) ? 'bg-emerald-100 text-emerald-800' :
                          esEstadoRechazado(p.estado) ? 'bg-rose-100 text-rose-800' :
                          'bg-amber-100 text-amber-800'
                        ]"
                      >
                        {{ esEstadoAprobado(p.estado) ? 'Aprobado' : (esEstadoRechazado(p.estado) ? 'Rechazado' : 'En Validación') }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- CASO 3: APERTURA DE VOTACIONES (PARA TODOS LOS EVENTOS) -->
          <div v-else-if="tipo === 'votacion_apertura'" class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5">
              <span class="text-[#C20E1A]">2.</span> PUESTA A CERO DE LA URNA ELECTRÓNICA VIRTUAL
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center">
              <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                <div class="text-3xl font-black text-emerald-700">0 VOTOS</div>
                <div class="text-xs font-bold text-emerald-900 uppercase mt-1">Urna Electrónica en Cero</div>
                <p class="text-[11px] text-emerald-700 mt-1">Base de datos de sufragios inicializada y verificada con cero registros</p>
              </div>
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="text-3xl font-black text-[#FFB800]">{{ totales.censo_votantes }}</div>
                <div class="text-xs font-bold text-slate-800 uppercase mt-1">Votantes en Censo Habilitado</div>
                <p class="text-[11px] text-slate-600 mt-1">Censo oficial cargado y validado en el sistema</p>
              </div>
            </div>

            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5 pt-2">
              <span class="text-[#C20E1A]">3.</span> OPCIONES Y CANDIDATOS HABILITADOS EN EL TARJETÓN VIRTUAL
            </h3>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
              <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-800 text-white font-bold uppercase text-[10px]">
                  <tr>
                    <th class="py-2 px-3 text-center">No.</th>
                    <th class="py-2 px-3">Opción / Candidato / Proyecto</th>
                    <th class="py-2 px-3">Identificador</th>
                    <th class="py-2 px-3">Categoría</th>
                    <th class="py-2 px-3">Dependencia / Detalle</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="(cand, idx) in resultados_candidatos" :key="cand.id" class="hover:bg-gray-50">
                    <td class="py-2 px-3 font-bold text-center text-[#C20E1A]">{{ idx + 1 }}</td>
                    <td class="py-2 px-3 font-bold text-gray-900">{{ cand.nombre }}</td>
                    <td class="py-2 px-3 text-gray-600 font-mono text-[11px]">{{ cand.identificacion }}</td>
                    <td class="py-2 px-3">{{ cand.subtitulo || 'N/A' }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ cand.dependencia || 'N/A' }}</td>
                  </tr>
                  <tr v-if="resultados_candidatos.length === 0">
                    <td colspan="5" class="py-4 text-center text-gray-400 italic">
                      No hay opciones o candidatos registrados para este evento.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- CASO 4: CIERRE DE VOTACIONES Y ESCRUTINIO (PARA TODOS LOS EVENTOS) -->
          <div v-else-if="tipo === 'votacion_cierre'" class="space-y-4 pt-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5">
              <span class="text-[#C20E1A]">2.</span> PARTICIPACIÓN ELECTORAL
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
              <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-2xl font-black text-gray-800">{{ totales.censo_votantes }}</div>
                <div class="text-[11px] text-gray-500 uppercase font-semibold">Censo Habilitado</div>
              </div>
              <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-200">
                <div class="text-2xl font-black text-[#C20E1A]">{{ totales.total_votos }}</div>
                <div class="text-[11px] text-[#C20E1A] uppercase font-semibold">Votos Depositados</div>
              </div>
              <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                <div class="text-2xl font-black text-emerald-700">{{ totales.porcentaje_participacion }}%</div>
                <div class="text-[11px] text-emerald-800 uppercase font-semibold">% Participación</div>
              </div>
            </div>

            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1.5 pt-2">
              <span class="text-[#C20E1A]">3.</span> ESCRUTINIO FINAL Y RESULTADOS OFICIALES
            </h3>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
              <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-800 text-white font-bold uppercase text-[10px]">
                  <tr>
                    <th class="py-2.5 px-3 text-center">Posición</th>
                    <th class="py-2.5 px-3">Opción / Candidato / Proyecto</th>
                    <th class="py-2.5 px-3">Categoría</th>
                    <th class="py-2.5 px-3">Dependencia / Detalle</th>
                    <th class="py-2.5 px-3 text-right">Votos</th>
                    <th class="py-2.5 px-3 text-right">% Votación</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="(r, idx) in resultados_candidatos" :key="r.id" class="hover:bg-gray-50">
                    <td class="py-2 px-3 font-bold text-center text-[#C20E1A]">#{{ idx + 1 }}</td>
                    <td class="py-2 px-3 font-bold text-gray-900">{{ r.nombre }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ r.subtitulo || 'N/A' }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ r.dependencia || 'N/A' }}</td>
                    <td class="py-2 px-3 text-right font-black text-slate-900">{{ r.votos }}</td>
                    <td class="py-2 px-3 text-right font-bold text-emerald-700">{{ r.porcentaje }}%</td>
                  </tr>
                  <tr v-if="resultados_candidatos.length === 0">
                    <td colspan="6" class="py-4 text-center text-gray-400 italic">
                      No se registraron candidatos o votos para este evento.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- CONSTANCIA FORMAL DE CIERRE -->
          <div class="pt-4 border-t border-gray-200 text-xs text-gray-700 text-justify space-y-2">
            <p>
              En constancia de lo actuado y para los fines legales y reglamentarios pertinentes, se suscribe la presente acta oficial en la ciudad de Pereira, el día <strong>{{ formatearFechaTexto(config.fecha_acta) }}</strong>, por el Secretario de las TIC y los secretarios o delegados oficiales del evento.
            </p>
          </div>

          <!-- BLOQUE DE FIRMAS -->
          <div class="pt-8 page-break-inside-avoid">
            <div class="text-center font-bold text-xs uppercase tracking-wider text-gray-500 mb-6">
              Firmas Oficiales del Proceso Electoral
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 max-w-3xl mx-auto">
              <div
                v-for="firmante in firmantes"
                :key="firmante.id"
                class="flex flex-col items-center text-center p-4 bg-gray-50/50 rounded-xl border border-gray-100"
              >
                <!-- Imagen de la Firma Digital -->
                <div class="h-20 flex items-center justify-center mb-2">
                  <img
                    v-if="firmante.firma"
                    :src="getFirmaUrl(firmante.firma)"
                    alt="Firma"
                    class="max-h-16 max-w-[200px] object-contain"
                  />
                  <div v-else-if="firmante.is_dependencia" class="text-xs text-amber-700 font-semibold px-2 py-1 bg-amber-50 rounded border border-amber-200">
                    [Espacio para Firma y Sello Oficial]
                  </div>
                  <div v-else class="text-xs text-gray-300 italic">
                    [Firma no registrada]
                  </div>
                </div>

                <div class="w-3/4 border-t-2 border-gray-800 mb-2"></div>
                <div class="font-bold text-sm text-gray-900">{{ firmante.nombre }}</div>
                <div class="text-xs text-[#C20E1A] font-semibold uppercase">{{ firmante.cargo }}</div>
                <div class="text-xs text-gray-500">{{ firmante.identificacion }}</div>
              </div>
            </div>
          </div>

          <!-- PIE DE PÁGINA -->
          <div class="pt-8 border-t border-gray-200 text-center text-[11px] text-gray-400">
            Alcaldía de Pereira - Carrera 7 No. 18-55, Palacio Municipal | Plataforma VOTA YA
          </div>

        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  DocumentTextIcon,
  ArrowLeftIcon,
  PrinterIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  tipo: String,
  config: Object,
  evento: Object,
  dependencia_evento: String,
  es_comision: Boolean,
  fechas: Object,
  firmantes: Array,
  postulaciones: Array,
  candidatos_aprobados: Array,
  totales: Object,
  resultados_candidatos: Array,
  fecha_generacion: String
});

const breadcrumbLinks = [
  { url: '/dashboard', text: 'Inicio' },
  { url: route('actas-comision.index', { id_evento: props.evento?.id }), text: 'Actas Oficiales' },
  { url: '', text: props.config.codigo }
];

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return '';
  const clean = String(fechaStr).trim().replace('T', ' ');
  if (clean.includes(' de ')) return clean;

  const parts = clean.split(' ');
  const datePart = parts[0];
  const timePart = parts[1] || null;

  const datePieces = datePart.split('-');
  if (datePieces.length === 3) {
    const meses = [
      'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const dia = String(parseInt(datePieces[2], 10)).padStart(2, '0');
    const mesNum = parseInt(datePieces[1], 10) - 1;
    const mes = meses[mesNum] || datePieces[1];
    const anio = datePieces[0];

    if (timePart) {
      const timePieces = timePart.split(':');
      const h = parseInt(timePieces[0], 10) || 0;
      const m = String(timePieces[1] || '00').padStart(2, '0');
      const ampm = h >= 12 ? 'PM' : 'AM';
      const h12 = h % 12 || 12;
      return `${dia} de ${mes} de ${anio} - ${String(h12).padStart(2, '0')}:${m} ${ampm}`;
    }

    return `${dia} de ${mes} de ${anio}`;
  }

  return fechaStr;
};

const formatearFechaTexto = (fechaStr) => {
  if (!fechaStr) return '';
  const clean = String(fechaStr).trim().replace('T', ' ').split(' ')[0];
  if (clean.includes(' de ')) return clean;

  const datePieces = clean.split('-');
  if (datePieces.length === 3) {
    const meses = [
      'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    const dia = parseInt(datePieces[2], 10);
    const mesNum = parseInt(datePieces[1], 10) - 1;
    const mes = meses[mesNum] || datePieces[1];
    const anio = datePieces[0];
    return `${dia} de ${mes} de ${anio}`;
  }

  return fechaStr;
};

const esEstadoAprobado = (estado) => {
  const est = String(estado || '').trim().toLowerCase();
  return est === 'aprobado' || est === 'aprobada' || est === 'habilitado' || est === '1';
};

const esEstadoRechazado = (estado) => {
  const est = String(estado || '').trim().toLowerCase();
  return est === 'rechazado' || est === 'rechazada' || est === '2';
};

const getFirmaUrl = (filename) => {
  if (!filename) return '';
  return `/storage/uploads/delegado/${filename}`;
};

const imprimirActa = () => {
  window.print();
};
</script>

<style>
@media print {
  body {
    background-color: white !important;
    font-size: 11pt !important;
  }
  .print\:hidden {
    display: none !important;
  }
  .page-break-inside-avoid {
    page-break-inside: avoid;
  }
}
</style>
