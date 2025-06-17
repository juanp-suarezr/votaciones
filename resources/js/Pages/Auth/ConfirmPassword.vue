<script setup>
import * as faceapi from 'face-api.js'
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const video = ref(null)
const message = ref('')
const mode = ref('register')
const isCameraReady = ref(false)

const devices = ref([])
const selectedDeviceId = ref('')

onMounted(async () => {
  try {
    // Cargar modelos
    await faceapi.nets.tinyFaceDetector.loadFromUri('/models/tiny_face_detector')
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models/face_recognition')
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models/face_landmark_68')

    // Obtener dispositivos
    const allDevices = await navigator.mediaDevices.enumerateDevices()
    devices.value = allDevices.filter(device => device.kind === 'videoinput')

    // Seleccionar por defecto la cámara integrada (si la hay)
    const preferida = devices.value.find(d => d.label.toLowerCase().includes('user')) || devices.value[0]
    selectedDeviceId.value = preferida?.deviceId || ''

    await startCamera(selectedDeviceId.value)
  } catch (error) {
    console.error('Error al iniciar:', error)
    message.value = 'No se pudo acceder a la cámara.'
  }
})

const startCamera = async (deviceId) => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: { exact: deviceId } }
    })
    video.value.srcObject = stream
    isCameraReady.value = true
  } catch (error) {
    console.error('Error al iniciar cámara:', error)
    message.value = 'Error al iniciar la cámara.'
  }
}

// Si el usuario cambia la cámara, reiniciamos el stream
watch(selectedDeviceId, async (newDeviceId) => {
  if (newDeviceId) {
    await startCamera(newDeviceId)
  }
})

const processFace = async () => {
  if (!isCameraReady.value) {
    message.value = 'La cámara no está lista.'
    return
  }

  try {
    const detection = await faceapi
      .detectSingleFace(video.value, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor()

      console.log("entro -- validador");


    if (!detection) {
      message.value = 'No se detectó un rostro.'
      return
    }

    const descriptor = detection.descriptor
    console.log('emb', descriptor);

    const response = await axios.post(`/face-${mode.value}`, {
      embedding: descriptor,
      user_id: 1,
    })

    message.value = response.data.message
  } catch (error) {
    console.error(error)
    message.value = 'Error al procesar el rostro.'
  }
}
</script>

<template>
  <div>
    <label for="camera">Seleccionar cámara:</label>
    <select id="camera" v-model="selectedDeviceId">
      <option v-for="device in devices" :key="device.deviceId" :value="device.deviceId">
        {{ device.label || 'Cámara desconocida' }}
      </option>
    </select>

    <video ref="video" autoplay muted width="320" height="240" />

    <div>
      <button @click="mode = 'register'; processFace()">Registrar rostro</button>
      <button @click="mode = 'validate'; processFace()">Validar rostro</button>
    </div>

    <p>{{ message }}</p>
  </div>
</template>
