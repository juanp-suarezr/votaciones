<template>
  <Head title="Editar proyecto" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Editar Proyecto {{ proyecto.id }} </template>

    <div class="flex flex-col bg-white border shadow-sm rounded-xl w-full">
      <div
        class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 grid grid-cols-2 gap-4"
      >
        <h3 class="mt-1 text-gray-500">Nuevo proyecto</h3>
        <div class="text-right">
          <SecondaryLink :href="route('proyectos.index')">
            Regresar
          </SecondaryLink>
        </div>
      </div>
      <div class="p-4 md:p-5">
        <form @submit.prevent="submit" class="grid sm:grid-cols-2 gap-4">
          <!-- nombre -->
          <div class="mb-2">
            <InputLabel for="nombre" value="Nombre del proyecto" />
            <TextInput
              id="nombre"
              type="text"
              class="mt-1 block w-full"
              v-model="form.detalle"
              required
              autofocus
              autocomplete="nombre"
            />
            <InputError class="mt-2" :message="form.errors.detalle" />
          </div>

          <!-- numero tarjeton -->
          <div class="mb-2">
            <InputLabel for="numero_tarjeton" value="Numero tarjeton" />
            <TextInput
              id="numero_tarjeton"
              type="number"
              class="mt-1 block w-full"
              v-model="form.numero_tarjeton"
              autocomplete="numero_tarjeton"
            />
            <InputError class="mt-2" :message="form.errors.numero_tarjeton" />
          </div>

          <!-- descripción -->
          <div class="col-span-2 mb-2">
            <p class="text-gray-600 text-sm">
              Información adicional sobre el evento (max 500 caracteres)
            </p>
            <Textarea
              v-model="form.descripcion"
              variant="filled"
              autoResize
              rows="5"
              class="mt-1 block w-full"
              required
              autocomplete="descripcion"
            />
            <p
              class="text-gray-500 text-sm mt-2"
              :class="{ 'text-red-500': isMaxPalabras }"
            >
              {{ palabrasEnTestimonio }} caracteres
              <span v-if="isMaxPalabras">Limite de caracteres superado</span>
            </p>
            <InputError class="mt-2" :message="form.errors.descripcion" />
          </div>

          <!-- tipos, eventos, parametros y subtipos -->
          <div class="col-span-2 flex md:grid grid-cols-2 flex-wrap gap-4">
            <!-- tipo - eventos -->
            <div class="w-auto">
              <!-- tipo proyecto -->
              <div class="w-auto">
                <InputLabel for="tipos" value="Tipos de usuarios" />

                <div
                  class="card flex inline-flex justify-content-center border border-gray-400 rounded-md shadow-md py-1 px-2 mt-2 !text-gray-500"
                >
                  {{ form.tipo }}
                </div>
                <InputError class="mt-2" :message="form.errors.tipos" />
              </div>

              <!-- eventos -->
              <div class="w-auto mt-4">
                <InputLabel for="evento" value="Asignar evento" />
                <div
                  v-if="eventos.length"
                  class="card flex justify-content-center"
                >
                  <MultiSelect
                    id="eventos"
                    v-model="form.eventos"
                    display="chip"
                    :options="eventos"
                    option-label="nombre"
                    option-value="id"
                    placeholder="Seleccione los eventos"
                    :maxSelectedLabels="6"
                    class="w-full md:w-20rem"
                  />
                </div>
                <p v-else class="text-xs text-gray-600">
                  No hay eventos registrados, registrar uno nuevo
                  <a :href="route('eventos.create')">Aquí</a>
                </p>
                <InputError class="mt-2" :message="form.errors.eventos" />
              </div>
            </div>

            <!-- parametros y subtipos -->
            <div class="w-auto">
              <!-- Parámetro y Detalle -->
              <div class="w-full h-full mt-2">
                <div class="flex flex-wrap items-center w-auto gap-4">
                  <InputLabel
                    for="tipo_user"
                    :value="'Subtipos - ' + ObtenerSubtipo()"
                  />
                  <SecondaryButton
                    @click="isSubtipo = !isSubtipo"
                    class="items-center"
                  >
                    {{ isSubtipo ? "Cancelar" : "Agregar subtipo" }}
                  </SecondaryButton>
                </div>

                <div
                  v-if="isSubtipo"
                  class="px-2 py-4 w-full border border-gray-400 rounded-md shadow-md mt-4"
                >
                  <!-- Parámetro -->
                  <div>
                    <InputLabel for="parametro" value="Parámetro" />
                    <Dropdown
                      id="parametro"
                      v-model="selectedParametro"
                      :options="parametros"
                      option-label="detalle"
                      option-value="cod"
                      placeholder="Seleccione un parámetro"
                      class="w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.parametro" />
                  </div>

                  <!-- Detalle del Parámetro -->
                  <div class="mt-4">
                    <InputLabel for="parametroDetalle" value="Subtipo" />
                    <Select
                      id="parametroDetalle"
                      v-model="form.subtipo"
                      :options="filteredDetalles"
                      filter
                      optionLabel="detalle"
                      placeholder="Seleccione un subtipo"
                      checkmark
                      :highlightOnSelect="false"
                      class="w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.subtipo" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- estado -->
          <div class="mb-2">
            <InputLabel for="estado" value="Estado" />

            <select
              id="estado"
              v-model="form.estado"
              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2. px-4"
            >
              <option value="1">Activo</option>
              <option value="0">Bloqueado</option>
            </select>
            <InputError class="mt-2" :message="form.errors.estado" />
          </div>

          <!-- Imagen -->
          <div class="col-span-2">
            <p class="text-gray-600 text-sm">
              Imagen de proyecto (resolución recomendada: 1080x1080px)
            </p>
            <div class="border-2 border-gray-300 rounded-md p-2">
              <!-- Mostrar la imagen actual si existe -->
              <div class="flex justify-center">
                <img
                  v-if="imageUrl || props.proyecto.imagen"
                  :src="imageUrl || getImageUrl(props.proyecto.imagen)"
                  :alt="form.detalle"
                  class="w-2/6 h-auto"
                />
                <PhotoIcon v-else class="w-1/6 text-gray-300" />
              </div>

              <!-- Botón para cambiar la imagen -->
              <div class="mt-4">
                <label
                  for="imagen"
                  class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer"
                >
                  Cambiar Imagen
                </label>
                <TextInput
                  id="imagen"
                  type="file"
                  class="hidden"
                  accept="image/*"
                  @input="onAdvancedUpload($event.target.files[0])"
                />
                <InputError class="mt-2" :message="form.errors.imagen" />
              </div>

              <!-- Botón para eliminar la imagen -->
              <div
                v-if="imageUrl || props.proyecto.imagen"
                class="flex justify-center mt-2"
              >
                <button
                  @click="removeImage"
                  type="button"
                  class="bg-red-500 text-white px-4 py-2 rounded"
                >
                  Eliminar Imagen
                </button>
              </div>
            </div>
          </div>

          <div>
            <div class="mt-4 flex flex-col items-end">
              <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Editar proyecto
              </PrimaryButton>
            </div>
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
import { Head, Link, useForm } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import MultiSelect from "primevue/multiselect";
import { inject, ref, computed, watch } from "vue";
import { PhotoIcon } from "@heroicons/vue/24/solid";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import Select from "primevue/select";
import Dropdown from "primevue/dropdown";

const swal = inject("$swal");

//variable de imagen
const imageUrl = ref(null);
//max palabras
const isMaxPalabras = ref(false);

const props = defineProps({
  proyecto: {
    type: Object,
    default: () => ({}),
  },
  tipos: {
    type: Object,
    default: () => ({}),
  },
  eventos: {
    type: Object,
    default: () => ({}),
  },
  parametros: {
    type: Array,
    default: () => [],
  },
  subtipos: {
    type: Array,
    default: () => [],
  },
});

console.log(props.proyecto);

//para eventos seleccionados
const eventosBD =
  props.proyecto.eventos !== "NA"
    ? props.proyecto.eventos.split("|").map((id) => parseInt(id))
    : [];

const form = useForm({
  detalle: props.proyecto.detalle || "",
  descripcion: props.proyecto.descripcion || "",
  numero_tarjeton: props.proyecto.numero_tarjeton || "",
  tipo: props.proyecto.tipo || "",
  eventos: eventosBD || [],
  subtipo: props.proyecto.subtipo || "",
  tipo: props.proyecto.tipo,
  estado: props.proyecto.estado || "",
  imagen: props.proyecto.imagen,
});

const selectedParametro = ref(null);

//por default o cambiar
const isSubtipo = ref(false);

const breadcrumbLinks = [
  { url: "/proyectos", text: "Listado de proyectos" },
  { url: "", text: "Editar proyecto" },
];

const ObtenerSubtipo = () => {
  if (form.subtipo == 0) {
    return "por default";
  } else if (typeof form.subtipo === "object") {
    return form.subtipo.detalle;
  } else {
    return props.subtipos.find(
      (element) => element.id === parseInt(form.subtipo)
    )?.detalle;
  }
};

const filteredDetalles = computed(() => {
  if (!selectedParametro.value) {
    return [];
  }

  return props.subtipos.filter(
    (detalle) => detalle.codParametro === selectedParametro.value
  );
});

watch(isSubtipo, (newValue) => {
  if (!newValue) {
    form.subtipo = 0; // Reiniciar subtipo al abrir el dropdown
  }
});

// Limite palabras
const palabrasEnTestimonio = computed(() => {
  const palabras = form.descripcion.trim();
  const result = palabras.length;

  if (result >= 500) {
    isMaxPalabras.value = true;
  } else {
    isMaxPalabras.value = false;
  }

  return result;
});

watch(palabrasEnTestimonio, () => {
  form.descripcion = form.descripcion.trim(); // Asegúrate de que no haya espacios al principio o al final
});

//IMAGEN
const getImageUrl = (imageName) => {
  // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
  return `/storage/uploads/proyectos/${imageName}`;
};

//cuando suba la img
const onAdvancedUpload = (ev) => {
  form.imagen = ev;
  console.log(form.imagen);

  if (!form.imagen) return;

  //reader para mostrar el doc cargado
  const reader = new FileReader();
  reader.onload = () => {
    imageUrl.value = reader.result;
  };
  reader.readAsDataURL(form.imagen);
};

// Eliminar la imagen seleccionada
const removeImage = () => {
  form.imagen = null;
  imageUrl.value = null;
};

const submit = () => {
  console.log(form);

  form.post(route("proyectoUpdate", { id: props.proyecto.id }), {
    forceFormData: true,
    onError: () =>
      swal({
        title: "Error en el registro",
        text: "Por favor verifique los datos ingresados",
        icon: "error",
      }),
    onSuccess: () =>
      swal({
        title: "Registro Actualizado",
        text: "El proyecto se ha actualizado exitosamente",
        icon: "success",
      }).then((result) => {
        window.location.reload();
      }),
  });
};
</script>
