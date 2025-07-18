<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header> Dashboard </template>

    <!-- si tiene correcciones -->
    <div
      class="items-center px-4 m-auto"
      v-if="info_votante[0]?.subtipo != 0 && info_votante[0]?.estado == 'Rechazado'"
    >
      <div class="border-2 border-gray-400 p-2 border-dashed sm:flex">
        <p class="m-auto">
          Para ser habilitado para votar debe corregir sus datos personales.
        </p>
        <PrimaryLink
          class="md:text-base mt-4"
          :class="{ 'opacity-25': isLoading }"
          :disabled="isLoading"
          type="button"
          :href="route('corregir-registro.edit', info_votante[0].id_votante)"
        >
          Corregir datos
        </PrimaryLink>
      </div>
    </div>

    <!-- votantes -->
    <div v-if="$page.props.user.roles.includes('Usuarios')" class="">
      <h2 class="text-gray-600 text-2xl inline-flex">Votaciones pendientes</h2>
      <!-- eventos votaciones -->
      <div class="md:grid md:grid-cols-2 gap-4 mt-4 mb-4">
        <div
          class="items-center px-4 m-auto w-full"
          v-for="ev in eventosPendientes"
          :key="ev.id"
        >
          <!-- sin diseño -->
          <div
            v-if="
              ev.votantes[0].estado == 'Activo' &&
              !ev.tipos.includes('withBanner')
            "
            class="border-2 border-gray-400 px-1 py-2 border-dashed flex flex-wrap"
          >
            <p class="m-auto sm:text-4xl text-xl">
              {{ ev.nombre }}
              <br />
              <span class="sm:text-xl text-sm text-gray-600"
                >{{ ev.fecha_inicio }} - {{ ev.fecha_fin }}</span
              >
              <br />
              <span
                v-if="votos.find((item) => item.id_eventos == ev.id)"
                class="sm:text-2xl text-base text-gray-800 italic"
              >
                Usted ha votado por
                {{
                  ev.tipos.includes("Proyecto")
                    ? (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const proyectoVotado = proyectos.find(
                          (p) => p.id_proyecto == voto.id_proyecto
                        );
                        return (
                          proyectoVotado?.proyecto?.detalle ??
                          "(Voto en blanco)"
                        );
                      })()
                    : (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const candidatoVotado = candidatos.find(
                          (c) => c.id_votante == voto.id_candidato
                        );
                        return (
                          candidatoVotado?.votante?.nombre ??
                          "(Voto no encontrado)"
                        );
                      })()
                }}
              </span>
              <br />
            </p>

            <PrimaryLink
              v-if="
                ev.estado == 'Activo' &&
                !votos.find((item) => item.id_eventos == ev.id)
              "
              class="sm:text-2xl text-xl m-auto mt-2"
              :class="{ 'opacity-25': isLoading }"
              :disabled="isLoading"
              :href="
                route('votos.index', {
                  evento: ev.id,
                  tipo_evento: ev.tipos,
                  tipo_user: ev.votantes.length != 0 ? ev.votantes[0].tipo : '',
                  subtipo_user:
                    ev.votantes.length != 0 ? ev.votantes[0].subtipo : '',
                })
              "
            >
              Votar
            </PrimaryLink>
          </div>
          <!-- con banner -->
          <div
            v-if="
              ev.votantes[0].estado == 'Activo' &&
              ev.tipos.includes('withBanner')
            "
            class="border-2 border-gray-400 px-1 py-2 border-dashed flex flex-wrap"
          >
            <Tag
              v-if="ev.votantes[0].subtipo"
              :value="
                'Comuna/Corregimiento: ' +
                getComuna(ev.votantes.length != 0 ? ev.votantes[0].subtipo : '')
              "
              class="!bg-primary text-[8px] sm:text-sm !text-white w-full flex p-2 z-10"
            />
            <a
              class="w-full h-full bg-indigo-200 text-indigo-800 cursor-pointer"
              :href="
                route('votos.index', {
                  evento: ev.id,
                  tipo_evento: ev.tipos,
                  tipo_user: ev.votantes.length != 0 ? ev.votantes[0].tipo : '',
                  subtipo_user:
                    ev.votantes.length != 0 ? ev.votantes[0].subtipo : '',
                })
              "
            >
              <img
                :src="getBannerImg('banner_' + ev.id)"
                alt="Imagen de evento"
                class="w-full h-full object-cover"
              />
            </a>
            <p class="m-auto sm:text-4xl text-xl">
              <span class="sm:text-xl text-sm text-gray-600"
                >{{ ev.fecha_inicio }} - {{ ev.fecha_fin }}</span
              >
              <br />
              <span
                v-if="votos.find((item) => item.id_eventos == ev.id)"
                class="sm:text-2xl text-base text-gray-800 italic"
              >
                Usted ha votado por
                {{
                  ev.tipos.includes("Proyecto")
                    ? (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const proyectoVotado = proyectos.find(
                          (p) => p.id_proyecto == voto.id_proyecto
                        );
                        return (
                          proyectoVotado?.proyecto?.detalle ??
                          "(Voto en blanco)"
                        );
                      })()
                    : (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const candidatoVotado = candidatos.find(
                          (c) => c.id_votante == voto.id_candidato
                        );
                        return (
                          candidatoVotado?.votante?.nombre ??
                          "(Voto no encontrado)"
                        );
                      })()
                }}
              </span>
              <br />
            </p>
            <div
              v-if="votos.find((item) => item.id_eventos == ev.id)"
              class="sm:text-2xl text-base text-gray-800 mt-4"
            >
              <PrimaryButton @click="descargarCertificado(ev.id)">
                Descargar certificado
              </PrimaryButton>
            </div>

            <PrimaryLink
              v-if="
                ev.estado == 'Activo' &&
                !votos.find((item) => item.id_eventos == ev.id)
              "
              class="sm:text-2xl text-xl m-auto mt-2"
              :class="{ 'opacity-25': isLoading }"
              :disabled="isLoading"
              :href="
                route('votos.index', {
                  evento: ev.id,
                  tipo_evento: ev.tipos,
                  tipo_user: ev.votantes.length != 0 ? ev.votantes[0].tipo : '',
                  subtipo_user:
                    ev.votantes.length != 0 ? ev.votantes[0].subtipo : '',
                })
              "
            >
              Votar
            </PrimaryLink>
          </div>
        </div>
      </div>
      <h2 class="text-gray-600 sm:text-2xl inline-flex">Votaciones cerradas</h2>
      <!-- eventos votaciones cerradas -->
      <div class="md:grid md:grid-cols-2 gap-4 mt-4">
        <div
          class="items-center px-4 m-auto w-full"
          v-for="ev in eventosCerrados"
          :key="ev.id"
        >
          <div class="border-2 border-gray-400 px-1 py-2 border-dashed">
            <p class="m-auto sm:text-4xl text-xl pe-2">
              {{ ev.nombre }}
              <br />
              <span class="sm:text-xl text-sm text-gray-600"
                >{{ ev.fecha_inicio }} - {{ ev.fecha_fin }}</span
              >
              <br />
              <span
                v-if="votos.find((item) => item.id_eventos == ev.id)"
                class="sm:text-2xl text-base text-gray-800 italic"
              >
                Usted ha votado por
                {{
                  ev.tipos.includes("Proyecto")
                    ? (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const proyectoVotado = proyectos.find(
                          (p) => p.id_proyecto == voto.id_proyecto
                        );
                        return (
                          proyectoVotado?.proyecto?.detalle ??
                          "(Voto en blanco)"
                        );
                      })()
                    : (() => {
                        const voto = votos.find(
                          (item) => item.id_eventos == ev.id
                        );
                        const candidatoVotado = candidatos.find(
                          (c) => c.id_votante == voto.id_candidato
                        );
                        return (
                          candidatoVotado?.votante?.nombre ??
                          "(Voto no encontrado)"
                        );
                      })()
                }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
    <!-- ADMIN -->
    <div
      v-if="
        $page.props.user.roles.includes('Administrador') ||
        $page.props.user.roles.includes('Supervisor') ||
        $page.props.user.roles.includes('Visor')
      "
      class=""
    >
      <div v-if="eventos_admin.length" class="flex flex-col justify-center">
        <div class="sm:flex justify-center items-center">
          <select
            id="evento_selected"
            name="evento_selected"
            v-model="evento_selected"
            @change="handleEnterKey"
            class="block w-full p-2 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
          >
            <option selected value="" disabled>
              Elegir evento de votación
            </option>
            <option
              class="flex cursor mb-2 sm:mb-auto"
              v-for="eventos in eventos_admin"
              :key="eventos.id"
              :value="eventos.id"
            >
              {{ eventos.nombre }}
            </option>
          </select>
        </div>

        <div class="sm:grid grid-cols-2 gap-4 mt-4">
          <div
            class="w-full flex flex-col justify-center rounded-xl p-2 shadow-xl flex m-auto h-full bg-gray-500 mb-4"
          >
            <h2
              class="text-gray-200 text-lg sm:text-xl font-bold text-center flex justify-center"
            >
              Nivel de votaciones
              <br />
              Total Votantes habilitados - {{ info_events.votantes }}
            </h2>
            <Knob
              class="flex justify-center mt-4"
              v-model="info_events.votos"
              :size="200"
              readonly
              :max="info_events.votantes"
            />
          </div>

          <div
            class="w-full flex flex-col justify-center rounded-xl p-2 shadow-xl flex m-auto h-full bg-gray-500"
          >
            <h2
              class="text-gray-200 text-lg sm:text-xl font-bold text-center flex justify-center"
            >
              Total votos según tipo
            </h2>
            <Chart
              type="bar"
              :data="chartData"
              :options="chartOptions"
              class="h-full m-2"
            />
          </div>
        </div>
      </div>

      <div v-else class="flex justify-center">
        <Message
          severity="info"
          :sticky="sticky"
          class="text-center w-fit"
          :life="3000"
          >Los eventos de votación activos serán mostrados en esta
          sección</Message
        >
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, inject, onMounted } from "vue";
import { useToast } from "vue-toast-notification";
import Message from "primevue/message";
import Chart from "primevue/chart";
import Knob from "primevue/knob";
import Tag from "primevue/tag";
import comunas from "@/shared/comunas.json"; // Importa el JSON
import { info } from "autoprefixer";

const props = defineProps({
  eventos: Object,
  votos: Object,
  candidatos: Object,
  proyectos: Object,
  eventos_admin: Object,
  votantes: Object,
  info_votante: Object,
});

console.log(props);

const eventosPendientes = ref([]);
const eventosCerrados = ref([]);

onMounted(() => {
  eventosPendientes.value = props.eventos.filter(
    (item) =>
      item.estado != "Cerrado" &&
      item.votantes.length > 0 &&
      item.evento_padre == null
  );
  eventosCerrados.value = props.eventos.filter(
    (item) => item.estado == "Cerrado" && item.votantes.length > 0
  );

  chartData.value = setChartData();
  chartOptions.value = setChartOptions();
});

const $toast = useToast();

//VARS PARA CHART
const chartData = ref();
const chartOptions = ref();

//para identificar routeo con error
const searchParams = new URLSearchParams(window.location.search);
const errorMessage = searchParams.get("error");

//var de event select
const evento_selected = ref(
  props.eventos_admin.length ? props.eventos_admin[0].id : ""
);

//tiempo carga milisegundos (5 min)
const reloadInterval = 5 * 60 * 1000;
//informacion de evento, segun seleccionado
const evento_info = ref(
  props.eventos_admin.length
    ? props.eventos_admin.find((item) => item.id == evento_selected.value)
    : []
);
//tipos de votantes segun evento

const tipos = ref(
  evento_info.value.tipos != "NA"
    ? evento_info.value.tipos.replace(/\s*\|\s*/g, ", ").split(", ")
    : "NA"
);

//Mostrar votos segun tipo
const showVotosXtipo = () => {
  let votoXtipo = [];
  tipos.value.forEach((element) => {
    votoXtipo.push(
      evento_info.value.votos.filter((item) => item.tipo == element).length
    );
  });

  return votoXtipo
    .join(", ")
    .split(",")
    .map((label) => label.trim());
};

//get img banners
const getBannerImg = (url) => `assets/img/banners/${url}.webp`;

//get comuna name
const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna)?.label;
};

//chartdata
const setChartData = () => {
  return {
    labels: transformLabels(
      evento_info.value.tipos != "NA"
        ? evento_info.value.tipos.replace(/\s*\|\s*/g, ", ")
        : "NA"
    ),
    datasets: [
      {
        label: "Votos",
        data: showVotosXtipo(),

        backgroundColor: ["rgba(59, 130, 246, 1)"],
      },
    ],
  };
};

//set style chart
const setChartOptions = () => {
  const textColor = "#3b82f6";
  const textColorSecondary = "#fff";
  const surfaceBorder = "#000";

  return {
    indexAxis: "y",
    maintainAspectRatio: false,
    aspectRatio: 2,
    plugins: {
      legend: {
        labels: {
          color: textColorSecondary,
        },
      },
    },
    scales: {
      x: {
        ticks: {
          color: textColorSecondary,
          font: {
            weight: 500,
          },
        },
        grid: {
          display: false,
          drawBorder: true,
        },
      },
      y: {
        ticks: {
          color: textColorSecondary,
        },
        grid: {
          color: surfaceBorder,
          drawBorder: true,
        },
      },
    },
  };
};

function transformLabels(labelString) {
  return labelString.split(",").map((label) => label.trim());
}

const info_events = useForm({
  votos:
    evento_info.value.votos != null
      ? props.eventos_admin.find((item) => item.id == evento_selected.value)
          .votos.length
      : 0,
  votantes: props.votantes.filter(
    (item) => item.id_evento == evento_selected.value
  ).length,
});

if (errorMessage) {
  // Mensaje de error
  console.log(errorMessage);
  let instance = $toast.open({
    message: "Usted ya ha realizado el voto, No puede volver a votar",
    type: "error",
    position: "top-right",
    duration: 8000,
    pauseOnHover: true,
  });
}

const handleEnterKey = () => {
  evento_info.value = props.eventos_admin.length
    ? props.eventos_admin.find((item) => item.id == evento_selected.value)
    : [];

  info_events.votos =
    evento_info.value.votos != null ? evento_info.value.votos.length : 0;

  tipos.value =
    evento_info.value.tipos != "NA"
      ? evento_info.value.tipos.replace(/\s*\|\s*/g, ", ").split(", ")
      : "NA";
  info_events.votantes = props.votantes.filter(
    (item) => item.id_evento == evento_selected.value
  ).length;
  chartData.value = setChartData();
  chartOptions.value = setChartOptions();
};

//dercargar certificado
const descargarCertificado = (ev) => {
  window.open(route("certificados.descargar", ev), "_blank");
};
</script>

<style>
.p-knob-text {
  fill: white !important;
}
</style>
