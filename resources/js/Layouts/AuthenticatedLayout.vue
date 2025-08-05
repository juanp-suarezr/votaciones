<template>
  <div class="flex h-screen bg-gray-200 font-roboto">
    <Navigation />

    <div class="flex flex-1 flex-col overflow-hidden">
      <Header />

      <main class="flex-1 overflow-y-auto mb-4 overflow-x-hidden bg-gray-200">
        <!-- titulo -->
        <div
          class="w-full flex justify-between items-center h-auto p-2 px-4 bg-azul shadow-lg"
        >
          <!-- titulo text -->
          <h3
            class="min-[300px]:text-sm md:text-md lg:text-lg font-medium text-white"
          >
            <slot name="header" />
          </h3>
          <!-- return button -->
          <div class="">
            <!-- si tiene mas de 2 -->
            <div v-if="breadCrumbLinks.length == 2">
              <SecondaryButtonReturn class="!bg-transparent text-white" />
            </div>
            <!-- si tiene solo 1 -->
            <a v-else-if="breadCrumbLinks.length == 1" href="/">
              <HomeIcon class="h-6 w-6 text-white cursor-pointer" />
            </a>
            <!-- Dashboard -->
            <div v-else>
              <HomeModernIcon class="h-6 w-6 text-white" />
            </div>
          </div>
        </div>
        <!-- breadcrumb -->
        <div class="mx-auto px-6 pt-4">
          <BreadCrumb :links="breadCrumbLinks" />
        </div>
        <!-- main part -->
        <div class="mx-auto px-6 py-8 relative">
          <!-- contenido -->
          <slot />

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
          </button>

          <!-- Panel fijo lateral -->
          <div
            v-show="mostrarPanel"
            class="lg:block fixed right-4 top-[25%] lg:w-72 md:w-64 w-56 space-y-4 z-10 transition-all duration-300"
          >
            

            <!-- Tarjeta de soporte -->
            <div
              class="bg-white shadow-md rounded-xl p-4 border border-red-200"
            >
              <h4
                class="text-sm md:text-base font-semibold text-red-700 mb-2 flex items-center gap-1"
              >
                <InformationCircleIcon class="h-4 w-4" />
                ¿Necesitas soporte?
              </h4>
              <p class="text-xs md:text-base text-gray-600">Escríbenos a:</p>
              <a
                href="mailto:mesadeayudaempleabilidad@gmail.com"
                class="text-[10px] md:text-sm text-red-800 mt-1 break-words underline hover:text-red-600"
              >
                mesadeayudaempleabilidad@gmail.com
              </a>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from "@/Layouts/Header.vue";
import Navigation from "@/Layouts/Navigation.vue";
import BreadCrumb from "@/Components/BreadCrumb.vue";
import SecondaryButtonReturn from "@/Components/SecondaryButtonReturn.vue";
import { onMounted, ref, watch } from "vue";
import { HomeIcon, HomeModernIcon, XCircleIcon,
  InformationCircleIcon,
  ChatBubbleLeftRightIcon, } from "@heroicons/vue/24/solid";

const props = defineProps({
  breadCrumbLinks: {
    type: Array,
    default: () => [],
  },
});

console.log(props);
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

</script>
