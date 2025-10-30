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
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON

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
  email: "",
  password: "",

  jurados: "",
});


const submit = () => {
  console.log(form);
  form.comuna = form.comuna.value; // Asegúrate de que comuna sea un string
  form.punto_votacion = form.punto_votacion.id; // Aseg

  form.post(route("registro-jurados.store"), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();

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
