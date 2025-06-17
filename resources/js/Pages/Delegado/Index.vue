<template>
  <Head title="Delegado" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Delegado</h1>
        <PrimaryLink :href="route('delegado.create')"> Agregar </PrimaryLink>
      </div>
    </template>

    <div class="p-4">
      <!-- Tabla -->
      <div class="bg-white shadow-md rounded-lg p-4">
        <DataTable
          :value="delegados.data"
          responsive-layout="scroll"
          class="p-datatable-sm"
        >
          <template #header>
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-bold">Delegado asignado</h2>
              <Pagination :links="delegados.links" />
            </div>
          </template>

          <Column header="nombre">
            <template #body="slotProps">
              <p>
                <b>Nombre:</b>
                {{ slotProps.data.nombre || "Sin información" }}
              </p>
            </template>
          </Column>
          <Column header="cargo">
            <template #body="slotProps">
              {{ slotProps.data.cargo || "N/A" }}
            </template>
          </Column>

          
          <Column header="Foto">
            <template #body="slotProps">
              <img
                v-if="slotProps.data.firma"
                :src="getUrl(slotProps.data.firma)"
                alt="Firma delegado"
                v-tooltip="slotProps.data.caracteristicas"
                class="w-56 h-full object-cover rounded cursor-pointer"
                @click="openLightbox(getUrl(slotProps.data.firma))"
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
                  v-tooltip.bottom="'Editar'"
                  @click="editCanino(slotProps.data.id)"
                >
                  <PencilIcon class="h-6 w-6 text-blue-800" />
                </SecondaryButton>
                <SecondaryButton
                  class="border-red-800 hover:!bg-red-200"
                  v-tooltip.bottom="'Eliminar'"
                  @click="deleteCanino(slotProps.data.id)"
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
import { PencilIcon, TrashIcon, EyeIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
  delegados: {
    type: Object,
    default: () => ({}),
  },
});

console.log(props.delegados);

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

const getUrl = (url) => `/storage/uploads/delegado/${url}`;

const editCanino = (id) => {
  router.visit(`/caninos/${id}/edit`);
};

const deleteCanino = (id) => {
  if (confirm("¿Estás seguro de que deseas eliminar este canino?")) {
    router.delete(`/caninos/${id}`);
  }
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
