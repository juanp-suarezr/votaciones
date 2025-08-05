<template>
  <AuthenticatedLayout>
    <Head title="Registro de jurados" />

    <div
      class="w-full sm:max-w-6xl h-full mx-auto bg-white shadow-lg rounded-lg px-4 sm:px-6 py-12 z-10"
    >
      <h1 class="text-2xl font-bold text-center text-gray-800">
        Registro Jurados
      </h1>

      <form
        @submit.prevent="submit"
        enctype="multipart/form-data"
        class="sm:grid sm:grid-cols-2 gap-4 mt-6"
      >
        <!-- nombre -->
        <div class="mb-2">
          <InputLabel for="nombre" value="Nombre(*)" />
          <TextInput
            id="nombre"
            type="text"
            class="mt-1 block w-full"
            v-model="form.nombre"
            required
            autofocus
            autocomplete="off"
          />
          <InputError class="mt-2" :message="form.errors.nombre" />
        </div>
        <!-- identificacion -->
        <div class="mb-2">
          <InputLabel for="identificacion" value="Identificación" />
          <TextInput
            id="identificacion"
            type="number"
            class="mt-1 block w-full"
            v-model="form.identificacion"
            required
            autofocus
            autocomplete="off"
          />
          <InputError class="mt-2" :message="form.errors.identificacion" />
        </div>
        <!-- contacto -->
        <div class="mb-2">
          <InputLabel for="contacto" value="Numero de contacto" />
          <TextInput
            id="contacto"
            type="number"
            class="mt-1 block w-full"
            v-model="form.contacto"
            required
            autofocus
            autocomplete="off"
          />
          <InputError class="mt-2" :message="form.errors.contacto" />
        </div>

        <!-- comuna -->
        <div class="mb-2">
          <InputLabel for="comuna" value="Comuna/corregimiento correspondiente" />
          <Select
            id="comuna"
            v-model="form.comuna"
            :options="comunas"
            filter
            optionLabel="label"
            placeholder="Seleccione comuna"
            checkmark
            :highlightOnSelect="false"
            class="w-full"
          />
          <InputError class="mt-2" :message="form.errors.comuna" />
        </div>
        <!-- punto de votación -->
        <div class="mb-2">
          <InputLabel for="punto_votacion" value="Punto de votación" />
          <Select
            id="punto_votacion"
            v-model="form.punto_votacion"
            :options="puntos_votacion"
            filter
            optionLabel="detalle"
            placeholder="Seleccione un punto de votación"
            checkmark
            :highlightOnSelect="false"
            class="w-full"
          />
          <InputError class="mt-2" :message="form.errors.punto_votacion" />
        </div>
        <!-- evento -->
        <div class="mb-2">
          <InputLabel for="id_evento" value="Asignar Evento" />
          <select
            id="id_evento"
            v-model="form.id_evento"
            class="block mt-1 w-full rounded-md form-select focus:border-sky-600"
          >
            <option value="" disabled selected>Seleccione un evento</option>
            <option
              v-for="evento in eventos"
              :key="evento.id"
              :value="evento.id"
            >
              {{ evento.nombre }}
            </option>
          </select>
          <InputError class="mt-2" :message="form.errors.id_evento" />
        </div>
        <!-- codigo verificación -->
        <div class="mb-2">
          <InputLabel
            for="codigo_verificacion"
            value="Código de verificación"
          />
          <TextInput
            id="codigo_verificacion"
            type="text"
            class="mt-1 block w-full"
            v-model="form.codigo_verificacion"
            required
            autocomplete="off"
          />
          <InputError class="mt-2" :message="form.errors.codigo_verificacion" />
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

        <!-- aviso contraseña -->
        <div class="mb-2">
          <div class="bg-azul rounded-md p-2 w-full h-full">
            <p class="text-white text-base">
              Asegúrese de que la contraseña sea facil de recordar para usted,
              ya que la necesitará para acceder a su cuenta en el momento de la
              votación.
            </p>
          </div>
        </div>
        <!-- Contraseña -->
        <div class="mb-2">
          <InputLabel
            for="password"
            value="Contraseña (mínimo: 8 caracteres)"
          />
          <Password id="password" v-model="form.password" required toggleMask />

          <InputError class="mt-2" :message="form.errors.password" />
        </div>
        <!-- firma -->
        <div>
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
              @change="onFileChange"
            />
            <InputError class="mt-2" :message="form.errors.firma" />
            <div v-if="firmaPreview" class="mt-2">
              <img
                :src="firmaPreview"
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
                class="text-xl text-blue-600 underline"
              >
                Limpiar
              </button>
              <button
                type="button"
                @click="saveCanvas"
                class="text-xl text-green-600 underline"
              >
                Usar firma
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
            >Manifiesto que acepto la
            <a
              href="https://www.pereira.gov.co/loader.php?lServicio=Tools2&lTipo=descargas&lFuncion=visorpdf&file=https%3A%2F%2Fwww.pereira.gov.co%2Floader.php%3FlServicio%3DTools2%26lTipo%3Ddescargas%26lFuncion%3DexposeDocument%26idFile%3D211273%26tmp%3De1c59e50ed23a13cda9087f627ac4f4d%26urlDeleteFunction%3Dhttps%253A%252F%252Fwww.pereira.gov.co%252Floader.php%253FlServicio%253DTools2%2526lTipo%253Ddescargas%2526lFuncion%253DdeleteTemporalFile%2526tmp%253De1c59e50ed23a13cda9087f627ac4f4d&pdf=1&tmp=e1c59e50ed23a13cda9087f627ac4f4d&fileItem=211273"
              target="_blank"
              class="underline !text-azul cursor-pointer"
              >Declaración</a
            >
            juramentada</label
          >
          <InputError class="mt-1" :message="form.errors.declaracion" />
        </div>

        <div class="mt-4 flex flex-col items-end">
          <PrimaryButton
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Registrarse
          </PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
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
import Select from "primevue/select";
import Password from "primevue/password";
import tipo_documento from "@/shared/tipo_documento.json"; // Importa el JSON
import comunas from "@/shared/comunas.json"; // Importa el JSON

import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";
import axios from "axios";
const swal = inject("$swal");
import { useReCaptcha } from "vue-recaptcha-v3";
import { PhotoIcon } from "@heroicons/vue/24/solid";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const firmaPreview = ref(null);
const firmaModo = ref("imagen"); // 'imagen' o 'canvas'
const canvas = ref(null);
let drawing = false;

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  puntos_votacion: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  nombre: "",
  identificacion: "",
  contacto: "",
  comuna: "",
  punto_votacion: "",
  id_evento: "",
  tipo: "jurado",
  firma: null,
  email: "",
  password: "",
  codigo_verificacion: "",
  checked: false,
  declaracion: false,
});

const onFileChange = (e) => {
  const file = e.target.files[0];
  form.firma = file;
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      firmaPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    firmaPreview.value = null;
  }
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

const submit = () => {
  console.log(form);
  form.comuna = form.comuna.value; // Asegúrate de que comuna sea un string
  form.punto_votacion = form.punto_votacion.id; // Aseg

  form.post(route("jurados.store"), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      firmaPreview.value = null;
      clearCanvas();
      swal
        .fire({
          title: "Registro exitoso",
          text: "El jurado ha sido registrado correctamente.",
          icon: "success",
          confirmButtonText: "Aceptar",
        })
        .then((result) => {
          window.location.href = route("welcome");
        });
    },
    onError: (errors) => {
      console.error(errors);
      swal.fire({
        title: "Error",
        text: "Hubo un problema al registrar el jurado. Por favor, revisa los campos e intenta nuevamente.",
        icon: "error",
        confirmButtonText: "Aceptar",
      });
    },
  });
};
</script>
