<template>
  <Head title="Detalle de Ruta" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Detalle de Ruta </template>

    <div class="w-full grid md:grid-cols-3 gap-4">
      <div class="md:col-span-2 p-4 bg-white rounded-md shadow-md h-full">
        <h2 class="text-lg font-bold mb-4">Información de la Ruta</h2>
        
        <div class="space-y-4">
          <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ruta o Path</label>
            <p class="text-gray-900">{{ ruta.path }}</p>
          </div>

          <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Inicio</label>
            <p class="text-gray-900">{{ ruta.fecha_inicio ? formatDate(ruta.fecha_inicio) : 'N/A' }}</p>
          </div>

          <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Fin</label>
            <p class="text-gray-900">{{ ruta.fecha_fin ? formatDate(ruta.fecha_fin) : 'N/A' }}</p>
          </div>

          <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            <p class="text-gray-900">
              <span :class="ruta.estado === 'activo' || ruta.estado === 1 || ruta.estado === '1' ? 'bg-green-100 text-green-800 px-2 py-1 rounded' : 'bg-red-100 text-red-800 px-2 py-1 rounded'">
                {{ ruta.estado === 'activo' || ruta.estado === 1 || ruta.estado === '1' ? 'Activo' : 'Bloqueado' }}
              </span>
            </p>
          </div>

          <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Creación</label>
            <p class="text-gray-900">{{ ruta.created_at ? formatDate(ruta.created_at) : 'N/A' }}</p>
          </div>

          <div class="pb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Última Actualización</label>
            <p class="text-gray-900">{{ ruta.updated_at ? formatDate(ruta.updated_at) : 'N/A' }}</p>
          </div>
        </div>

        <div class="mt-6 flex gap-4">
          <SecondaryLink :href="route('rutas.edit', ruta.id)">
            Editar
          </SecondaryLink>
          <SecondaryLink :href="route('rutas.index')">
            Volver al Listado
          </SecondaryLink>
        </div>
      </div>

      <div class="md:col-span-1 flex flex-col items-center h-full p-5 bg-white shadow-sm rounded-md">
        <div class="p-4 md:p-5 w-full border border-gray rounded-md shadow-sm">
          <div class="mb-6">
            <h3 class="pb-4">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Ruta
              </span>
            </h3>
            <p class="">{{ ruta.path }}</p>
          </div>
          <div class="mb-8">
            <h3 class="mb-3">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Fechas
              </span>
            </h3>
            <p>Inicio: {{ ruta.fecha_inicio ? formatDate(ruta.fecha_inicio) : 'N/A' }}</p>
            <p>Fin: {{ ruta.fecha_fin ? formatDate(ruta.fecha_fin) : 'N/A' }}</p>
          </div>
          <div class="mb-8">
            <h3 class="mb-3">
              <span class="bg-azul text-white px-2 py-1 rounded-md shadow-md">
                Estado
              </span>
            </h3>
            <p>
              {{ ruta.estado === 'activo' || ruta.estado === 1 || ruta.estado === '1' ? 'Activo' : 'Bloqueado' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import SecondaryLink from "@/Components/SecondaryLink.vue";

const props = defineProps({
  ruta: Object,
});

const breadcrumbLinks = [
  { url: "/rutas", text: "Listado de rutas" },
  { url: "", text: "Detalle de ruta " + props.ruta.path },
];

const formatDate = (date) => {
  if (!date) return 'N/A';
  const [year, month, day] = date.split("T")[0].split("-");
  return `${year}-${month}-${day}`;
};
</script>
