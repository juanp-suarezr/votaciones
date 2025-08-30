<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

import { EyeIcon } from "@heroicons/vue/24/solid";
import Select from "primevue/select";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SecondaryButtonReturn from "@/Components/SecondaryButtonReturn.vue";
const swal = inject("$swal");

const props = defineProps({
  evento: {
    type: Object,
    default: () => [],
  },
  resultados: {
    type: Object,
    default: () => [],
  },
  eventos_hijos: {
    type: Object,
    default: () => [],
  },
});

console.log(props);

const form = useForm({
  id_evento: props.evento.id,
  subtipo: "",
  comuna: "",
});

const comuna = ref("");
const evento_hijo = ref(props.evento.id);

const comunas = props.resultados.data;
//modales
const showModal = ref(false);

const handleEnterKey = () => {
  router.get(
    "/resultado-comunas",
    {
      id_evento: props.evento.evento_hijo[0]?.id_evento_padre,
      subtipo: comuna.value.value,
      id_evento_hijo: evento_hijo.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const limpiar = () => {
  comuna.value = "";
  evento_hijo.value = 16;

  router.get(
    "/resultado-comunas",
    {
      id_evento: props.evento.evento_hijo[0]?.id_evento_padre,
      subtipo: comuna.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const openModal = (info) => {
  showModal.value = true;
  form.subtipo = info.value;
  form.comuna = info.label;
};

const Submit = (num, info) => {
  form.subtipo = info.value;
  form.comuna = info.label;
  if (num == 1) {
    form.get(route("resultado-proyectos"), {
      preserveState: true,
    });
  } else {
    form.get(route("resultado-generales"), {
      preserveState: true,
    });
  }
};
</script>

<template>
  <Head title="Votaciones por comunas" />

  <div
    class="relative min-h-screen bg-center bg-option2 selection:bg-red-500 selection:text-white flex flex-col justify-center"
  >
    <div class="w-full flex justify-between sm:px-16 px-4">
      <div class="flex gap-4 items-center">
        <a
          class="sm:flex justify-start mt-2 pe-4 border-r border-black"
          href="/welcome"
        >
          <img src="/assets/img/logo.png" alt="Logo" class="h-24 w-auto" />
        </a>
        <img
          src="/assets/img/voto_electronico.png"
          alt="Logo"
          class="h-32 w-auto"
        />
      </div>

      <SecondaryButtonReturn class="h-full flex inline-flex mt-8 !text-base">
        Regresar
      </SecondaryButtonReturn>
    </div>
    <!-- contenido -->
    <div class="w-full flex justify-center my-auto sm:px-16 px-4">
      <div
        class="w-full border border-gray-200 bg-white shadow-md rounded-lg p-4"
      >
        <h2 class="font-bold mt-2 text-2xl">Resultados de Votación</h2>
        <p class="mt-2 text-base">
          {{ evento.nombre }} - Resultados por Comuna/Corregimiento
        </p>
        <!-- total registros voto virtual -->
        <p class="flex gap-2 mt-2">
          <b>Total registrados habilitados para votar virtualmente o tics: </b>
          {{ evento.evento_hijo[0]?.evento_padre?.votantes_activos_count }}
        </p>
        <!-- total registros voto fisico -->
        <p class="flex gap-2 mt-2">
          <b>Total registrados habilitados para votar presencial físico: </b>
          {{
            evento.acta_escrutinio.reduce(
              (total, item) => total + item.total_ciudadanos,
              0
            )
          }}
        </p>
        <!-- filtros -->
        <div class="mt-4 w-full flex flex-wrap gap-4">
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
            class="block h-full w-auto px-4 py-1 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
          />
          <select
            id="evento_padre"
            v-model="evento_hijo"
            @change="handleEnterKey"
            class="block h-full w-auto px-4 py-1 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">-- Seleccione un evento --</option>
            <option
              v-for="evento in eventos_hijos"
              :key="evento.id"
              :value="evento.id"
            >
              {{ evento.nombre }}
            </option>
          </select>
          <div class="flex gap-4 p-4">
            <SecondaryButton class="p-2 h-full" @click="limpiar">
              Limpiar
            </SecondaryButton>
          </div>
        </div>
        <!-- table -->
        <div
          class="flex flex-col overflow-x-auto mt-6"
          v-if="resultados.data.length > 0"
        >
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
                        Comuna
                      </th>

                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        Votos Virtuales
                      </th>
                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        Votos Presenciales TIC
                      </th>
                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        Votos Físicos
                      </th>
                      <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                      >
                        Total
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
                      v-for="res in resultados.data"
                      :key="res.id"
                      class="text-gray-700"
                    >
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ res.label }}
                        </p>
                      </td>
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ res.votos_virtuales }}
                        </p>
                      </td>
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ res.votos_presenciales_Tic }}
                        </p>
                      </td>
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <p class="text-gray-900 whitespace-no-wrap">
                          {{ res.votos_fisicos }}
                        </p>
                      </td>
                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <b class="text-gray-900 whitespace-no-wrap">
                          {{
                            res.votos_virtuales +
                            res.votos_fisicos +
                            res.votos_presenciales_Tic
                          }}
                        </b>
                      </td>

                      <td
                        class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                      >
                        <div class="flex gap-2">
                          <button
                            class="border border-gray-400 rounded-full hover:!bg-gray-200 p-1"
                            v-tooltip.bottom="'Ver detalles'"
                            @click="Submit(1, res)"
                          >
                            <EyeIcon class="h-6 w-6 text-gray-800" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- no hay datos -->
        <div
          v-else
          class="flex flex-col overflow-x-auto justify-center items-center"
        >
          <em> No hay comunas con votaciones realizadas </em>
        </div>
        <!-- paginator -->
        <div
          class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between"
        >
          <pagination
            v-if="resultados.data.length > 0"
            :links="resultados.links"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- Modal inicio -->
  <Modal :show="showModal" :closeable="true">
    <template #default>
      <h2
        class="p-4 sm:text-4xl text-2x text-center font-bold text-gray-800 text-center flex tex-center justify-center bg-azul text-white"
      >
        Votación
        <br />
        Comuna/Corregimiento: {{ form.comuna }}
      </h2>

      <div class="sm:flex gap-4 sm:text-2xl text-xl my-6 p-4">
        <!-- info 1 -->
        <div class="sm:w-1/2 px-4 mb-12">
          <p class="mx-auto text-center">Ver votación por proyecto</p>
          <button
            type="button"
            class="flex mx-auto mt-4 text-xl sm:text-2xl bg-primary hover:bg-secondary text-white rounded-lg shadow-md p-2"
            @click="Submit(1)"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Continuar
          </button>
        </div>
        <!-- info 2 -->
        <div class="sm:w-1/2 px-4 mb-8">
          <p class="mx-auto text-center">Ver votación datos generales</p>
          <button
            type="button"
            class="flex mx-auto mt-4 text-xl sm:text-2xl bg-azul hover:bg-azul/80 text-white rounded-lg shadow-md p-2"
            @click="Submit(2)"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Continuar
          </button>
        </div>
      </div>
    </template>
  </Modal>
</template>

<style>
</style>
