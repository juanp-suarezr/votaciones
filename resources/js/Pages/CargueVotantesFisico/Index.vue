<template>
  <Head title="Cargue de votantes físicos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Cargue de votantes físicos </template>

    <!-- Botones de acción -->
    <div class="flex flex-wrap gap-4 mb-8">
      <!-- Botón para carga masiva -->
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

      <!-- Botón para cargar información de votantes -->
      <div class="flex flex-col">
        <label class="text-xs font-semibold mb-1">Actualizar información</label>
        <Link
          :href="route('votantesFisicos.cargarInformacion')"
          class="mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          Cargar información de votantes
        </Link>
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
                {{ votante.votante.nombre || 'N/A' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ votante.votante.identificacion }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ votante.votante.genero || 'N/A' }}
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
        <Pagination :links="votantes_fisicos.links" />
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
                Identificación de votante
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Comuna/Corregimiento
              </th>
              <th class="px-4 py-2 text-left text-xs font-bold text-gray-700">
                Tipo
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
                {{ voto.votante.nombre || 'N/A' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-900">
                {{ voto.votante.identificacion || 'N/A' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-900">
                {{ getComuna(voto.votante.comuna) || 'N/A' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-900">
                {{ voto.tipo || 'N/A' }}
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
        <Pagination :links="votos_anulados.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, inject, watch, onMounted } from "vue";
import { Head, useForm, usePage, router, Link } from "@inertiajs/vue3";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import comunas from "@/shared/comunas_completas.json";

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

const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna)?.label;
};

const enviarArchivo = () => {
  if (!form.file) return;
  // Enviar con axios para recibir JSON y evitar redirección que provoca full reload
  const data = new FormData();
  data.append("file", form.file);

  axios
    .post(route("votantesFisicos.cargueMasivo"), data, {
      headers: { "Content-Type": "multipart/form-data" },
    })
    .then((res) => {
      const inserted = res.data.numRegistrosInsertados ?? 0;
      const inconsistencias = res.data.numInconsistencias ?? 0;
      swal({
        title: "Registros Cargados",
        text: `Se han importado ${inserted} nuevos registros correctamente y se ha generado ${inconsistencias} inconsistencias`,
        icon: "success",
      });

      // limpiar formulario local
      form.reset();
      file.value = null;

      // refrescar datos de la página (sin full reload)
      router.get(route("votantesFisicos.index"), {}, { preserveState: false });
    })
    .catch((err) => {
      console.error(err);
      swal.fire("Error", "Hubo un problema al cargar el archivo.", "error");
    });
};

const formatDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  return d.toLocaleString();
};

const page = usePage();

// Cuando lleguen los flashes desde el backend, mostrar el SweetAlert y limpiar el formulario
watch(
  () => ({
    inserted: page.props.numRegistrosInsertados,
    inconsistencias: page.props.numInconsistencias,
  }),
  (newVal) => {
    const { inserted, inconsistencias } = newVal;
    if (inserted !== undefined || inconsistencias !== undefined) {
      const i = inserted ?? 0;
      const inc = inconsistencias ?? 0;
      swal({
        title: "Registros Cargados",
        text: `Se han importado ${i} nuevos registros correctamente y se ha generado ${inc} inconsistencias`,
        icon: "success",
      });

      // limpiar formulario local
      form.reset();
      file.value = null;
    }
  }
);

// También en mounted si la prop ya venía en la carga inicial
onMounted(() => {
  const inserted = page.props.numRegistrosInsertados || undefined;
  const inconsistencias = page.props.numInconsistencias || undefined;
  if (inserted !== undefined || inconsistencias !== undefined) {
    const i = inserted ?? 0;
    const inc = inconsistencias ?? 0;
    swal({
      title: "Registros Cargados",
      text: `Se han importado ${i} nuevos registros correctamente y se ha generado ${inc} inconsistencias`,
      icon: "success",
    });
    form.reset();
    file.value = null;
  }
});
</script>
