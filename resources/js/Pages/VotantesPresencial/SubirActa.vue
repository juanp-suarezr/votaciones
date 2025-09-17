<template>
  <Head title="Carga de acta" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Carga acta de escrutinio y registros </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 border-b border-gray-200">
        <h1 class="sm:text-xl text-lg font-bold">Proceso de votación presencial física - {{ eventos_hijos_vigentes[0].nombre }}</h1>
        <p class="mt-2">
            {{ faltan_actas }} actas por enviar
        </p>
      </div>

      <!-- contenido en grid -->
      <form @submit.prevent="submit" class="md:grid md:grid-cols-2 gap-4 p-6">
        <!-- contenido 1 -->
        <div class="bg-white p-4 rounded-lg shadow h-auto">
          <h2 class="text-lg font-semibold mb-4">Subir acta de escrutinio</h2>
          <p>Sube el acta de escrutinio en formato imagen.</p>

          <!-- cargue archivo img -->
          <div class="flex justify-center items-center mt-4">
            <div class="border-2 border-gray-300 rounded-md h-full">
              <TextInput
                id="evidencia"
                type="file"
                class="!border-0"
                accept="image/*"
                required
                @input="onFileChange($event)"
                :maxFileSize="2e6"
              />
              <InputError class="mt-2" :message="form.errors.evidencia" />

              <div class="flex justify-center">
                <img
                  v-if="imageUrl"
                  :src="imageUrl"
                  :alt="form.evidencia"
                  class="w-4/6 h-full object-contain"
                />
                <PhotoIcon
                  v-else
                  class="w-2/6 text-gray-300 flex justify-center items-center"
                />
              </div>
              <div v-if="imageUrl" class="flex justify-center mt-2">
                <button
                  @click="removeImage()"
                  type="button"
                  class="bg-red-500 text-white px-4 py-2 rounded"
                >
                  Eliminar Imagen
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- contenido 2 -->
        <div class="bg-white p-4 rounded-lg shadow h-full">
          <h2 class="text-lg font-semibold mb-4">Registro de información</h2>
          <!-- <p>Registra la información y numero de votos por proyecto</p> -->

          <!-- campos de form -->
          <div class="mt-4 flex flex-wrap gap-4">
            <!-- nombre testigo -->
            <div class="mb-2 w-full md:pe-8">
              <InputLabel for="nombre_testigo" value="Nombre del testigo" />
              <TextInput
                id="nombre_testigo"
                v-model="form.nombre_testigo"
                class="mt-1 block w-full"
                type="text"
                required
              />
              <InputError :message="form.errors.nombre_testigo" class="mt-2" />
            </div>
            <!-- identificación testigo -->
            <div class="mb-2 sm:w-[calc(50%-.8rem)]">
              <InputLabel
                for="Identification_testigo"
                value="Identificación del testigo"
              />
              <TextInput
                id="Identification_testigo"
                v-model="form.Identification_testigo"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError
                :message="form.errors.Identification_testigo"
                class="mt-2"
              />
            </div>
            <!-- contacto testigo -->
            <div class="mb-2 sm:w-[calc(50%-0.8rem)]">
              <InputLabel for="contacto_testigo" value="Contacto del testigo" />
              <TextInput
                id="contacto_testigo"
                v-model="form.contacto_testigo"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError
                :message="form.errors.contacto_testigo"
                class="mt-2"
              />
            </div>
            <!-- fecha inicio -->
            <div class="mb-2">
              <InputLabel for="fecha_inicio" value="Fecha de inicio" />
              <TextInput
                id="fecha_inicio"
                v-model="form.fecha_inicio"
                class="mt-1 block w-full"
                type="date"
                required
              />
              <InputError :message="form.errors.fecha_inicio" class="mt-2" />
            </div>
            <!-- fecha fin -->
            <div class="mb-2">
              <InputLabel for="fecha_fin" value="Fecha de fin" />
              <TextInput
                id="fecha_fin"
                v-model="form.fecha_fin"
                class="mt-1 block w-full"
                type="date"
                required
              />
              <InputError :message="form.errors.fecha_fin" class="mt-2" />
            </div>
            <!-- hora inicio -->
            <div class="mb-2">
              <InputLabel for="hora_inicio" value="Hora de inicio" />
              <TextInput
                id="hora_inicio"
                v-model="form.hora_inicio"
                class="mt-1 block w-full"
                type="time"
                required
              />
              <InputError :message="form.errors.hora_inicio" class="mt-2" />
            </div>
            <!-- hora cierre -->
            <div class="mb-2">
              <InputLabel for="hora_cierre" value="Hora de cierre" />
              <TextInput
                id="hora_cierre"
                v-model="form.hora_cierre"
                class="mt-1 block w-full"
                type="time"
                required
              />
              <InputError :message="form.errors.hora_cierre" class="mt-2" />
            </div>
            <!-- total ciudadanos -->
            <div class="mb-2 w-1/4">
              <InputLabel
                for="total_ciudadanos"
                value="Total ciudadanos registrados"
              />
              <TextInput
                id="total_ciudadanos"
                v-model="form.total_ciudadanos"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError
                :message="form.errors.total_ciudadanos"
                class="mt-2"
              />
            </div>
            <!-- observaciones -->
            <div class="mb-2 w-full">
              <InputLabel
                for="observaciones"
                value="Observaciones (max 250 caracteres)"
              />
              <Textarea
                v-model="form.observaciones"
                variant="filled"
                autoResize
                rows="5"
                class="mt-1 block w-full"
                required
                autocomplete="observaciones"
              />
              <p
                class="text-gray-500 text-sm mt-2"
                :class="{ 'text-red-500': isMaxPalabras }"
              >
                {{ palabrasEnTestimonio }} caracteres
                <span v-if="isMaxPalabras">Limite de caracteres superado</span>
              </p>
              <InputError :message="form.errors.observaciones" class="mt-2" />
            </div>
            <div class="w-full">
              <h2>Registro de votos</h2>
            </div>
            <!-- votos nulos -->
            <div class="mb-2 w-1/4">
              <InputLabel for="votos_nulos" value="Votos nulos" />
              <TextInput
                id="votos_nulos"
                v-model="form.votos_nulos"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError :message="form.errors.votos_nulos" class="mt-2" />
            </div>
            <!-- votos blancos -->
            <div class="mb-2 w-1/4">
              <InputLabel for="votos_blancos" value="Votos blancos" />
              <TextInput
                id="votos_blancos"
                v-model="form.votos_blancos"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError :message="form.errors.votos_blancos" class="mt-2" />
            </div>
            <!-- votos no marcados -->
            <div class="mb-2 w-1/4">
              <InputLabel for="votos_no_marcados" value="Votos no marcados" />
              <TextInput
                id="votos_no_marcados"
                v-model="form.votos_no_marcados"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError
                :message="form.errors.votos_no_marcados"
                class="mt-2"
              />
            </div>
            <!-- bucle for para proyectos -->
            <div
              v-for="proyecto in proyectosPorEvento(eventos_hijos_vigentes[0].id)"
              :key="proyecto.id"
              class="mb-2 w-full"
            >
              <InputLabel
                :for="'votos_proyecto_' + proyecto.id"
                :value="
                  'Votos proyecto --' +
                  proyecto.numero_tarjeton +
                  ' ' +
                  proyecto.nombre
                "
              />
              <TextInput
                :id="'votos_proyecto_' + proyecto.id"
                v-model="form.votos_proyectos[proyecto.id]"
                class="mt-1 block w-full"
                type="number"
                required
              />
              <InputError
                :message="
                  form.errors['votos_proyectos.' + proyecto.id]
                "
                class="mt-2"
              />
            </div>
            <!-- total votantes -->
            <div class="mb-2 w-1/4">
              <InputLabel for="total_votos" value="Total votantes" />
              <b class="">{{form.total_votantes}}</b>

            </div>
          </div>
        </div>
        <div class="flex justify-center items-center w-full col-span-2 mt-4">
          <PrimaryButton
            class="w-auto"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Enviar
          </PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";
import { IdentificationIcon, PhotoIcon } from "@heroicons/vue/24/solid";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import Textarea from "primevue/textarea";
const swal = inject("$swal");

const props = defineProps({
  proyectos: Object,
  evento: Object,
  comuna: Number,
  puesto_votacion: Number,
  id_jurado: Number,
  eventos_hijos_vigentes: Object,
  actas_enviadas :Object,
  faltan_actas: Number,
});

console.log(props);

const form = useForm({
  id_evento: props.eventos_hijos_vigentes[0].id,
  id_jurado: props.id_jurado,
  comuna: props.comuna,
  puesto_votacion: props.puesto_votacion,
  nombre_testigo: "",
  Identification_testigo: "",
  contacto_testigo: "",
  fecha_inicio: "",
  fecha_fin: "",
  hora_inicio: "",
  hora_cierre: "",
  votos_nulos: 0,
  votos_blancos: 0,
  votos_no_marcados: 0,
  total_ciudadanos: 0,
  total_votantes: 0,
  observaciones: "",
  evidencia: null,
  votos_proyectos: {},
});

//variable de imagen
const imageUrl = ref(null);
//max palabras
const isMaxPalabras = ref(false);


// Limite palabras
const palabrasEnTestimonio = computed(() => {
  const palabras = form.observaciones.trim();
  const result = palabras.length;

  if (result >= 250) {
    isMaxPalabras.value = true;
  } else {
    isMaxPalabras.value = false;
  }

  return result;
});

watch(palabrasEnTestimonio, () => {
  form.observaciones = form.observaciones.trim(); // Asegúrate de que no haya espacios al principio o al final
});

watch(
  [
    () => form.votos_blancos,
    () => form.votos_nulos,
    () => form.votos_no_marcados,
    () => form.votos_proyectos,
  ],
  () => {
    const total_votos_especiales =
      parseInt(form.votos_blancos) +
      parseInt(form.votos_nulos) +
      parseInt(form.votos_no_marcados);

    const total_proyectos = Object.values(form.votos_proyectos).reduce(
      (acc, votos) => parseInt(acc) + parseInt(votos || 0),
      0
    );

    form.total_votantes = total_votos_especiales + total_proyectos;
  },
  { deep: true }
);

// Asignar proyectos al evento actual
const proyectosPorEvento = id_evento => {
    console.log(id_evento);

    return props.proyectos[id_evento] || [];
}

const onFileChange = (event) => {
  const file = event.target.files[0];
  if (!file) {
    imageUrl.value = null;
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");

      // Valores máximos permitidos
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

      // Convertir a Blob comprimido
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

          imageUrl.value = URL.createObjectURL(compressedFile);
          form.evidencia = compressedFile;
        },
        "image/jpeg",
        0.8 // Calidad
      );
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Eliminar la imagen seleccionada
const removeImage = () => {
  form.evidencia = null;
  imageUrl.value = null;
};

const submit = () => {
  const total_votos =
    parseInt(form.votos_blancos) +
    parseInt(form.votos_nulos) +
    parseInt(form.votos_no_marcados);
  const total_proyectos = Object.values(form.votos_proyectos).reduce(
    (acc, votos) => parseInt(acc) + parseInt(votos || 0),
    0
  );

  console.log("Total votos:", total_votos);
  console.log("Total proyectos:", total_proyectos);
  console.log("Total ciudadanos:", form.total_ciudadanos);
  console.log("Total votantes:", form.total_votantes);

  //Validacion 1: total de registros ciudadano tiene que ser igual a total de votantes
  if (parseInt(form.total_ciudadanos) != parseInt(form.total_votantes)) {
    swal.fire({
      icon: "error",
      title: "Error",
      text: "El total de ciudadanos registrados debe ser igual al total de ciudadanos votantes",
    });
    return;
    //Validaciopn 2: total de votantes == a total de votos
  } else if (
    parseInt(form.total_votantes) !=
    total_votos + parseInt(total_proyectos)
  ) {
    swal.fire({
      icon: "error",
      title: "Error",
      text: "El total de votantes debe ser igual a la suma de votos nulos, blancos, no marcados y votos por proyecto.",
    });
    return;
  }

  console.log(form);

  form.post(route("actaPresencial.store"), {
    onSuccess: () => {
      swal.fire({
        icon: "success",
        title: "Éxito",
        text: "Acta de escrutinio cargada correctamente.",
      });
      form.reset();
      imageUrl.value = null; // Limpiar la imagen después del envío
    },
    onError: (errors) => {
      console.error(errors);
        swal.fire({
            icon: "error",
            title: "Error",
            text: "Hubo un error al enviar el formulario. Por favor, verifica los datos e intenta nuevamente.",
        });
    },
  });
};

const breadcrumbLinks = [{ url: "", text: "Carga acta de escrutinio" }];
</script>
