<template>
  <Head title="Usuarios registrados o validados" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Gestión de registros validados</h1>
      </div>
    </template>

    <div class="p-4">
      <!-- Filtros -->
      <div class="bg-white shadow-md rounded-lg p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <TextInput
            id="identificacion"
            v-model="identificacion"
            placeholder="Identificación"
            class="w-full"
          />
          <TextInput
            id="nombre"
            v-model="nombre"
            placeholder="Nombre o ID"
            class="w-full"
          />
          <Select
            id="comuna"
            v-model="subtipo"
            :options="comunas"
            filter
            optionLabel="label"
            placeholder="Seleccione comuna"
            checkmark
            :highlightOnSelect="false"
            class="w-full"
          />
          <Select
            id="estado"
            v-model="estado"
            :options="opcionesEstado"
            filter
            optionLabel="label"
            placeholder="Seleccione estado"
            checkmark
            :highlightOnSelect="false"
            class="w-full"
          />
        </div>
        <div class="mt-4 flex justify-end">
          <PrimaryButton @click="handleSearch">Buscar</PrimaryButton>
          <SecondaryButton @click="clearAll">Limpiar Filtros</SecondaryButton>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-white shadow-md rounded-lg p-4">
        <DataTable
          :value="votantes.data"
          responsive-layout="scroll"
          class="p-datatable-sm"
        >
          <template #header>
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-bold">Lista de votantes para validar</h2>
              <Pagination :links="votantes.links" />
            </div>
          </template>
          <Column header="ID">
            <template #body="slotProps">
              {{ slotProps.data.id || "N/A" }}
            </template>
          </Column>
          <Column header="Propietario">
            <template #body="slotProps">
              <p>
                <b>Nombre:</b>
                {{ slotProps.data.votante?.nombre || "Sin información" }}
                <br />
                <b>{{ slotProps.data.votante?.tipo_documento }}:</b>
                {{ slotProps.data.votante?.identificacion || "N/A" }}
                <br />
              </p>
            </template>
          </Column>
          <Column header="Sector">
            <template #body="slotProps">
              {{ getComuna(slotProps.data.subtipo) || "N/A" }}
              <br />
              {{ slotProps.data.votante?.barrio || "N/A" }}
            </template>
          </Column>

          <Column header="Evento">
            <template #body="slotProps">
              {{ slotProps.data.evento.nombre || "N/A" }}
            </template>
          </Column>

          <Column header="Estado">
            <template #body="slotProps">
              <span
                class="p-2 rounded-md"
                :class="[
                  slotProps.data.estado === 'Activo'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800',
                ]"
                >{{ slotProps.data.estado }}</span
              >
            </template>
          </Column>

          <Column header="Acciones">
            <template #body="slotProps">
              <div class="flex gap-2"></div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";
import Calendar from "primevue/calendar";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Select from "primevue/select";
import Tag from "primevue/tag";
import { PencilIcon, TrashIcon, EyeIcon } from "@heroicons/vue/24/solid";
import dayjs from "dayjs";
import "dayjs/locale/es";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import comunas from "@/shared/comunas.json"; // Importa el JSON

const props = defineProps({
  votantes: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

console.log(props.votantes);

// Filtros
let identificacion = ref(props.filters.identificacion || "");
let nombre = ref(props.filters.nombre || "");
let subtipo = ref(props.filters.subtipo || "");
let estado = ref(props.filters.estado || "");

const opcionesEstado = [
  { label: "Activo", value: "Activo" },
  { label: "Rechazado", value: "Rechazado" },
];

// Funciones
const handleSearch = () => {
  router.get(
    "/historial_registros",
    {
      identificacion: identificacion.value,
      nombre: nombre.value,
      subtipo: subtipo.value,
      estado: estado.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const clearAll = () => {
  identificacion.value = "";
  nombre.value = "";
  subtipo.value = "";
  estado.value = "";
  handleSearch();
};

const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna)?.label;
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
