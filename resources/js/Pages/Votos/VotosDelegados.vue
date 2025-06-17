<template>
  <Head title="Votaciones" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header> Votaciones </template>

    <div class="items-center flex flex-col justify-center mx-2">
      <div class="flex flex-col w-full">
        <h1 class="sm:text-3xl text-xl font-semibold text-justify">
          Para votar basta con dar click en el botón azul "Seleccionar" de cada
          tarjeta según el delegado especifico
        </h1>
        <p class="sm:text-2xl text-lg text-gray-600 text-justify mt-2">
          Recuerda que puedes votar por un delegado masculino y por una delegada
          femenina
        </p>
        <p class="sm:text-2xl text-lg text-gray-600 text-justify mt-2">
          Si no deseas votar por ningún candidato, puedes seleccionar la opción
          de Voto en Blanco
        </p>
        <p
          class="sm:text-2xl text-lg text-gray-600 text-justify mt-2 italic font-semibold"
        >
          Después dar click en el botón continuar
        </p>
      </div>
      <!-- votacion delegado 1 -->
      <div class="mt-6 w-full" v-if="activo === 1">
        <div class="mt-6">
          <h3 class="sm:text-3xl text-xl">Delegados</h3>
        </div>
        <div
          class="sm:flex w-full sm:grid md:grid-cols-3 grid-cols-2 gap-4 mt-6"
        >
          <!-- votos delegados -->
          <div
            v-for="candi in delegado"
            :key="candi.id"
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard === candi.id ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto w-full flex justify-center items-center">
              <div
                v-if="candi.votante.imagen == 'user.png'"
                class="w-full flex justify-center items-center h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <p class="m-auto sm:text-4xxl text-xxl object-cover">
                  {{ getInitials(candi.votante.nombre) }}
                </p>
              </div>
              <div
                v-else
                :image="getImageUrl(candi.votante.imagen)"
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageUrl(candi.votante.imagen)"
                  alt="Imagen de candidato"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- Candidato -->
            <div class="mt-4 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                {{ candi.votante.nombre }}
              </h2>
              <h3
                v-if="candi.votante.nombre != 'Voto En Blanco'"
                class="sm:text-xl text-lg text-gray-800 mt-2"
              >
                CC {{ candi.votante.identificacion }}
              </h3>
            </div>
            <!-- votacion -->
            <div class="mt-4 mx-auto">
              <PrimaryButton
                type="button"
                @click="delegado1(candi)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
          <!-- VOTO EN BLANCO -->
          <div
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard === null ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto">
              <div
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageUrl('12345678_candidatos.jpg')"
                  alt="Imagen de candidato"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- Candidato -->
            <div class="mt-6 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                VOTO <br />
                EN BLANCO
              </h2>
            </div>
            <!-- votacion -->
            <div class="mt-6 mx-auto">
              <PrimaryButton
                type="button"
                @click="delegado1(null)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
        </div>
        <!-- botones -->
        <div class="flex justify-end mt-6">
          <PrimaryButton
            type="button"
            @click="next(2)"
            class="sm:text-3xl text-xl"
          >
            Continuar
          </PrimaryButton>
        </div>
      </div>
      <!-- votacion delegada 2 -->
      <div class="mt-6 w-full" v-if="activo === 2">
        <div class="mt-6">
          <h3 class="sm:text-3xl text-xl">Delegadas</h3>
        </div>
        <div
          class="sm:flex w-full sm:grid md:grid-cols-3 grid-cols-2 gap-4 mt-6"
        >
          <!-- votos delegados -->
          <div
            v-for="candi in delegada"
            :key="candi.id"
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard1 === candi.id ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto w-full flex justify-center items-center">
              <div
                v-if="candi.votante.imagen == 'user.png'"
                class="w-full flex justify-center items-center h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <p class="m-auto sm:text-4xxl text-xxl object-cover">
                  {{ getInitials(candi.votante.nombre) }}
                </p>
              </div>
              <div
                v-else
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageUrl(candi.votante.imagen)"
                  alt="Imagen de candidato"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- Candidato -->
            <div class="mt-4 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                {{ candi.votante.nombre }}
              </h2>
              <h3
                v-if="candi.votante.nombre != 'Voto En Blanco'"
                class="sm:text-xl text-lg text-gray-800 mt-2"
              >
                CC {{ candi.votante.identificacion }}
              </h3>
            </div>
            <!-- votacion -->
            <div class="mt-4 mx-auto">
              <PrimaryButton
                type="button"
                @click="delegado2(candi)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
          <!-- VOTO EN BLANCO -->
          <div
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard1 === null ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto">
              <div
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageUrl('12345678_candidatos.jpg')"
                  alt="Imagen de candidato"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- Candidato -->
            <div class="mt-6 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                VOTO <br />
                EN BLANCO
              </h2>
            </div>
            <!-- votacion -->
            <div class="mt-6 mx-auto">
              <PrimaryButton
                type="button"
                @click="delegado2(null)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
        </div>
        <!-- botones -->
        <div class="flex justify-between mt-6">
          <button
            class="sm:text-3xl text-xl text-gray-600 border border-gray-400 rounded-md px-4 py-1 hover:bg-gray-300"
            @click="previus(1)"
          >
            Anterior
          </button>
          <PrimaryButton
            type="button"
            @click="next(3)"
            class="sm:text-3xl text-xl"
          >
            Continuar
          </PrimaryButton>
        </div>
      </div>
      <!-- votacion Proyectos -->
      <div class="mt-6 w-full" v-if="activo === 3">
        <div class="mt-6">
          <h3 class="sm:text-3xl text-xl">Proyectos</h3>
        </div>
        <div
          class="sm:flex w-full sm:grid md:grid-cols-2 grid-cols-2 gap-4 mt-6"
        >
          <!-- votos proyectos -->
          <div
            v-for="pro in proyectos"
            :key="pro.id"
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard2 === pro.id ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto w-full flex justify-center items-center">
              <div
                v-if="pro.proyecto.imagen == 'NA'"
                class="w-full flex justify-center items-center h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <p class="m-auto sm:text-4xxl text-xxl object-cover">
                  {{ getInitials(pro.proyecto.detalle) }}
                </p>
              </div>
              <div
                v-else
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageProyectoUrl(pro.proyecto.imagen)"
                  alt="Imagen de proyecto"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- proyecto -->
            <!-- proyectos -->
            <div class="mt-4 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                Numero tarjeton -- {{ pro.proyecto.numero_tarjeton }}
              </h2>

              <p class="sm:text-2xl text-xl text-justify">
                <b>Detalle:</b> <br />
                {{ pro.proyecto.detalle }}
              </p>
              <p class="sm:text-2xl text-xl text-justify">
                <b>Descripción:</b> <br />
                {{ truncate(pro.proyecto.detalle, 80) }}
                <span
                  v-if="pro.proyecto.detalle.length > 80"
                  class="text-blue-600 cursor-pointer underline"
                  @click="showModal(pro)"
                >
                  Ver más
                </span>
              </p>

              <p
                class="sm:text-xl text-lg font-bold capitalize text-gray-600 text-justify"
              >
                Tipo: {{ pro.proyecto.tipo }}
              </p>
            </div>
            <!-- votacion -->
            <div class="mt-4 mx-auto">
              <PrimaryButton
                type="button"
                @click="proyecto(pro)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
          <!-- VOTO EN BLANCO -->
          <div
            :class="[
              'flex flex-col w-full h-full p-2 inline-block m-auto mb-4 rounded-lg shadow-xl bg-gray-200 hover:bg-gray-300 text-gray-800',
              selectedCard2 === null ? 'border-4 border-red-500' : '',
            ]"
          >
            <div class="mx-auto">
              <div
                class="w-full h-[200pt] sm:h-[300pt] bg-indigo-200 text-indigo-800"
              >
                <img
                  :src="getImageUrl('12345678_candidatos.jpg')"
                  alt="Imagen de voto en blanco"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
            <!-- Candidato -->
            <div class="mt-6 mx-auto">
              <h2
                class="sm:text-4xl text-2xl font-bold capitalize text-justify"
              >
                VOTO <br />
                EN BLANCO
              </h2>
            </div>
            <!-- votacion -->
            <div class="mt-8 mx-auto">
              <PrimaryButton
                type="button"
                @click="proyecto(null)"
                class="sm:text-2xl text-xl"
              >
                Seleccionar
              </PrimaryButton>
            </div>
          </div>
        </div>
        <!-- botones -->
        <div class="flex justify-between mt-6">
          <button
            class="sm:text-3xl text-xl text-gray-600 border border-gray-400 rounded-md px-4 py-1 hover:bg-gray-300"
            @click="previus(2)"
          >
            Anterior
          </button>
          <PrimaryButton
            type="button"
            @click="votar()"
            class="sm:text-6xl text-2xl"
          >
            Enviar votación
          </PrimaryButton>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar la descripción completa -->
    <div
      v-if="modalVisible"
      class="fixed inset-0 bg-black px-4 sm:px-44 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg w-full">
        <h2 class="sm:text-4xl text-xl font-bold mb-4 text-center">
          Detalle del Proyecto
        </h2>
        <p class="mb-4 text-base sm:text-2xl">
          {{ modalProyecto.proyecto.detalle }}
        </p>
        <p class="mb-4 text-base sm:text-2xl">
          <b>descripción:</b> {{ modalProyecto.proyecto.descripcion }}
        </p>
        <button
          @click="modalVisible = false"
          class="bg-indigo-600 text-white px-4 py-2 rounded"
        >
          Cerrar
        </button>
      </div>
    </div>

    <!-- Modal para mostrar mensaje de confirmación -->
    <div
      v-if="ModalConfirmacion"
      class="fixed inset-0 bg-black px-4 sm:px-44 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg w-full">
        <h2
          class="sm:text-8xl text-2xl font-bold mb-8 pb-4 sm:py-8 border-b border-gray-300 text-center"
        >
          🗳️ Confirmar voto
        </h2>
        <p class="mb-4 text-base sm:text-4xl">
          <span class="text-lg sm:text-5xl font-bold"
            >Está a punto de votar por el siguiente proyecto:</span
          >
          <br />
          {{ selectedProject.proyecto.detalle }}
          <br />
          Número de tarjetón: {{ selectedProject.proyecto.numero_tarjeton }}
        </p>
        <div class="w-full flex flex-wrap justify-center gap-4 sm:gap-6">
          <button
            @click="ModalConfirmacion = false"
            class="bg-red-600 text-white px-4 py-2 rounded sm:text-5xl"
          >
            Cancelar
          </button>
          <button
            @click="votar()"
            class="bg-indigo-600 text-white px-4 py-2 rounded sm:text-5xl"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Avatar from "primevue/avatar";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import InputError from "@/Components/InputError.vue";
import { inject, ref } from "vue";
import comunas from "@/shared/comunas.json"; // Importa el JSON
import Tag from "primevue/tag";
const swal = inject("$swal");

const props = defineProps({
  delegados: Object,
  votante: Object,
  proyectos: Object,
});

console.log(props);
//modales
const modalVisible = ref(false);
const ModalConfirmacion = ref(false);
//info modal
const modalProyecto = ref({ proyecto: { detalle: "" } });

const delegado = props.delegados.filter(
  (delegado) => delegado.votante.genero == "Masculino"
);

const delegada = props.delegados.filter(
  (delegada) => delegada.votante.genero == "Femenino"
);

const form = useForm({
  id_votante: props.votante[0].id,
  id_candidato: 0, //delegado
  id_evento_padre: 0, //evento principal
  id_candidato1: null, //delegada
  id_proyecto: 0, //proyecto
  id_evento: 0, //evwento hijo
  tipo: "",
  subtipo: 0,
  estado: "Activo",
});

const breadcrumbLinks = [{ url: "", text: "Votaciones" }];
const selectedCard = ref(0); // Almacena el ID del candidato seleccionado
const selectedCard1 = ref(0); // Almacena el ID del candidato seleccionado 1
const selectedCard2 = ref(0); // Almacena el ID del proyecto seleccionado
const selectedProject = ref([]); //confirmacion proyectos
const activo = ref(1); // Almacena el número de la pestaña activa

//funtion para avatar letter
const getInitials = function (name) {
  let parts = name.split(" ");
  let initials = "";
  let count = 0;

  for (var i = 0; i < parts.length && count < 2; i++) {
    if (parts[i].length > 0 && parts[i] !== "") {
      initials += parts[i][0];
      count++;
    }
  }
  return initials;
};

//MOSTRAR IMAGEN EN TABLA
//IMAGEN
const getImageUrl = (imageName) => {
  // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
  return `/storage/uploads/usuarios/${imageName}`;
};

const getImageProyectoUrl = (imageName) => {
  // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
  return `/storage/uploads/proyectos/${imageName}`;
};

const delegado1 = (candidato) => {
  console.log(candidato);

  form.id_candidato = candidato ? candidato.id_votante : -1;
  form.id_evento_padre = candidato
    ? candidato.id_evento
    : props.delegados[0].id_evento;
  form.tipo = candidato ? candidato.tipo : props.delegados[0].tipo;
  form.subtipo = candidato ? candidato.subtipo : props.delegados[0].subtipo;

  // Establecer el ID del candidato seleccionado
  selectedCard.value = candidato ? candidato.id : null;
};

const delegado2 = (candidato) => {
  console.log(candidato);

  form.id_candidato1 = candidato ? candidato.id_votante : 0;

  // Establecer el ID del candidato seleccionado
  selectedCard1.value = candidato ? candidato.id : null;
};

const truncate = (text, length) => {
  if (!text) return "";
  return text.length > length ? text.substring(0, length) + "..." : text;
};

const showModal = (pro) => {
  modalProyecto.value = pro;
  modalVisible.value = true;
};

const proyecto = (proyecto) => {
  console.log(proyecto);

  form.id_proyecto = proyecto ? proyecto.id_proyecto : null;
  form.id_evento = proyecto ? proyecto.id_evento : props.proyectos[0].id_evento;

  // Establecer el ID del candidato seleccionado
  selectedCard2.value = proyecto ? proyecto.id : null;
};

const next = (num) => {
  let isValidate = false;
  switch (num) {
    case 2:
      isValidate = form.id_candidato != 0;
      break;
    case 3:
      isValidate = form.id_candidato1 != null;
      break;

    default:
      break;
  }

  if (!isValidate) {
    swal({
      title: "Error",
      text: "Debes seleccionar un candidato o proyecto antes de continuar",
      icon: "error",
    });
  } else {
    activo.value = num;
  }
};

const previus = (num) => {
  activo.value = num;
};

const votar = () => {
  let isValidate = false;
  isValidate = form.id_proyecto != 0;

  if (!isValidate) {
    swal({
      title: "Error",
      text: "Debes seleccionar un candidato o proyecto antes de continuar",
      icon: "error",
    });
    return;
  }

  form.post(route("votos.store"), {
    onSuccess: () =>
      swal({
        title: "Voto registrado exitosamente",
        text: "Su voto ha sido registrado",
        icon: "success",
      }).then((result) => {
        router.get("dashboard");
      }),
    onError: () =>
      swal({
        title: "Error en el registro",
        text: "Su voto no fue asignado, vuelve a intentar nuevamente",
        icon: "error",
      }),
  });
};
</script>
