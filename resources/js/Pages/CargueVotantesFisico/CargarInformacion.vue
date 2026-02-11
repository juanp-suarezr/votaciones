<template>
  <Head title="Cargar Información de Votantes" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Cargar Información de Votantes </template>

    <!-- Descripción -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <h3 class="text-sm font-semibold text-blue-800 mb-2">Instrucciones:</h3>
      <ul class="text-sm text-blue-700 list-disc list-inside">
        <li>El archivo Excel debe contener las siguientes columnas en este orden:</li>
        <ul class="ml-5 list-disc list-inside">
          <li><strong>1. nombre</strong> - Nombre completo del votante</li>
          <li><strong>2. tipo documento</strong> - Tipo de documento (CC, TI, etc.)</li>
          <li><strong>3. numero identificacion</strong> - (obligatorio) Número de identificación del votante</li>
          <li><strong>4. email</strong> - (opcional) Correo electrónico</li>
          <li><strong>5. fecha nacimiento</strong> - Fecha de nacimiento (formato: dd/mm/yyyy)</li>
          <li><strong>6. Genero</strong> - Género del votante</li>
          <li><strong>7. Grupo poblacional</strong> - Etnia o grupo poblacional</li>
          <li><strong>8. condicion</strong> - Condición del votante</li>
          <li><strong>9. numero telefono</strong> - (opcional) Número de celular</li>
          <li><strong>10. direccion</strong> - Dirección de residencia</li>
          <li><strong>11. barrio</strong> - Barrio</li>
          <li><strong>12. comuna</strong> - ID de la comuna</li>
        </ul>
        <li>Los datos del Excel comienzan a partir de la <strong>fila 3</strong> (fila 1 y 2 son encabezados).</li>
        <li>Solo se actualizarán voters con relación al evento <strong>Presupuesto Participativo</strong>.</li>
        <li>Los registros se actualizarán según el <strong>número de identificación</strong> (columna 3).</li>
        <li>Solo se actualizarán los campos que vengan con datos en el Excel.</li>
        <li><strong>El informe detallado se enviará al correo configurado.</strong></li>
      </ul>
    </div>

    <!-- Input de cargue de archivos -->
    <div class="flex items-center gap-4 mb-8">
      <div class="flex flex-col w-full max-w-md">
        <label for="carga-informacion" class="text-xs font-semibold mb-1"
          >Cargar información de votantes (Excel)</label
        >
        <input
          id="carga-informacion"
          type="file"
          class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
          @change="onFileChange"
          accept=".csv,.xlsx,.xls"
        />
        <button
          class="mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!form.file || form.processing"
          @click="enviarArchivo"
        >
          <span v-if="form.processing">Procesando...</span>
          <span v-else>Actualizar Información</span>
        </button>
      </div>
    </div>

    <!-- Botón para volver -->
    <div class="mt-6">
      <Link
        :href="route('votantesFisicos.index')"
        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volver a Votantes Físicos
      </Link>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, inject } from "vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const swal = inject("$swal");

const breadcrumbLinks = [
  { url: route("votantesFisicos.index"), text: "Cargue de votantes físicos" },
  { url: "", text: "Cargar Información de Votantes" },
];

const file = ref(null);

const form = useForm({
  file: null,
});

const onFileChange = (e) => {
  file.value = e.target.files[0];
  form.file = file.value;
};

const enviarArchivo = () => {
  if (!form.file) {
    swal({
      title: "Error",
      text: "Por favor seleccione un archivo primero",
      icon: "error",
    });
    return;
  }

  const data = new FormData();
  data.append("file", form.file);

  form.processing = true;

  axios
    .post(route("votantesFisicos.cargarInformacion"), data, {
      headers: { "Content-Type": "multipart/form-data" },
    })
    .then((res) => {
      const actualizados = res.data.numRegistrosActualizados ?? 0;
      const noEncontrados = res.data.numNoEncontrados ?? 0;
      const errores = res.data.numErrores ?? 0;

      // Solo mostrar datos generales en el modal
      swal({
        title: "Proceso Completado",
        html: `
          <div class="text-left">
            <p>✅ <strong>Actualizados:</strong> ${actualizados}</p>
            <p>⚠️ <strong>No encontrados:</strong> ${noEncontrados}</p>
            <p>🚨 <strong>Errores:</strong> ${errores}</p>
            <hr class="my-3">
            <p class="text-green-600 font-semibold">📧 El informe detallado ha sido enviado al correo configurado.</p>
          </div>
        `,
        icon: "success",
      });

      // Limpiar formulario local
      form.reset();
      file.value = null;
    })
    .catch((err) => {
      console.error(err);
      swal.fire(
        "Error",
        err.response?.data?.message || "Hubo un problema al procesar el archivo.",
        "error"
      );
    })
    .finally(() => {
      form.processing = false;
    });
};
</script>
