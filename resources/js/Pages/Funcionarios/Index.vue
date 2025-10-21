<template>
  <Head title="Funcionarios" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Funcionarios Planeación</h1>
      </div>
    </template>

    <div
      class="inline-block min-w-full overflow-hidden mb-3 grid md:grid-cols-3 gap-4"
    >
      <div>
        <Select
          id="estado"
          v-model="estado"
          :options="[
            { label: 'Activo', value: 1 },
            { label: 'Inactivo', value: 0 },
          ]"
          filter
          optionLabel="label"
          placeholder="Seleccione el estado"
          checkmark
          :highlightOnSelect="false"
          @change="handleEnterKey"
          class="block w-full px-4 py-1 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
      <div class="...">
        <label
          for="default-search"
          class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white"
          >Search</label
        >
        <div class="relative">
          <div
            class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"
          >
            <svg
              class="w-4 h-4 text-gray-500 dark:text-gray-400"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 20 20"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
              />
            </svg>
          </div>
          <input
            type="search"
            @keydown.enter="handleEnterKey"
            v-model="search"
            id="default-search"
            class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-0 focus:border-transparent"
            placeholder="Busqueda de usuarios"
            required
          />
          <button
            type="submit"
            @click="handleEnterKey"
            class="text-white absolute end-2.5 bottom-2.5 bg-sky-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-sky-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          >
            Buscar
          </button>
        </div>
      </div>

      <div class="flex gap 4 p-4">
        <SecondaryButton class="me-4 py-2 h-full" @click="limpiar">
          Limpiar
        </SecondaryButton>
        <PrimaryLink :href="route('funcionarios.create')">
          Agregar funcionario
        </PrimaryLink>
        <a
          :href="
            route('funcionarios.zip', {
              search: search,
              estado: estado,
            })
          "
          class="flex inline-flex h-full text-white bg-green-800 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2"
        >
          <DocumentArrowDownIcon class="h-6 w-6 me-2" />Exportar
        </a>
      </div>
    </div>

    <div class="p-4">
      <!-- Tabla -->
      <div class="bg-white shadow-md rounded-lg p-4">
        <DataTable
          :value="funcionarios.data"
          responsive-layout="scroll"
          class="p-datatable-sm"
        >
          <template #header>
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-bold">funcionarios</h2>
              <Pagination :links="funcionarios.links" />
            </div>
          </template>

          <Column header="nombre">
            <template #body="slotProps">
              <p>
                {{ slotProps.data.nombre || "Sin información" }}
              </p>
            </template>
          </Column>
          <Column header="Identificación">
            <template #body="slotProps">
              {{ slotProps.data.identificacion || "N/A" }}
            </template>
          </Column>
          <Column header="Area">
            <template #body="slotProps">
              {{ slotProps.data.area || "N/A" }}
            </template>
          </Column>
          <Column header="Grupo Sanguíneo">
            <template #body="slotProps">
              {{ slotProps.data.grupo_sanguineo || "No tiene" }}
            </template>
          </Column>

          <Column header="Foto">
            <template #body="slotProps">
              <img
                v-if="
                  slotProps.data.foto != 'NA' && slotProps.data.foto != null
                "
                :src="getUrl(slotProps.data.foto)"
                :alt="slotProps.data.nombre"
                v-tooltip="slotProps.data.nombre"
                class="w-24 h-full object-cover rounded cursor-pointer"
                @click="openLightbox(getUrl(slotProps.data.foto))"
              />
              <span v-else>No disponible</span>
            </template>
          </Column>

          <Column header="Estado">
            <template #body="slotProps">
              <span
                class="text-green-200 bg-green-600 p-2 rounded-md"
                v-if="slotProps.data.estado"
              >
                Activo
              </span>
              <span class="text-red-200 bg-red-600 p-2 rounded-md" v-else>
                Inactivo
              </span>
            </template>
          </Column>
          <Column header="Acciones">
            <template #body="slotProps">
              <div class="flex gap-2">
                <SecondaryButton
                  class="border-blue-800 hover:!bg-blue-200"
                  v-tooltip.bottom="'Descargar qr'"
                  @click="DescargarQR(slotProps.data.id)"
                >
                  <EyeIcon class="h-6 w-6 text-blue-800" />
                </SecondaryButton>
                <SecondaryButton
                  class="border-blue-800 hover:!bg-blue-200"
                  v-tooltip.bottom="'Editar'"
                  @click="editDelegado(slotProps.data.id)"
                >
                  <PencilIcon class="h-6 w-6 text-blue-800" />
                </SecondaryButton>
                <SecondaryButton
                  v-if="slotProps.data.estado"
                  class="border-red-800 hover:!bg-red-200"
                  v-tooltip.bottom="'Inactivar'"
                  @click="InactivarFuncionario(slotProps.data.id)"
                >
                  <TrashIcon class="h-6 w-6 text-red-800" />
                </SecondaryButton>
                <SecondaryButton
                  v-else
                  class="border-red-800 hover:!bg-red-200"
                  v-tooltip.bottom="'Activar'"
                  @click="ActivarFuncionario(slotProps.data.id)"
                >
                  <TrashIcon class="h-6 w-6 text-red-800" />
                </SecondaryButton>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Lightbox -->
    <VueEasyLightbox
      :visible="showLightbox"
      :imgs="[previewImage]"
      @hide="closeLightbox"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";
import Calendar from "primevue/calendar";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import VueEasyLightbox from "vue-easy-lightbox";
import {
  PencilIcon,
  TrashIcon,
  EyeIcon,
  DocumentArrowDownIcon,
} from "@heroicons/vue/24/solid";
import comunas from "@/shared/comunas.json";
import { Select } from "primevue";

const props = defineProps({
  funcionarios: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const estado = ref(props.filters.estado);
const search = ref(props.filters.search);

// Lightbox
const previewImage = ref("");
const showLightbox = ref(false);

const openLightbox = (imageUrl) => {
  previewImage.value = imageUrl;
  showLightbox.value = true;
};

const closeLightbox = () => {
  showLightbox.value = false;
};

const handleEnterKey = () => {
  console.log(estado.value);

    let estadoValue = null;

  if (estado.value !== null && estado.value !== undefined) {

    estadoValue = estado.value.value;
  }

  router.get(
    "/funcionarios",
    { search: search.value, estado: estadoValue },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const limpiar = () => {
  search.value = "";
  estado.value = null;
  router.get(
    "/funcionarios",
    { search: search.value, estado: estado.value },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const getUrl = (url) => `/assets/img/funcionarios_planeacion/${url}`;

const editFuncionario = (id) => {
  router.visit(`/funcionarios/${id}/edit`);
};

const deleteFuncionario = (id) => {
  if (confirm("¿Estás seguro de que deseas eliminar el delegado?")) {
    router.delete(`/caninos/${id}`);
  }
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
