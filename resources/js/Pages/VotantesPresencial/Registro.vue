<template>
  <Head title="Registro votantes" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Registro de votante </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 border-b border-gray-200">
        <h1>Registro votante</h1>
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
          <!-- indentificacion -->
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
          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-end">
            <button
              type="button"
              class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white p-2 rounded-md shadow-xl"
              @click="nextStep(1)"
              :class="{ 'opacity-25': loading }"
              :disabled="loading"
            >
              Siguiente
            </button>
          </div>
        </div>
        <!-- parte 2 -- informacion -->
        <div class="sm:grid sm:grid-cols-2 gap-6" v-if="active == 1">
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

          <!-- Barrio -->
          <div class="mb-2">
            <InputLabel for="barrio" value="Barrio/Vereda" />
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
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";

import comunas from "@/shared/comunas.json"; // Importa el JSON
import tipo_documento from "@/shared/tipo_documento.json"; // Importa el JSON
import departamentos from "@/shared/departamentos.json"; // Importa el JSON
import ciudades from "@/shared/ciudades.json"; // Importa el JSON
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

const swal = inject("$swal");

const breadcrumbLinks = [{ url: "", text: "Registro votantes" }];

const form = useForm({
  nombre: "",
  identificacion: "",
  tipo_documento: "",
  nacimiento: "",
  comuna: usePage().props.user.jurado.comuna,
  id_jurado: usePage().props.user.jurado.id,
  id_evento: usePage().props.user.jurado.evento.id,
  genero: "",
  etnia: "",
  condicion: "",
  direccion: "",
  barrio: "",
});

const loading = ref(false);

//ITEMS DEL STEP
const items = ref([
  {
    label: "Validación previa",
  },
  {
    label: "Información",
  },
]);

const itemsMobil = ref([
  {
    label: "paso 1",
  },
  {
    label: "paso 2",
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

//SABER SI SE ACTIVA EL INPUT DE OTROS
const IsNewGenero = ref(false);

//si existe y no ha votado
const existeNoVota = ref(false);

//WATCH GENERO
watch(IsNewGenero, (value) => {
  if (value) {
    form.genero = "";
  }
});

const formatDate = (date) => {
  if (!date) return "";
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
  loading.value = true;
  if (form.identificacion && form.tipo_documento) {
    // Limpia mensajes previos
    errorMessage.value = "";

    console.log(usePage().props);

    // Llama al servicio para comprobar la identificación
    try {
      const data = await checkIdentificacionService(form.identificacion);
      console.log("Identificación data:", data);
      loading.value = false;
      if (data.existe) {
        errorMessage.value = "La identificación ya está registrada.";
        if (data.votante && data.votante.votos.length > 0) {
          errorMessage.value +=
            " Y el votante ya ha emitido su voto en las elecciones.";
        } else {
          window.location.href = route("votos.index", {
            id_votante: data.votante.id,
            evento: usePage().props.user.jurado.evento.id,
            tipo_evento: usePage().props.user.jurado.evento.tipos,
            tipo_user:
              data.votante.hash_votantes.length != 0
                ? data.votante.hash_votantes[0].tipo
                : "",
            subtipo_user:
              data.votante.hash_votantes.length != 0
                ? data.votante.hash_votantes[0].subtipo
                : "",
          });

          return;
        }
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
    loading.value = false;
    if (!form.identificacion || errorMessage.value != "") {
      form.errors.identificacion =
        errorMessage.value || "Este campo es requerido.";
    }
    if (!form.tipo_documento) {
      form.errors.tipo_documento = "Este campo es requerido.";
    }
  }
};

//llamado validador identificacion
const checkIdentificacionService = async (identificacion) => {
  const response = await axios.post("/validar-identificacion-presencial", {
    identificacion,
    registro_presencial: true, // Indica que es un registro presencial
  });
  console.log(response.data);
  return response.data; // true si existe, false si no
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
    form.nombre &&
    form.nacimiento &&
    form.genero &&
    form.etnia &&
    form.condicion &&
    form.barrio &&
    form.direccion
  ) {
    isValidate.value = true;
    if (!validateEdad()) return;
    submit();
  }

  if (isValidate.value) {
  } else {
    if (!form.nombre) {
      form.errors.nombre = "Este campo es requerido.";
    }
    if (!form.nacimiento) {
      form.errors.nacimiento = "Este campo es requerido.";
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

    if (!form.barrio) {
      form.errors.barrio = "Este campo es requerido.";
    }
    if (!form.direccion) {
      form.errors.direccion = "Este campo es requerido.";
    }
  }
};

const submit = () => {
  form.post(route("votantesPresencial.store"), {
    onSuccess: () => {
      swal({
        title: "Registro realizado",
        text: "Registro de votante realizado exitosamente.",
        icon: "success",
      }).then((result) => {
        console.log(result);

        window.location.href = route("votos.index", {
          id_votante: usePage().props.flash.success.id_votante,
          evento: usePage().props.user.jurado.evento.id,
          tipo_evento: usePage().props.user.jurado.evento.tipos,
          tipo_user:
            usePage().props.flash.success.hash_votante.tipo,
          subtipo_user:
            usePage().props.flash.success.hash_votante.subtipo,
        });
      });
    },
    onError: (errors) => {
      swal({
        title: "Error",
        text: "Ocurrió un error al registrar el votante.",
        icon: "error",
      }).then((result) => {
        active.value = 0;
      });

      console.error(errors); // Puedes ver los errores específicos aquí
    },
  });
};
</script>
