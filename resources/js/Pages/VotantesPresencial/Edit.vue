<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head } from "@inertiajs/vue3";
defineProps({
  acta: {
    type: Object,
    default: () => ({}),
  },
  parametros: {
    type: Object,
    default: () => ({}),
  },

});

const breadcrumbLinks = [
  { url: "/users", text: "Actas de escrutinio" },
  { url: "", text: "Editar Acta escrutinio" },
];
</script>

<template>
  <Head title="Editar Acta de escrutinio" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Editar Acta de escrutinio </template>


  </AuthenticatedLayout>
</template>
