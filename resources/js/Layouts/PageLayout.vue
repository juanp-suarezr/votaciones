<template>
  <div
    class="flex flex-wrap h-full min-h-screen w-full justify-center bg-black px-6 py-6 relative"
  >
    <!-- Fondo con la palabra REGISTRO -->
    <div class="absolute inset-0 bg-pattern h-full min-h-screen"></div>
    <Link
      class="w-full relative flex justify-center mb-2 cursor-pointer"
      href="/welcome"
    >
      <img
        class="w-full sm:max-w-6xl rounded-lg shadow-md bg-white"
        src="/assets/img/voto_electronico.png"
      />
    </Link>
    <!-- Contenido del slot -->
    <slot />
    <!-- aviso en construccion -->
    <!-- <div
      class="lg:flex fixed top-2 z-20 p-2 bg-naranja text-white rounded-lg shadow-lg"
    >
      <h4 class="sm:text-5xl text-3xl">
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
  </div>
</template>

<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {
  HomeIcon,
  HomeModernIcon,
  XCircleIcon,
  InformationCircleIcon,
  ChatBubbleLeftRightIcon,
} from "@heroicons/vue/24/solid";
import { Link, useForm } from "@inertiajs/vue3";
import { Textarea } from "primevue";
import { inject, ref, computed, watch, onMounted } from "vue";

const swal = inject("$swal");

const form = useForm({
  nombre: "",
  identificacion: "",
  celular: "",
  descripcion: "",
  campo_obligatorio: "",
});

defineProps(["isRegister"]);

const STORAGE_KEY = "panel_visible";

// Inicializar el panel desde localStorage o por tamaño de pantalla
const mostrarPanel = ref(false);

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

<style scoped>
/* Fondo con la palabra REGISTRO repetida */
.bg-pattern {
  background: radial-gradient(
      transparent 34%,
      #ffffff 35%,
      #ffffff 45%,
      transparent 46%
    ),
    radial-gradient(
      circle at left,
      transparent 39%,
      #ffffff 40%,
      #ffffff 45%,
      transparent 46%
    ),
    radial-gradient(
      circle at right,
      transparent 39%,
      #ffffff 40%,
      #ffffff 45%,
      transparent 46%
    );
  background-size: 6em 6em;
  background-color: #f2f2f2;
}
</style>
