<template>
  <Head title="Eventos para votar" />

  <SimpleLayout>
    <template #header> Selección el evento para votar </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 border-b border-gray-200">
        <h1 class="text-xl font-bold">Eventos disponibles para votar</h1>
      </div>

      <div class="mt-4">
        <div v-if="props.eventos_hijos && props.eventos_hijos.length > 0">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="hijo in props.eventos_hijos"
              :key="hijo.eventos.id"
              class="bg-gray-50 rounded-lg shadow-md p-6 flex flex-col justify-between"
            >
              <div>
                <h2 class="text-lg font-bold text-primary mb-2">
                  {{ hijo.eventos.nombre }}
                </h2>
                <p class="text-sm text-gray-700 mb-2">
                  {{ hijo.eventos.descripcion }}
                </p>
                <p class="text-xs text-gray-500 mb-1">
                  Vigencia: {{ formatDate(hijo.eventos.fecha_inicio) }} -
                  {{ formatDate(hijo.eventos.fecha_fin) }}
                </p>
                <p class="text-xs text-gray-500 mb-1">
                  Estado:
                  <span
                    :class="
                      hijo.eventos.estado === 'Activo'
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                    >{{ hijo.eventos.estado }}</span
                  >
                </p>
                <p class="text-xs text-gray-500 mb-1">
                  Dependencia: {{ hijo.eventos.dependencias }}
                </p>
              </div>
              <div class="mt-4 flex justify-end">
                <PrimaryButton
                  :disabled="hijo.eventos.estado !== 'Activo'"
                  @click="irAVotar(hijo.eventos)"
                  class="w-full"
                >
                  Votar en este evento
                </PrimaryButton>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 block">
          No hay eventos disponibles para votar en este momento.
          <div class="mt-4">
            <PrimaryLink
              class="block mt-2 flex flex-1 w-auto justify-center p-2"
              type="button"
              :href="route('votantesPresencial.create')"
            >
              Finalizar votación
            </PrimaryLink>
          </div>
        </div>
      </div>
    </div>
  </SimpleLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";

const props = defineProps({
  eventos_hijos: {
    type: Object,
  },
  votante: {
    type: Object,
  },
});

console.log(props);

const breadcrumbLinks = [{ url: "", text: "Eventos para votar" }];

const formatDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const irAVotar = (evento) => {
  let cantidad = props.eventos_hijos.length;

  window.location.href = route("votos-jurados.index", {
    id_votante: props.votante.id,
    evento: evento.id,
    id_evento_padre: props.eventos_hijos[0].id_evento_padre,
    tipo_user:
      props.votante.hash_votantes.length !== 0
        ? props.votante.hash_votantes[0].tipo
        : "",
    subtipo_user: usePage().props.user.jurado.comuna,
    last_vote: cantidad - 1,
  });
};
</script>
