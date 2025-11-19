<template>
  <Head title="Auditoria votos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Rutas votaciones </template>

    <div class="w-full overflow-x-scroll md:overflow-x-auto">
      <div class="text-right p-4">
        <PrimaryLink :href="route('rutas.create')"> Agregar </PrimaryLink>
      </div>
    </div>

    <div class="flex flex-col overflow-x-auto">
      <div class="inline-block rounded-lg shadow">
        <div class="inline-block min-w-full py-2">
          <div class="overflow-x-auto">
            <table class="min-w-full whitespace-no-wrap">
              <thead>
                <tr
                  class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    #
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Ruta
                  </th>

                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha inicio
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha fin
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Estado
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(ruta, index) in rutas.data"
                  :key="ruta.id"
                  class="text-gray-700"
                >
                  <td class="border-b border-gray-200 bg-white px-5 text-sm">
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ index + 1 }}
                    </p>
                  </td>
                  <td class="border-b border-gray-200 bg-white py-5 text-sm">
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ ruta.path }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ formatDate(ruta.fecha_inicio) }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ formatDate(ruta.fecha_fin) }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ ruta.estado }}
                    </p>
                  </td>

                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <SecondaryLink
                      type="button"
                      :href="route('rutas.edit', ruta.id)"
                      class="mb-2"
                    >
                      Editar
                    </SecondaryLink>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div
      class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between"
    >
      <pagination :links="rutas.links" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import Avatar from "primevue/avatar";
import Select from "primevue/select";
import SecondaryLink from "@/Components/SecondaryLink.vue";

const props = defineProps({
  rutas: {
    type: Object,
    default: () => ({}),
  },
});

const breadcrumbLinks = [{ url: "", text: "listado de rutas de votaciones" }];

const formatDate = (date) => {
  const [year, month, day] = date.split("T")[0].split("-");
  return `${year}-${month}-${day}`;
};

</script>
