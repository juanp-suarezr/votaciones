<template>
  <Head title="Cargue de votantes físicos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Cargue de votantes físicos </template>

    <!-- Input de cargue de archivos -->
    <div class="flex items-center gap-4 mb-8">
      <div class="flex flex-col">
        <label for="carga-masiva" class="text-xs font-semibold mb-1"
          >Carga masiva de votantes físicos</label
        >
        <input
          id="carga-masiva"
          type="file"
          class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
          @change="onFileChange"
          accept=".csv,.xlsx"
        />
        <button
          class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          :disabled="!form.file || form.processing"
          @click="enviarArchivo"
        >
          <span v-if="form.processing">Cargando...</span>
          <span v-else>Enviar archivo</span>
        </button>
      </div>
    </div>

    <!-- Tabla de votantes físicos -->
    <div class="mb-10">
      <h2 class="text-lg font-bold mb-2">Votantes físicos</h2>
      <div class="overflow-x-auto">
        <table
          class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden"
        >
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Nombre
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Identificación
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Género
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Fecha de creación
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="votante in votantes_fisicos.data"
              :key="votante.id"
              class="bg-white hover:bg-gray-50"
            >
              <td class="px-4 py-2 text-sm text-gray-900">
                {{ votante.votante.nombre }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ votante.votante.identificacion }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ votante.votante.genero }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ formatDate(votante.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        class="flex flex-col items-center border-t bg-white/40 mt-1 px-5 py-3 xs:flex-row xs:justify-between"
      >
        <pagination :links="votantes_fisicos.links" />
      </div>
    </div>

    <!-- Tabla de votos anulados -->
    <div>
      <h2 class="text-lg font-bold mb-2">Votos anulados</h2>
      <div class="overflow-x-auto">
        <table
          class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden"
        >
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Nombre de votante
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Cantidad de votos anulados
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Fecha de creación
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Evento
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="voto in votos_anulados.data"
              :key="voto.id"
              class="bg-white hover:bg-gray-50"
            >
              <td class="px-4 py-2 text-sm text-gray-900">
                {{ voto.votante.nombre }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ voto.cantidad_anulada }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ voto.created_at }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ voto.evento.nombre }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        class="flex flex-col items-center border-t bg-white/40 mt-1 px-5 py-3 xs:flex-row xs:justify-between"
      >
        <pagination :links="votos_anulados.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, inject } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const swal = inject("$swal");

const props = defineProps({
  votantes_fisicos: {
    type: Array,
    default: () => [],
  },
  votos_anulados: {
    type: Array,
    default: () => [],
  },
  numRegistrosInsertados: {
    type: Number,
    default: undefined,
  },
  numInconsistencias: {
    type: Number,
    default: undefined,
  },
});

console.log(props);

const breadcrumbLinks = [{ url: "", text: "Cargue de votantes físicos" }];

const file = ref(null);

const form = useForm({
  file: null,
});

const onFileChange = (e) => {
  file.value = e.target.files[0];
  form.file = file.value;
};

const enviarArchivo = () => {
  if (!form.file) return;
  form.post(route("votantesFisicos.cargueMasivo"), {
    forceFormData: true,
    onSuccess: () => {
      if (
        props.numRegistrosInsertados !== undefined &&
        props.numInconsistencias !== undefined
      ) {
        swal({
          title: "Registros Cargados",
          text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y se ha generado ${props.numInconsistencias} inconsistencias`,
          icon: "success",
        });
      } else if (
        props.numRegistrosInsertados !== undefined &&
        props.numInconsistencias == undefined
      ) {
        // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
        swal({
          title: "Registros Cargados",
          text: `Se han importado ${props.numRegistrosInsertados} nuevos registros correctamente y 0 inconsistencias`,
          icon: "success",
        });
      } else if (
        props.numRegistrosInsertados == undefined &&
        props.numRegistrosActualizados !== undefined
      ) {
        // Si 'success' no está presente en la respuesta, mostrar un mensaje de error genérico
        swal({
          title: "Registros Cargados",
          text: `Se han importado 0 nuevos registros correctamente y se ha generado ${props.numInconsistencias} inconsistencias`,
          icon: "success",
        });
      } else {
        swal({
          title: "Registros Cargados",
          text: "Se han importado los registros de forma masiva",
          icon: "success",
        });
      }

      form.reset();
      file.value = null;
      window.location.reload();
    },
    onError: () => {
      swal.fire("Error", "Hubo un problema al cargar el archivo.", "error");
      window.location.reload();
    },
  });
};

const formatDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  return d.toLocaleString();
};
</script>
