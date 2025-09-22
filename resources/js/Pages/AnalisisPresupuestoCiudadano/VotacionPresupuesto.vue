<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

import { EyeIcon } from "@heroicons/vue/24/solid";
import Select from "primevue/select";

import SecondaryLink from "@/Components/SecondaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SecondaryButtonReturn from "@/Components/SecondaryButtonReturn.vue";


const swal = inject("$swal");

const props = defineProps({

  comuna: {
    type: String,
    default: () => "",
  },
  resultados_eventos: {
    type: Object,
    default: () => "",
  },

});

console.log(props);


</script>

<template>
  <Head title="Votaciones por vigencia - proyectos" />

  <div class="relative min-h-screen bg-center bg-option2 selection:bg-red-500 selection:text-white flex flex-col justify-center">
    <div class="w-full flex flex-wrap justify-between sm:px-16 px-4">
      <a class="sm:flex justify-start mt-2" href="/welcome">
        <img src="/assets/img/logo.png" alt="Logo" class="h-24 w-auto" />
      </a>
      <SecondaryButtonReturn class="h-full flex inline-flex mt-8 !text-base">
        Regresar
      </SecondaryButtonReturn>
    </div>
    <!-- contenido -->
    <div class="w-full flex justify-center my-auto sm:px-16 px-4">
      <div class="w-full border border-gray-200 bg-white shadow-md rounded-lg p-4">
        <h2 class="font-bold mt-2 text-2xl">Resultados de Votación</h2>
        <p class="mt-2 text-base">Sector -- {{ comuna }}</p>

        <div class="mt-8 w-full sm:grid sm:grid-cols-2 gap-12 w-full px-4">
          <div
            v-for="evento in props.resultados_eventos"
            :key="evento.evento_id"
            class="w-auto mb-4"
          >
            <h3 class="text-xl font-bold mb-2 text-primary">
              {{ evento.evento_nombre }}
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full divide-y divide-gray-200 rounded-lg overflow-hidden shadow-md">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="p-2 text-left text-base sm:text-xl font-bold text-gray-700">Proyecto</th>
                    <th class="p-2 text-center text-base sm:text-xl font-bold text-gray-700">Total votos</th>
                    <th class="p-2 text-center text-base sm:text-xl font-bold text-gray-700">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="proyecto in evento.resultados"
                    :key="proyecto.id_proyecto"
                    class="bg-white hover:bg-gray-100 shadow"
                  >
                    <td class="p-2 text-base sm:text-xl font-medium text-gray-900">{{ proyecto.nombre }}</td>
                    <td class="p-2 text-center text-base sm:text-xl font-bold text-primary">{{ proyecto.total }}</td>
                    <td class="p-2 text-center text-base sm:text-xl font-bold text-primary">{{ proyecto.total == 0 ? 0 :((proyecto.total/evento.total_votos)*100).toFixed(2) }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">

              <div class="bg-gray-100 rounded-md hover:scale-105 hover:bg-gray-200 shadow-md p-3 text-center">
                <span class="block text-base sm:text-xl text-gray-500">Votos nulos</span>
                <span class="font-bold text-lg text-red-600">{{ evento.votos_nulos }} votos - {{ evento.votos_nulos == 0 ? 0 :((evento.votos_nulos/evento.total_votos)*100).toFixed(2) }}%</span>
              </div>
              <div class="bg-gray-100 rounded-md hover:scale-105 hover:bg-gray-200 shadow-md p-3 text-center">
                <span class="block text-base sm:text-xl text-gray-500">Votos no marcados</span>
                <span class="font-bold text-lg text-yellow-600">{{ evento.votos_no_marcados }} votos - {{ evento.votos_no_marcados == 0 ? 0 :((evento.votos_no_marcados/evento.total_votos)*100).toFixed(2) }}%</span>
              </div>
              <div class="bg-gray-100 rounded-md hover:scale-105 hover:bg-gray-200 shadow-md p-3 text-center">
                <span class="block text-base sm:text-xl text-gray-500 font-bold">Total votos</span>
                <span class="font-bold text-lg text-green-600 font-bold">{{ evento.total_votos }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
</style>
