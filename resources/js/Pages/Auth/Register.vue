<template>
  <PageLayout>
    <Head title="Registro de Usuario" />

    <div
      class="w-full sm:max-w-6xl h-full mx-auto bg-white shadow-lg rounded-lg px-4 sm:px-6 py-12 z-10"
    >
      <h1 class="text-2xl font-bold text-center text-gray-800">
        Registro votante
      </h1>

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
            <p
              v-if="form.errors.identificacion"
              class="mt-2 text-sm md:text-base text-red-600"
            >
              {{ form.errors.identificacion }}
            </p>
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
          <div class="mb-2 sm:block hidden">
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
          <!-- fecha nacimiento mobil -->
          <div class="mb-2 sm:hidden grid grid-cols-3 gap-2">
            <InputLabel
              class="col-span-3"
              for="nacimiento"
              value="Fecha de Nacimiento"
            />
            <!-- dia -->
            <div class="w-auto mt-1">
              <InputLabel for="nacimientoDia" value="Día" />
              <TextInput
                id="nacimientoDia"
                type="number"
                class="block"
                v-model="diaNacimiento"
              />
            </div>
            <!-- mes -->
            <div class="w-auto mt-1">
              <InputLabel for="nacimientoMes" value="Mes" />
              <TextInput
                id="nacimientoMes"
                type="number"
                class="block"
                v-model="mesNacimiento"
              />
            </div>
            <!-- Annio -->
            <div class="w-auto mt-1">
              <InputLabel for="nacimientoAnnio" value="Año" />
              <TextInput
                id="nacimientoAnnio"
                type="number"
                class="block"
                v-model="AnnioNacimiento"
              />
            </div>
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

          <!-- Celular -->
          <div class="w-full mb-2">
            <!-- celular -->
            <div>
              <InputLabel for="celular" value="Celular" />
              <div class="w-full flex flex-wrap gap-2 mt-1">
                <!-- indicativo bloqueado -->
                <div
                  class="flex items-center w-auto[20%] block p-2 border border-gray-400 bg-gray-100 rounded-md"
                >
                  <p>+57</p>
                </div>
                <!-- celular -->
                <TextInput
                  id="celular"
                  type="number"
                  class="w-[80%] flex"
                  v-model="form.celular"
                  required
                />
              </div>

              <InputError class="mt-2" :message="form.errors.celular" />
            </div>
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
              placeholder="Seleccione comuna/corregimiento de dirección"
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
            <div class="w-full mt-2" v-if="isOtroBarrio">
              <InputLabel for="barrio_otro" value="Cual?" />
              <TextInput
                id="barrio_otro"
                type="text"
                class="mt-1 block w-full"
                v-model="form.barrio"
              />
            </div>
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
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>
          <!-- aviso contraseña -->
          <div class="mb-2">
            <div class="bg-azul rounded-md p-2 w-full h-full">
              <p class="text-white text-base">
                Digite una contraseña fácil de recordar para luego acceder a
                votar.
              </p>
            </div>
          </div>
          <!-- Contraseña -->
          <div class="mb-2">
            <InputLabel
              for="password"
              value="Contraseña (mínimo: 8 caracteres)"
            />
            <Password
              id="password"
              v-model="form.password"
              required
              toggleMask
              autocomplete="current-password"
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
                >Política</a
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
              >Manifiesto que acepto, bajo juramento, la
              <a
                href="https://www.pereira.gov.co/loader.php?lServicio=Tools2&lTipo=descargas&lFuncion=visorpdf&file=https%3A%2F%2Fwww.pereira.gov.co%2Floader.php%3FlServicio%3DTools2%26lTipo%3Ddescargas%26lFuncion%3DexposeDocument%26idFile%3D211273%26tmp%3De1c59e50ed23a13cda9087f627ac4f4d%26urlDeleteFunction%3Dhttps%253A%252F%252Fwww.pereira.gov.co%252Floader.php%253FlServicio%253DTools2%2526lTipo%253Ddescargas%2526lFuncion%253DdeleteTemporalFile%2526tmp%253De1c59e50ed23a13cda9087f627ac4f4d&pdf=1&tmp=e1c59e50ed23a13cda9087f627ac4f4d&fileItem=211273"
                target="_blank"
                class="underline !text-azul cursor-pointer"
                >Declaración</a
              >
              sobre la veracidad de la información registrada</label
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
                    :src="imageUrl"
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
  </PageLayout>

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
          la cámara (sin gorras, tapabocas, gafas).
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
          class="bg-secondary hover:bg-primary z-1000 text-base sm:text-base text-white p-2 rounded-md shadow-xl flex mx-auto mt-4 disabled:bg-gray-500"
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
        class="p-4 sm:text-4xl text-2x font-bold text-gray-800 text-justify flex tex-center justify-center bg-azul text-white"
      >
        Aviso Importante para el proceso de registro
      </h2>

      <div class="text-justify sm:text-2xl text-xl p-6 mt-6">
        <p>
          El dispositivo que utilice para registrarse debe contar con cámara, ya
          que es un requisito indispensable para la validación de identidad.
        </p>
        <br />
        <p>
          Asegúrese de ingresar correctamente toda la información solicitada, ya
          que cualquier error podría resultar en el rechazo.
        </p>
        <br />
        <p>
          Al momento de seleccionar su comuna o corregimiento, hágalo con
          precisión. Recuerde que el derecho al voto solo podrá ejercerse una
          única vez y no habrá posibilidad de repetir el proceso.
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
import barrios from "@/shared/barrios.json"; // Importa el JSON
import condicion from "@/shared/condicion.json"; // Importa el JSON
import etnia from "@/shared/etnia.json"; // Importa el JSON
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";
import axios from "axios";
const swal = inject("$swal");
import { useReCaptcha } from "vue-recaptcha-v3";
import { PhotoIcon } from "@heroicons/vue/24/solid";

//imagen
import frontEjemplo from "../../../../public/assets/img/cedulaFrontEjemplo.webp";
import backEjemplo from "../../../../public/assets/img/cedulaBackEjemplo.webp";

import * as faceapi from "face-api.js";

const form = useForm({
  nombre: "",
  identificacion: "",
  tipo_documento: "",
  nacimiento: "",
  cedula_front: null,
  genero: "",
  etnia: "",
  condicion: "",
  direccion: "",
  celular: "",
  comuna: "",
  barrio: "",
  email: "",
  password: "",
  embedding: "",
  edad_estimada: null,
  photo: "",
  validaciones: "",
  checked: false,
  declaracion: false,
  recaptcha_token: "",
  campoObligatorio: "",
});

const { executeRecaptcha } = useReCaptcha();

const departamentoSelected = ref("");
const ciudadesxDep = ref([]);
const comunaSelected = ref("");
const barriosXComuna = ref([]);

//fecha nacimiento
const diaNacimiento = ref("");
const mesNacimiento = ref("");
const AnnioNacimiento = ref("");

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

//verificar si el barrio es otro
const isOtroBarrio = ref(false);

//WATCH FECHA NACIMIENTO MOBIL
watch([diaNacimiento, mesNacimiento, AnnioNacimiento], ([dia, mes, annio]) => {
  if (dia && mes && annio) {
    // Formato YYYY-MM-DD
    const diaStr = String(dia).padStart(2, "0");
    const mesStr = String(mes).padStart(2, "0");
    form.nacimiento = `${annio}-${mesStr}-${diaStr}`;
  }
});

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

//WATCH BARRIO -- OTROS
watch(
  () => form.barrio,
  (value) => {
    console.log(value);

    if (value == "Otro") {
      form.barrio = "";
      isOtroBarrio.value = true;
    }
  }
);

// Observa todos los campos del formulario y elimina errores automáticamente
watch(
  () => form.data(),
  (nuevoValor) => {
    Object.keys(form.errors).forEach((campo) => {
      if (nuevoValor[campo]) {
        form.errors[campo] = null;
      }
    });
  },
  { deep: true }
);

async function verificarCamaraONecesaria() {
  if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
    swal.fire({
      icon: "warning",
      title: "Navegador no compatible",
      text: "Tu navegador no permite acceder a los dispositivos multimedia.",
    });
    return;
  }

  try {
    const dispositivos = await navigator.mediaDevices.enumerateDevices();
    const camaras = dispositivos.filter(
      (dispositivo) => dispositivo.kind === "videoinput"
    );

    if (camaras.length === 0) {
      swal.fire({
        icon: "warning",
        title: "Cámara no detectada",
        text: "Es necesario que el dispositivo tenga una cámara para continuar con el proceso.",
      });
    } else {
      console.log("Cámara detectada, puede continuar.");
      // Aquí puede continuar con su proceso
    }
  } catch (error) {
    console.error("Error al verificar dispositivos:", error);
    Swal.fire({
      icon: "error",
      title: "Error al verificar la cámara",
      text: "Ocurrió un problema al intentar detectar la cámara.",
    });
  }
}

const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (!file) return;

  const processImage = (file, callback) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        const maxWidth = 1024;
        const maxHeight = 1024;

        let width = img.width;
        let height = img.height;

        // Redimensionar solo si excede el tamaño máximo
        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            height = maxHeight;
          }
        }

        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0, width, height);

        // Convertir a JPEG comprimido
        canvas.toBlob(
          (blob) => {
            if (blob.size > 2e6) {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Incluso comprimida, la imagen supera los 2MB.",
              });
              return;
            }
            const compressedFile = new File([blob], file.name, {
              type: "image/jpeg",
              lastModified: Date.now(),
            });
            callback(compressedFile, URL.createObjectURL(compressedFile));
          },
          "image/jpeg",
          0.8 // calidad
        );
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  // Procesar y asignar dependiendo del campo
  processImage(file, (finalFile, previewUrl) => {
    form[field] = finalFile;

    if (field === "cedula_front") {
      imageUrl.value = previewUrl;
    }
  });
};

// Eliminar la imagen seleccionada
const removeImage = (num) => {
  if (num === 1) {
    form.cedula_front = null;
    imageUrl.value = null;
  } else if (num === 2) {
    form.cedula_back = null;
    imageUrl1.value = null;
  }
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

    await faceapi.nets.ageGenderNet.loadFromUri("/models/age_gender");

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
    swal.fire({
      icon: "error",
      title: "Error al acceder a la cámara",
      text: "No se pudo acceder a la cámara. Por favor, asegúrese de que su dispositivo tenga una cámara y que haya otorgado los permisos necesarios.",
    });
    console.error("Error al iniciar:", error);
    message.value = "No se pudo acceder a la cámara.";
  }
};

//validar registro biometrico
const registerAndValidate = async () => {
  loadingButtonBiometric.value = true;
  message.value = "";

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
    swal.fire({
      title: "Validación en progreso",
      text: "Mire a la cámara hasta que finalice la validación.",
      icon: "info",
      timer: 1000,
      showConfirmButton: false,
    });
    const detection = await faceapi
      .detectSingleFace(video.value, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor()
      .withAgeAndGender();

    console.log("entro -- validador");

    //capturar foto de rostro
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
    //fin

    if (!detection) {
      loadingButtonBiometric.value = false;
      message.value = "No se detectó un rostro.";

      counterCamera.value += 1;

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
              submit();
            } else if (result.dismiss === swal.DismissReason.cancel) {
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
    const edad = Math.round(detection.age);
    form.edad_estimada = edad;
    const genero = detection.gender;
    console.log(`Edad estimada: ${edad}, Género: ${genero}`);

    if (edad < 14) {
      await swal.fire({
        title: "Advertencia",
        text: `La edad estimada es ${edad.toFixed(
          0
        )} años. Parece menor de la edad permitida para votar.`,
        icon: "warning",
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      await swal.fire({
        title: "Notificación",
        text: `La edad estimada es ${edad.toFixed(0)} años.`,
        icon: "info",
        timer: 2000,
        showConfirmButton: false,
      });
    }

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
                submit();
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
              submit();
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
            submit();
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

  const nacimiento = form.nacimiento;
  const [anioNac, mesNac, diaNac] = nacimiento.split("-").map(Number);

  let edad = hoy.getFullYear() - anioNac;

  const m = hoy.getMonth() - (mesNac - 1);
  console.log(hoy.getMonth());

  console.log(m);

  console.log(hoy.getDate());
  if (m < 0 || (m === 0 && hoy.getDate() < diaNac)) {
    edad--;
  }
  if (edad < 14) {
    errorEdad.value = "Edad minima para participar: 14 años.";
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
    form.nacimiento
  ) {
    console.log(form.nacimiento);

    if (!validateEdad()) return;
    // Limpia mensajes previos
    errorMessage.value = "";

    // Llama al servicio para comprobar la identificación
    try {
      const existe = await checkIdentificacionService(form.identificacion);
      console.log("Identificación existe:", existe);
      if (existe) {
        errorMessage.value = "Tu documento de identidad ya está registrado.";
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
    form.celular &&
    form.comuna &&
    form.barrio &&
    form.direccion &&
    form.password &&
    form.checked &&
    form.declaracion
  ) {
    console.log(form.password.length);

    if (form.password.length >= 8) {
      isValidate.value = true;
      active.value = 2;
    }
  }

  if (isValidate.value) {
  } else {
    if (form.password) {
      form.errors.password =
        "El campo de contraseña debe contener minimo 8 caracteres. " +
        form.password.length +
        "/8 caracteres ingresados";
    }
    if (!form.genero) {
      form.errors.genero = "Este campo es requerido.";
    }
    if (!form.etnia) {
      form.errors.etnia = "Este campo es requerido.";
    }
    if (!form.condicion) {
      form.errors.condicion = "Este campo es requerido.";
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

const validarDatos3 = () => {
  isValidate.value = false;
  if (form.cedula_front) {
    isValidate.value = true;
  }

  if (isValidate.value) {
    showModalBiometrico();
  } else {
    if (!form.cedula_front) {
      form.errors.cedula_front = "Este campo es requerido.";
    }
  }
};

const submit = async () => {
  const recaptchaToken = await executeRecaptcha("register");

  // Agregar el token de reCAPTCHA al formulario
  form.recaptcha_token = recaptchaToken;
  form.post(route("create-user"), {
    onSuccess: () => {
      swal({
        title: "Registro realizado",
        text: "Registro de usuario realizado exitosamente. Para poder votar, se realizará internamente la validación de su información, el estado del trámite será notificado a través del correo electrónico registrado. Recuerde que su usuario es su número de identificación y la contraseña es la que ingresó al momento del registro.",
        icon: "success",
      }).then((result) => {
        window.location.href = route("welcome");
      });
      sessionStorage.removeItem("fallo_camara");
      sessionStorage.removeItem("fallo_registro");
      sessionStorage.removeItem("registro_duplicado");
      form.reset();
      stopCamera();
    },
    onError: (errors) => {
      stopCamera();
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
  try {
    if (video.value && video.value.srcObject) {
      const stream = video.value.srcObject;
      stream.getTracks().forEach((track) => track.stop());
      video.value.srcObject = null;
    }
  } catch (error) {
    console.error("Error al detener la cámara:", error);
  }
};

watch(biometricoModal, (newVal) => {
  if (newVal == false) {
    stopCamera();
  }
});

onMounted(() => {
  verificarCamaraONecesaria();
});
</script>
