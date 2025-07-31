<template>
  <Head title="Validación del votante" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Detalle del Registro</h1>
      </div>
    </template>

    <div class="p-4">
      <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-lg font-bold mb-4 text-blue-900">Datos del votante</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <b>Nombre:</b>
            {{ votante.votante?.nombre || "Sin información" }}
          </div>
          <div>
            <b>Tipo de documento:</b>
            {{ votante.votante?.tipo_documento || "N/A" }}
          </div>
          <div>
            <b>Número de documento:</b>
            {{ votante.votante?.identificacion || "N/A" }}
          </div>
          <div>
            <b>Fecha expedición documento:</b>
            {{ votante.votante?.fecha_expedicion || "N/A" }}
          </div>
          <div>
            <b>Fecha de nacimiento:</b>
            {{ votante.votante?.nacimiento || "N/A" }}
          </div>
          <div>
            <b>Genero:</b>
            {{ votante.votante?.genero || "N/A" }}
          </div>
          <div>
            <b>Comuna/Corregimiento:</b>
            {{ getComuna(votante.subtipo) || "N/A" }}
          </div>
          <div>
            <b>Barrio:</b>
            {{ votante.votante?.barrio || "N/A" }}
          </div>
          <div>
            <b>Dirección:</b>
            {{ votante.votante?.direccion || "N/A" }}
          </div>
          <div v-if="votante.intentos > 0">
            <b>motivo rechazo:</b>
            {{ votante.motivo || "N/A" }}
          </div>
        </div>
      </div>

      <div
        class="bg-white shadow-md rounded-lg p-6 flex flex-wrap gap-8 items-start"
      >
        <!-- Foto del votante -->
        <div class="flex-shrink-0 flex flex-col items-center">
          <h2>Fotografía</h2>
          <img
            v-if="votante.votante?.user.biometrico"
            :src="getUrlPhoto(votante.votante?.user.biometrico.photo)"
            alt="Foto del votante"
            @click="
              openLightbox(getUrlPhoto(votante.votante?.user.biometrico.photo))
            "
            class="w-64 h-64 cursor-pointer object-cover rounded-lg border shadow"
          />
          <span v-else class="text-gray-400 italic">Sin foto</span>
        </div>

        <!-- Foto de cedula -->
        <div class="flex-shrink-0 flex flex-col items-center">
          <h2>frontal</h2>
          <img
            v-if="votante.votante?.user.biometrico"
            :src="
              getUrlDocumentos(votante.votante?.user.biometrico.cedula_front)
            "
            alt="Foto del votante"
            @click="
              openLightbox(
                getUrlDocumentos(votante.votante?.user.biometrico.cedula_front)
              )
            "
            class="w-full h-64 cursor-pointer object-cover rounded-lg border shadow"
          />
          <span v-else class="text-gray-400 italic">Sin foto</span>
        </div>

        <!-- Foto de cedula -->
        <div class="flex-shrink-0 flex flex-col items-center">
          <h2>Posterior</h2>
          <img
            v-if="votante.votante?.user.biometrico"
            :src="
              getUrlDocumentos(votante.votante?.user.biometrico.cedula_back)
            "
            alt="Foto del votante"
            @click="
              openLightbox(
                getUrlDocumentos(votante.votante?.user.biometrico.cedula_back)
              )
            "
            class="w-full h-64 cursor-pointer object-cover rounded-lg border shadow"
          />
          <span v-else class="text-gray-400 italic">Sin foto</span>
        </div>

        <!-- Firma -->
        <div class="flex-shrink-0 flex flex-col items-center">
          <h2>Firma</h2>
          <img
            v-if="votante.votante?.user.biometrico"
            :src="getUrlFirma(votante.votante?.user.biometrico.firma)"
            alt="Foto del votante"
            @click="
              openLightbox(getUrlFirma(votante.votante?.user.biometrico.firma))
            "
            class="w-full h-64 cursor-pointer object-cover rounded-lg border shadow"
          />
          <span v-else class="text-gray-400 italic">Sin foto</span>
        </div>
      </div>

      <!-- Botones para ver PDFs -->
      <div
        class="bg-white shadow-md rounded-lg p-6 mt-6 flex flex-col md:flex-row gap-8 items-start"
      >
        <div class="w-auto">
          <div class=""><b>Validación:</b> {{ votante.validaciones }}</div>
          <div class="" v-if="duplicados.length > 0">
            <button
              @click="verDuplicados = true"
              class="bg-secondary text-white hover:scale-105 hover:bg-primary p-2 cursor-pointer rounded-md mt-2"
            >
              Ver duplicados
            </button>
          </div>
        </div>

        <!-- Select de motivos de rechazo -->
        <div class="flex flex-col gap-2 w-full md:w-1/3">
          <b>Motivo de rechazo:</b>
          <select v-model="form.motivo" class="border rounded p-2">
            <option value="" disabled>Seleccione un motivo</option>
            <option value="aprobado">No hay motivos (aprobado)</option>
            <option value="Documentos incorrectos">
              Documentos incorrectos
            </option>
            <option value="Identidad no validada">Identidad no validada</option>
            <option value="Parte frontal del documento poco visible">
              Parte frontal del documento poco visible
            </option>
            <option value="Parte Trasera del documento poco visible">
              Parte Trasera del documento poco visible
            </option>
            <option value="Firma poco visible">Firma poco visible</option>
            <option
              value="Firma diferente a la presentada en el documento de identificación"
            >
              Firma diferente a la presentada en el documento de identificación
            </option>
            <option value="Registro duplicado">Registro duplicado</option>
            <option
              value="Información errónea, en comparación con la documentación"
            >
              Información errónea, en comparación con la documentación
            </option>
            <option value="Dirección diferente a la comuna/corregimiento">
              Dirección diferente a la comuna/corregimiento
            </option>
          </select>
        </div>
      </div>

      <!-- Botones aprobar/rechazar -->
      <div
        class="flex flex-col md:flex-row gap-4 mt-8 justify-center items-center h-full"
      >
        <PrimaryButton
          @click="aprobarRegistro"
          class="flex h-full items-center"
          :disabled="form.processing || (form.motivo !== '' && form.motivo !== 'aprobado')"
        >
          Aprobar
        </PrimaryButton>
        <SecondaryButton
          @click="rechazarRegistro"
          class="!bg-red-500 text-white"
          :disabled="form.processing"
        >
          Rechazar
        </SecondaryButton>
      </div>

      <!-- Lightbox -->
      <VueEasyLightbox
        :visible="showLightbox"
        :imgs="[previewImage]"
        @hide="closeLightbox"
      />
    </div>

    <!-- Modal loading -->
    <Modal :show="verDuplicados" :closeable="true">
      <template #default>
        <!-- Encabezado con botón de cierre -->
        <div
          class="flex justify-between items-center bg-azul text-white px-4 py-4"
        >
          <h2 class="text-2xl font-semibold text-center">
            Registros duplicados o similares
          </h2>
          <button
            @click="verDuplicados = false"
            class="text-white text-xl hover:text-gray-300"
          >
            X
          </button>
        </div>

        <!-- Contenido dinámico -->
        <div class="px-6 py-4">
          <div
            v-for="item in paginatedItems"
            :key="item.id"
            class="flex gap-4 items-center mb-4 border-b pb-2"
          >
            <img
              :src="getUrlPhoto(item.photo)"
              alt="Foto"
              class="w-36 h-full object-cover rounded"
            />
            <div>
              <p class="text-gray-800 font-medium">ID: {{ item.user.votantes.hash_votantes[0].id }}</p>
              <p class="text-gray-800 font-medium">Nombre: {{ item.user.votantes.nombre }}</p>
              <p class="text-gray-800 font-medium">Identificación: {{ item.user.votantes.identificacion }}</p>
              <p class="text-gray-800 font-medium">Creación: {{ formatDate(item.user.votantes.hash_votantes[0].created_at) }}</p>
              <p class="text-gray-800 font-medium">Estado: {{ item.estado }}</p>
            </div>
          </div>

          <!-- Paginación -->
          <div class="flex justify-end items-center gap-2 mt-6">
            <button
              class="px-3 py-1 bg-gray-200 text-sm rounded hover:bg-gray-300"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              Anterior
            </button>
            <span>Página {{ currentPage }} de {{ totalPages }}</span>
            <button
              class="px-3 py-1 bg-gray-200 text-sm rounded hover:bg-gray-300"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Siguiente
            </button>
          </div>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import comunas from "@/shared/comunas.json";
import VueEasyLightbox from "vue-easy-lightbox";
import Modal from "@/Components/Modal.vue";

import { ref, inject, computed } from "vue";

const swal = inject("$swal");

const props = defineProps({
  votante: {
    type: Object,
    required: true,
  },
  duplicados: {
    type: Object,
  },
});

console.log(props);

const form = useForm({
  id: props.votante.id,
  motivo: "",
});

// Lightbox
const previewImage = ref("");
const showLightbox = ref(false);
//modal duppicados
const verDuplicados = ref(false);
const currentPage = ref(1);
const perPage = 5;

const totalPages = computed(() => Math.ceil(props.duplicados.length / perPage));

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return props.duplicados.slice(start, start + perPage);
});

const openLightbox = (imageUrl) => {
  previewImage.value = imageUrl;
  showLightbox.value = true;
};

const closeLightbox = () => {
  showLightbox.value = false;
};

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

// Breadcrumb
const breadcrumbLinks = [
  { url: route("gestion_registros.index"), text: "Gestion de registros" },
  { url: "", text: "Detalle registro" },
];

const getUrlPhoto = (url) => `/storage/uploads/fotos/${url}`;
const getUrlDocumentos = (url) => `/storage/uploads/documentos/${url}`;
const getUrlFirma = (url) => `/storage/uploads/firmas/${url}`;

const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna)?.label;
};

const aprobarRegistro = () => {
  form.post(route("aprobarRegistro"), {
    onSuccess: function () {
      swal({
        title: "Registro Aprobado",
        text: "El registro ha sido aprobado exitosamente",
        icon: "success",
      }).then(() => {
        window.location.href = route("gestion_registros.index");
      });
    },
    onError: function () {
      swal({
        title: "Error al aprobar registro",
        text: "Error al intentar aprobar este registro, por favor vuelve a intentar",
        icon: "error",
      });
    },
  });
};

const rechazarRegistro = () => {
  form.post(route("rechazarRegistro"), {
    onSuccess: function () {
      swal({
        title: "Registro Rechazado",
        text: "El registro ha sido rechazado exitosamente",
        icon: "success",
      }).then(() => {
        window.location.href = route("gestion_registros.index");
      });
    },
    onError: function () {
      swal({
        title: "Error al rechazar registro",
        text: "Error al intentar rechazar este registro, por favor vuelve a intentar",
        icon: "error",
      });
    },
  });
};
</script>
