<template>
  <Head title="Actas virtuales" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Actas virtuales </template>

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
      <div>
        <select
          id="modalidad"
          name="modalidad"
          v-model="modalidad"
          @change="handleEnterKey"
          class="block w-full px-4 py-3 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
        >
          <option selected value="">Filtrar por modalidad</option>
          <option value="virtual">Virtual</option>
          <option value="presencial">Presencial</option>
        </select>
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
            placeholder="Busqueda de jurados"
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
      </div>
    </div>
    <div class="flex flex-col overflow-x-auto" v-if="actas.data.length > 0">
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
                    Comunas
                  </th>

                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Punto votación
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Jurado
                  </th>

                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Tipo acta
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Modalidad
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha registrada
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
                  v-for="acta in actas.data"
                  :key="acta.id"
                  class="text-gray-700"
                >
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{
                        acta.modalidad == "virtual"
                          ? "Virtual"
                          : getParametros(acta.comunas) ||
                            "no se encuentra el dato"
                      }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{
                        acta.modalidad == "virtual"
                          ? "Virtual"
                          : getParametros(acta.puesto_votacion) ||
                            "no se encuentra el dato"
                      }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p
                      class="text-gray-900 whitespace-no-wrap"
                      v-if="acta.modalidad == 'presencial'"
                    >
                      {{ acta.jurado.nombre }}
                      <br />
                      <b>cc:</b> {{ acta.jurado.identificacion }}
                    </p>
                    <p v-else>Virtual</p>
                  </td>

                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p
                      class="text-white font-bold whitespace-no-wrap inline-flex px-2 rounded-md bg-red-200"
                      :class="{ '!bg-green-600': acta.tipo === 'inicial' }"
                    >
                      {{ acta.tipo }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p
                      class="text-white whitespace-no-wrap inline-flex px-2 rounded-md bg-naranja"
                      :class="{ '!bg-blue-600': acta.modalidad === 'virtual' }"
                    >
                      {{ acta.modalidad }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{
                        acta.tipo == "inicial"
                          ? formatDate(acta.fecha_inicio)
                          : formatDate(acta.fecha_cierre)
                      }}
                    </p>
                  </td>

                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <div class="flex gap-2">
                      <Link
                        class="border border-gray-400 rounded-full hover:!bg-gray-200 p-1"
                        v-tooltip.bottom="'Ver detalles'"
                        :href="
                          route('actasVirtuales.show', {
                            id: acta.id,
                          })+ '?tipo=' + acta.tipo
                        "
                      >
                        <EyeIcon class="h-6 w-6 text-gray-800" />
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div
      v-else
      class="flex flex-col overflow-x-auto justify-center items-center"
    >
      <em> No hay actas registradas actualmente </em>
    </div>
    <div
      class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between"
    >
      <pagination v-if="actas.data.length > 0" :links="actas.links" />
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
import SecondaryLink from "@/Components/SecondaryLink.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import Select from "primevue/select";
import {
  DocumentArrowDownIcon,
  EyeIcon,
  PencilSquareIcon,
} from "@heroicons/vue/24/solid";

import comunas from "@/shared/comunas.json"; // Importa el JSON

const props = defineProps({
  actas: {
    type: Object,
    default: () => ({}),
  },
  eventos: {
    type: Object,
    default: () => ({}),
  },
  parametros: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

console.log(props);

const breadcrumbLinks = [{ url: "", text: "reporte de escrutinios" }];

// pass filters in search
let search = ref(props.filters.search);
let comuna = ref(props.filters.comuna ?? "");
let modalidad = ref(props.filters.modalidad ?? "");

let id_evento = ref(props.eventos.find((item) => item.id == 16));

const getParametros = (id) => {
  return props.parametros.find((item) => item.id === parseInt(id)).detalle;
};

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  const hours = String(d.getHours()).padStart(2, "0");
  const minutes = String(d.getMinutes()).padStart(2, "0");

  return `${year}-${month}-${day} ${hours}:${minutes}`;
};

const handleEnterKey = () => {
  router.get(
    "/actasVirtuales",
    {
      search: search.value,
      id_evento: id_evento.value.id,
      subtipo: comuna.value.value,
      modalidad: modalidad.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const limpiar = () => {
  search.value = "";
  id_evento.value = props.eventos.find((item) => item.id == 16);
  comuna.value = "";
  modalidad.value = "";

  router.get(
    "/actasVirtuales",
    {
      search: search.value,
      id_evento: id_evento.value,
      subtipo: comuna.value,
      modalidad: modalidad.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

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
