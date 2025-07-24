<template>
  <PageLayout>
    <Head title="Registro de Usuario" />

    <div
      class="w-full sm:max-w-6xl h-full mx-auto bg-white shadow-lg rounded-lg px-4 sm:px-6 py-12 z-10"
    >
      <h1 class="text-2xl font-bold text-center text-gray-800">
        Registro votante
      </h1>

      <div class="mt-4 justify-center">
        <Steps
          class="hidden sm:block"
          :model="items"
          v-model:activeStep="active"
        />
        <Steps
          class="sm:hidden block"
          :model="itemsMobil"
          v-model:activeStep="active"
        />
      </div>

      <form class="m-auto py-4">
        <!-- parte 1 -- validación -->
        <div class="sm:grid sm:grid-cols-2 gap-6 h-full" v-if="active == 0">
          <!-- Nombre -->
          <div class="mb-2">
            <InputLabel for="nombre" value="Nombre Completo" />
            <TextInput
              id="nombre"
              type="text"
              class="mt-1 block w-full"
              v-model="form.nombre"
              required
            />
            <InputError class="mt-2" :message="form.errors.nombre" />
          </div>

          <!-- Identificación -->
          <div class="mb-2">
            <InputLabel for="identificacion" value="Identificación" />
            <TextInput
              id="identificacion"
              type="text"
              class="mt-1 block w-full"
              v-model="form.identificacion"
              required
            />
            <InputError class="mt-2" :message="form.errors.identificacion" />
          </div>

          <!-- Tipo de Documento -->
          <div class="mb-2">
            <InputLabel for="tipo_documento" value="Tipo de Documento" />
            <select
              id="tipo_documento"
              v-model="form.tipo_documento"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>Seleccione tipo de documento</option>
              <option
                v-for="tipoDoc in tipo_documento"
                :key="tipoDoc.id"
                :value="tipoDoc.nombre"
              >
                {{ tipoDoc.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.tipo_documento" />
          </div>

          <!-- Fecha de Expedición -->
          <div class="mb-2">
            <InputLabel for="fecha_expedicion" value="Fecha de Expedición" />
            <TextInput
              id="fecha_expedicion"
              type="date"
              class="mt-1 block w-full"
              v-model="form.fecha_expedicion"
              required
            />
            <InputError class="mt-2" :message="form.errors.fecha_expedicion" />
          </div>

          <!-- Lugar de Expedición -->
          <div
            class="cols-span-2 w-full mb-2"
            v-if="form.tipo_documento !== 'Cédula Extranjería'"
          >
            <InputLabel for="lugar_expedicion" value="Lugar de Expedición" />
            <!-- departamento -->
            <div class="mb-2 mt-1">
              <Select
                id="departamento"
                v-model="departamentoSelected"
                :options="departamentos"
                filter
                optionLabel="departamento"
                placeholder="Seleccione departamento"
                checkmark
                :highlightOnSelect="false"
                class="w-full"
              />
            </div>

            <!-- ciudad -->
            <div class="" v-if="form.tipo_documento !== 'Pasaporte'">
              <Select
                :disabled="
                  departamentoSelected === null || departamentoSelected === ''
                "
                id="lugar_expedicion"
                v-model="form.lugar_expedicion"
                :options="ciudadesxDep"
                filter
                optionLabel=""
                placeholder="Seleccione lugar de expedición"
                checkmark
                :highlightOnSelect="false"
                class="w-full"
              />
            </div>
            <InputError class="mt-2" :message="form.errors.lugar_expedicion" />
          </div>

          <!-- Fecha de Nacimiento -->
          <div class="mb-2">
            <InputLabel for="nacimiento" value="Fecha de Nacimiento" />
            <TextInput
              id="nacimiento"
              type="date"
              class="mt-1 block w-full"
              v-model="form.nacimiento"
              required
            />
            <InputError class="mt-2" :message="form.errors.nacimiento" />
            <p v-if="errorEdad" class="text-red-500">{{ errorEdad }}</p>
          </div>

          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-end">
            <button
              type="button"
              class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white p-2 rounded-md shadow-xl"
              @click="nextStep(1)"
            >
              Siguiente
            </button>
          </div>
        </div>
        <!-- parte 2 -- datos demograficos -->
        <div class="sm:grid sm:grid-cols-2 gap-6" v-if="active == 1">
          <!-- Género -->
          <div class="mb-2">
            <InputLabel for="genero" value="Género" />
            <div class="mt-2 block sm:flex">
              <div class="">
                <input
                  type="radio"
                  id="masculino"
                  value="Masculino"
                  v-model="form.genero"
                />
                <label class="ps-2 text-sm sm:text-base" for="masculino"
                  >Masculino</label
                >
              </div>
              <div class="sm:ms-6">
                <input
                  type="radio"
                  id="two"
                  value="Femenino"
                  v-model="form.genero"
                />
                <label class="ps-2 text-sm sm:text-base" for="two"
                  >Femenino</label
                >
              </div>
              <!-- otros generos -->
              <div v-if="IsNewGenero" class="sm:ms-6">
                <InputLabel
                  class="sm:text-sm text-xs"
                  for="genero"
                  value="¿Cual?"
                />
                <TextInput
                  id="genero"
                  type="text"
                  class="mt-1 block w-full p-0 sm:p-1"
                  v-model="form.genero"
                  autocomplete="genero"
                />
                <InputError class="mt-1" :message="form.errors.genero" />
              </div>
              <div v-else class="sm:ms-6">
                <input
                  type="radio"
                  id="tres"
                  value="true"
                  v-model="IsNewGenero"
                />
                <label class="ps-2 text-sm sm:text-base" for="tres">Otro</label>
              </div>
            </div>
          </div>

          <!-- Etnia -->
          <div class="mb-2">
            <InputLabel for="etnia" value="Grupo poblacional" />
            <select
              id="etnia"
              v-model="form.etnia"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>
                Seleccione grupo poblacional perteneciente
              </option>
              <option v-for="et in etnia" :key="et.id" :value="et.nombre">
                {{ et.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.etnia" />
          </div>
          <!-- Condicion -->
          <div class="mb-2">
            <InputLabel for="condicion" value="Condición poblacional" />
            <select
              id="condicion"
              v-model="form.condicion"
              class="mt-1 block w-full border-gray-300 rounded-lg"
            >
              <option value="" disabled>
                Seleccione condición poblacional
              </option>
              <option v-for="et in condicion" :key="et.id" :value="et.nombre">
                {{ et.nombre }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.condicion" />
          </div>

          <!-- Celular -->
          <div class="w-full mb-2">
            <!-- celular -->
            <div>
              <InputLabel for="celular" value="Celular" />
              <div class="w-full flex flex-wrap gap-2 mt-1">
                <!-- indicativo bloqueado -->
                <div
                  class="flex items-center w-auto[20%] block p-2 border border-gray-400 bg-gray-100 rounded-md"
                >
                  <p>+57</p>
                </div>
                <!-- celular -->
                <TextInput
                  id="celular"
                  type="number"
                  class="w-[80%] flex"
                  v-model="form.celular"
                  required
                />
              </div>

              <InputError class="mt-2" :message="form.errors.celular" />
            </div>
          </div>

          <!-- Comuna -->
          <div class="mb-2">
            <InputLabel for="comuna" value="Comuna/Corregimiento" />
            <Select
              id="comuna"
              v-model="form.comuna"
              :options="comunas"
              filter
              optionLabel="label"
              placeholder="Seleccione comuna del proyecto"
              checkmark
              :highlightOnSelect="false"
              class="w-full"
            />
            <InputError class="mt-2" :message="form.errors.comuna" />
          </div>

          <!-- Barrio -->
          <div class="mb-2">
            <InputLabel for="barrio" value="Barrio/Vereda" />
            <TextInput
              id="barrio"
              type="text"
              class="mt-1 block w-full"
              v-model="form.barrio"
            />
            <InputError class="mt-2" :message="form.errors.barrio" />
          </div>
          <!-- Dirección -->
          <div class="mb-2">
            <InputLabel for="direccion" value="Dirección" />
            <TextInput
              id="direccion"
              type="text"
              class="mt-1 block w-full"
              v-model="form.direccion"
              required
            />
            <InputError class="mt-2" :message="form.errors.direccion" />
          </div>
          <!-- Correo -->
          <div class="mb-2">
            <InputLabel for="email" value="Correo Electrónico" />
            <TextInput
              id="email"
              type="email"
              class="mt-1 block w-full"
              v-model="form.email"
              required
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>
          <!-- aviso contraseña -->
          <div class="mb-2">
            <div class="bg-azul rounded-md p-2 w-full h-full">
              <p class="text-white text-base">
                Asegúrese de que la contraseña sea facil de recordar para usted,
                ya que la necesitará para acceder a su cuenta en el momento de
                la votación.
              </p>
            </div>
          </div>
          <!-- Contraseña -->
          <div class="mb-2">
            <InputLabel
              for="password"
              value="Contraseña (mínimo: 8 caracteres)"
            />
            <Password
              id="password"
              v-model="form.password"
              required
              toggleMask
            />

            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- ncheck tratamiento datos -->
          <div class="my-4 col-span-2 mb-2">
            <input
              type="checkbox"
              id="consentimiento1"
              name="consentimiento1"
              required
              v-model="form.checked"
            />
            <label
              for="consentimiento1"
              class="ps-4 pe-12 sm:text-base text-sm text-gray-500"
              >Conozco y Acepto la Política de Privacidad de Datos
              <a
                href="https://www.pereira.gov.co/publicaciones/38/politicas-de-privacidad-y-condiciones-de-uso/"
                target="_blank"
                class="underline !text-azul cursor-pointer"
                >politica</a
              ></label
            >
            <InputError class="mt-1" :message="form.errors.checked" />
          </div>
          <!-- declaración juramentada -->
          <div class="mb-4 col-span-2 mb-2">
            <input
              type="checkbox"
              id="consentimiento2"
              name="consentimiento2"
              required
              v-model="form.declaracion"
            />
            <label
              for="consentimiento2"
              class="ps-4 pe-12 sm:text-base text-sm text-gray-500"
              >Manifiesto que acepto la
              <a
                href="https://www.pereira.gov.co/loader.php?lServicio=Tools2&lTipo=descargas&lFuncion=visorpdf&file=https%3A%2F%2Fwww.pereira.gov.co%2Floader.php%3FlServicio%3DTools2%26lTipo%3Ddescargas%26lFuncion%3DexposeDocument%26idFile%3D211273%26tmp%3De1c59e50ed23a13cda9087f627ac4f4d%26urlDeleteFunction%3Dhttps%253A%252F%252Fwww.pereira.gov.co%252Floader.php%253FlServicio%253DTools2%2526lTipo%253Ddescargas%2526lFuncion%253DdeleteTemporalFile%2526tmp%253De1c59e50ed23a13cda9087f627ac4f4d&pdf=1&tmp=e1c59e50ed23a13cda9087f627ac4f4d&fileItem=211273"
                target="_blank"
                class="underline !text-azul cursor-pointer"
                >Declaración</a
              >
              juramentada</label
            >
            <InputError class="mt-1" :message="form.errors.declaracion" />
          </div>
          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(0)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end h-full">
              <PrimaryButton
                type="button"
                class="h-full"
                @click="validarDatos2"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Siguiente
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- parte 3 -- registro datos -->
        <div class="gap-6" v-if="active == 2">
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
                <h4>Ejemplo parte frontal</h4>
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
                  accept="image/*"
                  @input="onFileChange('cedula_front', $event)"
                  :maxFileSize="2e6"
                />
                <InputError class="mt-2" :message="form.errors.cedula_front" />

                <div class="flex justify-center">
                  <img
                    v-if="imageUrl"
                    :src="imageUrl"
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
          <!-- parte trasera documento -->
          <div class="sm:grid sm:grid-cols-2 gap-2 h-full sm:px-16 px-4">
            <!-- titulo -->
            <div class="col-span-2 text-sm sm:text-base text-gray-800 pt-6">
              <h3 class="text-lg font-semibold">
                Cargue documento de identificación parte trasera
              </h3>
              <p>
                Para cargar el documento en su parte trasera, asegúrese de que
                la imagen sea clara y legible. El documento debe estar bien
                iluminado y sin reflejos.
              </p>
            </div>
            <!-- ejemplo de doc trasero -->
            <div class="w-full h-full mt-4">
              <div class="w-full">
                <h4>Ejemplo parte trasera</h4>
                <img
                  :src="backEjemplo"
                  alt="Documento parte trasera ejemplo"
                  class="w-full h-full object-contain mt-2"
                />
              </div>
            </div>
            <!-- Cédula trasera -->
            <div class="mb-2 h-full flex justify-center items-center mt-4">
              <div class="border-2 border-gray-300 rounded-md p-2 h-full">
                <TextInput
                  id="cedula_back"
                  type="file"
                  class="mt-1 !border-0"
                  accept="image/*"
                  @input="onFileChange('cedula_back', $event)"
                  :maxFileSize="2e6"
                />
                <InputError class="mt-2" :message="form.errors.cedula_back" />

                <div class="flex justify-center">
                  <img
                    v-if="imageUrl1"
                    :src="imageUrl1"
                    :alt="form.cedula_back"
                    class="w-4/6 h-full object-contain"
                  />
                  <PhotoIcon
                    v-else
                    class="w-2/6 text-gray-300 flex justify-center items-center"
                  />
                </div>
                <div v-if="imageUrl1" class="flex justify-center mt-2">
                  <button
                    @click="removeImage(2)"
                    type="button"
                    class="bg-red-500 text-white px-4 py-2 rounded"
                  >
                    Eliminar Imagen
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="w-full gap-2 h-full sm:px-16 px-4 mt-4">
            <InputLabel for="firma" value="Firma" />
            <div class="flex gap-4 mb-2">
              <button
                type="button"
                :class="[
                  'px-2 py-1 rounded',
                  firmaModo === 'imagen'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200',
                ]"
                @click="firmaModo = 'imagen'"
              >
                Cargar imagen
              </button>
              <button
                type="button"
                :class="[
                  'px-2 py-1 rounded',
                  firmaModo === 'canvas'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200',
                ]"
                @click="firmaModo = 'canvas'"
              >
                Dibujar firma
              </button>
            </div>
            <div v-if="firmaModo === 'imagen'">
              <TextInput
                id="firma"
                type="file"
                class="mt-1 block w-full"
                accept="image/*"
                @change="onFileChange('firma', $event)"
                :maxFileSize="2e6"
              />
              <InputError class="mt-2" :message="form.errors.firma" />
              <div v-if="firmaPreview" class="mt-2">
                <img
                  :src="firmaPreview"
                  alt="Vista previa de la firma"
                  class="h-24 border rounded"
                />
              </div>
            </div>
            <div v-else>
              <canvas
                ref="canvas"
                height="150"
                class="border border-gray-400 rounded-md bg-white cursor-crosshair sm:w-[300px] w-[250px]"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart.prevent="startDrawing"
                @touchmove.prevent="draw"
                @touchend="stopDrawing"
              ></canvas>
              <div class="flex gap-2 mt-1">
                <button
                  type="button"
                  @click="clearCanvas"
                  class="text-xl sm:text-2xl text-blue-600 underline"
                >
                  Limpiar
                </button>
                <button
                  type="button"
                  @click="saveCanvas"
                  class="text-xl sm:text-2xl text-green-600 underline"
                >
                  Usar firma
                </button>
              </div>
              <InputError class="mt-2" :message="form.errors.firma" />
              <div v-if="firmaPreview" class="mt-2">
                <img
                  :src="firmaPreview"
                  alt="Vista previa de la firma"
                  class="h-24 border rounded"
                />
              </div>
            </div>
          </div>
          <!-- CAMPO OBLIGATORIO PARA EL USUARIO -->
          <div class="w-full">
            <TextInput
              id="campo_obligatorio"
              type="text"
              class="mt-1 !border-0 hidden"
              v-model="form.campoObligatorio"
            />
          </div>
          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2 mt-4">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(1)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end">
              <PrimaryButton
                type="button"
                @click="validarDatos3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Siguiente
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- parte 4 -- verificacion -->
        <div class="gap-6" v-if="active == 3">
          <div class="text-sm sm:text-base text-gray-800 pt-6 sm:px-16 px-4">
            Ingrese el código de verificación que le fue enviado a su correo o
            celular. Si no recibió el código, por favor revise su bandeja de
            entrada o carpeta de spam. o vuelva a atrás y valide nuevamente.
          </div>
          <div class="w-full mt-6">
            <InputLabel for="codigo" value="Código de Verificación" />
            <TextInput
              id="codigo"
              type="text"
              class="mt-1 block w-full"
              v-model="form.codigo"
              required
            />
            <InputError class="mt-2" :message="form.errors.codigo" />
          </div>

          <!-- Botón de Enviar -->
          <div class="col-span-2 flex justify-between gap-2 mt-4">
            <div class="flex pt-4 justify-end">
              <button
                type="button"
                @click="prevStep(0)"
                class="bg-secondary hover:bg-primary text-xs sm:text-sm text-white px-4 rounded-md shadow-xl"
              >
                Atrás
              </button>
            </div>
            <div class="flex pt-4 justify-end">
              <PrimaryButton
                type="button"
                @click="submit"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Registrarse
              </PrimaryButton>
            </div>
          </div>
        </div>
      </form>
    </div>
  </PageLayout>

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

  <!-- Modal botnes validar -->
  <Modal :show="botonesValidarModal" :closeable="false">
    <template #default>
      <h2
        class="py-4 text-2xl font-semibold text-gray-800 flex tex-center justify-center bg-azul text-white"
      >
        Validar por código de verificación
      </h2>

      <div class="flex justify-center gap-4 text-center h-full my-12">
        <PrimaryButton
          type="button"
          class="h-full"
          @click="validationUser(0)"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Validar con correo
        </PrimaryButton>
        <SecondaryButton
          @click="validationUser(1)"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Validar con sms
        </SecondaryButton>
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
          la cámara (sin gorras, tapabocas, gafas).
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

  <!-- Modal inicio -->
  <Modal :show="InicioModal" :closeable="true">
    <template #default>
      <h2
        class="p-4 sm:text-4xl text-2x font-bold text-gray-800 text-justify flex tex-center justify-center bg-azul text-white"
      >
        Aviso Importante para el proceso de registro
      </h2>

      <div class="text-justify sm:text-2xl text-xl p-6 mt-6">
        <p>
          El dispositivo que utilice para registrarse debe contar con cámara, ya
          que es un requisito indispensable para la validación de identidad.
        </p>
        <br />
        <p>
          Asegúrese de ingresar correctamente toda la información solicitada, ya
          que cualquier error podría resultar en el rechazo. ¡Recuerde! solo
          tiene tres oportunidades para registrarse.
        </p>
        <br />
        <p>
          Al momento de seleccionar su comuna o corregimiento, hágalo con
          precisión. Recuerde que el derecho al voto solo podrá ejercerse una
          única vez y no habrá posibilidad de repetir el proceso.
        </p>
      </div>

      <div class="flex justify-center gap-4 text-center h-full my-6">
        <PrimaryButton
          type="button"
          class="h-full text-xl sm:text-2xl"
          @click="InicioModal = false"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Continuar
        </PrimaryButton>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm, Head } from "@inertiajs/vue3";
import Steps from "primevue/steps";
import Select from "primevue/select";
import ProgressSpinner from "primevue/progressspinner";
import Password from "primevue/password";
import tipo_documento from "@/shared/tipo_documento.json"; // Importa el JSON
import departamentos from "@/shared/departamentos.json"; // Importa el JSON
import ciudades from "@/shared/ciudades.json"; // Importa el JSON
import comunas from "@/shared/comunas.json"; // Importa el JSON
import condicion from "@/shared/condicion.json"; // Importa el JSON
import etnia from "@/shared/etnia.json"; // Importa el JSON
import { inject, ref, computed, watch, onMounted, onUnmounted } from "vue";
import axios from "axios";
const swal = inject("$swal");
import { useReCaptcha } from "vue-recaptcha-v3";
import { PhotoIcon } from "@heroicons/vue/24/solid";

//imagen
import frontEjemplo from "../../../../public/assets/img/cedulaFrontEjemplo.webp";
import backEjemplo from "../../../../public/assets/img/cedulaBackEjemplo.webp";

import * as faceapi from "face-api.js";

const form = useForm({
  nombre: "",
  identificacion: "",
  tipo_documento: "",
  fecha_expedicion: "",
  lugar_expedicion: "",
  nacimiento: "",
  cedula_front: null,
  cedula_back: null,
  genero: "",
  etnia: "",
  condicion: "",
  direccion: "",
  celular: "",
  comuna: "",
  barrio: "",
  email: "",
  password: "",
  embedding: "",
  photo: "",
  validaciones: "",
  checked: false,
  declaracion: false,
  firma: "",
  recaptcha_token: "",
  campoObligatorio: "",
  codigo: "",
});

const { executeRecaptcha } = useReCaptcha();

const departamentoSelected = ref("");
const ciudadesxDep = ref([]);

//firma
const firmaPreview = ref(null);
const firmaModo = ref("imagen"); // 'imagen' o 'canvas'
const canvas = ref(null);
let drawing = false;

//modal loading
const loadingModal = ref(false);

//modal Inicio
const InicioModal = ref(true);

//ITEMS DEL STEP
const items = ref([
  {
    label: "Información Personal",
  },
  {
    label: "Datos Demográficos",
  },
  {
    label: "Registro datos",
  },
  {
    label: "Verificación",
  },
]);

const itemsMobil = ref([
  {
    label: "paso 1",
  },
  {
    label: "paso 2",
  },
  {
    label: "paso 3",
  },
  {
    label: "paso 4",
  },
]);

//step activo
const active = ref(0);
//mensaje error
const errorMessage = ref("");
//error fecha nacimiento
const errorEdad = ref("");
//validado
const isValidate = ref(false);
//abrir modal
const biometricoModal = ref(false);
//SABER SI SE ACTIVA EL INPUT DE OTROS
const IsNewGenero = ref(false);

//Modal botones de validar
const botonesValidarModal = ref(false);

//imagenes documentos
//variable de imagen frontal
const imageUrl = ref(null);
//variable de imagen documento posterior
const imageUrl1 = ref(null);

//face api js
const devices = ref([]);
const selectedDeviceId = ref("");
const video = ref(null);
const message = ref("");
const isCameraReady = ref(false);
const loadingButtonBiometric = ref(false);

//CONTADOR DE ERROR EN LA INICIALIZACION CAMARA
const counterCamera = ref(0);

//WATCH GENERO
watch(IsNewGenero, (value) => {
  if (value) {
    form.genero = "";
  }
});

//WATCH DEPARTAMENTOS
watch(departamentoSelected, (newValue) => {
  if (newValue) {
    ciudadesxDep.value = ciudades.find(
      (ciudad) => ciudad.id === newValue.id
    ).ciudades;
  }
});

const onFileChange = (field, event) => {
  form[field] = event.target.files[0];
  if (field === "cedula_front") {
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl.value = e.target.result;
    };
    reader.readAsDataURL(form[field]);
  } else if (field === "cedula_back") {
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl1.value = e.target.result;
    };
    reader.readAsDataURL(form[field]);
  } else if (field == "firma") {
    const reader = new FileReader();
    reader.onload = (e) => {
      firmaPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Eliminar la imagen seleccionada
const removeImage = (num) => {
  if (num === 1) {
    form.cedula_front = null;
    imageUrl.value = null;
  } else if (num === 2) {
    form.cedula_back = null;
    imageUrl1.value = null;
  }
};

const formatDate = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

//PARTE DE FIRMA
// Canvas firma
const startDrawing = (e) => {
  drawing = true;
  const ctx = canvas.value.getContext("2d");
  const pos = getCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
  if (!drawing) return;
  const ctx = canvas.value.getContext("2d");
  const pos = getCoordinates(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.strokeStyle = "#222";
  ctx.lineWidth = 2;
  ctx.stroke();
};

const stopDrawing = () => {
  drawing = false;
};

const clearCanvas = () => {
  const ctx = canvas.value.getContext("2d");
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
  firmaPreview.value = null;
  form.firma = null;
};

const saveCanvas = () => {
  canvas.value.toBlob((blob) => {
    form.firma = new File([blob], "firma.webp", { type: "image/webp" });
    const reader = new FileReader();
    reader.onload = (e) => {
      firmaPreview.value = e.target.result;
    };
    reader.readAsDataURL(form.firma);
  }, "image/webp");
};

// 🔧 Función para obtener coordenadas, sea touch o mouse
const getCoordinates = (e) => {
  const rect = canvas.value.getBoundingClientRect();
  if (e.touches && e.touches.length > 0) {
    return {
      x: e.touches[0].clientX - rect.left,
      y: e.touches[0].clientY - rect.top,
    };
  } else {
    return {
      x: e.offsetX,
      y: e.offsetY,
    };
  }
};

//STEP PART
//prev
const prevStep = (num) => {
  active.value = num;
  isValidate.value = true;
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
            showCancelButton: true,
            cancelButtonText: "Volver a intentar",
            confirmButtonText: "Continuar",
          })
          .then((result) => {
            if (result.isConfirmed) {
              // Continuar sin validar
              form.embedding = "";
              form.validaciones = "no_rostro";
              //poner llamado a modal de botones
              biometricoModal.value = false;
              botonesValidarModal.value = true;
            } else if (result.dismiss === Swal.DismissReason.cancel) {
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
      .post(route("face-validate"), formData, {
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
              showCancelButton: true,
              cancelButtonText: "Volver a intentar",
              confirmButtonText: "Continuar",
            })
            .then((result) => {
              if (result.isConfirmed) {
                // Continuar sin validar
                form.validaciones = "registro_duplicado";
                biometricoModal.value = false;
                botonesValidarModal.value = true;
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
              botonesValidarModal.value = true;
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
              botonesValidarModal.value = true;
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
            botonesValidarModal.value = true;
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

const validateEdad = () => {
  if (!form.nacimiento) {
    errorEdad.value = "Debes ingresar la fecha de nacimiento.";
    return false;
  }
  const hoy = new Date();

  const nacimiento = form.nacimiento;
  const [anioNac, mesNac, diaNac] = nacimiento.split("-").map(Number);

  let edad = hoy.getFullYear() - anioNac;

  const m = hoy.getMonth() - (mesNac - 1);
  console.log(hoy.getMonth());

  console.log(m);

  console.log(hoy.getDate());
  if (m < 0 || (m === 0 && hoy.getDate() < diaNac)) {
    edad--;
  }
  if (edad < 14) {
    errorEdad.value =
      "No cumple con el requisito de edad mínima (14 años), por lo tanto, no puede ejercer el derecho al voto.";
    return false;
  }
  errorEdad.value = "";
  return true;
};

//validar datos step1
const validateStep1 = async () => {
  isValidate.value = false;
  if (
    form.nombre &&
    form.identificacion &&
    form.tipo_documento &&
    form.fecha_expedicion &&
    (form.lugar_expedicion || form.tipo_documento != "Cédula Ciudadanía") &&
    form.nacimiento
  ) {
    if (!validateEdad()) return;
    // Limpia mensajes previos
    errorMessage.value = "";

    // Llama al servicio para comprobar la identificación
    try {
      const existe = await checkIdentificacionService(form.identificacion);
      console.log("Identificación existe:", existe);
      if (existe) {
        errorMessage.value = "La identificación ya está registrada.";
        isValidate.value = false;
      } else {
        errorMessage.value = "";
        isValidate.value = true;
      }
    } catch (error) {
      errorMessage.value =
        "Error al validar la identificación. Intenta de nuevo.";
      isValidate.value = false;
    }
  }

  if (isValidate.value) {
    active.value = 1;
  } else {
    if (!form.nombre) {
      form.errors.nombre = "Este campo es requerido.";
    }
    if (!form.identificacion || errorMessage.value != "") {
      form.errors.identificacion =
        errorMessage.value || "Este campo es requerido.";
    }
    if (!form.tipo_documento) {
      form.errors.tipo_documento = "Este campo es requerido.";
    }
    if (!form.fecha_expedicion) {
      form.errors.fecha_expedicion = "Este campo es requerido.";
    }

    if (!form.lugar_expedicion) {
      form.errors.lugar_expedicion = "Este campo es requerido.";
    }
    if (!form.nacimiento) {
      form.errors.nacimiento = "Este campo es requerido.";
    }
  }
};

//llamado validador identificacion
const checkIdentificacionService = async (identificacion) => {
  const response = await axios.post("/validar-identificacion", {
    identificacion,
  });

  return response.data.existe; // true si existe, false si no
};

//next
const nextStep = (num) => {
  isValidate.value = false;
  switch (num) {
    case 1:
      validateStep1();
      break;

    case 2:
      validarDatos2();
      break;

    default:
      break;
  }

  if (isValidate.value) {
    active.value = num;
  }
};

//validar datos step2
const validarDatos2 = () => {
  isValidate.value = false;
  if (
    form.genero &&
    form.etnia &&
    form.condicion &&
    form.celular &&
    form.comuna &&
    form.barrio &&
    form.direccion &&
    form.email &&
    form.password &&
    form.checked &&
    form.declaracion
  ) {
    isValidate.value = true;
    active.value = 2;
  }

  if (isValidate.value) {
  } else {
    if (!form.genero) {
      form.errors.genero = "Este campo es requerido.";
    }
    if (!form.etnia) {
      form.errors.etnia = "Este campo es requerido.";
    }
    if (!form.condicion) {
      form.errors.condicion = "Este campo es requerido.";
    }
    if (!form.celular) {
      form.errors.celular = "Este campo es requerido.";
    }
    if (!form.comuna) {
      form.errors.comuna = "Este campo es requerido.";
    }

    if (!form.barrio) {
      form.errors.barrio = "Este campo es requerido.";
    }
    if (!form.direccion) {
      form.errors.direccion = "Este campo es requerido.";
    }
    if (!form.email) {
      form.errors.email = "Este campo es requerido.";
    }
    if (!form.password) {
      form.errors.password = "Este campo es requerido.";
    }
    if (!form.checked) {
      form.errors.checked = "Este campo es requerido.";
    }
    if (!form.declaracion) {
      form.errors.declaracion = "Este campo es requerido.";
    }
  }
};

const validarDatos3 = () => {
  isValidate.value = false;
  if (form.cedula_front && form.cedula_back && form.firma) {
    isValidate.value = true;
  }

  if (isValidate.value) {
    showModalBiometrico();
  } else {
    if (!form.cedula_front) {
      form.errors.cedula_front = "Este campo es requerido.";
    }
    if (!form.cedula_back) {
      form.errors.cedula_back = "Este campo es requerido.";
    }
    if (!form.firma) {
      form.errors.firma = "Este campo es requerido";
    }
  }
};

const validationUser = async (num) => {
  // Mostrar el modal de carga mientras se realiza la validación
  botonesValidarModal.value = false;
  loadingModal.value = true;
  console.log(form.embedding);

  try {
    // Continuar con la validación de correo o SMS
    const formData = new FormData();

    switch (num) {
      case 0:
        formData.append("via", "correo");
        break;
      case 1:
        formData.append("via", "sms");
        break;
    }

    const recaptchaToken = await executeRecaptcha("register");

    // Agregar el token de reCAPTCHA al formulario
    form.recaptcha_token = recaptchaToken;

    formData.append("nombre", form.nombre);
    formData.append("identificacion", form.identificacion);
    formData.append("tipo_documento", form.tipo_documento);
    formData.append("fecha_expedicion", form.fecha_expedicion);
    formData.append("lugar_expedicion", form.lugar_expedicion);
    formData.append("nacimiento", form.nacimiento);
    formData.append("etnia", form.etnia);
    formData.append("condicion", form.condicion);
    formData.append("direccion", form.direccion);
    formData.append("genero", form.genero);
    formData.append("comuna", form.comuna);
    formData.append("barrio", form.barrio);
    formData.append("email", form.email);
    formData.append("celular", form.celular);
    formData.append("password", form.password);
    formData.append("recaptcha_token", recaptchaToken);
    formData.append("campoObligatorio", form.campoObligatorio);

    // Enviar la solicitud al backend
    const response = await axios.post(route("validate-user"), formData);

    loadingModal.value = false;

    console.log(response);

    if (response.data.isSpam) {
      form.validaciones = "posible robot o spam";
    }

    swal({
      title: "Código enviado",
      text: "Revisa tu correo o SMS para el código de verificación.",
      icon: "success",
    });
    active.value = 3; // Ir al paso de verificación
  } catch (error) {
    console.error(error);
    let mensajes =
      "Hubo un problema al validar tu información. Por favor, inténtalo nuevamente.";

    // Si Laravel devuelve errores en error.response.data.errors
    if (error.response && error.response.data && error.response.data.errors) {
      const errores = error.response.data.errors;

      // Convertimos el objeto de errores a un array de mensajes
      mensajes = Object.values(errores)
        .flat() // une arrays internos en uno solo
        .join("\n"); // separa con salto de línea para mostrar en SweetAlert
    }
    swal.fire({
      title: "Error",
      text: mensajes,
      icon: "error",
    });
  } finally {
    loadingModal.value = false; // Ocultar el modal de carga
  }
};

const submit = () => {
  form.post(route("create-user"), {
    onSuccess: () => {
      swal({
        title: "Registro realizado",
        text: "Registro de usuario realizado exitosamente. Para poder votar, se realizará internamente la validación de su información, el estado del tramite sera notificado a su correo.",
        icon: "success",
      }).then((result) => {
        window.location.href = route("welcome");
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
      }).then((result) => {
        active.value = 0;
      });

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
