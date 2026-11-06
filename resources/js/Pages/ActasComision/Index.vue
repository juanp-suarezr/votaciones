<template>
  <Head title="Actas Oficiales - Eventos Electorales" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <DocumentTextIcon class="h-7 w-7 text-sky-700" />
            Actas Oficiales de Votación
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Generación y certificación oficial de actas de apertura, cierre y escrutinio para los eventos de la Alcaldía de Pereira.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
          <!-- SELECTOR DE EVENTO INTERACTIVO -->
          <div class="flex items-center gap-2 bg-white border border-gray-300 rounded-xl px-3 py-1.5 shadow-sm text-xs font-semibold">
            <span class="text-gray-500 font-bold whitespace-nowrap">Evento:</span>
            <select
              v-model="selectedEventoId"
              @change="cambiarEvento"
              class="border-none bg-transparent font-bold text-sky-900 focus:ring-0 text-xs py-1 pr-8 cursor-pointer"
            >
              <option v-for="ev in eventos_disponibles" :key="ev.id" :value="ev.id">
                [ID {{ ev.id }}] {{ ev.nombre }}
              </option>
            </select>
          </div>

          <!-- Pestañas Actas vs Firmantes -->
          <div class="flex gap-1.5 bg-gray-100 p-1 rounded-xl">
            <button
              @click="activeTab = 'actas'"
              :class="[
                'px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5',
                activeTab === 'actas'
                  ? 'bg-sky-700 text-white shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <DocumentDuplicateIcon class="h-4 w-4" />
              Actas ({{ actas.length }})
            </button>
            <button
              @click="activeTab = 'firmantes'"
              :class="[
                'px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5',
                activeTab === 'firmantes'
                  ? 'bg-sky-700 text-white shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <PencilSquareIcon class="h-4 w-4" />
              Firmantes ({{ firmantes.length }})
            </button>
          </div>
        </div>
      </div>
    </template>

    <div class="py-6 max-w-7xl mx-auto space-y-6">

      <!-- BANNER DE RESUMEN DEL EVENTO SELECCIONADO -->
      <div class="bg-gradient-to-r from-sky-900 via-sky-800 to-indigo-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
          <img src="/assets/img/logo.png" alt="" class="w-72 h-72 object-contain" />
        </div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-500/30 backdrop-blur-sm rounded-full text-xs font-semibold text-sky-200 mb-2">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              Alcaldía de Pereira | Dependencia: {{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Administración Central' }}
            </div>
            <h2 class="text-xl md:text-2xl font-bold tracking-tight">
              [ID {{ evento?.id }}] {{ evento?.nombre }}
            </h2>
            <p class="text-sky-100 text-xs sm:text-sm mt-1 max-w-2xl">
              {{ evento?.descripcion || 'Evento institucional habilitado para votación virtual en VOTA YA.' }}
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-sky-200">
              <span class="bg-white/10 px-2.5 py-1 rounded-md">
                <strong>Inicio Votación:</strong> {{ fechas.votacion_inicio }}
              </span>
              <span class="bg-white/10 px-2.5 py-1 rounded-md">
                <strong>Cierre Votación:</strong> {{ fechas.votacion_fin }}
              </span>
              <span v-if="es_comision" class="bg-amber-400/20 text-amber-200 border border-amber-300/30 px-2.5 py-1 rounded-md font-semibold">
                Incluye Actas de Inscripción de Candidatos
              </span>
            </div>
          </div>

          <!-- Métricas Rápidas -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 w-full md:w-auto">
            <div v-if="es_comision" class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
              <div class="text-2xl font-black text-white">{{ estadisticas.total_postulaciones }}</div>
              <div class="text-xs text-sky-200 uppercase font-medium">Postulados</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
              <div class="text-2xl font-black text-amber-300">{{ estadisticas.total_censo }}</div>
              <div class="text-xs text-sky-200 uppercase font-medium">Censo</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
              <div class="text-2xl font-black text-emerald-300">{{ estadisticas.total_votos }}</div>
              <div class="text-xs text-sky-200 uppercase font-medium">Votos</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 1: LISTADO DE ACTAS                              -->
      <!-- ======================================================== -->
      <div v-if="activeTab === 'actas'" class="space-y-6">

        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <FolderOpenIcon class="h-5 w-5 text-sky-600" />
            Actas Reglamentarias Disponibles
          </h2>
          <span class="text-xs text-gray-500 font-medium">
            {{ actas.length }} actas generadas para este evento
          </span>
        </div>

        <!-- Tarjetas de Actas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="acta in actas"
            :key="acta.id"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group"
          >
            <!-- Cabecera de la tarjeta -->
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-start justify-between gap-3 mb-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800">
                  {{ acta.codigo }}
                </span>
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold',
                    acta.estado === 'Disponible' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' :
                    acta.estado === 'En Curso' ? 'bg-amber-100 text-amber-800 border border-amber-200' :
                    'bg-gray-100 text-gray-700 border border-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'w-2 h-2 rounded-full',
                      acta.estado === 'Disponible' ? 'bg-emerald-500' :
                      acta.estado === 'En Curso' ? 'bg-amber-500 animate-pulse' : 'bg-gray-400'
                    ]"
                  ></span>
                  {{ acta.estado }}
                </span>
              </div>

              <div class="text-xs font-semibold uppercase tracking-wider text-sky-600 mb-1">
                {{ acta.categoria }}
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-sky-700 transition-colors">
                {{ acta.nombre }}
              </h3>
              <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                {{ acta.descripcion }}
              </p>
            </div>

            <!-- Cuerpo con Metadatos automáticos -->
            <div class="px-6 py-4 bg-gray-50/70 border-b border-gray-100 text-xs space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Fecha oficial detectada:</span>
                <span class="font-bold text-gray-900">{{ acta.fecha_texto }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Fuente del cronograma:</span>
                <span class="font-medium text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200">
                  {{ acta.origen_fecha }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Firmas requeridas:</span>
                <span class="font-semibold text-gray-700">
                  Secretario de las TIC + {{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Dependencia Evento' }}
                </span>
              </div>
            </div>

            <!-- Acciones -->
            <div class="p-4 bg-white flex items-center justify-between gap-3">
              <a
                :href="route('actas-comision.show', { tipo: acta.id, id_evento: evento.id })"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-700 hover:bg-sky-800 text-white font-semibold text-sm rounded-xl shadow-sm transition-colors duration-150"
              >
                <EyeIcon class="h-4 w-4" />
                Ver Acta Oficial
              </a>

              <a
                :href="route('actas-comision.pdf', { tipo: acta.id, id_evento: evento.id })"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-xl shadow-sm transition-colors duration-150"
                v-tooltip.top="'Descargar PDF oficial membretado'"
              >
                <ArrowDownTrayIcon class="h-4 w-4" />
                PDF
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 2: GESTIÓN DE FIRMANTES                           -->
      <!-- ======================================================== -->
      <div v-else-if="activeTab === 'firmantes'" class="space-y-6">

        <!-- Banner Explicativo de la Política de Firmas -->
        <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="space-y-1">
            <div class="flex items-center gap-2 text-sky-900 font-bold text-sm">
              <ShieldCheckIcon class="h-5 w-5 text-sky-600" />
              Política Institucional de Firmas en Actas
            </div>
            <p class="text-xs text-sky-800 max-w-3xl">
              1. En <strong>TODAS</strong> las actas oficiales se estampa obligatoriamente la firma digital del <strong>Secretario de las TIC</strong>.<br>
              2. Como segundo firmante, si el evento tiene un secretario registrado en este panel se estampará su firma; de lo contrario, se genera automáticamente el espacio oficial con el nombre de la dependencia del evento: <strong>{{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Dependencia Responsable' }}</strong>.
            </p>
          </div>

          <button
            @click="abrirModalNuevoFirmante"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap flex items-center gap-1.5"
          >
            <UserPlusIcon class="h-4 w-4" />
            + Asignar Secretario a este Evento
          </button>
        </div>

        <!-- Tabla de Firmantes del Evento -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">
              Firmantes que aparecerán en las actas de: [ID {{ evento?.id }}] {{ evento?.nombre }}
            </h3>
            <span class="text-xs text-gray-500 font-medium">
              {{ firmantes.length }} firmante(s) configurado(s)
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                  <th class="py-3.5 px-4">Firmante / Funcionario</th>
                  <th class="py-3.5 px-4">Identificación</th>
                  <th class="py-3.5 px-4">Cargo Institucional</th>
                  <th class="py-3.5 px-4">Firma Digital</th>
                  <th class="py-3.5 px-4 text-center">Tipo de Firmante</th>
                  <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <tr v-for="f in firmantes" :key="f.id" class="hover:bg-sky-50/40 transition-colors">
                  <td class="py-3.5 px-4 font-semibold text-gray-900">
                    <div class="flex items-center gap-2.5">
                      <div
                        :class="[
                          'w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs',
                          f.is_tic ? 'bg-indigo-100 text-indigo-700' :
                          f.is_dependencia ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700'
                        ]"
                      >
                        {{ f.nombre ? f.nombre.substring(0, 2).toUpperCase() : 'SE' }}
                      </div>
                      <div>
                        <div>{{ f.nombre }}</div>
                        <span class="text-xs text-gray-500">
                          {{ f.is_tic ? 'Firma Obligatoria Institucional' : f.is_dependencia ? 'Espacio por Dependencia del Evento' : 'Secretario Delegado' }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-gray-600 font-mono text-xs">
                    {{ f.identificacion }}
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium text-xs">
                    {{ f.cargo }}
                  </td>
                  <td class="py-3.5 px-4">
                    <div
                      v-if="f.firma"
                      class="border border-gray-200 rounded-lg p-1 bg-white inline-block shadow-sm cursor-pointer hover:border-sky-500"
                      @click="verFirmaGrande(getFirmaUrl(f.firma))"
                    >
                      <img
                        :src="getFirmaUrl(f.firma)"
                        alt="Firma"
                        class="h-12 w-28 object-contain"
                      />
                    </div>
                    <span v-else-if="f.is_dependencia" class="text-xs text-amber-700 font-medium bg-amber-50 px-2 py-1 rounded border border-amber-200">
                      Espacio Oficial de Dependencia
                    </span>
                    <span v-else class="text-xs text-gray-400 italic">
                      Sin firma cargada
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      v-if="f.is_tic"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200"
                    >
                      Secretario TIC
                    </span>
                    <span
                      v-else-if="f.is_dependencia"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"
                    >
                      Dependencia Evento
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                    >
                      Secretario Asignado
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div v-if="!f.is_dependencia && f.id > 0" class="inline-flex items-center gap-1.5">
                      <button
                        @click="editarFirmante(f)"
                        class="p-1.5 text-sky-700 hover:bg-sky-100 rounded-lg transition-colors"
                        v-tooltip.top="'Editar firmante'"
                      >
                        <PencilIcon class="h-4 w-4" />
                      </button>
                      <button
                        @click="eliminarFirmante(f.id)"
                        class="p-1.5 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors"
                        v-tooltip.top="'Eliminar firmante'"
                      >
                        <TrashIcon class="h-4 w-4" />
                      </button>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">
                      Automático
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL: CREAR / EDITAR SECRETARIO FIRMANTE                 -->
    <!-- ======================================================== -->
    <div
      v-if="modalFirmanteVisible"
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-gray-100 overflow-hidden animate-fadeIn">
        <!-- Header Modal -->
        <div class="bg-gradient-to-r from-sky-800 to-sky-900 px-6 py-4 text-white flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <PencilSquareIcon class="h-6 w-6 text-sky-300" />
            <h3 class="text-lg font-bold">
              {{ modoEdicion ? 'Editar Secretario Firmante' : 'Nuevo Secretario Firmante' }}
            </h3>
          </div>
          <button
            @click="cerrarModalFirmante"
            class="text-white/80 hover:text-white text-2xl font-bold leading-none"
          >
            &times;
          </button>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="guardarFirmanteSubmit" class="p-6 space-y-4">
          <!-- Nombre -->
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre Completo (*)</label>
            <input
              type="text"
              v-model="formFirmante.nombre"
              required
              placeholder="Ej: Dr. Carlos Alberto Gómez"
              class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
            />
          </div>

          <!-- Identificación y Cargo -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Identificación (C.C.) (*)</label>
              <input
                type="text"
                v-model="formFirmante.identificacion"
                required
                placeholder="Ej: 1088234567"
                class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo Institucional (*)</label>
              <input
                type="text"
                v-model="formFirmante.cargo"
                required
                placeholder="Ej: Secretario de Gestión Administrativa"
                class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
              />
            </div>
          </div>

          <!-- Evento Asignado -->
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Evento Asignado (*)</label>
            <select
              v-model="formFirmante.id_evento"
              class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
            >
              <option v-for="ev in eventos_disponibles" :key="ev.id" :value="ev.id">
                [ID {{ ev.id }}] {{ ev.nombre }}
              </option>
            </select>
          </div>

          <!-- SELECCIÓN Y CREACIÓN DE FIRMA -->
          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-gray-700 uppercase">
                Firma Digital {{ modoEdicion ? '(Opcional cambiar)' : '(*)' }}
              </label>
              <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                <button
                  type="button"
                  @click="modoFirma = 'canvas'"
                  :class="[
                    'px-2.5 py-1 text-xs font-bold rounded-md transition-colors',
                    modoFirma === 'canvas' ? 'bg-sky-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  Dibujar en Pantalla
                </button>
                <button
                  type="button"
                  @click="modoFirma = 'imagen'"
                  :class="[
                    'px-2.5 py-1 text-xs font-bold rounded-md transition-colors',
                    modoFirma === 'imagen' ? 'bg-sky-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  Subir Imagen
                </button>
              </div>
            </div>

            <!-- Canvas para Dibujar Firma -->
            <div v-show="modoFirma === 'canvas'" class="space-y-2">
              <div class="border-2 border-dashed border-gray-300 rounded-xl bg-white p-1 relative flex justify-center">
                <canvas
                  ref="canvasRef"
                  width="420"
                  height="140"
                  class="cursor-crosshair w-full max-w-[420px] h-[140px] touch-none bg-white rounded-lg"
                  @mousedown="startDrawing"
                  @mousemove="draw"
                  @mouseup="stopDrawing"
                  @mouseleave="stopDrawing"
                  @touchstart="startDrawingTouch"
                  @touchmove="drawTouch"
                  @touchend="stopDrawing"
                ></canvas>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-500 italic">Dibuje su trazo en el recuadro</span>
                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="limpiarCanvas"
                    class="px-2.5 py-1 text-gray-600 hover:text-rose-600 font-semibold"
                  >
                    Borrar trazo
                  </button>
                  <button
                    type="button"
                    @click="aplicarFirmaCanvas"
                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg"
                  >
                    Confirmar Firma
                  </button>
                </div>
              </div>
            </div>

            <!-- Subir Archivo de Imagen -->
            <div v-show="modoFirma === 'imagen'" class="space-y-2">
              <input
                type="file"
                accept="image/*"
                @change="onFileFirmaChange"
                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100"
              />
            </div>

            <!-- Vista Previa de la Firma -->
            <div v-if="firmaPreview" class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center gap-3">
              <div class="text-xs font-semibold text-gray-500">Vista previa:</div>
              <img :src="firmaPreview" alt="Vista previa de firma" class="h-14 max-w-[200px] object-contain border bg-white rounded p-1" />
              <button
                type="button"
                @click="firmaPreview = null; formFirmante.firma = null"
                class="text-xs text-rose-600 font-semibold ml-auto hover:underline"
              >
                Quitar
              </button>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="cerrarModalFirmante"
              class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="procesando"
              class="px-5 py-2.5 bg-sky-700 hover:bg-sky-800 text-white font-bold text-sm rounded-xl shadow-md transition-all disabled:opacity-50 flex items-center gap-2"
            >
              <CheckIcon class="h-4 w-4" />
              {{ modoEdicion ? 'Actualizar Firmante' : 'Guardar Firmante' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  DocumentTextIcon,
  DocumentDuplicateIcon,
  PencilSquareIcon,
  FolderOpenIcon,
  EyeIcon,
  ArrowDownTrayIcon,
  UserPlusIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  ShieldCheckIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  evento: Object,
  es_comision: Boolean,
  fechas: Object,
  actas: Array,
  firmantes: Array,
  secretario_tic: Object,
  estadisticas: Object,
  eventos_disponibles: Array
});

const breadcrumbLinks = [
  { url: '/dashboard', text: 'Inicio' },
  { url: '', text: 'Actas Oficiales de Votación' },
];

const activeTab = ref('actas');
const selectedEventoId = ref(props.evento?.id || 25);

const cambiarEvento = () => {
  router.get(
    route('actas-comision.index'),
    { id_evento: selectedEventoId.value },
    { preserveState: false }
  );
};

// Estado Modal Firmantes
const modalFirmanteVisible = ref(false);
const modoEdicion = ref(false);
const firmanteIdEditar = ref(null);
const modoFirma = ref('canvas');
const canvasRef = ref(null);
const firmaPreview = ref(null);
const procesando = ref(false);

const formFirmante = ref({
  nombre: '',
  identificacion: '',
  cargo: '',
  id_evento: props.evento?.id || 25,
  firma: null
});

// Canvas Drawing Logic
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
  formFirmante.value.firma = null;
};

const aplicarFirmaCanvas = () => {
  if (canvasRef.value) {
    const dataUrl = canvasRef.value.toDataURL('image/png');
    firmaPreview.value = dataUrl;
    formFirmante.value.firma = dataUrl;
  }
};

const onFileFirmaChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    formFirmante.value.firma = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      firmaPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const abrirModalNuevoFirmante = () => {
  modoEdicion.value = false;
  firmanteIdEditar.value = null;
  formFirmante.value = {
    nombre: '',
    identificacion: '',
    cargo: '',
    id_evento: props.evento?.id || 25,
    firma: null
  };
  firmaPreview.value = null;
  modalFirmanteVisible.value = true;
  setTimeout(initCanvas, 100);
};

const editarFirmante = (f) => {
  modoEdicion.value = true;
  firmanteIdEditar.value = f.id;
  formFirmante.value = {
    nombre: f.nombre,
    identificacion: f.identificacion,
    cargo: f.cargo,
    id_evento: f.id_evento || props.evento?.id || 25,
    firma: null
  };
  firmaPreview.value = f.firma ? getFirmaUrl(f.firma) : null;
  modalFirmanteVisible.value = true;
  setTimeout(initCanvas, 100);
};

const cerrarModalFirmante = () => {
  modalFirmanteVisible.value = false;
  limpiarCanvas();
};

const guardarFirmanteSubmit = () => {
  if (modoFirma.value === 'canvas' && !formFirmante.value.firma && canvasRef.value) {
    aplicarFirmaCanvas();
  }

  if (!modoEdicion.value && !formFirmante.value.firma) {
    alert('Por favor dibuje o cargue la firma digital del secretario.');
    return;
  }

  procesando.value = true;

  if (modoEdicion.value) {
    router.post(route('actas-comision.firmantes.update', firmanteIdEditar.value), formFirmante.value, {
      forceFormData: true,
      onSuccess: () => {
        procesando.value = false;
        cerrarModalFirmante();
      },
      onError: () => {
        procesando.value = false;
      }
    });
  } else {
    router.post(route('actas-comision.firmantes.store'), formFirmante.value, {
      forceFormData: true,
      onSuccess: () => {
        procesando.value = false;
        cerrarModalFirmante();
      },
      onError: () => {
        procesando.value = false;
      }
    });
  }
};

const eliminarFirmante = (id) => {
  if (confirm('¿Está seguro de eliminar este secretario firmante? Esta acción no se puede deshacer.')) {
    router.delete(route('actas-comision.firmantes.destroy', id));
  }
};

const getFirmaUrl = (filename) => {
  if (!filename) return '';
  return `/storage/uploads/delegado/${filename}`;
};

const verFirmaGrande = (url) => {
  window.open(url, '_blank');
};
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
