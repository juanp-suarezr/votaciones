<template>
  <Head :title="titulo || 'Sección Temporalmente Deshabilitada'" />

  <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200 text-center relative">
      <!-- Franja Institucional Bicolor Pereira -->
      <div class="h-1.5 w-full bg-gradient-to-r from-[#C20E1A] from-50% to-[#FFB800] to-50%"></div>

      <!-- Encabezado con Identidad Institucional -->
      <div class="p-6 border-b border-slate-100 flex items-center justify-center gap-3">
        <img
          src="/assets/img/logo.webp"
          @error="$event.target.src = '/assets/img/logo.png'"
          alt="Alcaldía de Pereira"
          class="h-10 max-w-[160px] object-contain"
        />
        <div class="w-px h-7 bg-slate-200"></div>
        <div class="text-left leading-tight">
          <strong class="block text-xs font-black uppercase text-[#C20E1A] tracking-wider">Alcaldía de Pereira</strong>
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">Plataforma Electoral VOTA YA</span>
        </div>
      </div>

      <!-- Contenido del Bloqueo -->
      <div class="p-8">
        <!-- Contenedor del Icono con tamaño estrictamente controlado -->
        <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-rose-50 border-2 border-rose-200 flex items-center justify-center text-[#C20E1A] shadow-inner">
          <LockClosedIcon class="w-10 h-10 shrink-0" />
        </div>

        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200 mb-3">
          Acceso Restringido
        </span>

        <h1 class="text-2xl font-extrabold text-slate-900 mb-3 leading-tight">
          {{ titulo || 'Ruta Temporalmente Deshabilitada' }}
        </h1>

        <p class="text-sm text-slate-600 leading-relaxed mb-6">
          {{ mensaje || 'Esta sección se encuentra temporalmente inactiva de conformidad con el cronograma del proceso electoral. Por favor intente más tarde o consulte con el administrador del sistema.' }}
        </p>

        <!-- Información de fechas si existen -->
        <div v-if="ruta && (ruta.fecha_inicio || ruta.fecha_fin)" class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-4 text-xs text-left mb-6 space-y-1.5">
          <div v-if="ruta.fecha_inicio" class="flex justify-between items-center">
            <span class="font-semibold text-slate-500">Fecha de apertura:</span>
            <span class="font-bold text-slate-900">{{ ruta.fecha_inicio }}</span>
          </div>
          <div v-if="ruta.fecha_fin" class="flex justify-between items-center">
            <span class="font-semibold text-slate-500">Fecha de cierre:</span>
            <span class="font-bold text-slate-900">{{ ruta.fecha_fin }}</span>
          </div>
        </div>

        <a
          href="/"
          class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#C20E1A] hover:bg-[#991B1B] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all duration-200"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Volver al Inicio
        </a>

        <p class="text-[11px] text-slate-400 mt-5">
          Secretaría de las TIC · Alcaldía de Pereira
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { LockClosedIcon, ArrowLeftIcon } from '@heroicons/vue/24/solid';

defineProps({
  titulo: String,
  mensaje: String,
  ruta: Object
});
</script>

