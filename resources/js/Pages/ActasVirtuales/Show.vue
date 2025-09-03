<template>
  <Head title="Detalle del acta" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <h1 class="text-xl font-bold">Detalle del acta</h1>
    </template>

    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-8 mt-8">
      <div class="mb-6">
        <h2 class="text-lg font-bold mb-2">Información general</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- id -->
          <div><span class="font-semibold">ID acta:</span> {{ acta.id }}</div>
          <!-- modalidad -->
          <div>
            <span class="font-semibold">Modalidad:</span> {{ acta.modalidad }}
          </div>
          <!-- fecha registrada -->
          <div>
            <span class="font-semibold">Fecha registrada:</span>
            {{
              tipo == "inicial"
                ? formatDate(acta.fecha_inicio)
                : formatDate(acta.fecha_cierre)
            }}
          </div>
          <div>
            <span class="font-semibold">Evento:</span> {{ acta.evento.nombre }}
          </div>

          <!-- puesto votacion -->
          <div>
            <span class="font-semibold col-span-2">Puesto de votación:</span>
            <span v-if="PuestoVotacionDetalle">
              {{ PuestoVotacionDetalle.detalle }}
            </span>
            <span v-else> No hay puesto de votación asignado </span>
          </div>

          <!-- comunas -->
          <div class="md:col-span-2">
            <span class="font-semibold">Comuna:</span>
            <template v-if="comunasDetalles.length">
              <ul class="list-disc ml-4">
                <li v-for="comuna in comunasDetalles" :key="comuna.id">
                  {{ comuna.detalle }}
                </li>
              </ul>
            </template>
            <span v-else> No hay comunas asignadas </span>
          </div>

          <!-- Info jurado -->
          <div
            class="md:col-span-2 mt-4 sm:grid sm:grid-cols-2 gap-6"
            v-if="acta.jurado"
          >
            <div class="mt-4 mb-2 sm:col-span-2">
              <h2 class="font-bold text-lg">Información Jurado</h2>
            </div>
            <!-- imagen jurado -->
            <div
              class="card shadow-lg rounded-md p-4 mb-4"
              v-if="acta.jurado.user.biometrico"
            >
              <img
                :src="getUrlBiometrico(acta.jurado.user.biometrico.photo)"
                class="w-full sm:h-36 sm:object-cover"
              />
            </div>
            <div
              class="card shadow-lg rounded-md p-4 sm:h-36 h-full flex items-center justify-center"
              v-else
            >
              Img jurado
            </div>
            <!-- informacion jurado -->
            <div class="flex flex-wrap gap-6 my-auto">
              <div>
                <b>Nombre:</b>
                {{ acta.jurado.nombre || "N/A" }}
              </div>
              <div>
                <b>identificacion:</b>
                cc. {{ acta.jurado.identificacion || "N/A" }}
              </div>
              <div>
                <b>contacto:</b>
                {{ acta.jurado.contacto || "N/A" }}
              </div>
            </div>


          </div>

          <!-- firmas delegados -->
          <div class="w-full col-span-2 mt-4">

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";

const props = defineProps({
  acta: Object,
  parametros: Array,
  tipo: String,
  delegados: Object,
});

console.log(props);

const breadcrumbLinks = [
  { url: "/actasVirtuales", text: "Actas virtuales" },
  { url: "", text: "Detalles Acta " + props.tipo },
];

const formatDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  return d.toLocaleString();
};

// Buscar el detalle de la comuna en los parámetros
const comunasDetalles = computed(() => {
  if (!props.acta.comunas || !props.parametros) return [];
  const ids = String(props.acta.comunas)
    .split("|")
    .map((id) => id.trim());
  return props.parametros.filter((p) => ids.includes(String(p.id)));
});

//Buscar el detalle del puesto votacion
const PuestoVotacionDetalle = computed(() => {
  if (!props.acta.puesto_votacion || !props.parametros) return null;
  return props.parametros.find(
    (p) => p.id === parseInt(props.acta.puesto_votacion)
  );
});
</script>
