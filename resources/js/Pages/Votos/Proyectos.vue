<template>
  <Head title="Votaciones" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      {{
        $page.props.auth.user.email == "ppt"
          ? "Elecciones Presupuesto Participativo"
          : "Votaciones"
      }}
    </template>

    <div class="items-center flex flex-col justify-center mx-2">
      <div class="sm:flex w-full sm:grid md:grid-cols-2 grid-cols-2 gap-4 mt-6">
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
              v-if="pro.proyecto.tipo_proyecto.imagen == null"
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
                :src="getImageProyectoUrl(pro.proyecto.tipo_proyecto.imagen)"
                alt="Imagen de proyecto"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          <Tag
            :value="getComuna(pro.proyecto.subtipo)"
            class="!bg-primary text-[8px] sm:text-sm !text-white w-full flex p-2"
          />
          <!-- proyecto -->
          <!-- proyectos -->
          <div class="mt-4 mx-auto">
            <h2 class="sm:text-4xl text-2xl font-bold capitalize text-justify">
              Proyecto -- {{ pro.proyecto.numero_tarjeton }}
            </h2>

            <p class="sm:text-2xl text-xl text-justify">
              <b>Nombre Proyecto:</b> <br />
              {{ pro.proyecto.detalle }}
            </p>

            <p class="sm:text-2xl text-xl text-justify">
              <b>Descripción:</b> <br />
              {{ truncate(pro.proyecto.descripcion, 80) }}
              <span
                v-if="pro.proyecto.descripcion.length > 80"
                class="text-blue-600 cursor-pointer underline"
                @click="showModal(pro)"
              >
                Ver más
              </span>
            </p>

            <!-- <p
              class="sm:text-xl text-lg font-bold capitalize text-gray-600 text-justify"
            >
              Tipo: {{ pro.proyecto.tipo }}
            </p> -->
          </div>
          <!-- votacion -->
          <div class="mt-4 mx-auto">
            <PrimaryButton
              type="button"
              @click="proyecto(pro)"
              class="sm:text-2xl text-xl"
            >
              Votar
            </PrimaryButton>
          </div>
        </div>
        <!-- VOTO EN BLANCO -->
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
            <h2 class="sm:text-4xl text-2xl font-bold capitalize text-justify">
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
              Votar
            </PrimaryButton>
          </div>
        </div>
      </div>

      <!-- botones -->
      <!-- <div class="flex justify-between mt-8">
        <PrimaryButton
          :disabled="selectedCard2 == 0"
          type="button"
          @click="ModalConfirmacion = true"
          class="sm:text-6xl text-2xl"
        >
          Enviar votación
        </PrimaryButton>
      </div> -->
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
        <!-- Botón lector por voz -->
        <div class="flex justify-end pr-6 pt-4">
          <button
            @click="leerAviso1(selectedProject)"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-lg sm:text-xl shadow-md"
          >
            🔊 Escuchar
          </button>
        </div>
        <p class="mb-4 text-base sm:text-4xl">
          <span class="text-lg sm:text-5xl font-bold"
            >Está a punto de votar por el siguiente proyecto:</span
          >
        </p>
        <div class="flex flex-wrap gap-8 mt-4 text-base sm:text-4xl">
          <div class="w-auto border-r pr-2 border-gray-600">
            <span class="text-gray-600">Número Proyecto:</span>
            {{
              selectedProject
                ? selectedProject.proyecto.numero_tarjeton
                : "Elecciones presupuesto participativo 2025"
            }}
          </div>
          <div class="w-auto pr-2">
            <span class="text-gray-600">Comuna/Corregimiento:</span>
            {{
              selectedProject
                ? getComuna(selectedProject.proyecto.subtipo)
                : getComuna(proyectos[0].proyecto.subtipo)
            }}
          </div>
        </div>
        <div class="mt-6 text-base sm:text-4xl">
          <span class="text-gray-600">Nombre Proyecto:</span>
          {{
            selectedProject
              ? selectedProject.proyecto.detalle
              : "VOTO EN BLANCO"
          }}
        </div>

        <div class="w-full flex flex-wrap justify-center gap-4 sm:gap-6 mt-6">
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

    <!-- Modal inicio -->
    <Modal :show="InicioModal" :closeable="true">
      <template #default>
        <!-- Título -->
        <h2
          class="p-4 sm:text-4xl text-2xl font-bold flex justify-center text-center bg-azul text-white"
        >
          Aviso Importante para el proceso de votación
        </h2>

        <!-- Botón lector por voz -->
        <div class="flex justify-end pr-6 pt-4">
          <button
            @click="leerAviso()"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-lg sm:text-xl shadow-md"
          >
            🔊 Escuchar
          </button>
        </div>

        <!-- Mensaje principal -->
        <div class="text-justify sm:text-2xl text-xl p-6 leading-relaxed">
          <p>
            Revise bien su elección antes de votar, ya que no se permitirá una
            segunda oportunidad para realizar esta acción.
          </p>
        </div>

        <!-- Listado accesible con scroll -->
        <div class="px-6 mt-4">
          <h3 class="text-2xl sm:text-3xl font-semibold mb-4 text-gray-800">
            Proyectos Disponibles
          </h3>

          <div class="max-h-80 overflow-y-auto pr-2 space-y-4">
            <ul class="space-y-4">
              <li
                v-for="pro in proyectos"
                :key="pro.id"
                class="p-4 rounded-xl shadow-md bg-gray-50 border border-gray-200"
              >
                <div class="flex flex-col gap-2">
                  <h4
                    class="font-bold text-xl sm:text-2xl text-blue-900 flex items-center gap-2"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-7 h-7 sm:w-8 sm:h-8 text-blue-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 1.567-3 3.5S10.343 15 12 15s3-1.567 3-3.5S13.657 8 12 8zm0 10c-3.314 0-6-2.463-6-5.5S8.686 7 12 7s6 2.463 6 5.5S15.314 18 12 18z"
                      />
                    </svg>

                    {{ pro.nombre }}
                  </h4>

                  <p class="text-lg sm:text-xl text-gray-700 leading-relaxed">
                    {{ pro.proyecto.detalle }}
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Botón continuar -->
        <div class="flex justify-center gap-4 text-center h-full my-8">
          <PrimaryButton
            type="button"
            class="h-full text-xl sm:text-2xl px-8 py-3"
            @click="InicioModal = false"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Continuar
          </PrimaryButton>
        </div>
      </template>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Avatar from "primevue/avatar";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryLink from "@/Components/SecondaryLink.vue";
import InputError from "@/Components/InputError.vue";
import Tag from "primevue/tag";
import { inject, ref } from "vue";
import comunas from "@/shared/comunas_completas.json"; // Importa el JSON
const swal = inject("$swal");
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  proyectos: Object,
  votante: Object,
  last_vote: Number,
});

console.log(props);

const form = useForm({
  isProyecto: true,
  id_votante: props.votante[0].id,
  id_candidato: 0,
  id_eventos: 0,
  tipo: "",
  subtipo: 0,
  estado: "Activo",
  last_vote: props.last_vote ? parseInt(props.last_vote) : 0,
});

const breadcrumbLinks = [{ url: "", text: "Votaciones" }];
//modales
const InicioModal = ref(true);
const modalVisible = ref(false);
const ModalConfirmacion = ref(false);
//info
const modalProyecto = ref({ proyecto: { detalle: "" } });
//selected
const selectedCard2 = ref(0); // Almacena el ID del proyecto seleccionado
const selectedProject = ref([]);

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

const getComuna = (idComuna) => {
  console.log(idComuna);

  return comunas.find((item) => item.value == idComuna)?.label;
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
  selectedProject.value = proyecto;
  console.log(proyecto);

  form.id_candidato = proyecto ? proyecto.id_proyecto : 0;
  form.id_eventos = proyecto
    ? proyecto.id_evento
    : props.proyectos[0].id_evento;
  form.tipo = proyecto
    ? proyecto.proyecto.tipo
    : props.proyectos[0].proyecto.tipo;
  form.subtipo = proyecto
    ? proyecto.proyecto.subtipo
    : props.proyectos[0].proyecto.subtipo;

  // Establecer el ID del candidato seleccionado
  selectedCard2.value = proyecto ? proyecto.id : null;
  ModalConfirmacion.value = true;
};

const votar = () => {
  form.post(route("votos.store"), {
    onSuccess: () => {
      swal({
        title: "Voto registrado exitosamente",
        text: "Su voto ha sido registrado",
        icon: "success",
        buttons: false, // Oculta botones
        closeOnClickOutside: false,
        closeOnEsc: false,
      });

      // Mostrar el botón OK después de un delay
      setTimeout(() => {
        swal({
          title: "Voto registrado exitosamente",
          text: "Su voto ha sido registrado",
          icon: "success",
          buttons: {
            confirm: "OK",
          },
        }).then(() => {
          router.get("dashboard");
        });
      }, 3000); // 2 segundos
    },
    onError: () =>
      swal({
        title: "Error en el registro",
        text: "Su voto no fue asignado, vuelve a intentar nuevamente",
        icon: "error",
      }),
  });
};

const leerTexto = (texto) => {
  // Cancelar si ya está leyendo algo
  window.speechSynthesis.cancel();

  const speech = new SpeechSynthesisUtterance(texto);
  speech.lang = "es-CO"; // Español Colombia
  speech.rate = 1; // Velocidad natural
  speech.pitch = 1; // Tono

  window.speechSynthesis.speak(speech);
};

const leerAviso = () => {
  let texto =
    "Aviso importante para el proceso de votación. " +
    "Revise bien su elección antes de votar, ya que no se permitirá una segunda oportunidad. " +
    "Proyectos disponibles: ";

  props.proyectos.forEach((pro, index) => {
    texto += `. Proyecto: ${index + 1}. ${pro.proyecto.detalle}. `;
  });

  leerTexto(texto);
};

/* ===========================================
   🔊 lector de voz para confirmar voto
   =========================================== */
const leerAviso1 = (selectedProject) => {
  let texto = "Confirmación de voto. ";

  if (selectedProject) {
    texto +=
      "Está a punto de votar por el siguiente proyecto: " +
      "Número del proyecto: " +
      selectedProject.proyecto.numero_tarjeton +
      ". " +
      "Comuna o Corregimiento: " +
      getComuna(selectedProject.proyecto.subtipo) +
      ". " +
      "Nombre del proyecto: " +
      selectedProject.proyecto.detalle +
      ". ";
  } else {
    // Caso VOTO EN BLANCO
    const primerProyecto = props.proyectos[0];

    texto +=
      "Está a punto de emitir un voto en blanco para las elecciones de presupuesto participativo 2025. " +
      "Comuna o Corregimiento: " +
      getComuna(primerProyecto.proyecto.subtipo) +
      ". " +
      "Voto en blanco. ";
  }

  leerTexto(texto);
};
</script>
