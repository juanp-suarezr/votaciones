<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

import axios from "axios";
import Select from "primevue/select";
import PrimaryButton from "@/Components/PrimaryButton.vue";
const swal = inject("$swal");

const props = defineProps({
  eventos: {
    type: Object,
    default: () => [],
  },
});

console.log(props);

const form = useForm({
  id_evento: "",
});

const submit = () => {
  form.get(route("resultado-comunas"), {
    preserveState: true,
  });
};

</script>

<template>
  <Head title="Buscar evento" />

  <div
    class="relative h-screen bg-center bg-option1 selection:bg-red-500 selection:text-white flex flex-col justify-center"
  >
    <div class="w-full sm:px-16 px-4">
      <a class="sm:flex justify-start mt-2" href="/welcome">
        <img src="/assets/img/logo.png" alt="Logo" class="h-24 w-auto" />
      </a>
    </div>
    <!-- contenido -->
    <div class="w-full flex justify-center my-auto sm:px-16 px-4">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-4xl font-bold text-center">Análisis de Resultados</h2>
        <p class="text-xl mt-2">
          Seleccione el evento electoral para ver los resultados
        </p>
        <!-- eventos -->
        <div class="w-auto mt-6">
          <InputLabel
            for="evento"
            value="Tipo de Evento Electoral"
            class="!text-lg"
          />
          <div v-if="eventos.length" class="card flex justify-content-center">
            <Select
              id="eventos"
              v-model="form.id_evento"
              :options="eventos"
              option-label="nombre"
              option-value="id"
              placeholder="Seleccione los eventos"
              class="w-full text-lg"
            />
          </div>
          <p v-else class="text-xs text-gray-600">No hay eventos registrados</p>
          <InputError class="mt-2" :message="form.errors.id_evento" />
        </div>
        <div class="mt-6 w-full" v-if="form.id_evento">
          <button
            @click="submit"
            class="w-full flex justify-center text-lg text-white bg-black hover:bg-black/90 rounded-lg p-2"
          >
            Ver Resultados
          </button>
        </div>
      </div>
    </div>
    <section class="section1 overflow-hidden" data-aos="fade-up">
      <svg class="curve" viewBox="0 0 370.417 79.375">
        <path
          class="wave"
          d="M-1.301 51.894C141.54 5.807 159.612 38.111 226.846 62.2c90.74 32.51 149.712-33.492 149.712-33.492v59.541H-1.302z"
          fill="#C20E1A"
          fill-rule="evenodd"
        />
      </svg>
    </section>
  </div>
</template>

<style>
</style>
