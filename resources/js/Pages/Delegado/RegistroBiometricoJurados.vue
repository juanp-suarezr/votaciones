<template>
  <Head title="Registro biométrico de jurados" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <h1 class="text-xl font-bold">Registro biométrico de jurados</h1>
    </template>

    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-8 mt-8">
      <form @submit.prevent="submit">
        <!-- parte 1 -- registro datos -->
        <div class="gap-6">
          <!-- parte frontal documento -->
          <div class="sm:grid sm:grid-cols-2 gap-2 h-full sm:px-16 px-4">
            <!-- titulo -->
            <div class="col-span-2 text-sm sm:text-base text-gray-800 pt-6">
              <h3 class="text-lg font-semibold">
                Cargue documento de identificación parte frontal
              </h3>
              <p>
                Para cargar el documento frontal, asegúrese de que la imagen sea
                clara y legible. El documento debe estar bien iluminado y sin
                reflejos.
              </p>
            </div>
            <!-- ejemplo de doc frontal -->
            <div class="w-full h-full mt-4">
              <div class="w-full">
                <h4 class="text-2xl underline">Ejemplo parte frontal</h4>
                <img
                  :src="frontEjemplo"
                  alt="Documento Frontal ejemplo"
                  class="w-full h-full object-contain mt-2"
                />
              </div>
            </div>
            <!-- Cédula Frontal -->
            <div class="mb-2 h-full flex justify-center items-center mt-4">
              <div class="border-2 border-gray-300 rounded-md p-2 h-full">
                <TextInput
                  id="cedula_front"
                  type="file"
                  class="mt-1 !border-0"
                  accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp"
                  @input="onFileChange('cedula_front', $event)"
                  :maxFileSize="2e6"
                />
                <InputError class="mt-2" :message="form.errors.cedula_front" />

                <div class="flex justify-center">
                  <img
                    v-if="imageUrl"
                    :src="getUrlDocumentos(imageUrl, 1)"
                    :alt="form.cedula_front"
                    class="w-4/6 h-full object-contain"
                  />
                  <PhotoIcon
                    v-else
                    class="w-2/6 text-gray-300 flex justify-center items-center"
                  />
                </div>
                <div v-if="imageUrl" class="flex justify-center mt-2">
                  <button
                    @click="removeImage(1)"
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded"
                  >
                    Eliminar Imagen
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- CAMPO OBLIGATORIO PARA EL USUARIO -->
          <div class="w-full">
            <TextInput
              id="campo_obligatorio"
              type="text"
              class="mt-3 !border-0 hidden"
              v-model="form.campoObligatorio"
            />
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <PrimaryButton :disabled="form.processing" @click="validarDatos">
            Registrar jurado
          </PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>

  <!-- Modal loading -->
  <Modal :show="loadingModal" :closeable="false">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Cargando datos...
      </h2>

      <div class="flex justify-center my-12">
        <ProgressSpinner aria-label="Loading" />
      </div>
    </template>
  </Modal>

  <!-- MODAL VALIDACION -->
  <Modal :show="biometricoModal" :closeable="true">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex flex-wrap tex-center justify-center bg-azul text-white"
      >
        Validación Biométrica
      </h2>

      <div class="mt-4 px-4">
        <p class="text-sm sm:text-base text-gray-800">
          Por favor, asegúrese de que su rostro esté bien iluminado y visible en
          la cámara (sin gorras, tapabocas, gafas). Si no se detecta un rostro,
          puede intentar nuevamente o continuar sin registro biométrico.
        </p>
      </div>
      <div class="my-12 px-4">
        <label for="camera">Seleccionar cámara:</label>
        <select
          id="camera"
          v-model="selectedDeviceId"
          class="rounded-lg w-auto ms-4"
        >
          <option
            v-for="device in devices"
            :key="device.deviceId"
            :value="device.deviceId"
          >
            {{ device.label || "Cámara desconocida" }}
          </option>
        </select>

        <video
          ref="video"
          autoplay
          muted
          width="400"
          height="340"
          class="rounded-xl shadow-lg flex mx-auto mt-6"
        />

        <button
          type="button"
          :disabled="loadingButtonBiometric"
          class="bg-secondary hover:bg-primary text-sm sm:text-base text-white p-2 rounded-md shadow-xl flex mx-auto mt-4 disabled:bg-gray-500"
          @click="registerAndValidate()"
        >
          Validar
        </button>
        {{ message }}
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";

import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";

import axios from "axios";
import { PhotoIcon } from "@heroicons/vue/24/solid";

//imagen
import frontEjemplo from "../../../../public/assets/img/cedulaFrontEjemplo.webp";

import * as faceapi from "face-api.js";
const swal = inject("$swal");

const breadcrumbLinks = [{ url: "", text: "Registro biometrico" }];

const form = useForm({
  cedula_front: null,
  campoObligatorio: "",
  embedding: null,
  photo: null,
  validaciones: "",
  checked: true,
  declaracion: true,
});

//validado
const isValidate = ref(false);
//abrir modal
const biometricoModal = ref(false);
//imagenes documentos
//variable de imagen frontal
const imageUrl = ref(null);

//face api js
const devices = ref([]);
const selectedDeviceId = ref("");
const video = ref(null);
const message = ref("");
const isCameraReady = ref(false);
const loadingButtonBiometric = ref(false);

//CONTADOR DE ERROR EN LA INICIALIZACION CAMARA
const counterCamera = ref(0);

const getUrlDocumentos = (url, num) => {
  if (num === 1) {
    if (form.cedula_front === null) {
      return `/storage/uploads/documentos/${url}`;
    }
  }
  return url;
};

const onFileChange = (field, event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Función para comprimir/redimensionar
  const compressImage = (file, callback) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        const maxWidth = 1024;
        const maxHeight = 1024;
        let width = img.width;
        let height = img.height;

        // Redimensionar manteniendo proporción
        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            height = maxHeight;
          }
        }

        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0, width, height);

        // Convertir a JPEG comprimido
        canvas.toBlob(
          (blob) => {
            if (blob.size > 2e6) {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Incluso comprimida, la imagen supera los 2MB.",
              });
              return;
            }
            const compressedFile = new File([blob], file.name, {
              type: "image/jpeg",
              lastModified: Date.now(),
            });
            callback(compressedFile, URL.createObjectURL(compressedFile));
          },
          "image/jpeg",
          0.8
        );
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  // Procesar la imagen
  compressImage(file, (finalFile, previewUrl) => {
    form[field] = finalFile;

    if (field === "cedula_front") {
      imageUrl.value = previewUrl;
    }
  });
};

// Eliminar la imagen seleccionada
const removeImage = (num) => {
  if (num === 1) {
    form.cedula_front = null;
    imageUrl.value = null;
  }
};

//abrir modal y camera
const showModalBiometrico = async () => {
  loadingModal.value = true;
  try {
    // ✅ Solicitar permiso para la cámara
    const permiso = await navigator.mediaDevices.getUserMedia({ video: true });
    permiso.getTracks().forEach((track) => track.stop()); // cerrar inmediatamente

    // Cargar modelos
    await faceapi.nets.tinyFaceDetector.loadFromUri(
      "/models/tiny_face_detector"
    );
    await faceapi.nets.faceRecognitionNet.loadFromUri(
      "/models/face_recognition"
    );
    await faceapi.nets.faceLandmark68Net.loadFromUri(
      "/models/face_landmark_68"
    );

    // Obtener dispositivos
    const allDevices = await navigator.mediaDevices.enumerateDevices();
    devices.value = allDevices.filter((device) => device.kind === "videoinput");

    // Seleccionar por defecto la cámara integrada (si la hay)
    const preferida =
      devices.value.find((d) => d.label.toLowerCase().includes("user")) ||
      devices.value[0];
    console.log(preferida);
    selectedDeviceId.value = preferida?.deviceId || "";

    loadingModal.value = false;
    biometricoModal.value = true;
    await startCamera(selectedDeviceId.value);
  } catch (error) {
    loadingModal.value = false;
    if (counterCamera.value == 3) {
    }
    console.error("Error al iniciar:", error);
    message.value = "No se pudo acceder a la cámara.";
  }
};

//validar registro biometrico
const registerAndValidate = async () => {
  message.value = "";
  loadingButtonBiometric.value = true;

  // Inicializar contadores si no existen
  const getCounter = (key) => parseInt(sessionStorage.getItem(key) || "0");
  const setCounter = (key, value) =>
    sessionStorage.setItem(key, value.toString());

  if (!isCameraReady.value) {
    const falloCamara = getCounter("fallo_camara") + 1;

    message.value = "La cámara no está lista.";
    loadingButtonBiometric.value = false;

    return;
  }

  try {
    const detection = await faceapi
      .detectSingleFace(video.value, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    console.log("entro -- validador");

    if (!detection) {
      loadingButtonBiometric.value = false;
      message.value = "No se detectó un rostro.";

      counterCamera.value += 1;

      // Capturar imagen del video
      const canvas = document.createElement("canvas");
      canvas.width = video.value.videoWidth;
      canvas.height = video.value.videoHeight;
      const context = canvas.getContext("2d");
      context.drawImage(video.value, 0, 0, canvas.width, canvas.height);

      const imageBlob = await new Promise((resolve) =>
        canvas.toBlob(resolve, "image/jpeg")
      );

      // Convertir Blob a un File para enviarlo como si fuera un archivo subido
      const file = new File([imageBlob], "photo.jpg", { type: "image/jpeg" });

      form.photo = file;

      if (counterCamera.value == 3) {
        swal
          .fire({
            title: "Error en validación",
            text: "Error, no se detectó un rostro, vuelve a intentar o avanzar con la imagen poco visible y posiblemente sin registro biométrico (alta probabilidad de rechazo)",
            icon: "error",
            showCancelButton: false,
            confirmButtonText: "Volver a intentar",
          })
          .then((result) => {
            if (result) {
              // Volver a intentar
              console.log("Usuario decide volver a intentar");
              message.value = "";
              counterCamera.value = 0;
            }
          });
      }

      return;
    }

    const descriptor = detection.descriptor;
    form.embedding = descriptor;
    console.log("emb", descriptor);

    // Capturar imagen del video
    // Capturar imagen del video
    const canvas = document.createElement("canvas");
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    const context = canvas.getContext("2d");
    context.drawImage(video.value, 0, 0, canvas.width, canvas.height);

    const imageBlob = await new Promise((resolve) =>
      canvas.toBlob(resolve, "image/jpeg")
    );

    // Convertir Blob a un File para enviarlo como si fuera un archivo subido
    const file = new File([imageBlob], "photo.jpg", { type: "image/jpeg" });

    form.photo = file;

    const formData = new FormData();
    formData.append("embedding", descriptor);
    loadingButtonBiometric.value = false;
    biometricoModal.value = false;
    loadingModal.value = true;
    axios
      .post(route("face-validate-jurado"), formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        message.value = response.data.message;
        console.log(response);
        loadingButtonBiometric.value = false;
        loadingModal.value = false;
        if (response.data.match) {
          swal
            .fire({
              title: "Error en validación",
              text: "Usted ya tiene un registro biométrico. Si está seguro que no se ha registrado, puede volver a intentarlo o continuar (probabilidad de rechazo).",
              icon: "error",
              showCancelButton: false,

              confirmButtonText: "Volver a intentar",
            })
            .then((result) => {
              if (result.isConfirmed) {
                // Continuar sin validar
                form.validaciones = "registro_duplicado";
                biometricoModal.value = false;
                submit();
                //poner llamado a modal de botones
              } else if (result.dismiss === swal.DismissReason.cancel) {
                // Volver a intentar
                console.log("Usuario decide volver a intentar");
              }
            });
        } else {
          swal({
            title: "Validación exitosa",
            text: "Validación biométrica exitosa",
            icon: "success",
            didClose: () => {
              //poner llamado a modal de botones
              biometricoModal.value = false;
              loadingModal.value = true;
              submit();
            },
          });
        }
      })
      .catch((error) => {
        loadingButtonBiometric.value = false;
        loadingModal.value = false;
        console.log(error);

        const falloRegistro = getCounter("fallo_registro") + 1;
        setCounter("fallo_registro", falloRegistro);

        swal
          .fire({
            title: "Error en validación",
            text: "Error en la validación, vuelve a intentar o avanzar sin registro biometrico (posibilidad de rechazo)",
            icon: "error",
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar",
          })
          .then((result) => {
            if (result.isConfirmed) {
              // Continuar sin validar
              form.embedding = "";
              form.validaciones = "fallo_registro_biometrico";
              biometricoModal.value = false;
              submit();
              //poner llamado a modal de botones
            } else if (result.dismiss === swal.DismissReason.cancel) {
              // Volver a intentar
              console.log("Usuario decide volver a intentar");
            }
          });
      });
  } catch (error) {
    console.error(error);
    loadingButtonBiometric.value = false;
    loadingModal.value = false;

    if (counterCamera.value == 3) {
      swal
        .fire({
          title: "Error en validación",
          text: "Error al procesar el rostro, vuelve a intentar o avanzar sin registro biometrico (posibilidad de rechazo)",
          icon: "error",
          showCancelButton: true,
          cancelButtonText: "Volver a intentar",
          confirmButtonText: "Continuar",
        })
        .then((result) => {
          if (result.isConfirmed) {
            // Continuar sin validar
            form.embedding = "";
            form.validaciones = "fallo_registro";
            //poner llamado a modal de botones
            biometricoModal.value = false;
            submit();
          } else if (result.dismiss === swal.DismissReason.cancel) {
            // Volver a intentar
            console.log("Usuario decide volver a intentar");
            message.value = "";
            counterCamera.value = 0;
          }
        });
    }
    message.value = "Error al procesar el rostro.";

    //poner llamado a modal de botones
  }
};

const validarDatos = () => {
  isValidate.value = false;
  console.log("Validando datos del paso 3", imageUrl.value);

  if (form.cedula_front || imageUrl) {
    console.log("Datos del paso 3 son válidos");

    isValidate.value = true;
  }

  if (isValidate.value) {
    // Si no tiene registro biométrico, abrir modal de validación
    showModalBiometrico();
  } else {
    if (!form.cedula_front) {
      form.errors.cedula_front = "Este campo es requerido.";
    }
  }
};

const submit = () => {
  form.post(route("registro-biometrico-jurado"), {
    onSuccess: () => {
      swal({
        title: "Registro actualizado",
        text: "Registro de jurado actualizado exitosamente.",
        icon: "success",
      }).then((result) => {
        window.location.href = route("dashboard");
      });
      sessionStorage.removeItem("fallo_camara");
      sessionStorage.removeItem("fallo_registro");
      sessionStorage.removeItem("registro_duplicado");
      form.reset();
      stopCamera();
    },
    onError: (errors) => {
      swal({
        title: "Error",
        text: "Ocurrió un error al registrar el usuario.",
        icon: "error",
      }).then((result) => {});

      console.error(errors); // Puedes ver los errores específicos aquí
    },
  });
};

//funciones camara
const startCamera = async (deviceId) => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: { exact: deviceId } },
    });
    video.value.srcObject = stream;
    isCameraReady.value = true;
  } catch (error) {
    console.error("Error al iniciar cámara:", error);
    message.value = "Error al iniciar la cámara.";
  }
};

// Si el usuario cambia la cámara, reiniciamos el stream
watch(selectedDeviceId, async (newDeviceId) => {
  if (newDeviceId) {
    await startCamera(newDeviceId);
  }
});

const stopCamera = () => {
  const stream = video.value?.srcObject;
  if (stream) {
    stream.getTracks().forEach((track) => track.stop());
  }
  video.value.srcObject = null;
};

watch(biometricoModal, (newVal) => {
  if (newVal == false) {
    stopCamera();
  }
});
</script>
