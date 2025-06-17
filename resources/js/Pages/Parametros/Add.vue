<template>
  <Head title="Agregar parámetro" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Nuevo Parámetro </template>

    <div class="w-full grid md:grid-cols-3 gap-4">
      <div class="md:col-span-2 p-4 bg-white rounded-md shadow-md h-full">
        <h2 class="text-lg font-bold mb-4">Agregar Nuevo Parámetro</h2>
        <form @submit.prevent="submit" class="sm:grid sm:grid-cols-2 gap-4">
          <!-- nombre -->
          <div class="mb-4">
            <label
              for="detalle"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Nombre del parámetro</label
            >
            <TextInput
              id="detalle"
              type="text"
              class="mt-1 block w-full"
              v-model="form.detalle"
              required
              autofocus
              autocomplete="nombre"
            />
            <p
              class="text-gray-500 text-sm mt-2"
              :class="{ 'text-red-500': isMaxPalabras }"
            >
              {{ palabrasEnTestimonio }} caracteres
              <span v-if="isMaxPalabras">Límite de caracteres superado</span>
            </p>
            <InputError class="mt-2" :message="form.errors.detalle" />
          </div>

          <!-- código -->
          <div class="mb-4">
            <label
              for="cod"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Código del parámetro</label
            >
            <TextInput
              id="cod"
              type="text"
              class="mt-1 block w-full"
              v-model="form.cod"
              required
              autocomplete="cod"
            />
            <InputError class="mt-2" :message="form.errors.cod" />
          </div>

          <!-- estado -->
          <div class="mb-4">
            <label
              for="estado"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Estado</label
            >
            <select
              id="estado"
              v-model="form.estado"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
            >
              <option value="1">Activo</option>
              <option value="0">Bloqueado</option>
            </select>
            <InputError class="mt-2" :message="form.errors.estado" />
          </div>

          <div class="mt-4 flex flex-col items-end">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing || isMaxPalabras || isExisting"
            >
              Registrar Parámetro
            </PrimaryButton>
          </div>
        </form>
      </div>

      <div
        class="md:col-span-1 flex flex-col items-center h-full p-5 bg-white shadow-sm rounded-md"
      >
        <div class="p-4 md:p-5 w-full border border-gray rounded-md shadow-sm">
          <!-- Contenido adicional -->
          <!-- nombre parámetro -->
          <div class="mb-6">
            <h3 class="pb-4">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Parámetro
              </span>
            </h3>
            <p class="">{{ form.detalle }}</p>
          </div>
          <!-- código -->
          <div class="mb-8">
            <h3 class="mb-3">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Código
              </span>
            </h3>
            <p>{{ form.cod }}</p>
          </div>
          <!-- mensaje de error -->
          <div v-if="isExisting">
            <em class="bg-red-600 font-bold text-white p-2">
              {{ validationCode }}
            </em>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, watch, inject } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const swal = inject("$swal");

const props = defineProps({
  codigos: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  detalle: "",
  cod: "",
  estado: "1",
});

const breadcrumbLinks = [
  { url: "/parametros", text: "Listado de parámetros" },
  { url: "", text: "Nuevo parámetro" },
];

//VARIABLES
const isMaxPalabras = ref(false);
const isExisting = ref(false);
const validationCode = ref("");

//FUNCIONES
const palabrasEnTestimonio = computed(() => {
  const palabras = form.detalle.trim();
  const result = palabras.length;

  if (result >= 100) {
    isMaxPalabras.value = true;
  } else {
    isMaxPalabras.value = false;
  }

  return result;
});

watch(
  () => form.detalle,
  () => {
    form.detalle = form.detalle.trim();
  }
);

// Función para validar el código
const validateCode = () => {
  isExisting.value = false;
  const currentCode = form.cod;
  const existingCode = props.codigos.find((item) => item.cod === currentCode);

  if (existingCode) {
    validationCode.value = "El código ya existe.";
    isExisting.value = true;
  } else {
    validationCode.value = "";
    isExisting.value = false;
  }
};

// Watch para observar cambios en form.cod
watch(
  () => form.cod,
  (newVal) => {
    if (newVal) {
      validateCode();
    }
  }
);

const submit = () => {
  if (isExisting.value) {
    return;
  }
  form.post(route("parametros.store"), {
    onSuccess: () =>
      swal({
        title: "Registro Guardado",
        text: "El parámetro se ha registrado exitosamente",
        icon: "success",
      }).then((result) => {
        window.location = route("parametros.index");
      }),
    onError: (errors) => {
      swal({
        title: "Error",
        text: "Ocurrió un error al registrar el parámetro.",
        icon: "error",
      });
    },
  });
};
</script>
