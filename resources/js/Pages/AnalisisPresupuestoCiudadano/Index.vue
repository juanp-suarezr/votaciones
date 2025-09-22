<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON
import axios from "axios";
import Select from "primevue/select";
import PrimaryButton from "@/Components/PrimaryButton.vue";
const swal = inject("$swal");

const form = useForm({
  id_comuna: "",
});

const submit = () => {
  form.get(route("resultado-presupuesto"), {
    preserveState: true,
  });
};
</script>

<template>
  <Head title="Buscar evento" />

  <div
    class="relative h-screen bg-center bg-option1 selection:bg-red-500 selection:text-white flex flex-col justify-center overflow-x-hidden"
  >
    <div class="w-full sm:px-16 px-4">
      <div class="sm:flex gap-4 items-center">
        <a
          class="sm:flex justify-start mt-2 pe-4 sm:border-r border-black"
          href="/welcome"
        >
          <img src="/assets/img/logo.png" alt="Logo" class="h-24 w-auto" />
        </a>
        <img
          src="/assets/img/voto_electronico.png"
          alt="Logo"
          class="h-32 w-auto"
        />
        <div class="w-full flex justify-end items-center">
          <a
            class="text-black sm:text-2xl text-xl"
            href="/resultado-comunas?id_evento=15"
          >
            Mas detalles resultados
          </a>
        </div>
      </div>
    </div>
    <!-- contenido -->
    <div class="w-full flex justify-center my-auto sm:px-16 px-4">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-4xl font-bold text-center">Análisis de Resultados</h2>
        <p class="text-xl mt-2">Seleccione la comuna para ver los resultados</p>
        <!-- comunas -->
        <div class="w-auto mt-6">
          <InputLabel
            for="comuna"
            value="Seleccione la comuna a consultar los resultados"
            class="!text-lg"
          />
          <div v-if="comunas.length" class="card flex justify-content-center">
            <Select
              id="comunas"
              v-model="form.id_comuna"
              :options="comunas"
              option-label="label"
              option-value="value"
              placeholder="Seleccione una comuna"
              class="w-full text-lg"
            />
          </div>
          <p v-else class="text-xs text-gray-600">No hay comunas registradas</p>
          <InputError class="mt-2" :message="form.errors.id_comuna" />
        </div>
        <div class="mt-6 w-full" v-if="form.id_comuna">
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
