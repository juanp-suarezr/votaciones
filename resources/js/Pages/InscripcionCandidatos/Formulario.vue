<template>
  <Head title="Inscripción Candidato - Comisión de Personal" />

  <div class="min-h-screen bg-slate-100 py-8 px-4 sm:px-6 lg:px-8 text-slate-800">
    <!-- Contenedor Principal con estilo de Documento Institucional -->
    <div class="max-w-4xl mx-auto bg-white border-2 border-slate-300 rounded-2xl shadow-xl overflow-hidden print:border-none print:shadow-none">
      
      <!-- ENCABEZADO INSTITUCIONAL -->
      <div class="border-b-2 border-slate-300 p-6 sm:p-8 bg-slate-50/80 text-center relative">
        <div class="flex items-center justify-between mb-4">
          <Link
            href="/"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition print:hidden"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Volver al portal principal</span>
          </Link>
          <span class="text-[11px] font-mono font-bold px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-full border border-indigo-200">
            Proceso Oficial
          </span>
        </div>

        <!-- LOGO OFICIAL DE LA ALCALDÍA DE PEREIRA -->
        <div class="flex justify-center mb-3">
          <img
            :src="logoAlcaldia"
            alt="Alcaldía de Pereira"
            class="h-16 sm:h-20 w-auto object-contain drop-shadow-xs"
          />
        </div>

        <div class="space-y-1">
          <h2 class="text-base sm:text-xl font-black uppercase tracking-wider text-slate-900">
            {{ titulo_encabezado }}
          </h2>
          <h3 v-if="subtitulo_encabezado" class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700">
            {{ subtitulo_encabezado }}
          </h3>
          <h1 class="text-xs sm:text-sm font-extrabold uppercase tracking-tight text-indigo-900 pt-0.5">
            {{ nombre_formulario }}
          </h1>
        </div>

        <p class="text-xs text-slate-500 mt-2 max-w-xl mx-auto">
          Formulario oficial de postulación para empleados públicos con condición de carrera administrativa. Seleccione el evento pendiente, ingrese su documento para validar su registro y adjunte los soportes obligatorios.
        </p>
      </div>

      <!-- BARRA DE ACCESIBILIDAD Y APOYO AUDITIVO (INCLUSIÓN DISCAPACIDAD VISUAL) -->
      <aside aria-label="Asistencia por voz y accesibilidad" class="bg-slate-900 text-white px-4 sm:px-6 py-3 border-b-2 border-[#C20E1A] flex flex-wrap items-center justify-between gap-3 shadow-inner print:hidden">
        <div class="flex items-center gap-2.5">
          <span class="p-2 bg-[#C20E1A]/20 border border-[#C20E1A]/40 rounded-xl text-[#FFB800] shadow-xs" aria-hidden="true">
            <SpeakerWaveIcon class="w-5 h-5" />
          </span>
          <div>
            <span class="text-xs font-black uppercase tracking-wider block">Asistencia por Voz / Inclusión Visual</span>
            <span class="text-[11px] text-slate-300">Escuche las instrucciones completas y cada sección del formulario</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="leerInstruccionesGenerales"
            class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm active:scale-95"
            :class="speaking ? 'bg-amber-400 text-slate-950 ring-2 ring-white animate-pulse' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
            :aria-label="speaking ? 'Reproduciendo instrucciones en voz alta' : 'Escuchar instrucciones generales del formulario'"
          >
            <SpeakerWaveIcon class="w-4 h-4" />
            <span>{{ speaking ? 'Reproduciendo...' : 'Leer Instrucciones' }}</span>
          </button>

          <button
            v-if="speaking"
            type="button"
            @click="pausarLectura"
            class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs"
            aria-label="Pausar lectura"
          >
            <PauseIcon class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Pausar</span>
          </button>

          <button
            v-else-if="paused"
            type="button"
            @click="toggleReproducir"
            class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs"
            aria-label="Reanudar lectura"
          >
            <PlayIcon class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Reanudar</span>
          </button>

          <button
            v-if="speaking || paused"
            type="button"
            @click="detenerLectura"
            class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs"
            aria-label="Detener lectura de audio"
          >
            <StopIcon class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Detener</span>
          </button>
        </div>
      </aside>

      <!-- VISTA DE ÉXITO CON RADICADO (SI YA SE POSTULÓ EXITOSAMENTE) -->
      <div v-if="postulacionExitosa" class="p-8 sm:p-12 text-center space-y-6">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
          <CheckCircleIcon class="w-10 h-10" />
        </div>

        <div class="space-y-2">
          <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 inline-block uppercase tracking-wider">
            ¡Inscripción Radicada Exitosamente!
          </span>
          <h2 class="text-2xl font-black text-slate-900">{{ postulacionExitosa.nombre }}</h2>
          <p class="text-xs text-slate-500">
            Su postulación ha sido radicada formalmente y entrará a la etapa de revisión y auditoría documental por la Dirección de Talento Humano.
          </p>
        </div>

        <!-- Ficha de Constancia de Radicación -->
        <div class="max-w-md mx-auto bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-5 text-left text-xs space-y-2.5 shadow-sm">
          <div class="flex justify-between items-center pb-2 border-b border-slate-200">
            <span class="text-slate-500 font-semibold uppercase">Número de Radicado:</span>
            <span class="font-mono text-sm font-black text-indigo-700">{{ postulacionExitosa.radicado }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Identificación:</span>
            <span class="font-mono font-bold text-slate-800">{{ postulacionExitosa.tipo_documento ? postulacionExitosa.tipo_documento + ' ' : '' }}{{ postulacionExitosa.identificacion }}</span>
          </div>
          <div class="flex justify-between items-center" v-if="postulacionExitosa.evento">
            <span class="text-slate-500 font-semibold">Evento:</span>
            <span class="font-bold text-indigo-900">{{ postulacionExitosa.evento }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Estamento / Planta:</span>
            <span class="font-bold text-slate-800">{{ postulacionExitosa.tipo_planta }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-500 font-semibold">Fecha de Radicación:</span>
            <span class="font-semibold text-slate-700">{{ postulacionExitosa.fecha }}</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-amber-700">
            <span class="font-semibold">Estado Actual:</span>
            <span class="font-bold uppercase bg-amber-100 px-2 py-0.5 rounded text-[11px] border border-amber-300">Pendiente de Verificación</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-4 print:hidden">
          <button
            type="button"
            @click="leerExito"
            class="px-5 py-2.5 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-2 shadow-sm"
            aria-label="Escuchar constancia de radicación en voz alta"
          >
            <SpeakerWaveIcon class="w-4 h-4" />
            <span>Escuchar Constancia</span>
          </button>

          <button
            type="button"
            @click="imprimirConstancia"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-2 shadow-sm"
          >
            <PrinterIcon class="w-4 h-4" />
            <span>Imprimir Comprobante</span>
          </button>

          <button
            type="button"
            @click="reiniciarFormulario"
            class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition shadow-xs"
          >
            Nueva Consulta / Registro
          </button>
        </div>
      </div>

      <!-- FORMULARIO DE POSTULACIÓN -->
      <div v-else class="p-6 sm:p-8 space-y-8">

        <!-- PASO 1: SELECCIÓN DE EVENTO PENDIENTE -->
        <div class="bg-slate-50 border-2 border-slate-300 rounded-xl p-5 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-indigo-700 text-white rounded-lg shadow-sm">
                <CalendarDaysIcon class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                  Paso 1: Seleccione el Evento de Votación (En Estado Pendiente)
                </h3>
                <p class="text-[11px] text-slate-500">
                  Solo se habilitan inscripciones de candidatos para eventos que se encuentran en preparación previa (estado Pendiente).
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="leerPaso1"
              class="p-2 text-indigo-700 hover:text-indigo-900 hover:bg-indigo-100 rounded-lg transition shrink-0 print:hidden"
              title="Escuchar instrucciones del Paso 1"
              aria-label="Escuchar instrucciones del Paso 1"
            >
              <SpeakerWaveIcon class="w-5 h-5" />
            </button>
          </div>

          <div v-if="eventos_pendientes && eventos_pendientes.length > 0" class="pt-2">
            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">
              Evento de Elección *
            </label>
            <select
              v-model="form.id_evento"
              :disabled="funcionarioValidado"
              class="w-full text-xs font-semibold border-2 border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3 bg-white disabled:bg-slate-100"
            >
              <option :value="null" disabled>-- Seleccione el evento al que desea postularse --</option>
              <option v-for="ev in eventos_pendientes" :key="ev.id" :value="ev.id">
                {{ ev.nombre }} (Estado: Pendiente)
              </option>
            </select>
            <p v-if="eventoSeleccionado" class="text-[11px] text-indigo-700 mt-1 font-medium">
              {{ eventoSeleccionado.descripcion || 'Sin descripción adicional' }}
            </p>
          </div>

          <div v-else class="p-3 bg-amber-50 border border-amber-300 rounded-lg text-xs text-amber-900 flex items-start gap-2">
            <InformationCircleIcon class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
            <div>
              <p class="font-bold">No hay eventos pendientes disponibles</p>
              <p class="text-[11px] text-amber-800 mt-0.5">
                Actualmente no existen convocatorias o eventos de votación en estado "Pendiente" para recepción de candidaturas. Consulte con el área de Talento Humano.
              </p>
            </div>
          </div>
        </div>

        <!-- PASO 2: BÚSQUEDA Y VALIDACIÓN DE FUNCIONARIO -->
        <div class="bg-indigo-50/60 border-2 border-indigo-200 rounded-xl p-5 space-y-4" :class="{ 'opacity-60 pointer-events-none': !form.id_evento }">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-indigo-600 text-white rounded-lg shadow-sm">
                <IdentificationIcon class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs sm:text-sm font-extrabold text-indigo-950 uppercase tracking-wider">
                  Paso 2: Validación de Funcionario Habilitado
                </h3>
                <p class="text-[11px] text-indigo-700">
                  Seleccione su tipo de documento e ingrese el número. El sistema validará su vinculación activa como empleado público y auto-completará sus datos oficiales de dependencia, provisión y planta.
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="leerPaso2"
              class="p-2 text-indigo-700 hover:text-indigo-900 hover:bg-indigo-100 rounded-lg transition shrink-0 print:hidden"
              title="Escuchar instrucciones del Paso 2"
              aria-label="Escuchar instrucciones del Paso 2"
            >
              <SpeakerWaveIcon class="w-5 h-5" />
            </button>
          </div>

          <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
            <div class="w-full sm:w-1/3">
              <select
                v-model="tipoDocConsulta"
                :disabled="funcionarioValidado"
                class="w-full text-xs font-semibold border-2 border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-slate-100 py-2 px-3"
              >
                <option value="CC">Cédula de Ciudadanía (CC)</option>
                <option value="CE">Cédula de Extranjería (CE)</option>
                <option value="TI">Tarjeta de Identidad (TI)</option>
                <option value="PEP">Permiso Especial (PEP)</option>
                <option value="PPT">Protección Temporal (PPT)</option>
              </select>
            </div>

            <div class="relative w-full sm:w-2/3">
              <input
                type="text"
                v-model="identificacionConsulta"
                @keyup.enter="consultarFuncionario"
                :disabled="funcionarioValidado || !form.id_evento"
                placeholder="Ingrese número de identificación..."
                class="w-full text-xs font-mono font-bold border border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 pr-28 disabled:bg-slate-100"
              />

              <button
                v-if="!funcionarioValidado"
                type="button"
                @click="consultarFuncionario"
                :disabled="cargandoConsulta || !identificacionConsulta || !form.id_evento"
                class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white rounded-md text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
              >
                <ArrowPathIcon v-if="cargandoConsulta" class="w-3.5 h-3.5 animate-spin" />
                <MagnifyingGlassIcon v-else class="w-3.5 h-3.5" />
                <span>{{ cargandoConsulta ? 'Validando...' : 'Consultar' }}</span>
              </button>

              <button
                v-else
                type="button"
                @click="cambiarFuncionario"
                class="absolute right-1.5 top-1.5 bottom-1.5 px-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-md text-[11px] font-semibold transition"
              >
                Cambiar
              </button>
            </div>
          </div>

          <!-- Mensaje de Error de Funcionario -->
          <div v-if="errorConsulta" class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800 flex items-start gap-2">
            <ExclamationTriangleIcon class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="font-bold">No habilitado para postulación</p>
              <p class="text-[11px] text-red-700 mt-0.5">{{ errorConsulta }}</p>
            </div>
          </div>

          <!-- Alerta si ya está postulado -->
          <div v-if="avisoYaPostulado" class="p-3 bg-amber-50 border border-amber-300 rounded-lg text-xs text-amber-900 flex items-start gap-2">
            <InformationCircleIcon class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="font-bold">Postulación ya registrada</p>
              <p class="text-[11px] text-amber-800 mt-0.5">{{ avisoYaPostulado }}</p>
            </div>
          </div>

          <!-- Confirmación de Funcionario Validado y Datos Laborales -->
          <div v-if="funcionarioValidado" class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg space-y-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
                <span class="font-bold text-xs text-emerald-900">Funcionario Activo Habilitado</span>
              </div>
              <span class="px-2 py-0.5 bg-emerald-200 text-emerald-900 rounded font-bold text-[10px] uppercase">
                Verificado
              </span>
            </div>

            <!-- Ficha resumida de los datos traídos -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 text-[11px] border-t border-emerald-200/80">
              <div>
                <span class="text-slate-500 font-semibold block">Dependencia:</span>
                <span class="font-bold text-slate-800 uppercase">{{ form.dependencia || 'No registrada' }}</span>
              </div>
              <div>
                <span class="text-slate-500 font-semibold block">Tipo de Provisión:</span>
                <span class="font-bold text-slate-800 uppercase">{{ form.provision || 'No registrada' }}</span>
              </div>
              <div>
                <span class="text-slate-500 font-semibold block">Planta Asignada:</span>
                <span class="font-bold text-indigo-900 uppercase">{{ form.planta || form.tipo_planta }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 3: FORMULARIO OFICIAL (ESTRUCTURA IDÉNTICA A LA IMAGEN INSTITUCIONAL) -->
        <form @submit.prevent="enviarInscripcion" class="space-y-6" :class="{ 'opacity-50 pointer-events-none': !funcionarioValidado }">
          
          <div class="border-2 border-slate-800 rounded-xl p-5 sm:p-6 bg-white space-y-5 shadow-sm">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-5 h-5 text-indigo-700" />
                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                  Paso 3: Verificación de Datos Oficiales y Declaración
                </h3>
              </div>
              <button
                type="button"
                @click="leerPaso3"
                class="p-2 text-indigo-700 hover:text-indigo-900 hover:bg-indigo-100 rounded-lg transition shrink-0 print:hidden"
                title="Escuchar requisitos y declaración jurada"
                aria-label="Escuchar requisitos y declaración jurada"
              >
                <SpeakerWaveIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- FILA 1: NOMBRES Y APELLIDOS + SELECCIÓN PLANTA CENTRAL / SGP -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
              <div class="w-full md:w-3/5 space-y-1">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-900">
                  Nombres y Apellidos Candidato:
                </label>
                <input
                  type="text"
                  v-model="form.nombre"
                  readonly
                  class="w-full text-xs font-bold uppercase bg-slate-50 border-2 border-slate-400 rounded-md px-3 py-2 text-slate-900 focus:outline-none"
                  placeholder="Auto-llenado con el documento verificado"
                />
              </div>

              <!-- CASILLAS PLANTA CENTRAL / SGP (ACCESIBLES Y RESPONSIVAS) -->
              <div class="w-full md:w-auto flex flex-wrap items-center gap-3 pt-1" role="radiogroup" aria-label="Selección de Estamento o Planta">
                <label
                  class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border-2 cursor-pointer select-none transition-all shadow-xs min-h-[44px]"
                  :class="form.tipo_planta === 'PLANTA CENTRAL' ? 'bg-indigo-50 border-indigo-600 text-indigo-950 font-black ring-2 ring-indigo-200' : 'bg-slate-50 border-slate-300 text-slate-700 hover:border-slate-400 font-bold'"
                >
                  <input
                    type="radio"
                    name="tipo_planta"
                    value="PLANTA CENTRAL"
                    v-model="form.tipo_planta"
                    class="w-4 h-4 text-indigo-600 border-2 border-slate-400 focus:ring-indigo-500 cursor-pointer"
                  />
                  <span class="text-xs uppercase tracking-wide">Planta Central</span>
                </label>

                <label
                  class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border-2 cursor-pointer select-none transition-all shadow-xs min-h-[44px]"
                  :class="form.tipo_planta === 'SGP' ? 'bg-indigo-50 border-indigo-600 text-indigo-950 font-black ring-2 ring-indigo-200' : 'bg-slate-50 border-slate-300 text-slate-700 hover:border-slate-400 font-bold'"
                >
                  <input
                    type="radio"
                    name="tipo_planta"
                    value="SGP"
                    v-model="form.tipo_planta"
                    class="w-4 h-4 text-indigo-600 border-2 border-slate-400 focus:ring-indigo-500 cursor-pointer"
                  />
                  <span class="text-xs uppercase tracking-wide">SGP</span>
                </label>
              </div>
            </div>

            <!-- FILA 2: TIPO Y NÚMERO DE IDENTIFICACIÓN -->
            <div class="space-y-1">
              <label class="block text-xs font-black uppercase tracking-wider text-slate-900">
                Tipo y Número de Identificación:
              </label>
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3.5 py-2 rounded-md font-black text-xs bg-indigo-100 text-indigo-900 border-2 border-indigo-300 uppercase shrink-0">
                  {{ form.tipo_documento || tipoDocConsulta }}
                </span>
                <input
                  type="text"
                  :value="form.identificacion"
                  readonly
                  class="w-full text-xs font-mono font-bold bg-slate-50 border-2 border-slate-400 rounded-md px-3 py-2 text-slate-900 focus:outline-none"
                  placeholder="Número de documento de identidad"
                />
              </div>
            </div>

            <!-- FILA 3: DATOS LABORALES AUTOCOMPLETADOS (DEPENDENCIA, PROVISIÓN, PLANTA) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3 bg-slate-50 border border-slate-300 rounded-lg">
              <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">
                  Dependencia:
                </label>
                <input
                  type="text"
                  v-model="form.dependencia"
                  readonly
                  class="w-full text-xs font-bold uppercase bg-white border border-slate-300 rounded px-2.5 py-1.5 text-slate-800"
                />
              </div>

              <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">
                  Provisión:
                </label>
                <input
                  type="text"
                  v-model="form.provision"
                  readonly
                  class="w-full text-xs font-bold uppercase bg-white border border-slate-300 rounded px-2.5 py-1.5 text-slate-800"
                />
              </div>

              <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">
                  Planta:
                </label>
                <input
                  type="text"
                  v-model="form.planta"
                  readonly
                  class="w-full text-xs font-bold uppercase bg-white border border-slate-300 rounded px-2.5 py-1.5 text-indigo-900"
                />
              </div>
            </div>

            <!-- FILA 4: DATOS DE CONTACTO (EMAIL Y CELULAR) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                  Correo Electrónico de Notificación:
                </label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="ejemplo@alcaldia.gov.co"
                  class="w-full text-xs border border-slate-300 rounded-md px-3 py-2 shadow-2xs focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                  Teléfono Móvil / Celular:
                </label>
                <input
                  type="text"
                  v-model="form.celular"
                  required
                  placeholder="3001234567"
                  class="w-full text-xs border border-slate-300 rounded-md px-3 py-2 shadow-2xs focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
            </div>

            <!-- DECLARACIÓN JURADA (TEXTO EXACTO DE LA IMAGEN) -->
            <div class="p-4 bg-slate-50 border border-slate-300 rounded-lg space-y-3">
              <p class="text-xs font-semibold text-slate-800 leading-relaxed">
                Declaro que cumplo con las calidades determinadas en la normatividad vigente, siendo estas:
              </p>
              <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                <li>No haber sido sancionados disciplinariamente durante el año anterior a la fecha de la inscripción de la candidatura; y</li>
                <li>Ser empleados de carrera administrativa.</li>
              </ul>

              <div class="pt-2 border-t border-slate-200">
                <label class="flex items-start gap-2.5 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    v-model="form.declaracion"
                    required
                    class="mt-0.5 rounded border-2 border-slate-600 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                  />
                  <span class="text-xs font-bold text-slate-900 leading-snug">
                    Acepto y declaro bajo gravedad de juramento el cumplimiento cabal de los requisitos anteriores.
                  </span>
                </label>
              </div>
            </div>

            <!-- FILA 5: FECHA POSTULACIÓN (SEGÚN LA IMAGEN EN TRES CASILLAS) -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
              <label class="text-xs font-black uppercase tracking-wider text-slate-900">
                Fecha Postulación:
              </label>

              <div class="flex items-center gap-1.5 font-mono">
                <!-- Casilla Día -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.dia"
                    readonly
                    class="w-12 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Día</span>
                </div>

                <span class="font-bold text-slate-400">/</span>

                <!-- Casilla Mes -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.mes"
                    readonly
                    class="w-12 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Mes</span>
                </div>

                <span class="font-bold text-slate-400">/</span>

                <!-- Casilla Año -->
                <div class="flex flex-col items-center">
                  <input
                    type="text"
                    :value="fecha_actual.anio"
                    readonly
                    class="w-16 text-center text-xs font-black bg-slate-50 border-2 border-slate-600 rounded px-1 py-1.5 text-slate-900"
                  />
                  <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">Año</span>
                </div>
              </div>
            </div>
          </div>

          <!-- PASO 4: CARGA OBLIGATORIA DE DOCUMENTOS -->
          <div class="border-2 border-indigo-200 rounded-xl p-5 sm:p-6 bg-slate-50/70 space-y-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <DocumentArrowUpIcon class="w-5 h-5 text-indigo-700" />
                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                  Paso 4: Carga de Documentación Obligatoria
                </h3>
              </div>
              <button
                type="button"
                @click="leerPaso4"
                class="p-2 text-indigo-700 hover:text-indigo-900 hover:bg-indigo-100 rounded-lg transition shrink-0 print:hidden"
                title="Escuchar requisitos de documentos"
                aria-label="Escuchar requisitos de documentos"
              >
                <SpeakerWaveIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              
              <!-- 1. CERTIFICADO DE PROCURADURÍA -->
              <div class="p-4 bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl space-y-3 transition">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-black text-slate-900 uppercase">
                    1. Certificado de Procuraduría *
                  </label>
                  <div class="flex items-center gap-1.5">
                    <button
                      type="button"
                      @click="leerProcuraduria"
                      class="text-indigo-600 hover:text-indigo-800 p-1 rounded print:hidden"
                      title="Escuchar requisito de Procuraduría"
                      aria-label="Escuchar requisito de Procuraduría"
                    >
                      <SpeakerWaveIcon class="w-4 h-4" />
                    </button>
                    <span class="text-[10px] text-indigo-700 font-black bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 uppercase">
                      Máx 3 meses
                    </span>
                  </div>
                </div>

                <p class="text-[11px] text-slate-500">
                  Certificado vigente no mayor a tres meses expedido por la Procuraduría General de la Nación. Formato PDF o imagen (PNG/JPG).
                </p>

                <div class="space-y-2">
                  <input
                    type="file"
                    ref="inputProcuraduria"
                    @change="handleFileProcuraduria"
                    accept=".pdf,.png,.jpg,.jpeg"
                    required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-100 file:text-indigo-800 hover:file:bg-indigo-200 cursor-pointer min-h-[44px]"
                  />
                  <div v-if="archivoProcNombre" class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1.5 p-2 bg-emerald-50 rounded-lg border border-emerald-200">
                    <CheckCircleIcon class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span class="truncate">{{ archivoProcNombre }}</span>
                  </div>
                </div>
              </div>

              <!-- 2. FOTOCOPIA DE CÉDULA LEGIBLE -->
              <div class="p-4 bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl space-y-3 transition">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-black text-slate-900 uppercase">
                    2. Fotocopia de Documento de Identidad *
                  </label>
                  <div class="flex items-center gap-1.5">
                    <button
                      type="button"
                      @click="leerCedula"
                      class="text-indigo-600 hover:text-indigo-800 p-1 rounded print:hidden"
                      title="Escuchar requisito de documento"
                      aria-label="Escuchar requisito de documento"
                    >
                      <SpeakerWaveIcon class="w-4 h-4" />
                    </button>
                    <span class="text-[10px] text-indigo-700 font-black bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 uppercase">
                      Legible
                    </span>
                  </div>
                </div>

                <p class="text-[11px] text-slate-500">
                  Documento de identidad legible por ambas caras o al 150%. Formato PDF o imagen (PNG/JPG).
                </p>

                <div class="space-y-2">
                  <input
                    type="file"
                    ref="inputCedula"
                    @change="handleFileCedula"
                    accept=".pdf,.png,.jpg,.jpeg"
                    required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-100 file:text-indigo-800 hover:file:bg-indigo-200 cursor-pointer min-h-[44px]"
                  />
                  <div v-if="archivoCedulaNombre" class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1.5 p-2 bg-emerald-50 rounded-lg border border-emerald-200">
                    <CheckCircleIcon class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span class="truncate">{{ archivoCedulaNombre }}</span>
                  </div>
                </div>
              </div>

              <!-- 3. FOTOGRAFÍA OPCIONAL PARA EL TARJETÓN -->
              <div class="p-4 bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl space-y-3 md:col-span-2 transition">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-bold text-slate-800 uppercase">
                    3. Fotografía Reciente del Candidato (Para el Tarjetón Electoral)
                  </label>
                  <div class="flex items-center gap-1.5">
                    <button
                      type="button"
                      @click="leerFoto"
                      class="text-indigo-600 hover:text-indigo-800 p-1 rounded print:hidden"
                      title="Escuchar recomendación de fotografía"
                      aria-label="Escuchar recomendación de fotografía"
                    >
                      <SpeakerWaveIcon class="w-4 h-4" />
                    </button>
                    <span class="text-[10px] text-slate-600 font-bold bg-slate-100 px-2 py-0.5 rounded border border-slate-300 uppercase">
                      Recomendado
                    </span>
                  </div>
                </div>

                <p class="text-[11px] text-slate-500">
                  Fotografía reciente tipo documento (fondo blanco) que será visible para los electores en la tarjeta electoral. Formato PNG o JPG.
                </p>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                  <input
                    type="file"
                    ref="inputFoto"
                    @change="handleFileFoto"
                    accept=".png,.jpg,.jpeg"
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer min-h-[44px]"
                  />
                  <div v-if="archivoFotoNombre" class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1.5 p-2 bg-emerald-50 rounded-lg border border-emerald-200 whitespace-nowrap">
                    <CheckCircleIcon class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span class="truncate max-w-xs">{{ archivoFotoNombre }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- BOTÓN DE ENVÍO Y RADICACIÓN -->
          <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500">
              * Todos los campos y documentos marcados son estrictamente obligatorios según normatividad.
            </span>

            <button
              type="submit"
              :disabled="enviando || !funcionarioValidado || !form.id_evento"
              class="w-full sm:w-auto px-8 py-3.5 bg-indigo-700 hover:bg-indigo-800 disabled:opacity-50 text-white font-extrabold rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2"
            >
              <ArrowPathIcon v-if="enviando" class="w-4 h-4 animate-spin" />
              <PaperAirplaneIcon v-else class="w-4 h-4" />
              <span>{{ enviando ? 'Procesando Radicación...' : 'Radicar Formulario de Inscripción' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- PIE DE PÁGINA INSTITUCIONAL -->
      <div class="p-4 bg-slate-50 border-t border-slate-200 text-center text-[11px] text-slate-500">
        Plataforma Electoral Institucional • Alcaldía de Pereira • Comisión de Personal
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import logoAlcaldia from '/public/assets/img/logo.png';
import { useAudioHelp } from '@/composables/useAudioHelp';
import {
  ArrowLeftIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ArrowPathIcon,
  DocumentArrowUpIcon,
  PaperAirplaneIcon,
  IdentificationIcon,
  CalendarDaysIcon,
  PrinterIcon,
  SpeakerWaveIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  eventos_pendientes: {
    type: Array,
    default: () => [],
  },
  fecha_actual: {
    type: Object,
    required: true,
  },
  titulo_encabezado: {
    type: String,
    default: 'ALCALDÍA DE PEREIRA',
  },
  subtitulo_encabezado: {
    type: String,
    default: '',
  },
  nombre_formulario: {
    type: String,
    default: 'FORMULARIO DE INSCRIPCIÓN CANDIDATOS – COMISIÓN DE PERSONAL',
  },
});

const page = usePage();

// Estado de la consulta inicial
const tipoDocConsulta = ref('CC');
const identificacionConsulta = ref('');
const cargandoConsulta = ref(false);
const errorConsulta = ref('');
const avisoYaPostulado = ref('');
const funcionarioValidado = ref(false);

// Archivos seleccionados (nombres para feedback)
const archivoProcNombre = ref('');
const archivoCedulaNombre = ref('');
const archivoFotoNombre = ref('');

// Formulario de Inertia
const form = useForm({
  identificacion: '',
  tipo_documento: 'CC',
  nombre: '',
  tipo_planta: 'PLANTA CENTRAL',
  dependencia: '',
  provision: '',
  planta: '',
  email: '',
  celular: '',
  fecha_postulacion: props.fecha_actual.completa,
  declaracion: false,
  archivo_procuraduria: null,
  archivo_cedula: null,
  foto: null,
  id_evento: props.eventos_pendientes && props.eventos_pendientes.length === 1 ? props.eventos_pendientes[0].id : null,
});

const eventoSeleccionado = computed(() => {
  if (!form.id_evento || !props.eventos_pendientes) return null;
  return props.eventos_pendientes.find(ev => ev.id === form.id_evento);
});

const enviando = ref(false);
const postulacionExitosa = computed(() => page.props.flash?.success_postulacion || null);

// Composable de Asistencia por Voz para Personas con Discapacidad Visual
const {
  speaking,
  paused,
  leerTexto,
  toggleReproducir,
  pausarLectura,
  detenerLectura,
} = useAudioHelp();

const leerInstruccionesGenerales = () => {
  const texto = `Formulario Oficial de Inscripción de Candidatos de la Alcaldía de Pereira para la Comisión de Personal.
Para postularse, siga los cuatro pasos:
Paso uno: Seleccione el evento electoral en estado pendiente.
Paso dos: Seleccione su tipo de documento e ingrese su número de identificación para validar su condición de empleado público activo. El sistema auto-completará sus datos oficiales.
Paso tres: Verifique sus nombres, seleccione su planta, ya sea Planta Central o SGP, y marque la aceptación obligatoria de la declaración juramentada de cumplimiento de requisitos normativos.
Paso cuatro: Cargue en formato PDF o imagen el certificado vigente de la Procuraduría General de la Nación no mayor a tres meses y la fotocopia legible de su documento de identidad. Puede subir opcionalmente una fotografía tipo documento con fondo blanco para la tarjeta electoral.
Al finalizar, presione el botón Radicar Formulario de Inscripción.`;
  leerTexto(texto);
};

const leerPaso1 = () => {
  leerTexto("Paso 1: Seleccione el Evento de Votación. Elija la convocatoria en estado pendiente a la que desea postularse.");
};

const leerPaso2 = () => {
  leerTexto("Paso 2: Validación de Funcionario Habilitado. Seleccione su tipo de documento e ingrese el número. Al presionar Consultar, el sistema validará su vinculación activa y autocompletará sus datos de dependencia, provisión y planta.");
};

const leerPaso3 = () => {
  leerTexto("Paso 3: Verificación de datos y declaración jurada. Confirme sus nombres, elija si pertenece a Planta Central o SGP, e ingrese su correo y celular. Marque la casilla obligatoria que certifica bajo juramento no tener sanciones disciplinarias vigentes en el último año y ser empleado de carrera administrativa.");
};

const leerPaso4 = () => {
  leerTexto("Paso 4: Carga de Documentación Obligatoria. Se requiere adjuntar el certificado de la Procuraduría vigente no mayor a tres meses y la fotocopia legible de su cédula. De forma opcional, puede adjuntar una foto reciente para el tarjetón electoral.");
};

const leerProcuraduria = () => {
  leerTexto("Requisito 1: Certificado vigente no mayor a tres meses expedido por la Procuraduría General de la Nación, en formato PDF o imagen.");
};

const leerCedula = () => {
  leerTexto("Requisito 2: Fotocopia legible de la cédula de ciudadanía o documento de identidad por ambas caras o al 150 por ciento, en formato PDF o imagen.");
};

const leerFoto = () => {
  leerTexto("Requisito 3 opcional: Fotografía reciente tipo documento con fondo blanco que se mostrará en la tarjeta electoral.");
};

const leerExito = () => {
  if (!postulacionExitosa.value) return;
  leerTexto(`¡Inscripción radicada exitosamente! Candidato: ${postulacionExitosa.value.nombre}. Número de radicado: ${postulacionExitosa.value.radicado}. Evento: ${postulacionExitosa.value.evento}. Estamento: ${postulacionExitosa.value.tipo_planta}. Su postulación se encuentra en estado pendiente de verificación por la Dirección de Talento Humano.`);
};

// Consultar si es funcionario y traer datos automáticamente
const consultarFuncionario = async () => {
  if (!identificacionConsulta.value) return;

  if (!form.id_evento) {
    Swal.fire({
      icon: 'warning',
      title: 'Seleccione un evento',
      text: 'Por favor seleccione primero el evento de votación en estado Pendiente al que desea postularse.',
    });
    return;
  }

  cargandoConsulta.value = true;
  errorConsulta.value = '';
  avisoYaPostulado.value = '';
  funcionarioValidado.value = false;

  try {
    const response = await axios.post(route('candidatos.inscripcion.consultar'), {
      identificacion: identificacionConsulta.value,
      tipo_documento: tipoDocConsulta.value,
      id_evento: form.id_evento,
    });

    if (response.data.success) {
      if (response.data.ya_postulado) {
        avisoYaPostulado.value = response.data.mensaje;
        return;
      }

      // Auto-llenar campos del formulario
      form.identificacion = response.data.identificacion;
      form.tipo_documento = response.data.tipo_documento || tipoDocConsulta.value;
      form.nombre = response.data.nombre;
      form.email = response.data.email || '';
      form.celular = response.data.celular || '';
      form.dependencia = response.data.dependencia || '';
      form.provision = response.data.provision || '';
      form.planta = response.data.planta || '';
      form.tipo_planta = response.data.tipo_planta || 'PLANTA CENTRAL';
      form.fecha_postulacion = props.fecha_actual.completa;

      funcionarioValidado.value = true;
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.mensaje) {
      errorConsulta.value = error.response.data.mensaje;
    } else {
      errorConsulta.value = 'Ocurrió un error al verificar la identificación. Por favor intente nuevamente.';
    }
  } finally {
    cargandoConsulta.value = false;
  }
};

const cambiarFuncionario = () => {
  funcionarioValidado.value = false;
  identificacionConsulta.value = '';
  errorConsulta.value = '';
  avisoYaPostulado.value = '';
  const currentEvento = form.id_evento;
  form.reset();
  form.id_evento = currentEvento;
  archivoProcNombre.value = '';
  archivoCedulaNombre.value = '';
  archivoFotoNombre.value = '';
};

// Manejo de archivos
const handleFileProcuraduria = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.archivo_procuraduria = file;
    archivoProcNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

const handleFileCedula = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.archivo_cedula = file;
    archivoCedulaNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

const handleFileFoto = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.foto = file;
    archivoFotoNombre.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
  }
};

// Envío del formulario
const enviarInscripcion = () => {
  if (!form.id_evento) {
    Swal.fire({
      icon: 'warning',
      title: 'Evento requerido',
      text: 'Debe seleccionar el evento de votación en estado Pendiente para la postulación.',
    });
    return;
  }

  if (!form.declaracion) {
    Swal.fire({
      icon: 'warning',
      title: 'Declaración obligatoria',
      text: 'Debe marcar la casilla de aceptación y declaración juramentada de cumplimiento normativo.',
    });
    return;
  }

  if (!form.archivo_procuraduria) {
    Swal.fire({
      icon: 'warning',
      title: 'Certificado de Procuraduría requerido',
      text: 'Debe adjuntar el certificado vigente no mayor a 3 meses de la Procuraduría.',
    });
    return;
  }

  if (!form.archivo_cedula) {
    Swal.fire({
      icon: 'warning',
      title: 'Fotocopia de Cédula requerida',
      text: 'Debe adjuntar la fotocopia legible de la cédula de ciudadanía.',
    });
    return;
  }

  Swal.fire({
    title: '¿Confirmar radicación de candidatura?',
    text: `Se registrará la postulación de ${form.nombre} (${form.identificacion}) para ${form.tipo_planta} en ${eventoSeleccionado.value ? eventoSeleccionado.value.nombre : 'el evento seleccionado'}.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, radicar inscripción',
    cancelButtonText: 'Revisar datos',
    confirmButtonColor: '#4338ca',
  }).then((result) => {
    if (result.isConfirmed) {
      enviando.value = true;
      form.post(route('candidatos.inscripcion.store'), {
        forceFormData: true,
        onFinish: () => {
          enviando.value = false;
        },
        onError: (errores) => {
          const mensaje = Object.values(errores).join('<br>• ');
          Swal.fire({
            icon: 'error',
            title: 'Inconsistencias en el formulario',
            html: `• ${mensaje}`,
          });
        },
      });
    }
  });
};

const imprimirConstancia = () => {
  window.print();
};

const reiniciarFormulario = () => {
  window.location.href = route('candidatos.inscripcion.publica');
};
</script>
