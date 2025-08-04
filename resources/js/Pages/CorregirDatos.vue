<template>
  <Head title="Corregir datos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Corregir datos </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 border-b border-gray-200">
        <h1>Corrección registro</h1>
      </div>

      <div class="mt-4 justify-center">
        <Steps
          class="hidden sm:block"
          :model="items"
          v-model:activeStep="active"
        />
        <Steps
          class="sm:hidden block"
          :model="itemsMobil"
          v-model:activeStep="active"
        />
      </div>

      <form class="m-auto py-4 px-4">
        <!-- parte 1 -- validación -->
        <div class="sm:grid sm:grid-cols-2 gap-6 h-full" v-if="active == 0">
          <!-- Nombre -->
          <div class="mb-2">
            <InputLabel for="nombre" value="Nombre Completo" />
            <TextInput
              id="nombre"
              type="text"
              class="mt-1 block w-full"
              v-model="form.nombre"
              required
            />
            <InputError class="mt-2" :message="form.errors.nombre" />
          </div>

          <!-- Identificación -->
          <div class="mb-2">
            <InputLabel for="identificacion" value="Identificación" />
            <TextInput
              id="identificacion"
              type="text"
              class="mt-1 block w-full"
              v-model="form.identificacion"
              required
            />
            <InputError class="mt-2" :message="form.errors.identificacion" />
          </div>

          <!-- Tipo de Documento -->
          <div class="mb-2">
            <InputLabel for="tipo_documento" value="Tipo de Documento" />
            <select
              id="tipo_documento"
              v-model="form.tipo_documento"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>Seleccione tipo de documento</option>
              <option
                v-for="tipoDoc in tipo_documento"
                :key="tipoDoc.id"
                :value="tipoDoc.nombre"
              >
                {{ tipoDoc.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.tipo_documento" />
          </div>

          <!-- Fecha de Nacimiento -->
          <div class="mb-2">
            <InputLabel for="nacimiento" value="Fecha de Nacimiento" />
            <TextInput
              id="nacimiento"
              type="date"
              class="mt-1 block w-full"
              v-model="form.nacimiento"
              required
            />
            <InputError class="mt-2" :message="form.errors.nacimiento" />
            <p v-if="errorEdad" class="text-red-500">{{ errorEdad }}</p>
          </div>

          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-end">
            <button
              type="button"
              class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white p-2 rounded-md shadow-xl"
              @click="nextStep(1)"
            >
              Siguiente
            </button>
          </div>
        </div>
        <!-- parte 2 -- datos demograficos -->
        <div class="sm:grid sm:grid-cols-2 gap-6" v-if="active == 1">
          <!-- Género -->
          <div class="mb-2">
            <InputLabel for="genero" value="Identidad de Género" />
            <div class="mt-2 block sm:flex">
              <div class="">
                <input
                  type="radio"
                  id="masculino"
                  value="Masculino"
                  v-model="form.genero"
                />
                <label class="ps-2 text-sm sm:text-base" for="masculino"
                  >Masculino</label
                >
              </div>
              <div class="sm:ms-6">
                <input
                  type="radio"
                  id="two"
                  value="Femenino"
                  v-model="form.genero"
                />
                <label class="ps-2 text-sm sm:text-base" for="two"
                  >Femenino</label
                >
              </div>
              <!-- otros generos -->
              <div v-if="IsNewGenero" class="sm:ms-6">
                <InputLabel
                  class="sm:text-sm text-xs"
                  for="genero"
                  value="¿Cual?"
                />
                <TextInput
                  id="genero"
                  type="text"
                  class="mt-1 block w-full p-0 sm:p-1"
                  v-model="form.genero"
                  autocomplete="genero"
                />
                <InputError class="mt-1" :message="form.errors.genero" />
              </div>
              <div v-else class="sm:ms-6">
                <input
                  type="radio"
                  id="tres"
                  value="true"
                  v-model="IsNewGenero"
                />
                <label class="ps-2 text-sm sm:text-base" for="tres">Otro</label>
              </div>
            </div>
          </div>

          <!-- Etnia -->
          <div class="mb-2">
            <InputLabel for="etnia" value="Grupo poblacional" />
            <select
              id="etnia"
              v-model="form.etnia"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>
                Seleccione grupo poblacional perteneciente
              </option>
              <option v-for="et in etnia" :key="et.id" :value="et.nombre">
                {{ et.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.etnia" />
          </div>
          <!-- Condicion -->
          <div class="mb-2">
            <InputLabel for="condicion" value="Condición poblacional" />
            <select
              id="condicion"
              v-model="form.condicion"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>
                Seleccione condición poblacional
              </option>
              <option v-for="et in condicion" :key="et.id" :value="et.nombre">
                {{ et.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.condicion" />
          </div>

          <!-- Comuna -->
          <div class="mb-2">
            <InputLabel for="comuna" value="Comuna/Corregimiento" />
            <Select
              id="comuna"
              v-model="comunaSelected"
              :options="comunas"
              filter
              optionLabel="label"
              placeholder="Seleccione comuna/corregimiento del proyecto"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
            <InputError class="mt-2" :message="form.errors.comuna" />
          </div>

          <!-- Barrio -->
          <div class="mb-2">
            <InputLabel for="barrio" value="Barrio/Vereda" />
            <Select
              id="barrio"
              v-model="form.barrio"
              :options="barriosXComuna"
              filter
              placeholder="Seleccione barrio/vereda de dirección"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
            <InputError class="mt-2" :message="form.errors.barrio" />
          </div>
          <!-- Dirección -->
          <div class="mb-2">
            <InputLabel for="direccion" value="Dirección" />
            <TextInput
              id="direccion"
              type="text"
              class="mt-1 block w-full"
              v-model="form.direccion"
              required
            />
            <InputError class="mt-2" :message="form.errors.direccion" />
          </div>

          <!-- ncheck tratamiento datos -->
          <div class="my-4 col-span-2 mb-2">
            <input
              type="checkbox"
              id="consentimiento1"
              name="consentimiento1"
              required
              v-model="form.checked"
            />
            <label
              for="consentimiento1"
              class="ps-4 pe-12 sm:text-base text-sm text-gray-500"
              >Conozco y Acepto la Política de Privacidad de Datos
              <a
                href="https://www.pereira.gov.co/publicaciones/38/politicas-de-privacidad-y-condiciones-de-uso/"
                target="_blank"
                class="underline !text-azul cursor-pointer"
                >politica</a
              ></label
            >
            <InputError class="mt-1" :message="form.errors.checked" />
          </div>
          <!-- declaración juramentada -->
          <div class="mb-4 col-span-2 mb-2">
            <input
              type="checkbox"
              id="consentimiento2"
              name="consentimiento2"
              required
              v-model="form.declaracion"
            />
            <label
              for="consentimiento2"
              class="ps-4 pe-12 sm:text-base text-sm text-gray-500"
              >Declaro bajo la gravedad de juramento que toda la información
              suministrada en este formulario es verídica, completa y
              corresponde a la realidad. Entiendo que proporcionar información
              falsa puede acarrear sanciones legales conforme a la normativa
              vigente.</label
            >
            <InputError class="mt-1" :message="form.errors.declaracion" />
          </div>
          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(0)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end h-full">
              <PrimaryButton
                type="button"
                class="h-full"
                @click="validarDatos2"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Siguiente
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- parte 3 -- registro datos -->
        <div class="gap-6" v-if="active == 2">
          <!-- parte frontal documento -->
          <div class="sm:grid sm:grid-cols-2 gap-2 h-full sm:px-16 px-4">
            <!-- titulo -->
            <div class="col-span-2 text-sm sm:text-base text-gray-800 pt-6">
              <h3 class="text-lg font-semibold">
                Cargue documento de identificación parte frontal
              </h3>
              <p>
                Para cargar el documento frontal, asegúrese de que la imagen sea
                clara y legible. El documento debe estar bien iluminado y sin
                reflejos.
              </p>
            </div>
            <!-- ejemplo de doc frontal -->
            <div class="w-full h-full mt-4">
              <div class="w-full">
                <h4 class="text-2xl underline">Ejemplo parte frontal</h4>
                <img
                  :src="frontEjemplo"
                  alt="Documento Frontal ejemplo"
                  class="w-full h-full object-contain mt-2"
                />
              </div>
            </div>
            <!-- Cédula Frontal -->
            <div class="mb-2 h-full flex justify-center items-center mt-4">
              <div class="border-2 border-gray-300 rounded-md p-2 h-full">
                <TextInput
                  id="cedula_front"
                  type="file"
                  class="mt-1 !border-0"
                  accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp"
                  @input="onFileChange('cedula_front', $event)"
                  :maxFileSize="2e6"
                />
                <InputError class="mt-2" :message="form.errors.cedula_front" />

                <div class="flex justify-center">
                  <img
                    v-if="imageUrl"
                    :src="getUrlDocumentos(imageUrl, 1)"
                    :alt="form.cedula_front"
                    class="w-4/6 h-full object-contain"
                  />
                  <PhotoIcon
                    v-else
                    class="w-2/6 text-gray-300 flex justify-center items-center"
                  />
                </div>
                <div v-if="imageUrl" class="flex justify-center mt-2">
                  <button
                    @click="removeImage(1)"
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded"
                  >
                    Eliminar Imagen
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- firma -->
          <div class="w-full gap-2 h-full sm:px-16 px-4 mt-4">
            <InputLabel for="firma" value="Firma" />
            <div class="flex gap-4 mb-2">
              <button
                type="button"
                :class="[
                  'px-2 py-1 rounded',
                  firmaModo === 'imagen'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200',
                ]"
                @click="firmaModo = 'imagen'"
              >
                Cargar imagen
              </button>
              <button
                type="button"
                :class="[
                  'px-2 py-1 rounded',
                  firmaModo === 'canvas'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200',
                ]"
                @click="firmaModo = 'canvas'"
              >
                Dibujar firma
              </button>
            </div>
            <div v-if="firmaModo === 'imagen'">
              <TextInput
                id="firma"
                type="file"
                class="mt-1 block w-full"
                accept="image/*"
                @change="onFileChange('firma', $event)"
                :maxFileSize="2e6"
              />
              <InputError class="mt-2" :message="form.errors.firma" />
              <div v-if="firmaPreview" class="mt-2">
                <img
                  :src="getUrlFirma(firmaPreview)"
                  alt="Vista previa de la firma"
                  class="h-24 border rounded"
                />
              </div>
            </div>
            <div v-else>
              <canvas
                ref="canvas"
                height="150"
                class="border border-gray-400 rounded-md bg-white cursor-crosshair sm:w-[300px] w-[250px]"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart.prevent="startDrawing"
                @touchmove.prevent="draw"
                @touchend="stopDrawing"
              ></canvas>
              <div class="flex gap-2 mt-1">
                <button
                  type="button"
                  @click="clearCanvas"
                  class="text-xl sm:text-2xl text-blue-600 underline"
                >
                  Limpiar canvas
                </button>
                <button
                  type="button"
                  @click="saveCanvas"
                  class="text-xl sm:text-2xl text-green-600 underline"
                >
                  Guardar firma
                </button>
              </div>
              <InputError class="mt-2" :message="form.errors.firma" />
              <div v-if="firmaPreview" class="mt-2">
                <img
                  :src="firmaPreview"
                  alt="Vista previa de la firma"
                  class="h-24 border rounded"
                />
              </div>
            </div>
          </div>
          <!-- CAMPO OBLIGATORIO PARA EL USUARIO -->
          <div class="w-full">
            <TextInput
              id="campo_obligatorio"
              type="text"
              class="mt-1 !border-0 hidden"
              v-model="form.campoObligatorio"
            />
          </div>
          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2 mt-4">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(1)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end">
              <PrimaryButton
                type="button"
                @click="validarDatos3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Siguiente
              </PrimaryButton>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>

  <!-- Modal loading -->
  <Modal :show="loadingModal" :closeable="false">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Cargando datos...
      </h2>

      <div class="flex justify-center my-12">
        <ProgressSpinner aria-label="Loading" />
      </div>
    </template>
  </Modal>

  <!-- Modal botnes validar -->
  <Modal :show="botonesValidarModal" :closeable="false">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Desea volver a realizar el registro biométrico
      </h2>

      <div class="flex justify-center gap-4 text-center h-full my-12">
        <PrimaryButton
          type="button"
          class="h-full"
          @click="showModalBiometrico()"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Volver a realizar el registro biométrico
        </PrimaryButton>
        <SecondaryButton
          @click="submit"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Continuar y guardar los datos
        </SecondaryButton>
      </div>
    </template>
  </Modal>

  <!-- MODAL VALIDACION -->
  <Modal :show="biometricoModal" :closeable="true">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex flex-wrap tex-center justify-center bg-azul text-white"
      >
        Validación Biométrica
      </h2>

      <div class="mt-4 px-4">
        <p class="text-sm sm:text-base text-gray-800">
          Por favor, asegúrese de que su rostro esté bien iluminado y visible en
          la cámara (sin gorras, tapabocas, gafas). Si no se detecta un rostro,
          puede intentar nuevamente o continuar sin registro biométrico.
        </p>
      </div>
      <div class="my-12 px-4">
        <label for="camera">Seleccionar cámara:</label>
        <select
          id="camera"
          v-model="selectedDeviceId"
          class="rounded-lg w-auto ms-4"
        >
          <option
            v-for="device in devices"
            :key="device.deviceId"
            :value="device.deviceId"
          >
            {{ device.label || "Cámara desconocida" }}
          </option>
        </select>

        <video
          ref="video"
          autoplay
          muted
          width="400"
          height="340"
          class="rounded-xl shadow-lg flex mx-auto mt-6"
        />

        <button
          type="button"
          :disabled="loadingButtonBiometric"
          class="bg-secondary hover:bg-primary text-sm sm:text-base text-white p-2 rounded-md shadow-xl flex mx-auto mt-4 disabled:bg-gray-500"
          @click="registerAndValidate()"
        >
          Validar
        </button>
        {{ message }}
      </div>
    </template>
  </Modal>

  <!-- Modal inicio -->
  <Modal :show="InicioModal" :closeable="true">
    <template #default>
      <h2
        class="py-4 sm:text-4xl text-2x font-bold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Aviso importante
      </h2>

      <div class="text-justify sm:text-2xl text-xl p-6 mt-6">
        <p>
          Por favor llenar todos los datos correctamente, para no ser rechazado,
          ¡recuerda! solo tiene
          {{ 3 - votante.hash_votantes[0].intentos }} oportunidades para
          registrarse
        </p>
      </div>

      <div class="flex justify-center gap-4 text-center h-full my-6">
        <PrimaryButton
          type="button"
          class="h-full text-xl sm:text-2xl"
          @click="InicioModal = false"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Continuar
        </PrimaryButton>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";

import comunas from "@/shared/comunas.json"; // Importa el JSON
import tipo_documento from "@/shared/tipo_documento.json"; // Importa el JSON

import barrios from "@/shared/barrios.json"; // Importa el JSON
import condicion from "@/shared/condicion.json"; // Importa el JSON
import etnia from "@/shared/etnia.json"; // Importa el JSON

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";

import Steps from "primevue/steps";
import Select from "primevue/select";
import ProgressSpinner from "primevue/progressspinner";
import axios from "axios";
import { PhotoIcon } from "@heroicons/vue/24/solid";

//imagen
import frontEjemplo from "../../../public/assets/img/cedulaFrontEjemplo.webp";

import * as faceapi from "face-api.js";
const swal = inject("$swal");

const breadcrumbLinks = [{ url: "", text: "Corregir datos" }];

const props = defineProps({
  votante: {
    type: Object,
    required: true,
  },
});

console.log("Votante data:", props.votante);

const form = useForm({
  id_votante: props.votante.id || "",
  nombre: props.votante.nombre || "",
  identificacion: props.votante.identificacion || "",
  tipo_documento: props.votante.tipo_documento || "",
  nacimiento: props.votante.nacimiento || "",
  cedula_front: null,
  genero: props.votante.genero || "",
  etnia: props.votante.etnia || "",
  condicion: props.votante.condicion || "",
  direccion: props.votante.direccion || "",
  comuna: props.votante.hash_votantes[0].subtipo || "",
  barrio: props.votante.barrio || "",

  embedding: props.votante.user.biometrico.embedding || "",
  photo: null,
  validaciones: props.votante.hash_votantes[0].validaciones || "",
  checked: true,
  declaracion: true,
  firma: null,
  campoObligatorio: "",
});

const comunaSelected = ref("");
const barriosXComuna = ref([]);

//firma
const firmaPreview = ref(null);
const firmaModo = ref("imagen"); // 'imagen' o 'canvas'
const canvas = ref(null);
let drawing = false;

//modal loading
const loadingModal = ref(false);

//modal Inicio
const InicioModal = ref(true);

//ITEMS DEL STEP
const items = ref([
  {
    label: "Información Personal",
  },
  {
    label: "Datos Demográficos",
  },
  {
    label: "Registro datos",
  },
]);

const itemsMobil = ref([
  {
    label: "paso 1",
  },
  {
    label: "paso 2",
  },
  {
    label: "paso 3",
  },
]);

//step activo
const active = ref(0);
//mensaje error
const errorMessage = ref("");
//error fecha nacimiento
const errorEdad = ref("");
//validado
const isValidate = ref(false);
//abrir modal
const biometricoModal = ref(false);
//SABER SI SE ACTIVA EL INPUT DE OTROS
const IsNewGenero = ref(false);

//Modal botones de validar
const botonesValidarModal = ref(false);

//imagenes documentos
//variable de imagen frontal
const imageUrl = ref(null);
//variable de imagen documento posterior
const imageUrl1 = ref(null);

//face api js
const devices = ref([]);
const selectedDeviceId = ref("");
const video = ref(null);
const message = ref("");
const isCameraReady = ref(false);
const loadingButtonBiometric = ref(false);

//CONTADOR DE ERROR EN LA INICIALIZACION CAMARA
const counterCamera = ref(0);

const getUrlDocumentos = (url, num) => {
  if (num === 1) {
    if (form.cedula_front === null) {
      return `/storage/uploads/documentos/${url}`;
    }
  }
  return url;
};

const getUrlFirma = (url) => {
  if (form.firma === null) {
    return `/storage/uploads/firmas/${url}`;
  }
  return url;
};

//WATCH GENERO
watch(IsNewGenero, (value) => {
  if (value) {
    form.genero = "";
  }
});

//WATCH DEPARTAMENTOS
watch(departamentoSelected, (newValue) => {
  console.log(form.lugar_expedicion);

  if (newValue) {
    ciudadesxDep.value = ciudades.find(
      (ciudad) => ciudad.id === newValue.id
    ).ciudades;
  }
});

//WATCH COMUNAS
watch(comunaSelected, (newValue) => {
  if (newValue) {
    form.comuna = newValue;
    barriosXComuna.value = barrios.find(
      (barrio) => barrio.id === parseInt(newValue.value)
    ).barrios;
    console.log(barriosXComuna.value);
  }
});

const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (file) {
    // Validar el tamaño del archivo
    if (file.size > 2e6) {
      swal.fire({
        icon: "error",
        title: "Error",
        text: "El archivo debe ser menor a 2MB.",
      });
      return;
    }
  }

  form[field] = event.target.files[0];
  if (field === "cedula_front") {
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl.value = e.target.result;
    };
    reader.readAsDataURL(form[field]);
  } else if (field == "firma") {
    const reader = new FileReader();
    reader.onload = (e) => {
      firmaPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Eliminar la imagen seleccionada
const removeImage = (num) => {
  if (num === 1) {
    form.cedula_front = null;
    imageUrl.value = null;
  }
};

const formatDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

//PARTE DE FIRMA
// Canvas firma
const startDrawing = (e) => {
  drawing = true;
  const ctx = canvas.value.getContext("2d");
  const pos = getCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
  if (!drawing) return;
  const ctx = canvas.value.getContext("2d");
  const pos = getCoordinates(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.strokeStyle = "#222";
  ctx.lineWidth = 2;
  ctx.stroke();
};

const stopDrawing = () => {
  drawing = false;
};

const clearCanvas = () => {
  const ctx = canvas.value.getContext("2d");
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
  firmaPreview.value = null;
  form.firma = null;
};

const saveCanvas = () => {
  canvas.value.toBlob((blob) => {
    form.firma = new File([blob], "firma.webp", { type: "image/webp" });
    const reader = new FileReader();
    reader.onload = (e) => {
      firmaPreview.value = e.target.result;
    };
    reader.readAsDataURL(form.firma);
  }, "image/webp");
};

// 🔧 Función para obtener coordenadas, sea touch o mouse
const getCoordinates = (e) => {
  const rect = canvas.value.getBoundingClientRect();
  if (e.touches && e.touches.length > 0) {
    return {
      x: e.touches[0].clientX - rect.left,
      y: e.touches[0].clientY - rect.top,
    };
  } else {
    return {
      x: e.offsetX,
      y: e.offsetY,
    };
  }
};

//STEP PART
//prev
const prevStep = (num) => {
  active.value = num;
  isValidate.value = true;
};

//abrir modal y camera
const showModalBiometrico = async () => {
  loadingModal.value = true;
  try {
    // ✅ Solicitar permiso para la cámara
    const permiso = await navigator.mediaDevices.getUserMedia({ video: true });
    permiso.getTracks().forEach((track) => track.stop()); // cerrar inmediatamente

    // Cargar modelos
    await faceapi.nets.tinyFaceDetector.loadFromUri(
      "/models/tiny_face_detector"
    );
    await faceapi.nets.faceRecognitionNet.loadFromUri(
      "/models/face_recognition"
    );
    await faceapi.nets.faceLandmark68Net.loadFromUri(
      "/models/face_landmark_68"
    );

    // Obtener dispositivos
    const allDevices = await navigator.mediaDevices.enumerateDevices();
    devices.value = allDevices.filter((device) => device.kind === "videoinput");

    // Seleccionar por defecto la cámara integrada (si la hay)
    const preferida =
      devices.value.find((d) => d.label.toLowerCase().includes("user")) ||
      devices.value[0];
    console.log(preferida);
    selectedDeviceId.value = preferida?.deviceId || "";

    loadingModal.value = false;
    biometricoModal.value = true;
    await startCamera(selectedDeviceId.value);
  } catch (error) {
    loadingModal.value = false;
    if (counterCamera.value == 3) {
    }
    console.error("Error al iniciar:", error);
    message.value = "No se pudo acceder a la cámara.";
  }
};

//validar registro biometrico
const registerAndValidate = async () => {
  message.value = "";
  loadingButtonBiometric.value = true;

  // Inicializar contadores si no existen
  const getCounter = (key) => parseInt(sessionStorage.getItem(key) || "0");
  const setCounter = (key, value) =>
    sessionStorage.setItem(key, value.toString());

  if (!isCameraReady.value) {
    const falloCamara = getCounter("fallo_camara") + 1;

    message.value = "La cámara no está lista.";
    loadingButtonBiometric.value = false;

    return;
  }

  try {
    const detection = await faceapi
      .detectSingleFace(video.value, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    console.log("entro -- validador");

    if (!detection) {
      loadingButtonBiometric.value = false;
      message.value = "No se detectó un rostro.";

      counterCamera.value += 1;

      // Capturar imagen del video
      const canvas = document.createElement("canvas");
      canvas.width = video.value.videoWidth;
      canvas.height = video.value.videoHeight;
      const context = canvas.getContext("2d");
      context.drawImage(video.value, 0, 0, canvas.width, canvas.height);

      const imageBlob = await new Promise((resolve) =>
        canvas.toBlob(resolve, "image/jpeg")
      );

      // Convertir Blob a un File para enviarlo como si fuera un archivo subido
      const file = new File([imageBlob], "photo.jpg", { type: "image/jpeg" });

      form.photo = file;

      if (counterCamera.value == 3) {
        swal
          .fire({
            title: "Error en validación",
            text: "Error, no se detectó un rostro, vuelve a intentar o avanzar con la imagen poco visible y posiblemente sin registro biométrico (alta probabilidad de rechazo)",
            icon: "error",
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar",
          })
          .then((result) => {
            if (result.isConfirmed) {
              // Continuar sin validar
              form.embedding = "";
              form.validaciones = "no_rostro";
              //poner llamado a modal de botones
              biometricoModal.value = false;
              botonesValidarModal.value = true;
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              // Volver a intentar
              console.log("Usuario decide volver a intentar");
              message.value = "";
              counterCamera.value = 0;
            }
          });
      }

      return;
    }

    const descriptor = detection.descriptor;
    form.embedding = descriptor;
    console.log("emb", descriptor);

    // Capturar imagen del video
    // Capturar imagen del video
    const canvas = document.createElement("canvas");
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    const context = canvas.getContext("2d");
    context.drawImage(video.value, 0, 0, canvas.width, canvas.height);

    const imageBlob = await new Promise((resolve) =>
      canvas.toBlob(resolve, "image/jpeg")
    );

    // Convertir Blob a un File para enviarlo como si fuera un archivo subido
    const file = new File([imageBlob], "photo.jpg", { type: "image/jpeg" });

    form.photo = file;

    const formData = new FormData();
    formData.append("embedding", descriptor);
    loadingButtonBiometric.value = false;
    biometricoModal.value = false;
    loadingModal.value = true;
    axios
      .post(route("face-validate"), formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        message.value = response.data.message;
        console.log(response);
        loadingButtonBiometric.value = false;
        loadingModal.value = false;
        if (response.data.match) {
          swal
            .fire({
              title: "Error en validación",
              text: "Usted ya tiene un registro biométrico. Si está seguro que no se ha registrado, puede volver a intentarlo o continuar (probabilidad de rechazo).",
              icon: "error",
              showCancelButton: true,
              cancelButtonText: "Volver a intentar",
              confirmButtonText: "Continuar",
            })
            .then((result) => {
              if (result.isConfirmed) {
                // Continuar sin validar
                form.validaciones = "registro_duplicado";
                biometricoModal.value = false;
                botonesValidarModal.value = true;
                //poner llamado a modal de botones
              } else if (result.dismiss === swal.DismissReason.cancel) {
                // Volver a intentar
                console.log("Usuario decide volver a intentar");
              }
            });
        } else {
          swal({
            title: "Validación exitosa",
            text: "Validación biométrica exitosa",
            icon: "success",
            didClose: () => {
              //poner llamado a modal de botones
              biometricoModal.value = false;
              loadingModal.value = true;
              submit();
            },
          });
        }
      })
      .catch((error) => {
        loadingButtonBiometric.value = false;
        loadingModal.value = false;
        console.log(error);

        const falloRegistro = getCounter("fallo_registro") + 1;
        setCounter("fallo_registro", falloRegistro);

        swal
          .fire({
            title: "Error en validación",
            text: "Error en la validación, vuelve a intentar o avanzar sin registro biometrico (posibilidad de rechazo)",
            icon: "error",
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar",
          })
          .then((result) => {
            if (result.isConfirmed) {
              // Continuar sin validar
              form.embedding = "";
              form.validaciones = "fallo_registro_biometrico";
              biometricoModal.value = false;
              botonesValidarModal.value = true;
              //poner llamado a modal de botones
            } else if (result.dismiss === swal.DismissReason.cancel) {
              // Volver a intentar
              console.log("Usuario decide volver a intentar");
            }
          });
      });
  } catch (error) {
    console.error(error);
    loadingButtonBiometric.value = false;
    loadingModal.value = false;

    if (counterCamera.value == 3) {
      swal
        .fire({
          title: "Error en validación",
          text: "Error al procesar el rostro, vuelve a intentar o avanzar sin registro biometrico (posibilidad de rechazo)",
          icon: "error",
          showCancelButton: true,
          cancelButtonText: "Volver a intentar",
          confirmButtonText: "Continuar",
        })
        .then((result) => {
          if (result.isConfirmed) {
            // Continuar sin validar
            form.embedding = "";
            form.validaciones = "fallo_registro";
            //poner llamado a modal de botones
            biometricoModal.value = false;
            botonesValidarModal.value = true;
          } else if (result.dismiss === swal.DismissReason.cancel) {
            // Volver a intentar
            console.log("Usuario decide volver a intentar");
            message.value = "";
            counterCamera.value = 0;
          }
        });
    }
    message.value = "Error al procesar el rostro.";

    //poner llamado a modal de botones
  }
};

const validateEdad = () => {
  if (!form.nacimiento) {
    errorEdad.value = "Debes ingresar la fecha de nacimiento.";
    return false;
  }
  const hoy = new Date();
  const nacimiento = new Date(form.nacimiento);
  let edad = hoy.getFullYear() - nacimiento.getFullYear();
  const m = hoy.getMonth() - nacimiento.getMonth();
  if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
    edad--;
  }
  if (edad < 14) {
    errorEdad.value =
      "No cumple con el requisito de edad mínima (14 años), por lo tanto, no puede ejercer el derecho al voto.";
    return false;
  }
  errorEdad.value = "";
  return true;
};

//validar datos step1
const validateStep1 = async () => {
  isValidate.value = false;
  if (
    form.nombre &&
    form.identificacion &&
    form.tipo_documento &&
    form.fecha_expedicion &&
    (form.lugar_expedicion || form.tipo_documento != "Cédula Ciudadanía") &&
    form.nacimiento
  ) {
    if (!validateEdad()) return;
    // Limpia mensajes previos
    errorMessage.value = "";

    // Llama al servicio para comprobar la identificación
    try {
      const existe = await checkIdentificacionService(form.identificacion);
      console.log("Identificación existe:", existe);
      if (existe) {
        errorMessage.value =
          "El número de cédula ingresado ya se encuentra registrado en el sistema. /n Por favor, continúe con el proceso de votación ingresando a la plataforma con su número de documento y la contraseña que registró previamente.";
        isValidate.value = false;
      } else {
        errorMessage.value = "";
        isValidate.value = true;
      }
    } catch (error) {
      errorMessage.value =
        "Error al validar la identificación. Intenta de nuevo.";
      isValidate.value = false;
    }
  }

  if (isValidate.value) {
    active.value = 1;
  } else {
    if (!form.nombre) {
      form.errors.nombre = "Este campo es requerido.";
    }
    if (!form.identificacion || errorMessage.value != "") {
      form.errors.identificacion =
        errorMessage.value || "Este campo es requerido.";
    }
    if (!form.tipo_documento) {
      form.errors.tipo_documento = "Este campo es requerido.";
    }
    if (!form.fecha_expedicion) {
      form.errors.fecha_expedicion = "Este campo es requerido.";
    }

    if (!form.lugar_expedicion) {
      form.errors.lugar_expedicion = "Este campo es requerido.";
    }
    if (!form.nacimiento) {
      form.errors.nacimiento = "Este campo es requerido.";
    }
  }
};

//llamado validador identificacion
const checkIdentificacionService = async (identificacion) => {
  const response = await axios.post("/validar-identificacion", {
    identificacion,
  });
  return response.data.existe; // true si existe, false si no
};

//next
const nextStep = (num) => {
  isValidate.value = false;
  switch (num) {
    case 1:
      validateStep1();
      break;

    case 2:
      validarDatos2();
      break;

    default:
      break;
  }

  if (isValidate.value) {
    active.value = num;
  }
};

//validar datos step2
const validarDatos2 = () => {
  isValidate.value = false;
  if (
    form.genero &&
    form.etnia &&
    form.condicion &&
    form.comuna &&
    form.barrio &&
    form.direccion &&
    form.checked &&
    form.declaracion
  ) {
    isValidate.value = true;
    active.value = 2;
  }

  if (isValidate.value) {
  } else {
    if (!form.genero) {
      form.errors.genero = "Este campo es requerido.";
    }
    if (!form.etnia) {
      form.errors.etnia = "Este campo es requerido.";
    }
    if (!form.condicion) {
      form.errors.condicion = "Este campo es requerido.";
    }

    if (!form.comuna) {
      form.errors.comuna = "Este campo es requerido.";
    }

    if (!form.barrio) {
      form.errors.barrio = "Este campo es requerido.";
    }
    if (!form.direccion) {
      form.errors.direccion = "Este campo es requerido.";
    }

    if (!form.checked) {
      form.errors.checked = "Este campo es requerido.";
    }
    if (!form.declaracion) {
      form.errors.declaracion = "Este campo es requerido.";
    }
  }
};

const validarDatos3 = () => {
  isValidate.value = false;
  if (
    (form.cedula_front || imageUrl) &&
    (form.firma || firmaPreview)
  ) {
    isValidate.value = true;
  }

  if (isValidate.value) {
    if (
      form.embedding &&
      props.votante.hash_votantes[0].validaciones != "no_rostro"
    ) {
      // Si ya tiene registro biométrico o no es necesario validar, continuar
      botonesValidarModal.value = true;
    } else {
      // Si no tiene registro biométrico, abrir modal de validación
      showModalBiometrico();
    }
  } else {
    if (!form.cedula_front) {
      form.errors.cedula_front = "Este campo es requerido.";
    }

    if (!form.firma) {
      form.errors.firma = 'Debe hacer clic en "Usar firma" para continuar';
    }
  }
};

const submit = () => {
  form.post(route("corregirDatos"), {
    onSuccess: () => {
      swal({
        title: "Registro actualizado",
        text: "Registro de usuario actualizado exitosamente. Se realizará la validación de su información, el estado sera notificado a su correo.",
        icon: "success",
      }).then((result) => {
        window.location.href = route("dashboard");
      });
      sessionStorage.removeItem("fallo_camara");
      sessionStorage.removeItem("fallo_registro");
      sessionStorage.removeItem("registro_duplicado");
      form.reset();
      stopCamera();
    },
    onError: (errors) => {
      swal({
        title: "Error",
        text: "Ocurrió un error al registrar el usuario.",
        icon: "error",
      }).then((result) => {
        active.value = 0;
      });

      console.error(errors); // Puedes ver los errores específicos aquí
    },
  });
};

//funciones camara
const startCamera = async (deviceId) => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: { exact: deviceId } },
    });
    video.value.srcObject = stream;
    isCameraReady.value = true;
  } catch (error) {
    console.error("Error al iniciar cámara:", error);
    message.value = "Error al iniciar la cámara.";
  }
};

// Si el usuario cambia la cámara, reiniciamos el stream
watch(selectedDeviceId, async (newDeviceId) => {
  if (newDeviceId) {
    await startCamera(newDeviceId);
  }
});

const stopCamera = () => {
  const stream = video.value?.srcObject;
  if (stream) {
    stream.getTracks().forEach((track) => track.stop());
  }
  video.value.srcObject = null;
};

watch(biometricoModal, (newVal) => {
  if (newVal == false) {
    stopCamera();
  }
});

const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna);
};

onMounted(() => {
  if (props.votante.user.biometrico.cedula_front) {
    imageUrl.value = props.votante.user.biometrico.cedula_front;
  }

  if (props.votante.user.biometrico.firma) {
    firmaPreview.value = props.votante.user.biometrico.firma;
  }

  if (props.votante.hash_votantes[0].subtipo) {
    form.comuna = getComuna(props.votante.hash_votantes[0].subtipo);
  }

  form.fecha_expedicion = formatDate(props.votante.fecha_expedicion);
  form.nacimiento = formatDate(props.votante.nacimiento);

  // Mostrar modal de inicio
  InicioModal.value = true;
});
</script>
