<template>
  <Head title="gestión Usuarios registrados" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Gestión de registros</h1>
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
            placeholder="Nombre"
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

          <Column header="Tiempo de espera">
            <template #body="slotProps">
              <Tag
                :severity="
                  calcularTiempoEspera(slotProps.data.created_at).color
                "
                :value="calcularTiempoEspera(slotProps.data.created_at).texto"
              >
              </Tag>
            </template>
          </Column>

          <Column header="Acciones">
            <template #body="slotProps">
              <div class="flex gap-2">
                <SecondaryLink
                  class="border-gray-800 hover:!bg-gray-200"
                  v-tooltip.bottom="'Ver detalles'"
                  :href="
                    route('gestion_registros.show', { id: slotProps.data.id })
                  "
                >
                  <EyeIcon class="h-6 w-6 text-gray-800" />
                </SecondaryLink>
              </div>
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

// Funciones
const handleSearch = () => {
  router.get(
    "/gestion_registros",
    {
      identificacion: identificacion.value,
      nombre: nombre.value,
      subtipo: subtipo.value,
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
  handleSearch();
};

// Calculate waiting time
const calcularTiempoEspera = (createdAt) => {
  if (!createdAt) return { texto: "Desconocido", color: "secondary" };

  const ahora = dayjs();
  const creado = dayjs(createdAt);
  const dias = ahora.diff(creado, "days");
  const horas = ahora.diff(creado, "hours") % 24;

  let color = "secondary";
  if (dias >= 1 && dias <= 3) {
    color = "success";
  } else if (dias >= 4 && dias <= 6) {
    color = "warn";
  } else if (dias >= 7) {
    color = "danger";
  }

  return { texto: `${dias} días`, color };
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
