<template>
  <Head title="Usuarios" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Votantes x evento </template>

    <div
      class="inline-block min-w-full overflow-hidden mb-3 grid md:grid-cols-3 gap-4"
    >
      <div>
        <Select
          id="eventos"
          v-model="id_evento"
          :options="eventos"
          filter
          optionLabel="nombre"
          placeholder="Seleccione el evento"
          checkmark
          :highlightOnSelect="false"
          @change="handleEnterKey"
          class="block w-full px-4 py-1 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
      <div>
        <Select
          id="comuna"
          v-model="comuna"
          :options="comunas"
          filter
          optionLabel="label"
          placeholder="Seleccione la comuna/corregimiento"
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
        <a
          :href="
            route('votantes.excel', {
              search: search,
              id_evento: id_evento.id,
              subtipo: comuna.value,
            })
          "
          class="flex inline-flex h-full text-white bg-green-800 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2"
        >
          <DocumentArrowDownIcon class="h-6 w-6 me-2" />Exportar
        </a>

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
                    colspan="2"
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Nombre
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Identificación
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Genero
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha de creación - registro
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha de votación
                  </th>

                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in votantes_voto.data"
                  :key="user.id"
                  class="text-gray-700"
                >
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <Avatar
                      :label="getInitials(user.votante.nombre)"
                      class="bg-indigo-200 text-indigo-800 text-xl"
                      size="large"
                      shape="circle"
                    />
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.votante.nombre }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.votante.tipo_documento }} - {{ user.votante.identificacion }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.votante.genero }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ formatDate(user.votante.created_at) }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ formatDate(user.created_at) }}
                    </p>
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
      <pagination :links="votantes_voto.links" />
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
import { DocumentArrowDownIcon } from "@heroicons/vue/24/solid";

import comunas from "@/shared/comunas.json"; // Importa el JSON

const props = defineProps({
  votantes_voto: {
    type: Object,
    default: () => ({}),
  },
  eventos: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});




const breadcrumbLinks = [{ url: "", text: "Reporte de votantes x evento" }];

// pass filters in search
let search = ref(props.filters.search);
let comuna = ref(props.filters.comuna ?? "");

let id_evento = ref(props.eventos.find(item => item.id == 16));

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const handleEnterKey = () => {
  router.get(
    "/votantesPresencial",
    { search: search.value, id_evento: id_evento.value.id, subtipo: comuna.value.value },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const limpiar = () => {

    search.value = "";
    id_evento.value = props.eventos.find(item => item.id == 16);
    comuna.value = "";

    router.get(
    "/votantesPresencial",
    { search: search.value, id_evento: id_evento.value, subtipo: comuna.value },
    {
      preserveState: true,
      replace: true,
    }
  );
}

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
watch(search, (value) => {
  console.log("Valor de búsqueda actualizado:", value);
});
</script>
