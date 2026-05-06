<template>
  <Head title="Agregar Funcionario" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Nuevo Funcionario </template>

    <div class="px-4 mx-auto bg-white border shadow-sm rounded-xl mt-8">
      <div
        class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 flex justify-between items-center"
      >
        <h3 class="text-gray-500">Nuevo Funcionario</h3>
        <SecondaryLink :href="route('funcionarios.index')">
          Regresar
        </SecondaryLink>
      </div>
      <div class="p-4 md:p-5">
        <form
          @submit.prevent="submit"
          enctype="multipart/form-data"
          class="sm:grid sm:grid-cols-2 gap-4"
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
            <InputLabel for="identificacion" value="Identificación(*)" />
            <TextInput
              id="identificacion"
              type="text"
              class="mt-1 block w-full"
              v-model="form.identificacion"
              required
              autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.identificacion" />
          </div>

          <!-- area -->
          <div class="mb-2">
            <InputLabel for="area" value="Área(*)" />
            <TextInput
              id="area"
              type="text"
              class="mt-1 block w-full"
              v-model="form.area"
              required
              autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.area" />
          </div>
          <!-- grupo_sanguineo -->
          <div class="mb-2">
            <InputLabel for="grupo_sanguineo" value="Grupo Sanguíneo(*)" />
            <select
              id="grupo_sanguineo"
              v-model="form.grupo_sanguineo"
              class="block mt-1 w-full rounded-md form-select focus:border-sky-600"
            >
              <option value="" disabled selected>Seleccione un grupo sanguíneo</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
            </select>
            <InputError class="mt-2" :message="form.errors.grupo_sanguineo" />
          </div>

          <!-- foto -->
          <div>
            <InputLabel for="foto" value="Foto" />
            <TextInput
              id="foto"
              type="file"
              class="mt-1 block w-full"
              accept="image/*"
              @change="onFileChange"
            />
            <InputError class="mt-2" :message="form.errors.foto" />
            <div v-if="fotoPreview" class="mt-2">
              <img
                :src="fotoPreview"
                alt="Vista previa de la foto"
                class="h-24 border rounded"
              />
            </div>
          </div>
          <div class="mt-4 flex flex-col items-end">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Registrar Funcionario
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import { ref } from "vue";
import comunas from "@/shared/comunas.json";
import Select from "primevue/select";
import { IdentificationIcon } from "@heroicons/vue/24/solid";

const fotoPreview = ref(null);

const breadcrumbLinks = [
  { url: route("funcionarios.index"), text: "Gestión de Funcionarios" },
  { url: "", text: "Añadir Funcionario" },
];

const form = useForm({
  nombre: "",
  identificacion: "",
  area: "",
  grupo_sanguineo: "",
  foto: null,
});

const onFileChange = (e) => {
  const file = e.target.files[0];
  form.foto = file;
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      fotoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    fotoPreview.value = null;
  }
};



const submit = () => {
  form.post(route("funcionarios.store"), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      fotoPreview.value = null;
    },
  });
};
</script>
