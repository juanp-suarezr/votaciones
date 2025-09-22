<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";
import Pagination from "@/Components/Pagination.vue";
import ProgressSpinner from "primevue/progressspinner";
import {
  XCircleIcon,
  InformationCircleIcon,
  ChatBubbleLeftRightIcon,
} from "@heroicons/vue/24/solid";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
const swal = inject("$swal");
import AOS from "aos";
import "aos/dist/aos.css";

AOS.init();

const props = defineProps({
  eventos: Object,
  puntos_votacion: Object,
  isActive: Boolean,
});

console.log(props);

const form = useForm({
  nombre: "",
  identificacion: "",
  celular: "",
  descripcion: "",
  campo_obligatorio: "",
});

const STORAGE_KEY = "panel_visible";

// Inicializar el panel desde localStorage o por tamaño de pantalla
const mostrarPanel = ref(false);

const IsSecondTime = ref(false);
//loading
const loading = ref(false);
//info
const registro = ref([]);

//is disabledsegun fecha
const eventoActivo = ref(props.isActive);

// pass filters in search
let identificacion = ref(null);

//busqueda
const buscarCertificados = async () => {
  loading.value = true;
  try {
    const response = await axios.get(
      `/validar-certificado/${identificacion.value}`
    );
    registro.value = response.data.registro; // Aquí recibes el paginado
  } catch (error) {
    registro.value = [];
    swal.fire({
      icon: "error",
      title: "Error",
      text: "Error en el proceso de búsqueda.",
    });
    // Puedes mostrar un swal de error aquí si lo deseas
  } finally {
    loading.value = false;
    IsSecondTime.value = true;
  }
};

//Liampiar filtros
const clearAll = () => {
  identificacion.value = "";
  registro.value = [];
};

//dercargar certificado
const descargarCertificado = (ev, idVotante, id_padre) => {
  window.open(
    route("certificados.descargar", {
      id: ev,
      idVotante: idVotante,
      id_padre: id_padre,
    }),
    "_blank"
  );
};

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY);

  if (saved !== null) {
    mostrarPanel.value = saved === "true";
  } else {
    // Default para pantallas grandes
    mostrarPanel.value = window.innerWidth >= 768;
  }
});

// Guardar el estado cada vez que cambia
watch(mostrarPanel, (nuevoValor) => {
  localStorage.setItem(STORAGE_KEY, nuevoValor);
});

const cambiarVista = () => {
  mostrarPanel.value = !mostrarPanel.value;
};

//enviar msj
const enviarSolicitud = () => {
  form.post(route("enviarSolicitud"), {
    onSuccess: function () {
      swal({
        title: "Solicitud enviada con éxito",
        text: "La solicitud ha sido aprobado exitosamente, muy pronto recibirá respuesta",
        icon: "success",
      }).then(() => {
        mostrarPanel.value = false;
      });
    },
    onError: function () {
      swal({
        title: "Error al enviar la solicitud",
        text: "Error al intentar enviar este registro, por favor vuelve a intentar",
        icon: "error",
      });
    },
  });
};
</script>

<template>
  <Head title="Welcome" />
  <link
    rel="preload"
    as="image"
    href="/assets/img/Bannervotoelectronico_1.png"
  />

  <div
    class="relative min-h-screen bg-dots-darker bg-option2 bg-center selection:bg-red-500 selection:text-white flex items-center justify-center body-landing"
  >
    <!-- aviso en construccion -->
    <!-- <div class="lg:flex fixed top-2 z-20 p-2 bg-naranja text-white rounded-lg shadow-lg">
        <h4 class="sm:text-5xl text-xl">
            Pagina en construcción, pronto nuevas actualizaciones
        </h4>
  </div> -->
    <!-- Botón flotante para abrir/cerrar panel -->
    <button
      @click="cambiarVista"
      class="lg:flex fixed right-4 bottom-6 z-20 p-2 bg-blue-700 hover:bg-blue-800 text-white rounded-full shadow-lg"
      title="Mostrar/Ocultar información"
    >
      <component
        :is="mostrarPanel ? XCircleIcon : InformationCircleIcon"
        class="w-6 h-6 lg:w-8 lg:h-8"
      />
      <p class="my-auto ps-2 text-lg sm:block hidden">
        {{ mostrarPanel ? "" : " Soporte" }}
      </p>
    </button>
    <!-- Panel fijo lateral -->
    <div
      v-show="mostrarPanel"
      class="lg:block fixed right-4 top-[10%] lg:w-72 md:w-64 w-56 space-y-4 z-10 transition-all duration-300"
    >
      <!-- Tarjeta de soporte -->
      <div class="bg-white shadow-md rounded-xl p-4 border border-red-200">
        <h4
          class="text-sm md:text-base font-semibold text-red-700 mb-2 flex items-center gap-1"
        >
          <InformationCircleIcon class="h-4 w-4" />
          ¿Necesitas soporte?
        </h4>
        <!-- Formulario de solicitud de soporte -->
        <div class="bg-white shadow-md rounded-xl p-4 border border-blue-200">
          <h4 class="text-sm md:text-base font-semibold text-blue-700 mb-2">
            Enviar solicitud
          </h4>
          <form @submit.prevent="enviarSolicitud">
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700"
                >Identificación</label
              >
              <input
                v-model="form.identificacion"
                type="number"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700">Celular</label>
              <input
                v-model="form.celular"
                type="tel"
                class="w-full p-2 rounded border border-gray-300 text-sm"
                required
              />
            </div>
            <div class="mb-2">
              <label class="text-xs md:text-sm text-gray-700"
                >Descripción</label
              >
              <Textarea
                v-model="form.descripcion"
                variant="filled"
                autoResize
                rows="3"
                class="mt-1 block w-full"
                required
                autocomplete="descripcion"
              />
            </div>
            <div class="mb-2 hidden">
              <label class="text-xs md:text-sm text-gray-700"
                >campo obligatorio</label
              >
              <input
                v-model="form.campo_obligatorio"
                rows="3"
                class="w-full p-2 rounded border border-gray-300 text-sm"
              />
            </div>
            <PrimaryButton
              class="mt-2"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Enviar
            </PrimaryButton>
          </form>
        </div>
      </div>
    </div>

    <div class="w-full">
      <div
        class="sm:flex justify-start mt-2 sm:px-16 px-4 py-4 overflow-x-hidden"
      >
        <div
          class="flex flex-col sm:flex-row gap-4 items-center hover:scale-105"
        >
          <img
            src="/assets/img/logo1.png"
            alt="Logo"
            class="sm:h-32 h-24 w-auto sm:border-r border-black"
          />
          <img
            src="/assets/img/voto_electronico.png"
            alt="Logo"
            class="sm:h-32 h-24 w-auto"
          />
        </div>
        <!-- <div class="w-full flex justify-end items-center">
          <a class="text-white text-xl" href="#certificados_info">
            Consultar certificado
          </a>
        </div> -->

        <div class="w-full flex justify-end items-center">
          <a
            class="text-black sm:text-2xl text-xl"
            href="/resultado-seleccionar-comuna"
          >
            Resultados votaciones
          </a>
        </div>
      </div>
      <!-- banner inicial -->
      <div class="w-full shadow-md overflow-hidden">
        <img
          src="/assets/img/Bannervotoelectronico.png"
          alt="Banner Principal"
          class="w-full h-full"
        />
      </div>

      <div class="sm:px-16 px-4 mt-8">
        <!-- seccion 1 -->
        <div
          class="grid grid-cols-1 md:grid-cols-5 gap-8 items-center bg-gray-200/60 rounded-xl shadow-lg px-8 pt-2 pb-4 mb-12"
        >
          <!-- Texto lado izquierdo -->
          <div
            class="flex flex-col justify-center items-start md:col-span-3 lg:px-16"
          >
            <h1
              class="text-5xl md:text-8xl font-extrabold text-naranja mb-6 leading-tight"
            >
              ¡Vota Ya! <br />
              <span class="text-black">Tu voz, tu decisión</span>
            </h1>
            <p class="text-xl md:text-3xl text-gray-800 mb-4">
              Plataforma de votaciones de elecciones de Presupuesto
              Participativo más segura y fácil para votar de forma digital.
            </p>
            <div class="flex justify-center text-center w-full">
              <img
                src="/assets/img/votaYa.png"
                alt="Logo Vota Ya - Participación ciudadana digital"
                class="h-full sm:w-2/6 transform transition-transform duration-500 hover:scale-110 hover:rotate-3 mt-2"
                loading="lazy"
              />
            </div>
          </div>

          <!-- Video lado derecho -->
          <div class="flex flex-col justify-center items-start md:col-span-2">
            <h2 class="text-2xl md:text-5xl font-bold text-primary mb-6 mt-4">
              ¿Sabes qué es el Presupuesto Participativo?
            </h2>
            <!-- Contenedor responsive -->
            <div
              class="w-full aspect-video shadow-md rounded-xl overflow-hidden"
            >
              <iframe
                class="w-full h-full"
                src="https://www.youtube.com/embed/w1sDLE88FqA?rel=0&modestbranding=1&playsinline=1"
                title="Video oficial de Vota Ya - Cómo participar en las elecciones digitales"
                frameborder="0"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              ></iframe>
            </div>

            <!-- Texto descriptivo para SEO -->
            <p class="mt-4 text-gray-700 text-lg sm:text-xl">
              En este video te contamos qué es el <b>Presupuesto Participativo</b> y
              cómo puedes ser parte de las <b>decisiones</b> que transforman tu
              <b>comunidad.</b>
            </p>
          </div>
        </div>

        <!-- seccion 2 paso a paso presupuesto participativo -->
        <div
          data-aos="fade-up"
          class="md:grid md:grid-cols-3 gap-8 items-stretch bg-gray-200/70 rounded-xl shadow-lg p-8 mb-12"
        >
          <!-- info general -->
          <div
            class="flex flex-col justify-center items-center col-span-3 mb-4"
          >
            <h2 class="text-2xl sm:text-5xl text-center font-bold text-black">
              Pasos para participar en las votaciones del
            </h2>
            <h2
              class="bg-primary px-2 !py-0 text-white text-2xl sm:text-5xl text-center font-bold"
            >
              Presupuesto Participativo
            </h2>
          </div>
          <!-- Paso 1 -->
          <div
            class="flex flex-col justify-between items-start bg-gray-400/40 rounded-lg p-6 shadow mb-2"
          >
            <h2 class="text-2xl sm:text-4xl font-bold text-naranja mb-4">
              1. Regístrate
            </h2>
            <p class="text-lg md:text-xl text-parrafo mb-4">
              Ingresa tus datos personales, sube foto de tu documento y realiza
              el registro biométrico. Recibirás una notificación por correo si
              tu registro fue aprobado. Si es rechazado, podrás corregir la
              información.
            </p>
            <Link
              href="#registro_info"
              class="mt-auto px-4 py-2 bg-red-600 text-white text-lg md:text-xl rounded shadow hover:bg-red-700 transition"
            >
              Instructivo
            </Link>
            <Link
              :href="route('register')"
              class="mt-2 px-4 py-2 bg-blue-600 text-white text-lg md:text-xl rounded-lg shadow hover:bg-red-700 transition"
            >
              ¡Regístrate -- Presupuesto Participativo!
            </Link>
          </div>
          <!-- Paso 2 -->
          <div
            class="flex flex-col justify-between items-start bg-gray-400/40 rounded-lg p-6 shadow mb-2"
            :class="{ '!bg-gray-600': eventoActivo == false }"
          >
            <h2
              class="text-2xl sm:text-4xl font-bold text-red-600 mb-4"
              :class="{ '!text-red-800': eventoActivo == false }"
            >
              2. Vota por tu proyecto
            </h2>
            <p class="text-lg md:text-xl text-parrafo mb-4">
              En las fechas estipuladas, ingresa al aplicativo de Presupuesto
              Participativo y vota por el proyecto de tu preferencia.
            </p>
            <Link
              :href="eventoActivo == false ? null : route('login-pp')"
              @click.prevent="
                eventoActivo == false ? null : $inertia.visit(route('login-pp'))
              "
              class="mt-auto px-4 py-2 text-white text-lg md:text-xl rounded shadow transition"
              :class="{
                'bg-indigo-700 hover:bg-indigo-800': eventoActivo,
                'bg-indigo-200/60 cursor-not-allowed': eventoActivo == false,
              }"
            >
              Ir a votación
            </Link>
          </div>
          <!-- Paso 3 -->
          <div
            class="flex flex-col justify-between items-start bg-gray-400/40 rounded-lg p-6 shadow mb-2"
          >
            <h2 class="text-2xl sm:text-4xl font-bold text-naranja mb-4">
              3. Descarga tu certificado
            </h2>
            <p class="text-lg md:text-xl md:text-lg text-parrafo mb-4">
              Descarga tu certificado de participación en el aplicativo de
              Presupuesto Participativo o accede a la sección específica para
              obtenerlo.
            </p>
            <Link
              href="#certificados_info"
              class="mt-auto px-4 py-2 bg-green-600 text-white text-lg md:text-xl rounded shadow hover:bg-green-700 transition flex items-center gap-2"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              Descargar certificado
            </Link>
          </div>
        </div>

        <!-- Nuevo grid landing registro info-->
        <div
          data-aos="fade-up"
          id="registro_info"
          class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-gray-200/70 rounded-xl shadow-lg p-8 mb-12"
        >
          <!-- Video lado izquierdo -->
          <div class="flex justify-center items-center">
            <video
              class="rounded-lg w-full h-auto max-h-[400px] shadow-md bg-gray-200 border border-naranja"
              controls
              poster="assets/img/VIDEO EN PRODUCCIÓN.png"
            >
              <source
                src="https://www.w3schools.com/html/mov_bbb.mp4"
                type="video/mp4"
              />
              Tu navegador no soporta el video.
            </video>
          </div>
          <!-- Texto lado derecho -->
          <div class="flex flex-col justify-center items-start">
            <h1
              class="text-4xl md:text-5xl font-extrabold text-red-600 mb-6 leading-tight"
            >
              ¡Registro! <br />
              <span class="text-black">Presupuesto Participativo</span>
            </h1>
            <p class="text-lg md:text-2xl text-gray-300 mb-4 text-parrafo">
              Participa y vota por los proyectos que más beneficien a tu
              comunidad de forma más segura y moderna.
              <br />
              <i>Tienes un plazo para inscribirte del 1 al 31 de octubre</i>
            </p>
            <Link
              :href="route('register')"
              class="mt-4 px-6 py-3 bg-red-600 text-white text-lg md:text-xl rounded-lg shadow hover:bg-red-700 transition"
            >
              ¡Regístrate -- Presupuesto Participativo!
            </Link>
          </div>
        </div>
        <!-- consultar certificados -->
        <div
          id="certificados_info"
          class="w-full bg-gray-200/70 rounded-xl shadow-lg p-8 mb-12"
        >
          <!-- Filtro de búsqueda -->
          <div class="w-full">
            <div class="bg-gray-400/40 shadow-md rounded-lg p-4">
              <span
                class="sm:text-3xl text-xl text-center font-semibold text-black mb-6"
              >
                Ingresar número de identificación para consultar los
                certificados registrados a tu nombre
              </span>
              <div class="sm:flex items-center gap-4 mt-2 h-full w-auto">
                <TextInput
                  id="identificacion"
                  v-model="identificacion"
                  placeholder="Ingrese el número de identificación"
                  class="sm:w-1/2 flex inline-flex mb-2 text-white bg-gray-400/40 text-lg md:text-xl text-parrafo"
                  @keydown.enter="buscarCertificados"
                />
                <SecondaryButton
                  class="bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br text-lg md:text-xl text-white font-normal"
                  @click="buscarCertificados"
                  >Buscar</SecondaryButton
                >
                <SecondaryButton
                  @click="clearAll"
                  class="hover:underline text-lg md:text-xl font-normal"
                >
                  Limpiar
                </SecondaryButton>
              </div>
            </div>
          </div>
          <!-- Tabla de resultados - right side -->
          <div class="mt-6">
            <div class="bg-gray-900/40 shadow-md rounded-lg p-4">
              <div
                v-if="registro.data && registro.data.length > 0"
                class="w-full overflow-x-auto"
              >
                <table
                  class="table-medium w-full border-collapse bg-white rounded-lg shadow-md"
                >
                  <thead
                    class="text-naranja text-bold border-b border-gray-400"
                  >
                    <tr>
                      <th class="px-4 py-2 text-left">#</th>
                      <th class="px-4 py-2 text-left">Nombre del Usuario</th>
                      <th class="px-4 py-2 text-left">Identificación</th>
                      <th class="px-4 py-2 text-left">Evento</th>

                      <th class="px-4 py-2 text-left">Descargar</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(info, index) in registro.data" :key="index">
                      <td class="px-4 py-2 text-parrafo">
                        {{ index + 1 }}
                      </td>
                      <td class="px-4 py-2 text-parrafo break-words">
                        {{ info.votante.nombre || "Sin información" }}
                      </td>
                      <td class="px-4 py-2 text-parrafo">
                        {{ info.votante.tipo_documento }} -
                        {{ info.votante.identificacion }}
                      </td>
                      <td class="px-4 py-2 text-parrafo">
                        {{ info.evento.nombre }}
                      </td>

                      <td class="px-4 py-2 text-parrafo">
                        <PrimaryButton
                          @click="
                            descargarCertificado(
                              info.evento.id,
                              info.votante.id,
                              info.evento.evento_hijo[0]?.evento_padre?.id
                            )
                          "
                        >
                          Descargar certificado
                        </PrimaryButton>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <!-- Diseño vertical para pantallas pequeñas -->
                <div
                  class="table-responsive grid grid-cols-1 gap-4 sm:hidden bg-white rounded-lg shadow-md"
                >
                  <div
                    v-for="(info, index) in registro.data"
                    :key="index"
                    class="rounded-lg p-4 shadow-md text-parrafo"
                  >
                    <p>
                      <strong class="text-naranja">#:</strong> {{ index + 1 }}
                    </p>
                    <p>
                      <strong class="text-naranja">Nombre del Usuario:</strong>
                      {{ info.votante.nombre || "Sin información" }}
                    </p>
                    <p>
                      <strong class="text-naranja">Identificación:</strong>
                      {{ info.votante.tipo_documento }} --
                      {{ info.votante.identificacion }}
                    </p>
                    <p>
                      <strong class="text-naranja">Evento:</strong>
                      {{ info.evento.nombre }}
                    </p>

                    <div class="px-4 py-2 text-parrafo">
                      <PrimaryButton
                        @click="
                          descargarCertificado(
                            info.evento.id,
                            info.votante.id,
                            info.evento.evento_hijo[0]?.evento_padre?.id
                          )
                        "
                      >
                        Descargar certificado
                      </PrimaryButton>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="w-full flex justify-center items-center"
                v-else-if="loading"
              >
                <ProgressSpinner
                  style="width: 50px; height: 50px"
                  strokeWidth="8"
                  fill="transparent"
                  animationDuration=".5s"
                  aria-label="Custom ProgressSpinner"
                />
              </div>
              <div class="w-full flex justify-center items-center" v-else>
                <em
                  v-if="identificacion && IsSecondTime"
                  class="text-xl text-white"
                  >No hay votaciones registradas al usuario con numero de
                  identificación --> {{ identificacion }}.
                </em>
                <div class="w-1/2 flex justify-center items-center" v-else>
                  <div class="bg-azul rounded-md shadow-lg p-4">
                    <h3 class="text-white sm:text-2xl text-xl text-center">
                      Certificado votaciones
                    </h3>
                  </div>
                </div>
              </div>

              <!-- Paginación -->
              <Pagination
                v-if="registro.data && registro.data.length > 0"
                :links="registro.links"
                class="mt-4"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.bg-dots-darker {
  background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

.bg-dots-lighter {
  background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
}

@media screen and (max-width: 900px) {
  .table-medium {
    display: none;
  }

  .table-responsive {
    display: block;
  }
}
</style>
