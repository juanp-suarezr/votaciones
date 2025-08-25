<template>
  <Head title="Auditoria validaciones" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Auditoria validaciones </template>

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

      <div class="flex gap 4 p-4">
        <SecondaryButton class="me-4 py-2 h-full" @click="limpiar">
          Limpiar
        </SecondaryButton>
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
                    Usuario validador
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Accion
                  </th>

                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    votante validado
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Ip Address
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    User Agent
                  </th>
                  <th
                    class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                  >
                    Fecha de registro
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in auditoria_registro.data"
                  :key="user.id"
                  class="text-gray-700"
                >
                  <td class="border-b border-gray-200 bg-white p-1 text-sm">
                    <Avatar
                      :label="getInitials(user.usuario.name)"
                      class="bg-indigo-200 text-indigo-800 text-xl"
                      size="large"
                      shape="circle"
                    />
                  </td>
                  <td class="border-b border-gray-200 bg-white py-5 text-sm">
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.usuario.name }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap" :class="{ 'text-green-600': user.accion === 'Activo', 'text-red-600': user.accion == 'Rechazado' }">
                      {{ user.accion == 'Activo' ? 'Aprobado' : user.accion }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.hash_votante.votante.nombre }} -
                      {{ user.hash_votante.votante.identificacion }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.ip_address }}
                    </p>
                  </td>
                  <td
                    class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                  >
                    <p class="text-gray-900 whitespace-no-wrap">
                      {{ user.user_agent }}
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
      <pagination :links="auditoria_registro.links" />
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


const props = defineProps({
  auditoria_registro: {
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

const breadcrumbLinks = [{ url: "", text: "auditoria/historial validaciones" }];

let id_user = ref(props.filters.id_user ?? "");

let id_evento = ref(props.eventos.find((item) => item.id == 15));

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const handleEnterKey = () => {
  router.get(
    "/auditoria-validaciones",
    {
      id_evento: id_evento.value.id,
      id_user: id_user.value.id,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

const limpiar = () => {
  id_user.value = null;
  id_evento.value = props.eventos.find((item) => item.id == 15);

  router.get(
    "/auditoria-validaciones",
    { id_evento: id_evento.value, id_user: id_user.value.id },
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

</script>
