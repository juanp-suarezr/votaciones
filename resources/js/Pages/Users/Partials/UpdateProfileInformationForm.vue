<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import MultiSelect from "primevue/multiselect";

import Dropdown from "primevue/dropdown";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { inject, ref, watch, computed } from "vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";

import Select from "primevue/select";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const swal = inject("$swal");
const props = defineProps({
  user: {
    type: Object,
    default: () => ({}),
  },
  roles: {
    type: Object,
    default: () => ({}),
  },
  userRoles: {
    type: Object,
    default: () => ({}),
  },
  tipos: {
    type: Object,
    default: () => ({}),
  },

  eventos: {
    type: Object,
    default: () => ({}),
  },
  parametros: {
    type: Array,
    default: () => [],
  },
  subtipos: {
    type: Array,
    default: () => [],
  },
});

console.log(props);

const selectedRoles = Object.values(props.userRoles);
const user = props.user;
//para eventos seleccionados
const eventosBD =
  user.eventos !== "NA"
    ? user.eventos.split("|").map((id) => parseInt(id))
    : [];
//evento seleccionado para elegir candidato o usuario
const eventoSeleccionado = ref([]);
const selectedParametro = ref(null);

//por default o cambiar
const isSubtipo = ref(false);

const form = useForm({
  name: user.name,
  email: user.email,
  identificacion: user.votantes ? user.votantes.identificacion : "",
  tipo: user.tipos != "NA" ? user.tipos.split("|") : "" || "",
  subtipo: user.subtipo == 0 ? 0 : user.subtipo,
  estado: user.estado,
  roles_user: selectedRoles,
  eventos: eventosBD || [],
});

const ObtenerSubtipo = () => {
  console.log(form.subtipo);

  if (form.subtipo == 0) {
    return "No contiene subtipo";
  } else if (typeof form.subtipo === "object") {

    return form.subtipo.detalle;
  } else {

    return props.subtipos.find((element) => element.id === parseInt(form.subtipo))
      ?.detalle;

  }
};

const filteredDetalles = computed(() => {
  if (!selectedParametro.value) {
    return [];
  }

  return props.subtipos.filter(
    (detalle) => detalle.codParametro === selectedParametro.value
  );
});

const submit = () => {
  form.tipo = form.tipo == "" ? "NA" : form.tipo.join("|");


  form.subtipo = typeof form.subtipo != 'object' ? form.subtipo : form.subtipo.id;

  form.patch(route("users.update", user.id), {
    onSuccess: () =>
      swal({
        title: "Registro Actualizado",
        text: "El usuario se ha actualizado exitosamente",
        icon: "success",
      }).then((result) => {
        window.location.reload();
      }),
  });
};

//checked para empresas o campistas
const tipo_user = ref(form.candidato ? "Candidato" : "Usuario");
const options_user = ref(["Candidato", "Usuario"]);

watch(tipo_user, (value) => {
  if (value == "Candidato") {
    form.candidato = 1;
  } else {
    form.candidato = 0;
  }
});
</script>

<template>
  <section>
    <header>
      <div class="text-right">
        <SecondaryLink :href="route('users.index')"> Regresar </SecondaryLink>
      </div>
      <h2 class="text-lg font-medium text-gray-900">Información usuario</h2>

      <p class="mt-1 text-sm text-gray-600">
        Actualice la información del usuario y la dirección de Usuario.
      </p>
    </header>
    <form @submit.prevent="submit" class="mt-6 space-y-6">
      <!-- nombre -->
      <div>
        <InputLabel for="name" value="Nombre" />
        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>
      <!-- correo -->
      <div>
        <InputLabel for="email" value="Usuario" />

        <TextInput
          id="email"
          type="text"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>
      <!-- indentificacion -->
      <div v-if="selectedRoles.find((item) => item != 'Administrador')">
        <InputLabel for="identificacion" value="Identificación" />

        <TextInput
          id="identificacion"
          type="identificacion"
          class="mt-1 block w-full"
          v-model="form.identificacion"
          required
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.identificacion" />
      </div>
      <!-- roles y tipos -->
      <div class="sm:flex sm:flex-wrap gap-4">
        <!-- tipos -->
        <div v-if="selectedRoles.find((item) => item != 'Administrador')">
          <InputLabel for="tipo" value="Tipo votante" />

          <div v-if="tipos" class="card flex justify-content-center">
            <MultiSelect
              id="tipo"
              v-model="form.tipo"
              display="chip"
              :options="Object.values(tipos)"
              placeholder="Seleccione tipo votante"
              :maxSelectedLabels="3"
              class="w-full md:w-20rem"
            />
          </div>

          <p v-else class="text-xs text-gray-600">
            No hay tipos de usuarios registrados, registrar uno nuevo
            <a :href="route('tipos.index')">Aquí</a>
          </p>
          <InputError class="mt-2" :message="form.errors.tipo" />
        </div>
        <!-- Parámetro y Detalle -->
        <div
          class="w-full h-full mt-2"
          v-if="selectedRoles.find((item) => item != 'Administrador')"
        >
          <div class="flex flex-wrap items-center w-auto gap-4">
            <InputLabel
              for="tipo_user"
              :value="'Subtipos - ' + ObtenerSubtipo()"
            />
            <SecondaryButton
              @click="isSubtipo = !isSubtipo"
              class="items-center"
            >
              {{
                isSubtipo
                  ? "Cancelar"
                  : form.subtipo != 0
                  ? "Actualizar subtipo"
                  : "Agregar subtipo"
              }}
            </SecondaryButton>
          </div>

          <div
            v-if="
              selectedRoles.find((item) => item != 'Administrador') && isSubtipo
            "
            class="px-2 py-4 w-full border border-gray-400 rounded-md shadow-md mt-4"
          >
            <!-- Parámetro -->
            <div>
              <InputLabel for="parametro" value="Parámetro" />
              <Dropdown
                id="parametro"
                v-model="selectedParametro"
                :options="parametros"
                option-label="detalle"
                option-value="cod"
                placeholder="Seleccione un parámetro"
                class="w-full"
              />
              <InputError class="mt-2" :message="form.errors.parametro" />
            </div>

            <!-- Detalle del Parámetro -->
            <div class="mt-4">
              <InputLabel for="parametroDetalle" value="Subtipo" />
              <Select
                id="parametroDetalle"
                v-model="form.subtipo"
                :options="filteredDetalles"
                filter
                optionLabel="detalle"
                placeholder="Seleccione un subtipo"
                checkmark
                :highlightOnSelect="false"
                class="w-full"
              />
              <InputError class="mt-2" :message="form.errors.subtipo" />
            </div>
          </div>
        </div>

        <!-- roles -->
        <div>
          <InputLabel for="roles" value="Roles" />

          <div class="card flex justify-content-center">
            <MultiSelect
              id="roles"
              v-model="form.roles_user"
              display="chip"
              :options="Object.values(roles)"
              placeholder="Seleccione rol"
              :maxSelectedLabels="3"
              class="w-full md:w-20rem"
            />
          </div>
          <InputError class="mt-2" :message="form.errors.roles_user" />
        </div>
      </div>

      <!-- evento -->
      <div>
        <InputLabel for="evento" value="Evento votante" />
        <div v-if="eventos.length" class="card flex justify-content-center">
          <MultiSelect
            id="eventos"
            v-model="form.eventos"
            display="chip"
            :options="eventos"
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione los eventos"
            :maxSelectedLabels="6"
            class="w-full md:w-20rem"
          />
        </div>
        <p v-else class="text-xs text-gray-600">
          No hay eventos registrados, registrar uno nuevo
          <a :href="route('eventos.index')">Aquí</a>
        </p>
        <InputError class="mt-2" :message="form.errors.eventos" />
      </div>

      <!-- roles y tipos -->
      <div class="sm:flex gap-4">
        <!-- estado -->
        <div>
          <InputLabel for="estado" value="Estado" />

          <select
            id="estado"
            v-model="form.estado"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
          >
            <option value="Activo">Activo</option>
            <option value="Bloqueado">Bloqueado</option>
          </select>
          <InputError class="mt-2" :message="form.errors.estado" />
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          >Guardar
        </PrimaryButton>
        <Transition
          enter-active-class="transition ease-in-out"
          enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
            Actualizado.
          </p>
        </Transition>
      </div>
    </form>
  </section>
</template>
