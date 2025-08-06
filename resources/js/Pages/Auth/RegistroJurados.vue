<template>
  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <Head title="Registro de jurados" />

    <div
      class="w-full h-full sm:grid sm:grid-cols-2 mx-auto bg-white shadow-lg rounded-lg px-4 sm:px-6 py-12 z-10 gap-6"
    >
      <div class="col-span-2 w-full">
        <h1 class="text-2xl font-bold text-center text-gray-800">
          Registro Jurados
        </h1>
        <SecondaryLink class="text-right" :href="route('delegados.index')">
          Regresar
        </SecondaryLink>
      </div>
      <!-- manual -->
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
          <InputLabel
            for="comuna"
            value="Comuna/corregimiento correspondiente"
          />
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

        <!-- Correo -->
        <div class="mb-2">
          <InputLabel for="email" value="Correo Electrónico" />
          <TextInput
            id="email"
            type="email"
            class="mt-1 block"
            v-model="form.email"
            required
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- aviso contraseña -->
        <div class="mb-2 col-span-2">
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



        <div class="mt-4 flex flex-col items-end">
          <PrimaryButton
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Registrarse
          </PrimaryButton>
        </div>
      </form>
      <!-- cargue masivo -->
      <!-- cargue -->
      <div class="w-full bg-white border shadow-sm rounded-xl">
        <div
          class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 flex"
        >
          <h3 class="text-gray-500 m-auto">CARGUE MASIVO</h3>
        </div>

        <div class="w-full h-full flex px-4">
          <div class="mt-6 w-full">
            <p class="">
              <b>Nota:</b> Para subir masivamente la lista de jurados, debe
              descargar la plantilla, dando clic en el siguiente botón.
            </p>

            <a
              :href="route('plantillaJur.excel')"
              class="flex inline-flex text-white bg-green-800 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 mt-4"
            >
              <i class="fa-solid fa-file-arrow-down m-auto me-2"></i>Descargar
              plantilla
            </a>

            <p class="mt-4">
              Luego de descargar la plantilla y cargar los datos, subir el mismo
              archivo excel en el apartado de abajo.
            </p>

            <form @submit.prevent="cargueMasivo">
              <!-- evento -->
              <div class="mt-2">
                <InputLabel for="id_evento" value="Asignar Evento" />
                <select
                  id="id_evento"
                  v-model="form.id_evento"
                  class="block mt-1 w-full rounded-md form-select focus:border-sky-600"
                >
                  <option value="" disabled selected>
                    Seleccione un evento
                  </option>
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

              <TextInput
                id="cargueMasivoJurados"
                type="file"
                class="mt-4 block w-full"
                @change="validateFile($event)"
              />
              <InputError class="mt-2" :message="form.errors.jurados" />

              <!-- boton registrar -->
              <div>
                <div class="mt-12 flex flex-col items-end">
                  <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Enviar
                  </PrimaryButton>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
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
import SecondaryLink from "@/Components/SecondaryLink.vue";

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

// Breadcrumb
const breadcrumbLinks = [
  { url: route("delegados.index"), text: "Gestion de jurados" },
  { url: "", text: "Añadir Jurado" },
];

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

  jurados: "",
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
          window.history.back;
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

const validateFile = (event) => {
  const file = event.target.files[0];

  // Validar si se ha seleccionado un archivo
  if (!file) {
    form.errors.jurados = "Por favor, selecciona un archivo.";
    return;
  }

  // Validar el tamaño del archivo (en bytes)
  if (file.size > 2 * 1024 * 1024) {
    // 2 MB en bytes
    form.errors.jurados = "El archivo no debe ser mayor a 2 MB.";
    return;
  }

  // Reiniciar errores si la validación es exitosa
  form.errors.jurados = null;
  // Asignar el archivo al modelo de datos
  form.jurados = file;
  console.log(form.jurados);
};

const cargueMasivo = () => {
  form.post(route("cargueJurados"), {
    forceFormData: true,
    onSuccess: () => {
      // Verificar si la propiedad 'success' está presente en la respuesta
      if (
        props.numRegistrosInsertados !== undefined &&
        props.numRegistrosActualizados !== undefined
      ) {
        swal({
          title: "Registros Cargados",
          text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y ${props.numRegistrosActualizados} actualizados`,
          icon: "success",
        });
      } else if (
        props.numRegistrosInsertados !== undefined &&
        props.numRegistrosActualizados == undefined
      ) {
        // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
        swal({
          title: "Registros Cargados",
          text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y 0 actualizados`,
          icon: "success",
        });
      } else if (
        props.numRegistrosInsertados == undefined &&
        props.numRegistrosActualizados !== undefined
      ) {
        // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
        swal({
          title: "Registros Cargados",
          text: `Se han importado 0 nuevos registros correctamente y ${props.numRegistrosActualizados} actualizados`,
          icon: "success",
        });
      } else {
        swal({
          title: "Registros Cargados",
          text: "Se han importado los registros de forma masiva",
          icon: "success",
        });
      }
    },
    // Manejar el error en caso de que ocurra algún problema con la solicitud
    onError: (error) => {
      swal({
        title: "Error",
        text: "Hubo un problema al procesar la solicitud.",
        icon: "error",
      });
    },
  });
};

</script>
