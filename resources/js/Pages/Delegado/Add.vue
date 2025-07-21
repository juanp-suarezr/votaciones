<template>
  <Head title="Agregar delegado" />

  <AuthenticatedLayout>
    <template #header> Nuevo delegado </template>

    <div class="px-4 mx-auto bg-white border shadow-sm rounded-xl mt-8">
      <div
        class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 flex justify-between items-center"
      >
        <h3 class="text-gray-500">Nuevo delegado</h3>
        <SecondaryLink :href="route('delegados.index')">
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
            <InputLabel for="identificacion" value="Identificación" />
            <TextInput
              id="identificacion"
              type="text"
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
            <InputLabel for="contacto" value="Contacto" />
            <TextInput
              id="contacto"
              type="text"
              class="mt-1 block w-full"
              v-model="form.contacto"
              required
              autofocus
              autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.contacto" />
          </div>
          <!-- cargo -->
          <div class="mb-2">
            <InputLabel for="cargo" value="Cargo" />
            <TextInput
              id="cargo"
              type="text"
              class="mt-1 block w-full"
              v-model="form.cargo"
              required
              autocomplete="off"
            />
            <InputError class="mt-2" :message="form.errors.cargo" />
          </div>
          <!-- tipo -->
          <div class="mb-2">
            <InputLabel for="tipo" value="Tipo" />
            <select
              id="tipo"
              v-model="form.tipo"
              class="block mt-1 w-full rounded-md form-select focus:border-sky-600"
            >
              <option value="" disabled selected>Seleccione un tipo</option>
              <option value="secretario">Secretario</option>
              <option value="jurado">Jurado</option>
            </select>
            <InputError class="mt-2" :message="form.errors.tipo" />
          </div>
          <!-- comuna -->
          <div class="mb-2">
            <InputLabel for="comuna" value="Comuna correspondiente" />
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
                width="200"
                height="100"
                class="border border-gray-400 rounded-md bg-white cursor-crosshair"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
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
              Registrar Delegado
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

const firmaPreview = ref(null);
const firmaModo = ref("imagen"); // 'imagen' o 'canvas'
const canvas = ref(null);
let drawing = false;

const props = defineProps({
  eventos: {
    type: Object,
    default: () => ({}),
  },
});

console.log(props);


const form = useForm({
  nombre: "",
  identificacion: "",
  contacto: "",
  cargo: "",
  tipo: "",
  comuna: "",
  id_evento: "",
  firma: null,
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

// Canvas firma
const startDrawing = (e) => {
  drawing = true;
  const ctx = canvas.value.getContext("2d");
  ctx.beginPath();
  ctx.moveTo(e.offsetX, e.offsetY);
};
const draw = (e) => {
  if (!drawing) return;
  const ctx = canvas.value.getContext("2d");
  ctx.lineTo(e.offsetX, e.offsetY);
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

const submit = () => {
  form.post(route("delegados.store"), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      firmaPreview.value = null;
      clearCanvas();
    },
  });
};
</script>
