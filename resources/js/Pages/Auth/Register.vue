<template>
  <PageLayout>
    <Head title="Registro de Usuario" />

    <div
      class="w-full sm:max-w-6xl h-full mx-auto bg-white shadow-lg rounded-lg px-6 py-12 z-10"
    >
      <h1 class="text-2xl font-bold text-center text-gray-800">
        Registro votante
      </h1>

      <div class="mt-4 justify-center">
        <Steps :model="items" v-model:activeStep="active" />
      </div>

      <form class="m-auto py-4">
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

          <!-- Fecha de Expedición -->
          <div class="mb-2">
            <InputLabel for="fecha_expedicion" value="Fecha de Expedición" />
            <TextInput
              id="fecha_expedicion"
              type="date"
              class="mt-1 block w-full"
              v-model="form.fecha_expedicion"
              required
            />
            <InputError class="mt-2" :message="form.errors.fecha_expedicion" />
          </div>

          <!-- Lugar de Expedición -->
          <div
            class="cols-span-2 w-full mb-2"
            v-if="form.tipo_documento !== 'Cédula Extranjería'"
          >
            <InputLabel for="lugar_expedicion" value="Lugar de Expedición" />
            <!-- departamento -->
            <div class="mb-2 mt-1">
              <Select
                id="departamento"
                v-model="departamentoSelected"
                :options="departamentos"
                filter
                optionLabel="departamento"
                placeholder="Seleccione departamento"
                checkmark
                :highlightOnSelect="false"
                class="w-full"
              />
            </div>

            <!-- ciudad -->
            <div class="" v-if="form.tipo_documento !== 'Pasaporte'">
              <Select
                :disabled="
                  departamentoSelected === null || departamentoSelected === ''
                "
                id="lugar_expedicion"
                v-model="form.lugar_expedicion"
                :options="ciudadesxDep"
                filter
                optionLabel=""
                placeholder="Seleccione lugar de expedición"
                checkmark
                :highlightOnSelect="false"
                class="w-full"
              />
            </div>
            <InputError class="mt-2" :message="form.errors.lugar_expedicion" />
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
          <!-- Cédula Frontal -->
          <div class="mb-2">
            <InputLabel for="cedula_front" value="Foto Documento Frontal" />
            <TextInput
              id="cedula_front"
              type="file"
              accept="image/*"
              class="mt-1 block w-full"
              @change="onFileChange('cedula_front', $event)"
              required
            />
            <InputError class="mt-2" :message="form.errors.cedula_front" />
          </div>
          <!-- Cédula Posterior -->
          <div class="mb-2">
            <InputLabel for="cedula_back" value="Foto Documento Posterior" />
            <TextInput
              id="cedula_back"
              type="file"
              accept="image/*"
              class="mt-1 block w-full"
              @change="onFileChange('cedula_back', $event)"
              required
            />
            <InputError class="mt-2" :message="form.errors.cedula_back" />
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
            <InputLabel for="genero" value="Género" />
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
            <InputLabel for="condicion" value="Condición" />
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
          <!-- Indicativo -->
          <div class="mb-2">
            <InputLabel for="indicativo" value="Indicativo" />
            <TextInput
              id="indicativo"
              type="text"
              maxlength="3"
              class="mt-1 block w-full"
              v-model="form.indicativo"
              required
            />
            <InputError class="mt-2" :message="form.errors.indicativo" />
          </div>
          <!-- Celular -->
          <div class="col-span-2 flex flex-wrap mb-1">
            <!-- celular -->
            <div>
              <InputLabel for="celular" value="Celular" />
              <TextInput
                id="celular"
                type="number"
                class="mt-1 block w-full"
                v-model="form.celular"
                required
              />
              <InputError class="mt-2" :message="form.errors.celular" />
            </div>
          </div>

          <!-- Comuna -->
          <div class="mb-2">
            <InputLabel for="comuna" value="Comuna" />
            <Select
              id="comuna"
              v-model="form.comuna"
              :options="comunas"
              filter
              optionLabel="label"
              placeholder="Seleccione comuna del proyecto"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
            <InputError class="mt-2" :message="form.errors.comuna" />
          </div>

          <!-- Barrio -->
          <div class="mb-2">
            <InputLabel for="barrio" value="Barrio" />
            <TextInput
              id="barrio"
              type="text"
              class="mt-1 block w-full"
              v-model="form.barrio"
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
          <!-- Correo -->
          <div class="mb-2">
            <InputLabel for="email" value="Correo Electrónico" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full"
              v-model="form.email"
              required
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>
          <!-- Contraseña -->
          <div class="mb-2">
            <InputLabel for="password" value="Contraseña" />
            <Password
              id="password"
              v-model="form.password"
              required
              toggleMask
            />

            <InputError class="mt-2" :message="form.errors.password" />
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
                Validar datos
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- parte 3 -- verificacion -->
        <div class="gap-6" v-if="active == 2">
          <div class="text-sm sm:text-base text-gray-800 pt-6 sm:px-16 px-4">
            Ingrese el código de verificación que le fue enviado a su correo o
            celular. Si no recibió el código, por favor revise su bandeja de
            entrada o carpeta de spam. o vuelva a atrás y valide nuevamente.
          </div>
          <div class="w-full mt-6">
            <InputLabel for="codigo" value="Código de Verificación" />
            <TextInput
              id="codigo"
              type="text"
              class="mt-1 block w-full"
              v-model="form.codigo"
              required
            />
            <InputError class="mt-2" :message="form.errors.codigo" />
          </div>

          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2 mt-4">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(0)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end">
              <PrimaryButton
                type="button"
                @click="submit"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Registrarse
              </PrimaryButton>
            </div>
          </div>
        </div>
      </form>
    </div>
  </PageLayout>

  <!-- Modal loading -->
  <Modal :show="loadingModal" :closeable="false">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Validando datos
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
        Validar por código de verificación
      </h2>

      <div class="flex justify-center gap-4 text-center h-full my-12">
        <PrimaryButton
          type="button"
          class="h-full"
          @click="validationUser(0)"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Validar con correo
        </PrimaryButton>
        <SecondaryButton
          @click="validationUser(1)"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Validar con sms
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
        Validando documento
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
</template>

<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, Head } from "@inertiajs/vue3";
import Steps from "primevue/steps";
import Select from "primevue/select";
import ProgressSpinner from "primevue/progressspinner";
import Password from "primevue/password";
import tipo_documento from "@/shared/tipo_documento.json"; // Importa el JSON
import departamentos from "@/shared/departamentos.json"; // Importa el JSON
import ciudades from "@/shared/ciudades.json"; // Importa el JSON
import comunas from "@/shared/comunas.json"; // Importa el JSON
import condicion from "@/shared/condicion.json"; // Importa el JSON
import etnia from "@/shared/etnia.json"; // Importa el JSON
import { inject, ref, computed, watch, onMounted } from "vue";
import axios from "axios";
const swal = inject("$swal");
import { useReCaptcha } from "vue-recaptcha-v3";

import * as faceapi from "face-api.js";

const form = useForm({
  nombre: "",
  identificacion: "",
  tipo_documento: "",
  fecha_expedicion: "",
  lugar_expedicion: "",
  nacimiento: "",
  cedula_front: null,
  cedula_back: null,
  genero: "",
  etnia: "",
  condicion: "",
  direccion: "",
  indicativo: "+57",
  celular: "",
  comuna: "",
  barrio: "",
  email: "",
  password: "",
  embedding: "",
  photo: "",
  validaciones: "",
  checked: false,
  declaracion: false,
  recaptcha_token: "",
  codigo: "",
});

const { executeRecaptcha } = useReCaptcha();

const departamentoSelected = ref("");
const ciudadesxDep = ref([]);

//modal loading
const loadingModal = ref(false);

//ITEMS DEL STEP
const items = ref([
  {
    label: "Paso 1",
  },
  {
    label: "Paso 2",
  },
  {
    label: "Paso 3",
  },
]);

//step activo
const active = ref(0);
//mensaje error
const errorMessage = ref("");
//error fecha nacimiento
const errorEdad = ref('')
//validado
const isValidate = ref(false);
//abrir modal
const biometricoModal = ref(false);
//SABER SI SE ACTIVA EL INPUT DE OTROS
const IsNewGenero = ref(false);

//Modal botones de validar
const botonesValidarModal = ref(false);

//face api js
const devices = ref([]);
const selectedDeviceId = ref("");
const video = ref(null);
const message = ref("");
const isCameraReady = ref(false);
const loadingButtonBiometric = ref(false);

//CONTADOR DE ERROR EN LA INICIALIZACION CAMARA
const counterCamera = ref(0);

//WATCH GENERO
watch(IsNewGenero, (value) => {
  if (value) {
    form.genero = "";
  }
});

//WATCH DEPARTAMENTOS
watch(departamentoSelected, (newValue) => {
  if (newValue) {
    ciudadesxDep.value = ciudades.find(
      (ciudad) => ciudad.id === newValue.id
    ).ciudades;
  }
});

const onFileChange = (field, event) => {
  form[field] = event.target.files[0];
};

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
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
    setCounter("fallo_camara", falloCamara);
    message.value = "La cámara no está lista.";
    loadingButtonBiometric.value = false;

    if (falloCamara >= 5) {
      form.validaciones = "fallo_camara";
      //poner llamado a modal de botones
      biometricoModal.value = false;
      botonesValidarModal.value = true;
    }
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
            text: "Error, no se detectó un rostro, vuelve a intentar o avanzar sin registro biometrico",
            icon: "error",
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar sin validar",
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
              text: "Usted ya tiene un registro biométrico. Si está seguro que no se ha registrado, puede volver a intentarlo o continuar sin validar.",
              icon: "error",
              showCancelButton: true,
              cancelButtonText: "Volver a intentar",
              confirmButtonText: "Continuar sin validar",
            })
            .then((result) => {
              if (result.isConfirmed) {
                // Continuar sin validar
                form.validaciones = "registro_duplicado";
                biometricoModal.value = false;
                botonesValidarModal.value = true;
                //poner llamado a modal de botones
              } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Volver a intentar
                console.log("Usuario decide volver a intentar");
              }
            });
        } else {
          swal({
            title: "Validación exitosa",
            text: "Validación de documento exitosa",
            icon: "success",
            didClose: () => {
              //poner llamado a modal de botones
              biometricoModal.value = false;
              botonesValidarModal.value = true;
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
            text: "Error en la validación, vuelve a intentar o avanzar sin registro biometrico",
            icon: "error",
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar sin validar",
          })
          .then((result) => {
            if (result.isConfirmed) {
              // Continuar sin validar
              form.embedding = "";
              form.validaciones = "fallo_registro_biometrico";
              biometricoModal.value = false;
              botonesValidarModal.value = true;
              //poner llamado a modal de botones
            } else if (result.dismiss === Swal.DismissReason.cancel) {
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
          text: "Error al procesar el rostro, vuelve a intentar o avanzar sin registro biometrico",
          icon: "error",
          showCancelButton: true,
          cancelButtonText: "Volver a intentar",
          confirmButtonText: "Continuar sin validar",
        })
        .then((result) => {
          if (result.isConfirmed) {
            // Continuar sin validar
            form.embedding = "";
            form.validaciones = "fallo_registro";
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
    message.value = "Error al procesar el rostro.";

    //poner llamado a modal de botones
  }
};

const validateEdad = () => {
  if (!form.nacimiento) {
    errorEdad.value = 'Debes ingresar la fecha de nacimiento.'
    return false
  }
  const hoy = new Date()
  const nacimiento = new Date(form.nacimiento)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const m = hoy.getMonth() - nacimiento.getMonth()
  if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
    edad--
  }
  if (edad < 14) {
    errorEdad.value = 'No es posible el registro de menores de 14 años.'
    return false
  }
  errorEdad.value = ''
  return true
}

//validar datos step1
const validateStep1 = async () => {
  isValidate.value = false;
  if (
    form.nombre &&
    form.identificacion &&
    form.tipo_documento &&
    form.fecha_expedicion &&
    form.nacimiento &&
    form.cedula_front &&
    form.cedula_back
  ) {
    if (!validateEdad()) return
    // Limpia mensajes previos
    errorMessage.value = "";

    // Llama al servicio para comprobar la identificación
    try {
      const existe = await checkIdentificacionService(form.identificacion);
      console.log("Identificación existe:", existe);
      if (existe) {
        errorMessage.value = "La identificación ya está registrada.";
        isValidate.value = false;
      } else {
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
    if (!form.cedula_front) {
      form.errors.cedula_front = "Este campo es requerido.";
    }
    if (!form.cedula_back) {
      form.errors.cedula_back = "Este campo es requerido.";
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
    form.celular &&
    form.comuna &&
    form.barrio &&
    form.direccion &&
    form.email &&
    form.password &&
    form.checked &&
    form.declaracion
  ) {
    isValidate.value = true;
  }

  if (isValidate.value) {
    showModalBiometrico();
  } else {
    if (!form.genero) {
      form.errors.genero = "Este campo es requerido.";
    }
    if (!form.etnia) {
      form.errors.etnia = "Este campo es requerido.";
    }
    if (!form.celular) {
      form.errors.celular = "Este campo es requerido.";
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
    if (!form.email) {
      form.errors.email = "Este campo es requerido.";
    }
    if (!form.password) {
      form.errors.password = "Este campo es requerido.";
    }
    if (!form.checked) {
      form.errors.checked = "Este campo es requerido.";
    }
    if (!form.declaracion) {
      form.errors.declaracion = "Este campo es requerido.";
    }
  }
};

const validationUser = async (num) => {
  // Mostrar el modal de carga mientras se realiza la validación
  botonesValidarModal.value = false;
  loadingModal.value = true;
  console.log(form.embedding);

  try {
    // Continuar con la validación de correo o SMS
    const formData = new FormData();

    switch (num) {
      case 0:
        formData.append("via", "correo");
        break;
      case 1:
        formData.append("via", "sms");
        break;
    }

    const recaptchaToken = await executeRecaptcha("register");

    // Agregar el token de reCAPTCHA al formulario
    form.recaptcha_token = recaptchaToken;

    formData.append("nombre", form.nombre);
    formData.append("identificacion", form.identificacion);
    formData.append("tipo_documento", form.tipo_documento);
    formData.append("fecha_expedicion", form.fecha_expedicion);
    formData.append("lugar_expedicion", form.lugar_expedicion);
    formData.append("nacimiento", form.nacimiento);
    formData.append("etnia", form.etnia);
    formData.append("direccion", form.direccion);
    formData.append("genero", form.genero);
    formData.append("comuna", form.comuna);
    formData.append("barrio", form.barrio);
    formData.append("email", form.email);
    formData.append("celular", form.celular);
    formData.append("indicativo", form.indicativo);
    formData.append("password", form.password);
    formData.append("recaptcha_token", recaptchaToken);

    // Enviar la solicitud al backend
    await axios.post(route("validate-user"), formData);

    loadingModal.value = false;

    swal({
      title: "Código enviado",
      text: "Revisa tu correo o SMS para el código de verificación.",
      icon: "success",
    });
    active.value = 2; // Ir al paso de verificación
  } catch (error) {
    console.error(error);
    console.error(form.errors);
    swal({
      title: "Error",
      text: "Hubo un problema al validar tu información. Por favor, inténtalo nuevamente.",
      icon: "error",
    });
  } finally {
    loadingModal.value = false; // Ocultar el modal de carga
  }
};

const submit = () => {
  form.post(route("create-user"), {
    onSuccess: () => {
      swal({
        title: "Registro realizado",
        text: "Registro de usuario realizado exitosamente. Se realizará la validación de su información, el estado sera notificado a su correo.",
        icon: "success",
      }).then((result) => {
        window.location.reload();
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
</script>
