<template>
  <Head title="Vista Previa de Votación" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Vista Previa de Votación - {{ evento }} <br>
      Proyecto: {{ proyecto.detalle }}
    </template>

    <div class="items-center flex flex-col justify-center mx-2">
      <div class="sm:flex w-full sm:grid md:grid-cols-2 grid-cols-2 gap-4 mt-6">
        <!-- votos proyectos -->
        <div
          class="flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 text-gray-800"
        >
          <div class="mx-auto w-full flex justify-center items-center">
            <div
              v-if="proyecto.tipo_proyecto && proyecto.tipo_proyecto.imagen == null"
              class="w-full flex justify-center items-center h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
            >
              <p class="m-auto sm:text-4xxl text-xxl object-cover">
                {{ getInitials(proyecto.detalle) }}
              </p>
            </div>
            <div
              v-else
              class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
            >
              <img
                :src="getImageProyectoUrl(proyecto.tipo_proyecto.imagen)"
                alt="Imagen de proyecto"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          <Tag
            :value="getComuna(proyecto.subtipo)"
            class="!bg-primary text-[8px] sm:text-sm !text-white w-full flex p-2"
          />
          <!-- proyecto -->
          <div class="mt-4 mx-auto">
            <h2 class="sm:text-4xl text-2xl font-bold capitalize text-justify">
              Tarjetón -- {{ proyecto.numero_tarjeton }}
            </h2>

            <p class="sm:text-2xl text-xl text-justify">
              <b>Nombre Proyecto:</b> <br />
              {{ proyecto.detalle }}
            </p>

            <p class="sm:text-2xl text-xl text-justify">
              <b>Descripción:</b> <br />
              {{ truncate(proyecto.descripcion, 80) }}
              <span
                v-if="proyecto.descripcion.length > 80"
                class="text-blue-600 cursor-pointer underline"
                @click="showModal(pro)"
              >
                Ver más
              </span>
            </p>
          </div>
        </div>
        <!-- VOTO EN BLANCO -->
        <div
          class="flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 text-gray-800"
        >
          <div class="mx-auto">
            <div
              class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
            >
              <img
                :src="getImageUrl('12345678_candidatos.jpg')"
                alt="Imagen de voto en blanco"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          <!-- Candidato -->
          <div class="mt-6 mx-auto">
            <h2 class="sm:text-4xl text-2xl font-bold capitalize text-justify">
              VOTO <br />
              EN BLANCO
            </h2>
          </div>
        </div>
      </div>

      <!-- botón regresar -->
      <div class="flex justify-center mt-8">
        <SecondaryButton @click="regresar">
          Regresar
        </SecondaryButton>
      </div>
    </div>

    <!-- Modal para mostrar la descripción completa -->
    <div
      v-if="modalVisible"
      class="fixed inset-0 bg-black px-4 sm:px-44 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg w-full">
        <h2 class="sm:text-4xl text-xl font-bold mb-4 text-center">
          Detalle del Proyecto
        </h2>
        <p class="mb-4 text-base sm:text-2xl">
          {{ modalProyecto.proyecto.detalle }}
        </p>
        <p class="mb-4 text-base sm:text-2xl">
          <b>descripción:</b> {{ modalProyecto.proyecto.descripcion }}
        </p>
        <button
          @click="modalVisible = false"
          class="bg-indigo-600 text-white px-4 py-2 rounded"
        >
          Cerrar
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import Tag from "primevue/tag";
import { ref } from "vue";
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
  proyecto: Object,
  evento: String
});

const breadcrumbLinks = [{ url: "", text: "Vista Previa de proyecto" }];
//modales
const modalVisible = ref(false);
//info
const modalProyecto = ref({ proyecto: { detalle: "" } });

//funtion para avatar letter
const getInitials = function (name) {
  let parts = name.split(" ");
  let initials = "";
  let count = 0;

  for (var i = 0; i < parts.length && count < 2; i++) {
    if (parts[i].length > 0 && parts[i] !== "") {
      initials += parts[i][0];
      count++;
    }
  }
  return initials;
};

//MOSTRAR IMAGEN EN TABLA
const getImageProyectoUrl = (imageName) => {
  return `/storage/uploads/proyectos/${imageName}`;
};

const getComuna = (idComuna) => {
  return comunas.find((item) => item.value == idComuna)?.label;
};

const truncate = (text, length) => {
  if (!text) return "";
  return text.length > length ? text.substring(0, length) + "..." : text;
};

const showModal = (pro) => {
  modalProyecto.value = pro;
  modalVisible.value = true;
};

const regresar = () => {
  router.back();
};
</script>