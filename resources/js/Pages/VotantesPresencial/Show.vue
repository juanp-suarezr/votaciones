<template>
  <Head title="Validación del votante" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Detalle del Acta de escrutinio</h1>
      </div>
    </template>

    <div class="p-4">
      <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="w-full mb-4 border-b-2 border-secondary pb-4">
          <ApplicationLogo class="h-24 !w-full object-contain" />
        </div>
        <div class="flex justify-between flex-wrap gap-2">
          <h2 class="text-lg font-bold mb-4 text-blue-900">
            Datos del reporte de escrutinio
          </h2>
          <p class="font-bold mb-4">
            {{ acta.evento.nombre }}
          </p>
        </div>
        <div class="md:grid md:grid-cols-2 gap-4">
          <!-- imagen de acta -->
          <div
            class="rounded-md w-full h-full flex justify-center shadow-md p-4"
          >
            <img
              :src="getUrlActa(acta.imagen)"
              class="w-full h-full cursor-pointer object-contain"
              @click="openLightbox(getUrlActa(acta.imagen))"
            />
          </div>
          <!-- info acta escrutinio -->
          <div class="rounded-md w-full justify-center shadow-md p-4">
            <div class="sm:grid sm:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <b>Comuna/Corregimiento:</b>
                <span v-if="acta.tipo === 'virtual'">
                  <template v-if="comunasDetalles.length">
                    <ul class="list-disc ml-4">
                      <li v-for="comuna in comunasDetalles" :key="comuna.id">
                        {{ comuna.detalle }}
                      </li>
                    </ul>
                  </template>
                </span>
                <span v-else>
                  {{ getParametros(acta.comuna) || "Sin información" }}
                </span>
              </div>
              <div class="md:col-span-2" v-if="acta.tipo !== 'virtual'">
                <b>Puesto de votación:</b>
                {{ getParametros(acta.puesto_votacion) || "N/A" }}
              </div>
              <div>
                <b>Fecha inicio:</b>
                {{ formatDate(acta.fecha_inicio) || "N/A" }}
              </div>
              <div>
                <b>Fecha Cierre:</b>
                {{ formatDate(acta.fecha_fin) || "N/A" }}
              </div>
              <div>
                <b>Hora de inicio:</b>
                {{ acta.hora_inicio || "N/A" }}
              </div>
              <div>
                <b>Hora de cierre:</b>
                {{ acta.hora_cierre || "N/A" }}
              </div>
              <div class="md:col-span-2">
                <b>Observaciones:</b>
                {{ acta.observaciones || "N/A" }}
              </div>
              <!-- Info jurado -->
              <div
                class="md:col-span-2 mt-4 mb-2"
                v-if="acta.tipo !== 'virtual'"
              >
                <h2 class="font-bold text-lg">Información Jurado</h2>
              </div>
              <!-- imagen jurado -->
              <div v-if="acta.tipo !== 'virtual'">
                <div
                  class="card shadow-lg rounded-md p-4 mb-4"
                  v-if="acta.jurado.user.biometrico"
                >
                  <img
                    :src="getUrlBiometrico(acta.jurado.user.biometrico.photo)"
                    class="w-full sm:h-48 sm:object-cover"
                  />
                </div>
                <div
                  class="card shadow-lg rounded-md p-4 sm:h-36 h-full flex items-center justify-center"
                  v-else
                >
                  Img jurado
                </div>
              </div>
              <!-- informacion jurado -->
              <div class="flex flex-col my-auto" v-if="acta.tipo !== 'virtual'">
                <div>
                  <b>Nombre:</b>
                  {{ acta.jurado.nombre || "N/A" }}
                </div>
                <div>
                  <b>identificacion:</b>
                  cc. {{ acta.jurado.identificacion || "N/A" }}
                </div>
                <div>
                  <b>contacto:</b>
                  {{ acta.jurado.contacto || "N/A" }}
                </div>
              </div>

              <!-- Info testigo -->
              <div
                class="md:col-span-2 mt-4 mb-2"
                v-if="acta.tipo !== 'virtual'"
              >
                <h2 class="font-bold text-lg">Información Testigo</h2>
              </div>
              <div v-if="acta.tipo !== 'virtual'">
                <b>Nombre:</b>
                {{ acta.nombre_testigo || "N/A" }}
              </div>
              <div v-if="acta.tipo !== 'virtual'">
                <b>identificacion:</b>
                cc. {{ acta.identificacion_testigo || "N/A" }}
              </div>
              <div v-if="acta.tipo !== 'virtual'">
                <b>contacto:</b>
                {{ acta.contacto_testigo || "N/A" }}
              </div>
              <!-- Info votaciones -->
              <div
                v-if="acta.tipo !== 'virtual'"
                class="md:col-span-2 mt-4 mb-2"
              >
                <h2 class="font-bold text-lg">Información votación</h2>
              </div>
              <!-- voto nulo -->
              <div>
                <b>Votos blanco:</b>
                {{ acta.votos_blanco || "N/A" }}
              </div>
              <!-- voto no marcados -->
              <div>
                <b>Votos no marcados:</b>
                {{ acta.votos_no_marcados || "N/A" }}
              </div>
              <!-- voto blanco -->
              <div>
                <b>Votos nulos:</b>
                {{ acta.votos_nulos || "N/A" }}
              </div>
              <!-- total -->
              <div>
                <b>Total votos:</b>
                {{ acta.total_ciudadanos || "N/A" }}
              </div>
              <div class="w-full col-span-2" v-if="acta.tipo !== 'virtual'">
                <table
                  v-if="acta.votos_fisico && acta.votos_fisico.length"
                  class="min-w-full whitespace-no-wrap"
                >
                  <thead>
                    <tr
                      class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                    >
                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        #
                      </th>

                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        Proyecto
                      </th>
                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        votos
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(pro, idx) in acta.votos_fisico"
                      :key="pro.id"
                      class="text-gray-700"
                    >
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ idx + 1 }}
                        </p>
                      </td>
                      <!-- proyecto name -->
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ pro.proyecto?.detalle ?? "Proyecto desconocido" }}
                        </p>
                      </td>
                      <!-- cantidad -->
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ pro.cantidad }}
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-else>
                  <em>No hay votos por proyectos</em>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- firmas delegados -->
        <div
          class="w-full col-span-2 flex flex-wrap justify-between mt-4"
          v-if="delegados.length"
        >
          <!-- delegados -->
          <div
            v-for="delegado in delegados"
            :key="delegado.id"
            class="w-auto p-4"
          >
            <!-- imagen de firma -->
            <div class="shadow-md border-b border-black mb-2 p-2">
              <img
                class="w-auto h-44 object-contain"
                :src="getFirma(delegado.firma)"
              />
            </div>
            <!-- informacion -->
            <div class="w-auto">
              <p>
                {{ delegado.nombre }}
              </p>
              <p>
                {{ delegado.identificacion }}
              </p>
              <p>
                {{ delegado.cargo }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>

  <!-- Lightbox -->
  <VueEasyLightbox
    :visible="showLightbox"
    :imgs="[previewImage]"
    @hide="closeLightbox"
  />
</template>

<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import VueEasyLightbox from "vue-easy-lightbox";

import { ref, inject, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const swal = inject("$swal");

const props = defineProps({
  acta: {
    type: Object,
    required: true,
  },
  parametros: {
    type: Object,
  },

  delegados: {
    type: Object,
  },
});

console.log(props.acta);

const breadcrumbLinks = [
  { url: "/actaPresencial", text: "Actas de escrutinio" },
  { url: "", text: "Detalles Acta escrutinio" },
];

// Lightbox
const previewImage = ref("");
const showLightbox = ref(false);

const openLightbox = (imageUrl) => {
  previewImage.value = imageUrl;
  showLightbox.value = true;
};

const closeLightbox = () => {
  showLightbox.value = false;
};

const getParametros = (id) => {
  console.log(id);

  return props.parametros.find((item) => item.id === parseInt(id)).detalle;
};

// Buscar el detalle de la comuna en los parámetros
const comunasDetalles = computed(() => {
  if (!props.acta.comuna || !props.parametros) return [];
  const ids = String(props.acta.comuna)
    .split("|")
    .map((id) => id.trim());
  return props.parametros.filter((p) => ids.includes(String(p.id)));
});

const getUrlBiometrico = (url) => `/storage/uploads/fotos/${url}`;
const getFirma = (url) => `/storage/uploads/delegado/${url}`;

const getUrlActa = (url) => {
  if (url === "virtual") {
    return `/assets/img/logo1.png`;
  }

  return `/storage/uploads/actas/${url}`;
};

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};
</script>
