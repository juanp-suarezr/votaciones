<template>
  <Head title="Agregar ruta" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Nueva Ruta </template>

    <div class="w-full grid md:grid-cols-3 gap-4">
      <div class="md:col-span-2 p-4 bg-white rounded-md shadow-md h-full">
        <h2 class="text-lg font-bold mb-4">Agregar Nueva Ruta</h2>
        <form @submit.prevent="submit" class="sm:grid sm:grid-cols-2 gap-4">
          <!-- Ruta o Path -->
          <div class="mb-4">
            <label
              for="ruta"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Ruta o Path</label
            >
            <TextInput
              id="ruta"
              type="text"
              class="mt-1 block w-full"
              v-model="form.ruta"
              required
              autofocus
              autocomplete="ruta"
            />
            <InputError class="mt-2" :message="form.errors.ruta" />
          </div>

          <!-- Fecha inicio -->
          <div class="mb-4">
            <label
              for="fecha_inicio"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Fecha inicio</label
            >
            <input
              id="fecha_inicio"
              type="date"
              class="mt-1 block w-full border border-gray-300 rounded-lg p-2"
              v-model="form.fecha_inicio"
              required
            />
            <InputError class="mt-2" :message="form.errors.fecha_inicio" />
          </div>

          <!-- Fecha fin -->
          <div class="mb-4">
            <label
              for="fecha_fin"
              class="block text-sm font-medium text-gray-700 mb-2"
              >Fecha fin</label
            >
            <input
              id="fecha_fin"
              type="date"
              class="mt-1 block w-full border border-gray-300 rounded-lg p-2"
              v-model="form.fecha_fin"
              :min="form.fecha_inicio"
              required
            />
            <InputError class="mt-2" :message="form.errors.fecha_fin" />
            <p v-if="isFechaFinInvalida" class="text-red-500 text-sm mt-2">
              La fecha fin no puede ser menor a la fecha inicio.
            </p>
          </div>

          <!-- Estado -->
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
              :class="{ 'opacity-25': form.processing || isFechaFinInvalida }"
              :disabled="form.processing || isFechaFinInvalida"
            >
              Registrar Ruta
            </PrimaryButton>
          </div>
        </form>
      </div>

      <div
        class="md:col-span-1 flex flex-col items-center h-full p-5 bg-white shadow-sm rounded-md"
      >
        <div class="p-4 md:p-5 w-full border border-gray rounded-md shadow-sm">
          <!-- Resumen de la ruta -->
          <div class="mb-6">
            <h3 class="pb-4">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Ruta
              </span>
            </h3>
            <p class="">{{ form.ruta }}</p>
          </div>
          <div class="mb-8">
            <h3 class="mb-3">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Fechas
              </span>
            </h3>
            <p>Inicio: {{ form.fecha_inicio }}</p>
            <p>Fin: {{ form.fecha_fin }}</p>
          </div>
          <div class="mb-8">
            <h3 class="mb-3">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Estado
              </span>
            </h3>
            <p>
              {{ form.estado === "1" ? "Activo" : "Bloqueado" }}
            </p>
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

const form = useForm({
  ruta: "",
  fecha_inicio: "",
  fecha_fin: "",
  estado: "1",
});

const breadcrumbLinks = [
  { url: "/rutas", text: "Listado de rutas" },
  { url: "", text: "Nueva ruta" },
];

// Validación de fechas
const isFechaFinInvalida = computed(() => {
  return (
    form.fecha_inicio &&
    form.fecha_fin &&
    form.fecha_fin < form.fecha_inicio
  );
});

// Watch para corregir fecha fin si es menor a inicio
watch(
  () => form.fecha_inicio,
  (newInicio) => {
    if (form.fecha_fin && form.fecha_fin < newInicio) {
      form.fecha_fin = "";
    }
  }
);

const submit = () => {
  if (isFechaFinInvalida.value) {
    swal({
      title: "Fechas inválidas",
      text: "La fecha fin no puede ser menor a la fecha inicio.",
      icon: "warning",
    });
    return;
  }
  form.post(route("rutas.store"), {
    onSuccess: () =>
      swal({
        title: "Registro Guardado",
        text: "La ruta se ha registrado exitosamente",
        icon: "success",
      }).then(() => {
        window.location = route("rutas.index");
      }),
    onError: () => {
      swal({
        title: "Error",
        text: "Ocurrió un error al registrar la ruta.",
        icon: "error",
      });
    },
  });
};
</script>
