<template>
  <Head title="Parámetros detalle" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Parámetros detalle </template>

    <div
      class="inline-block min-w-full overflow-hidden mb-3 grid md:grid-cols-3 gap-4"
    >
      <div>
        <select
          id="estado"
          name="estado"
          v-model="estado"
          @change="handleEnterKey"
          class="block w-full px-4 py-3 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
        >
          <option selected value="">Filtrar por estado</option>
          <option value="1">Activo</option>
          <option value="0">Bloqueado</option>
        </select>
      </div>
      <div class="...">
        <label
          for="default-search"
          class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white"
          >Search</label
        >
        <div class="relative">
          <div
            class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"
          >
            <svg
              class="w-4 h-4 text-gray-500 dark:text-gray-400"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 20 20"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
              />
            </svg>
          </div>
          <input
            type="search"
            @keydown.enter="handleEnterKey"
            v-model="search"
            id="default-search"
            class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-0 focus:border-transparent"
            placeholder="Buscar por nombre"
            required
          />
          <button
            type="submit"
            @click="handleEnterKey"
            class="text-white absolute end-2.5 bottom-2.5 bg-sky-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-sky-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          >
            Buscar
          </button>
        </div>
      </div>

      <div class="text-right p-4 gap-2 flex flex-wrap">
        <SecondaryButton @click="clearFilters"> Limpiar </SecondaryButton>
        <PrimaryButton @click="Actualizar('new')"> Agregar </PrimaryButton>
      </div>
    </div>

    <div class="w-full overflow-x-scroll md:overflow-x-auto rounded-lg shadow">
      <table class="w-full whitespace-no-wrap overflow-x-auto">
        <thead>
          <tr
            class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
          >
            <th
              class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
            >
              Nombre
            </th>
            <th
              class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
            >
              Código padre
            </th>

            <th
              class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
            >
              Estado
            </th>
            <th
              class="border-b-2 border-gray-200 bg-gray-100 sm:px-5 sm:py-3 p-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
            >
              Acciones
            </th>
          </tr>
        </thead>
        <tbody v-if="parametros.data.length > 0">
          <tr v-for="ev in parametros.data" :key="ev.id" class="text-gray-700">
            <td
              class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs"
            >
              <p class="text-gray-900 whitespace-no-wrap">{{ ev.detalle }}</p>
            </td>
            <td
              class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs"
            >
              <p class="text-gray-900 whitespace-no-wrap">
                {{ ev.codParametro }}
              </p>
            </td>

            <td
              class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs"
            >
              <p
                class="text-gray-900 whitespace-no-wrap inline-flex px-2 rounded-md bg-red-200"
                :class="{ '!bg-green-400': ev.estado === 1 }"
              >
                {{ ev.estado ? "Activo" : "Bloqueado" }}
              </p>
            </td>
            <td
              class="w-auto border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs flex flex-wrap gap-4"
            >
              <SecondaryButton
                type="button"
                @click="Actualizar(ev.id)"
                class="mb-2"
              >
                Editar
              </SecondaryButton>

              <DangerButton class="p-1 mb-2" @click="confirmUserDeletion(ev.id)"
                >Eliminar</DangerButton
              >
            </td>
          </tr>
        </tbody>
        <!-- cuando no hay registros -->
        <tbody v-else>
          <tr>
            <td
              class="border-b border-gray-200 bg-white sm:px-5 sm:py-5 p-2 sm:text-sm text-xs text-center"
              colspan="5"
            >
              <p
                class="whitespace-no-wrap bg-red-400 text-white text-base font-bold inline-flex px-2 rounded-md shadow-md"
              >
                No hay registros de parámetros detalle
              </p>
            </td>
          </tr>
        </tbody>
      </table>

      <div
        class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between"
      >
        <pagination :links="parametros.links" />
      </div>
    </div>

    <!-- modal actu o create -->
    <Modal :show="visible" @close="closeModal">
      <div class="p-6">
        <h2 class="text-xl font-semibold">
          {{
            parametroDetalle_id == "new"
              ? "Añadir Tipo de Usuario"
              : "Editar Tipo de Usuario"
          }}
        </h2>
        <!-- nombres -->
        <div class="mt-6">
          <InputLabel
            class="sm:text-sm text-xs"
            for="nombre"
            value="Nombre del parámetro detalle"
          />
          <TextInput
            id="nombre"
            type="text"
            class="mt-1 block w-full p-0 sm:p-2"
            v-model="form.detalle"
            required
            autocomplete="nombre"
          />
          <InputError class="mt-1" :message="form.errors.detalle" />
        </div>
        <!-- estado -->
        <div class="mt-4" v-if="parametroDetalle_id != 'new'">
          <InputLabel
            class="sm:text-sm text-xs"
            for="estado"
            value="Estado del parámetro detalle"
          />
          <select
            id="estado"
            name="estado"
            v-model="form.estado"
            class="mt-1 block w-full p-0 sm:p-2"
          >
            <option value="1">Activo</option>
            <option value="0">Bloqueado</option>
          </select>
          <InputError class="mt-1" :message="form.errors.estado" />
        </div>
        <!-- botones -->
        <div class="flex items-center justify-end mt-6">
          <Button
            class="bg-blue-800 text-white px-4 py-2 rounded-md hover:bg-blue-600 cursor"
            type="button"
            @click="EventoTipo(parametroDetalle_id)"
            >Guardar</Button
          >
        </div>
      </div>
    </Modal>

    <!-- modal para eliminar -->
    <Modal :show="confirmingDeletion" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          ¿Estás seguro de que quieres eliminar el parámetro?
        </h2>

        <p class="mt-1 text-sm text-gray-600">
          Una vez que se elimine el parámetro especifico, esté deja de existir.
          Pero no se borra los registros relacionados con el usuario votante o
          candidato o proyecto.
        </p>
        <div class="mt-6">
          <InputLabel for="password" value="Password" class="sr-only" />

          <TextInput
            id="password"
            v-model="form.password"
            type="password"
            class="mt-1 block w-3/4"
            placeholder="Contraseña"
            @keyup.enter="deleteTipo"
          />

          <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>

          <DangerButton
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteTipo"
          >
            Eliminar parámetro detallado
          </DangerButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch, inject } from "vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import Button from "primevue/button";

const props = defineProps({
  parametros: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});
const swal = inject("$swal");

// Modal de eliminacion
const confirmingDeletion = ref(false);
//Modal de añadir o actualizar
const visible = ref(false);
const parametroDetalle_id = ref(0);
//form
const form = useForm({
  password: "",
  detalle: "",
  codParametro: props.filters.codParametro,
  estado: "1",
});

const breadcrumbLinks = [
  { url: "/parametros", text: "listado de parámetros generales" },
  { url: "", text: "listado de parámetros detalle" },
];

const closeModal = () => {
  confirmingDeletion.value = false;
  visible.value = false;
  form.reset();
};

//MODAL EDIT or CREATE
const Actualizar = (id) => {
  let parametro = props.parametros.data.find((item) => item.id == id);

  form.detalle = parametro ? parametro.detalle : "";

  form.estado = parametro ? parametro.estado : "";
  visible.value = true;

  parametroDetalle_id.value = id;
};

//Añadir o actualizar
const EventoTipo = (num) => {
  if (num == "new") {
    

    form.post(route("parametrosDetalle.store"), {
      preserveScroll: true,
      replace: true,
      onSuccess: () => {
        closeModal();
        swal({
          title: "Registro Creado",
          text: "El parametro detalle se ha registrado exitosamente",
          icon: "success",
        });
      },
      onError: () => {
        closeModal();
        swal({
          title: "Error",
          text: "Ocurrió un error al registrar el parámetro.",
          icon: "error",
        });
      },
      onFinish: () => form.reset(),
    });
  } else {
    console.log(form.estado);
    console.log(parametroDetalle_id.value);

    form.patch(route("parametrosDetalle.update", parametroDetalle_id.value), {
      preserveScroll: true,
      replace: true,
      onSuccess: () => {
        closeModal();
        swal({
          title: "Registro Actualizado",
          text: "El parámetro se ha actualizado exitosamente",
          icon: "success",
        });
      },
      onError: () => {
        closeModal();
        swal({
          title: "Error",
          text: "Ocurrió un error al registrar el parámetro.",
          icon: "error",
        });
      },
      onFinish: () => form.reset(),
    });
  }
};

const confirmUserDeletion = (id) => {
  confirmingDeletion.value = true;
  parametroDetalle_id.value = id;
};
const deleteTipo = () => {
  form.delete(route("parametrosDetalle.destroy", parametroDetalle_id.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      swal({
        title: "Registro Eliminado",
        text: "El parámetro detalle se ha eliminado exitosamente",
        icon: "success",
      });
    },
    onError: () => {
        closeModal();
        swal({
          title: "Error",
          text: "Ocurrió un error al eliminar el parámetro.",
          icon: "error",
        });
      },
    onFinish: () => form.reset(),
  });
};

// pass filters in search
let search = ref(props.filters.search);
let estado = ref(props.filters.estado ?? "");
const handleEnterKey = () => {
  router.get(
    "/parametrosDetalle",
    {
      search: search.value,
      estado: estado.value,
      codParametro: props.filters.codParametro,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const clearFilters = () => {
  search.value = "";
  estado.value = "";
  handleEnterKey();
};
</script>
