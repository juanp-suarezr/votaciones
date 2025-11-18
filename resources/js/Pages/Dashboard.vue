<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header> Dashboard </template>

    <!-- si tiene correcciones -->
    <div
      class="items-center px-4 m-auto"
      v-if="
        info_votante[0]?.subtipo != 0 && info_votante[0]?.estado == 'Rechazado'
      "
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

    <!-- VISTA GESTOR -->
    <div v-if="$page.props.user.roles.includes('Gestor')" class="">
      <h2>Registrar usuario</h2>
      <!-- buscador de cedula para validar votacion -->
      <div class="mt-4 sm:w-1/2">
        <h2 class="text-gray-600 text-2xl inline-flex">
          Validar numero de identificación
        </h2>
        <form
          @submit.prevent="validarVotante(cedulaVotante)"
          @keydown.enter="validarVotante(cedulaVotante)"
          class="flex items-center mt-2"
        >
          <TextInput
            v-model="cedulaVotante"
            type="number"
            placeholder="Ingrese numero de identificación"
            class="block w-auto"
          />
          <PrimaryButton
            type="submit"
            class="ml-2 flex h-full justify-center items-center"
            :class="{ 'opacity-25': isLoading }"
            :disabled="isLoading"
          >
            Validar
          </PrimaryButton>
        </form>
      </div>
    </div>

    <!-- votantes -->
    <div
      id="driver1"
      v-if="$page.props.user.roles.includes('Usuarios')"
      class=""
    >
      <h2
        v-if="$page.props.auth.user.email == 'ppt'"
        class="text-gray-600 text-2xl"
      >
        Elecciones Presupuesto Participativo
      </h2>
      <h2 class="text-gray-600 text-2xl">Votaciones pendientes</h2>
      <p
        v-if="$page.props.auth.user.email == 'ppt'"
        class="text-gray-600 text-lg"
      >
        Si hay varias vigencias para votar, ingrese a cada una y seleccione el
        proyecto que considere más beneficioso para su comunidad
      </p>

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
              !ev.tipos.includes('withBanner') &&
              (ev.hash_proyectos_count > 0 || !ev.tipos.includes('Proyecto'))
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
            id="driver2"
            v-if="
              ev.votantes[0].estado == 'Activo' &&
              ev.tipos.includes('withBanner') &&
              (ev.hash_proyectos_count > 0 || !ev.tipos.includes('Proyecto'))
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
                ev.estado == 'Activo'
                  ? route('votos.index', {
                      evento: ev.id,
                      tipo_evento: ev.tipos,
                      tipo_user:
                        ev.votantes.length != 0 ? ev.votantes[0].tipo : '',
                      subtipo_user:
                        ev.votantes.length != 0 ? ev.votantes[0].subtipo : '',
                    })
                  : null
              "
            >
              <img
                :src="
                  getBannerImg('banner_' + (ev.is_hijo ? ev.id_padre : ev.id))
                "
                alt="Imagen de evento"
                class="w-full h-full object-cover"
                :class="{
                  'filter grayscale opacity-70': votos.find(
                    (item) => item.id_eventos == ev.id
                  ),
                }"
              />
            </a>
            <h4 class="m-auto sm:text-4xl text-xl w-full mt-4 px-2">
              {{ getRealName(ev.nombre) }}
            </h4>
            <p class="m-auto w-full">
              <span class="sm:text-xl text-sm text-gray-600 px-2"
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
              v-if="
                votos.find((item) => item.id_eventos == ev.id) &&
                ev.tipos.includes('Proyecto')
              "
              class="sm:text-2xl text-base text-gray-800 mt-4"
            >
              <PrimaryButton
                class="!bg-naranja"
                @click="descargarCertificado(ev.id, ev.id_padre)"
              >
                Descargar certificado {{ ev.id_padre }}
              </PrimaryButton>
            </div>

            <PrimaryLink
              v-if="
                ev.estado == 'Activo' &&
                !votos.find((item) => item.id_eventos == ev.id)
              "
              class="sm:text-2xl text-xl m-auto mt-2 driver3"
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
          <div
            v-if="!ev.tipos.includes('Proyecto') || ev.hash_proyectos_count > 0"
            class="border-2 border-gray-400 px-1 py-2 border-dashed"
          >
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
    <!-- JURADO -->
    <div
      class=""
      v-if="$page.props.user.roles.includes('Jurado') && cierre == false"
    >
      <div class="w-full sm:px-8 px-4 mb-4" v-if="!props.registro_biometrico">
        <div class="border-2 border-gray-400 p-2 border-dashed sm:flex">
          <p class="m-auto">
            Recuerde registrar su biometrico para poder empezar las votaciones.
          </p>
          <PrimaryLink
            class="md:text-base mt-4"
            :class="{ 'opacity-25': isLoading }"
            :disabled="isLoading"
            type="button"
            :href="route('registro-biometrico-jurado')"
          >
            Registro Biometrico
          </PrimaryLink>
        </div>
      </div>
      <h2 class="text-gray-600 text-2xl inline-flex">
        Gestión de registro electrónico en mesa
      </h2>
      <!-- boton registros -->
      <div
        v-if="existe_acta && props.registro_biometrico"
        class="sm:flex justify-between gap-4"
      >
        <PrimaryLink
          class="md:text-base mt-4"
          :class="{ 'opacity-25': isLoading }"
          :disabled="isLoading"
          type="button"
          :href="route('votantesPresencial.create')"
        >
          Registro Voto Electrónico en Mesa
        </PrimaryLink>
        <DangerButton
          class="md:text-base mt-4"
          :class="{ 'opacity-25': isLoading }"
          :disabled="isLoading"
          type="button"
          @click="cerrarEventoModal = true"
        >
          Cerrar votaciones en mesa
        </DangerButton>
      </div>
      <div v-else-if="props.registro_biometrico">
        <PrimaryLink
          class="md:text-base mt-4"
          :class="{ 'opacity-25': isLoading }"
          :disabled="isLoading"
          type="button"
          :href="route('ActaInicial.create')"
        >
          Iniciar votación en mesa electrónico
        </PrimaryLink>
      </div>
      <!-- buscador de cedula para validar votacion -->
      <div class="mt-4 sm:w-1/2">
        <h2 class="text-gray-600 text-2xl inline-flex">
          Buscar votante por número de identificación
        </h2>
        <form
          @submit.prevent="buscarVotante(cedulaVotante)"
          @keydown.enter="buscarVotante(cedulaVotante)"
          class="flex items-center mt-2"
        >
          <TextInput
            v-model="cedulaVotante"
            type="number"
            placeholder="Ingrese numero de identificación del votante"
            class="block w-auto"
          />
          <PrimaryButton
            type="submit"
            class="ml-2 flex h-full justify-center items-center"
            :class="{ 'opacity-25': isLoading }"
            :disabled="isLoading"
          >
            Buscar
          </PrimaryButton>
        </form>
      </div>
    </div>
    <div class="px-4 flex justify-center items-center" v-else-if="cierre">
      <div
        class="w-full max-w-4xl p-6 shadow-md rounded-md flex items-center justify-center bg-secondary hover:bg-primary hover:scale-105 text-white sm:text-4xl text-xl text-center"
      >
        Mesa de votación electrónica cerrada
        <br />
        Actas de cierre generadas
      </div>
    </div>
  </AuthenticatedLayout>

  <!-- Modal para mostrar mensaje de confirmación -->
  <div
    v-if="cerrarEventoModal"
    class="fixed inset-0 bg-black px-4 sm:px-8 bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white p-4 rounded-lg max-w-xl w-full">
      <h2
        class="sm:text-4xl text-xl font-bold mb-8 pb-4 sm:py-8 border-b border-gray-300 text-center"
      >
        🟥 Confirmar acción 🟥
      </h2>
      <p class="mb-4 text-base sm:text-4xl">
        <span class="text-base sm:text-2xl font-bold"
          >Está a punto de cerrar la votación. Confirme para continuar:</span
        >
      </p>

      <div class="w-full flex flex-wrap justify-center gap-4 sm:gap-6 mt-6">
        <button
          @click="cerrarEventoModal = false"
          class="bg-red-600 text-white px-4 py-2 rounded sm:text-2xl"
        >
          Cancelar
        </button>
        <button
          :class="{ 'opacity-25': isLoading }"
          :disabled="isLoading"
          @click="ActaCierre()"
          class="bg-indigo-600 text-white px-4 py-2 rounded sm:text-2xl"
        >
          Confirmar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryLink from "@/Components/PrimaryLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, watch, inject, onMounted } from "vue";
import { useToast } from "vue-toast-notification";
import Message from "primevue/message";
import Knob from "primevue/knob";
import Tag from "primevue/tag";
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON
import { info } from "autoprefixer";
import axios from "axios";
import TextInput from "@/Components/TextInput.vue";

import Chart from "primevue/chart";
import ChartDataLabels from "chartjs-plugin-datalabels";
import {
  Chart as ChartJS,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from "chart.js";
import DangerButton from "@/Components/DangerButton.vue";

ChartJS.register(
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  ChartDataLabels
);

//drivers
import { driver } from "driver.js";
import "driver.js/dist/driver.css";

const swal = inject("$swal");

const props = defineProps({
  eventos: Object,
  votos: Object,
  candidatos: Object,
  proyectos: Object,
  eventos_admin: Object,
  votantes: Object,
  info_votante: Object,
  existe_acta: Boolean,
  cierre: Boolean,
  registro_biometrico: Boolean,
});

console.log(props);

const isLoading = ref(false);

const eventosPendientes = ref([]);
const eventosCerrados = ref([]);

//variable de cedula votante
const cedulaVotante = ref("");

//modal cerrar evento
const cerrarEventoModal = ref(false);
//acta cierre?
const actaCerrada = ref(props.cierre);

onMounted(() => {
  // Lista final solo de hijos
  let pendientesHijos = [];
  let cerradosHijos = [];

  //EVENTOS PENDIENTES LOGICA
  pendientesHijos = props.eventos.filter(
    (item) =>
      item.estado != "Cerrado" &&
      item.votantes.length > 0 &&
      (!item.eventos_hijos || item.eventos_hijos.length === 0)
  );

  props.eventos.forEach((eventoPadre) => {
    // Si tiene hijos
    if (
      eventoPadre.estado != "Cerrado" &&
      eventoPadre.votantes.length > 0 &&
      Array.isArray(eventoPadre.eventos_hijos) &&
      eventoPadre.eventos_hijos.length > 0
    ) {
      eventoPadre.eventos_hijos.forEach((hijoObj) => {
        if (hijoObj.eventos) {
          // Copia el hijo y asigna el votante del padre
          const hijo = { ...hijoObj.eventos };
          hijo.votantes = eventoPadre.votantes ? [...eventoPadre.votantes] : [];
          hijo.id_padre = eventoPadre.id; // Opcional: para referencia al padre
          hijo.is_hijo = true; // Marca que es un hijo
          pendientesHijos.push(hijo);
        }
      });
    }
  });

  eventosPendientes.value = pendientesHijos;

  //EVENTOS CERRADOS LOGICA
  // Si no hay hijos, muestra los eventos que no son padres

  pendientesHijos = props.eventos.filter(
    (item) =>
      item.estado == "Cerrado" &&
      item.votantes.length > 0 &&
      (!item.eventos_hijos || item.eventos_hijos.length === 0)
  );
  // Recorre los eventos padres para encontrar y agregar sus hijos
  props.eventos.forEach((eventoPadre) => {
    // Si tiene hijos
    if (
      eventoPadre.estado == "Cerrado" &&
      eventoPadre.votantes.length > 0 &&
      Array.isArray(eventoPadre.eventos_hijos) &&
      eventoPadre.eventos_hijos.length > 0
    ) {
      eventoPadre.eventos_hijos.forEach((hijoObj) => {
        if (hijoObj.eventos) {
          // Copia el hijo y asigna el votante del padre
          const hijo = { ...hijoObj.eventos };
          hijo.votantes = eventoPadre.votantes ? [...eventoPadre.votantes] : [];
          hijo.id_padre = eventoPadre.id; // Opcional: para referencia al padre
          hijo.is_hijo = true; // Marca que es un hijo
          cerradosHijos.push(hijo);
        }
      });
    }
  });

  eventosCerrados.value = cerradosHijos;

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

let inicioVotacion = localStorage.getItem("inicioVotacion");

if (props.existe_acta && inicioVotacion == null && actaCerrada == false) {
  inicioVotacion = true;
  localStorage.setItem("inicioVotacion", true);
}

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

//get vigencia año text
const getRealName = (text) => {
  if (typeof text !== "string") return "";

  const keyword = "vigencia";
  const index = text.toLowerCase().lastIndexOf(keyword);
  console.log(text.toLowerCase());

  if (index === -1) {
    return text; // si no encuentra la palabra, devuelve todo
  }

  return text.slice(index).trim();
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
      datalabels: {
        color: "white",
        formatter: (value) => value,
        font: {
          weight: "bold",
          size: 12,
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

if (
  props.existe_acta == true &&
  inicioVotacion == true &&
  actaCerrada == false
) {
  localStorage.setItem("inicioVotacion", false);
  let instance = $toast.open({
    message: "Votación iniciada, actas de inicio registradas",
    type: "success",
    position: "top-right",
    duration: 8000,
    pauseOnHover: true,
  });
}

if (errorMessage) {
  console.log(usePage().props);

  if (usePage().props.auth.user.jurado) {
    let instance = $toast.open({
      message:
        "Ya se ha registrado las actas para este jurado, comuna y puesto de votación, en las vigencias correspondientes",
      type: "error",
      position: "top-right",
      duration: 8000,
      pauseOnHover: true,
    });
  } else {
    let instance = $toast.open({
      message: "Usted ya ha realizado el voto, No puede volver a votar",
      type: "error",
      position: "top-right",
      duration: 8000,
      pauseOnHover: true,
    });
  }
}

//abrir modal de confirmar cerrar votaciones
const ActaCierre = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get("/ActaCerrar");

    isLoading.value = false;
    actaCerrada.value = true;
    cerrarEventoModal.value = false;
    swal.fire({
      icon: "success",
      title: "Acta de cierre generada",
      text: "Mesa de votación electrónica cerrada:ingreso y emisión de votos deshabilitados.",
      didClose: () => {
        //poner llamado a modal de botones
        window.location.reload();
      },
    });

    return response.data;
  } catch (error) {
    isLoading.value = false;
    swal.fire({
      icon: "error",
      title: "Error",
      text: "Ocurrió un error al cerrar la votación. Intenta de nuevo.",
      confirmButtonColor: "#d33",
    });
    return false;
  }
};

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
const descargarCertificado = (ev, id_padre) => {
  window.open(
    route("certificados.descargar", {
      id: ev,
      idVotante: 0,
      id_padre: id_padre,
    }),
    "_blank"
  );
};

//buscar votante por cedula
//llamado validador identificacion
const buscarVotante = async (identificacion) => {
  isLoading.value = true;
  try {
    const response = await axios.post("/validar-identificacion-presencial", {
      identificacion,
      registro_presencial: true,
    });

    isLoading.value = false;

    if (response.data.existe) {
      if (response.data.votante && response.data.votante.votos.length > 0) {
        swal.fire({
          icon: "warning",
          title: "Votante con voto",
          text: "El votante ya ha votado.",
          confirmButtonColor: "#d33",
        });
      } else {
        swal.fire({
          icon: "success",
          title: "Votante encontrado",
          text: "El votante, se registro pero no ha votado.",
          confirmButtonColor: "#3085d6",
        });
      }
    } else {
      swal.fire({
        icon: "success",
        title: "Votante no encontrado",
        text: "No se encontró un votante con ese numero de identificación registrado previamente.",
        confirmButtonColor: "#3085d6",
      });
    }
    return response.data;
  } catch (error) {
    isLoading.value = false;
    swal.fire({
      icon: "error",
      title: "Error",
      text: "Ocurrió un error al validar la identificación del votante. Intenta de nuevo.",
      confirmButtonColor: "#d33",
    });
    return false;
  }
};

const validarVotante = async (identificacion) => {
  isLoading.value = true;
  try {
    const response = await axios.post("/validar-identificacion-presencial", {
      identificacion,
    });

    isLoading.value = false;

    if (response.data.existe) {
      swal.fire({
        icon: "success",
        title: "Votante encontrado",
        text: "La persona ya cuenta con registro realizado virtualmente.",
        confirmButtonColor: "#3085d6",
      });
    } else {
      if (response.data.votante && response.data.votante.votos.length > 0) {
        swal.fire({
          icon: "warning",
          title: "Votante con voto",
          text: "El votante ya ha realizado su registro y voto en mesa, en esta vigencia.",
          confirmButtonColor: "#d33",
        });
      } else {
        console.log("entro");

        router.get("/registro-gestion-administrativa", {
          identificacion: identificacion,
          id_votante: response.data.votante ? response.data.votante.id : null,
        });
      }
    }
    return response.data;
  } catch (error) {
    console.log(error);

    isLoading.value = false;
    swal.fire({
      icon: "error",
      title: "Error",
      text: "Ocurrió un error al validar la identificación de la persona. Intenta de nuevo.",
      confirmButtonColor: "#d33",
    });
    return false;
  }
};

onMounted(() => {
  console.log(eventosPendientes.value);

  // === DRIVER TOUR PARA NUEVOS USUARIOS ===
  if (
    usePage().props.user.roles.includes("Usuarios") && // Es votante
    eventosPendientes.value.length > 0 && // Tiene eventos activos
    eventosPendientes.value.some(
      (ev) =>
        typeof ev.tipos === "string" &&
        ev.tipos.split("|").includes("Presupuesto Participativo")
    ) && // Aún no tiene votos
    props.info_votante[0].estado === "Activo" &&
    props.info_votante[0].votante.Isdriver === 0 // Solo primera vez
  ) {
    const tutorial = driver({
      showProgress: true, // barra de progreso
      allowClose: true,
      overlayOpacity: 0.8,
      stagePadding: 8,
      keyboardControl: true,
      popoverClass: "driverjs-theme",
      nextBtnText: "Siguiente",
      prevBtnText: "Anterior",
      closeBtnText: "Cerrar",
      doneBtnText: "Finalizar",

      steps: [
        {
          element: "#driver1",
          popover: {
            title: "¡Bienvenido!",
            description:
              "En este espacio podrás participar en las votaciones activas de tu comuna o corregimiento.",
            side: "bottom",
            align: "center",
          },
        },
        {
          element: "#driver2",
          popover: {
            title: "Vigencias disponibles",
            description:
              "Para votar verás los banners organizados por vigencia, según la comuna/corregimiento en la que te inscribiste.",
            side: "top",
            align: "start",
          },
        },
        {
          element: ".driver3",
          popover: {
            title: "¿Cómo votar?",
            description:
              'Para votar en cada vigencia, presiona el botón azul "Votar". Irás al proceso de votación; al regresar al dashboard podrás continuar votando por las otras vigencias disponibles.',
            side: "left",
            align: "center",
          },
        },
      ],
    });

    // ✅ Iniciar el tour con la nueva API
    tutorial.drive();

    // avisar al backend que ya lo vio (tu endpoint /marcar-driver)
    axios.post("/marcar-driver", { isDriver: true }).catch((e) => {
      // si falla el post no interrumpimos la experiencia, pero puedes loguearlo
      console.warn("No se pudo marcar driver en backend:", e);
    });
  }
});
</script>

<style>
.p-knob-text {
  fill: white !important;
}

.driver-popover.driverjs-theme {
  background-color: #d3d8f7;
  color: #000;
  width: auto;
  max-width: 400px;
}

.driver-popover.driverjs-theme .driver-popover-title {
  font-size: 30px;
}

.driver-popover.driverjs-theme .driver-popover-title,
.driver-popover.driverjs-theme .driver-popover-description,
.driver-popover.driverjs-theme .driver-popover-progress-text {
  color: #000;
  font-size: 20px;
}

.driver-popover.driverjs-theme button {
  flex: 1;
  flex-wrap: wrap;
  text-align: center;
  background-color: #000;
  color: #ffffff;
  border: 2px solid #000;
  text-shadow: none;
  font-size: 14px;
  padding: 5px 8px;
  border-radius: 6px;
}

.driver-popover.driverjs-theme button:hover {
  background-color: #000;
  color: #ffffff;
}

.driver-popover.driverjs-theme .driver-popover-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.driver-popover.driverjs-theme .driver-popover-navigation-btns {
  justify-content: space-between;
  gap: 3px;
}

.driver-popover.driverjs-theme .driver-popover-close-btn {
  color: #9b9b9b;
}

.driver-popover.driverjs-theme .driver-popover-close-btn:hover {
  color: #000;
}

.driver-popover.driverjs-theme
  .driver-popover-arrow-side-left.driver-popover-arrow {
  border-left-color: #fde047;
}

.driver-popover.driverjs-theme
  .driver-popover-arrow-side-right.driver-popover-arrow {
  border-right-color: #fde047;
}

.driver-popover.driverjs-theme
  .driver-popover-arrow-side-top.driver-popover-arrow {
  border-top-color: #fde047;
}

.driver-popover.driverjs-theme
  .driver-popover-arrow-side-bottom.driver-popover-arrow {
  border-bottom-color: #fde047;
}
</style>
