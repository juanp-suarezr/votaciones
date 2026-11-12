<template>
  <Head :title="titulo_seccion || 'Actas Oficiales'" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <DocumentTextIcon class="h-7 w-7 text-sky-700" />
            {{ titulo_seccion || 'Actas Oficiales de Votación' }}
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            {{ modo === 'comision'
                ? 'Actas oficiales de inscripción de candidatos y votaciones para la Comisión de Personal.'
                : 'Generación y certificación oficial de actas de votación para elecciones institucionales de la Alcaldía de Pereira.' }}
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
          <!-- SELECTOR DE EVENTO INTERACTIVO -->
          <div v-if="eventos_disponibles.length > 1 || modo === 'general'" class="flex items-center gap-2 bg-white border border-gray-300 rounded-xl px-3 py-1.5 shadow-sm text-xs font-semibold">
            <span class="text-gray-500 font-bold whitespace-nowrap">Evento:</span>
            <select
              v-model="selectedEventoId"
              @change="cambiarEvento"
              class="border-none bg-transparent font-bold text-slate-800 focus:ring-0 text-xs py-1 pr-8 cursor-pointer max-w-[260px] truncate"
            >
              <option v-for="ev in eventos_disponibles" :key="ev.id" :value="ev.id">
                [ID {{ ev.id }}] {{ ev.nombre }}
              </option>
            </select>
          </div>

          <!-- Pestañas Actas vs Firmantes -->
          <div class="flex gap-1.5 bg-gray-100 p-1 rounded-xl">
            <button
              @click="activeTab = 'actas'"
              :class="[
                'px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer',
                activeTab === 'actas'
                  ? 'bg-[#C20E1A] text-white shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <DocumentDuplicateIcon class="h-4 w-4" />
              Actas ({{ actas.length }})
            </button>
            <button
              @click="activeTab = 'firmantes'"
              :class="[
                'px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer',
                activeTab === 'firmantes'
                  ? 'bg-[#C20E1A] text-white shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <PencilSquareIcon class="h-4 w-4" />
              Firmantes ({{ firmantes.length }})
            </button>
          </div>
        </div>
      </div>
    </template>

    <div class="py-6 max-w-7xl mx-auto space-y-6">

      <!-- BANNER INSTITUCIONAL PEREIRA / VOTA YA -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#221718] rounded-2xl shadow-xl text-white relative overflow-hidden border border-slate-700/60">
        <!-- Franja Bicolor Superior Oficial Pereira -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#C20E1A] from-50% to-[#FFB800] to-50%"></div>

        <div class="p-6 sm:p-7 relative">
          <!-- Marca de agua Escudo Pereira -->
          <div class="absolute -right-6 -bottom-8 opacity-10 pointer-events-none">
            <img src="/assets/img/escudo.webp" onerror="this.onerror=null; this.src='/assets/img/logo.png';" alt="Escudo de Pereira" class="w-64 h-64 object-contain" />
          </div>

          <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
              <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 border border-white/15 backdrop-blur-sm rounded-full text-xs font-bold text-[#FFB800] mb-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>ALCALDÍA DE PEREIRA</span>
                <span class="text-white/40">|</span>
                <span class="text-slate-200">Dependencia: {{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Administración Central' }}</span>
              </div>
              <h2 class="text-xl md:text-2xl font-black tracking-tight text-white">
                [ID {{ evento?.id }}] {{ evento?.nombre }}
              </h2>
              <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                {{ evento?.descripcion || 'Evento institucional habilitado para votación virtual en VOTA YA.' }}
              </p>
              <div class="flex flex-wrap items-center gap-2.5 mt-3 text-xs">
                <span class="bg-black/30 border border-white/10 px-2.5 py-1 rounded-lg text-slate-300">
                  <strong class="text-[#FFB800]">Inicio Votación:</strong> {{ fechas.votacion_inicio_texto || fechas.votacion_inicio }}
                </span>
                <span class="bg-black/30 border border-white/10 px-2.5 py-1 rounded-lg text-slate-300">
                  <strong class="text-[#FFB800]">Cierre Votación:</strong> {{ fechas.votacion_fin_texto || fechas.votacion_fin }}
                </span>
                <span v-if="es_comision" class="bg-[#C20E1A]/30 text-rose-200 border border-[#C20E1A]/40 px-2.5 py-1 rounded-lg font-bold">
                  Comisión de Personal
                </span>
              </div>
            </div>

            <!-- Métricas Rápidas -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 w-full md:w-auto">
              <div v-if="es_comision" class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
                <div class="text-2xl font-black text-white">{{ estadisticas.total_postulaciones }}</div>
                <div class="text-[11px] text-slate-300 uppercase font-bold tracking-wider">Postulados</div>
              </div>
              <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
                <div class="text-2xl font-black text-[#FFB800]">{{ estadisticas.total_censo }}</div>
                <div class="text-[11px] text-slate-300 uppercase font-bold tracking-wider">Censo</div>
              </div>
              <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 text-center border border-white/15">
                <div class="text-2xl font-black text-emerald-400">{{ estadisticas.total_votos }}</div>
                <div class="text-[11px] text-slate-300 uppercase font-bold tracking-wider">Votos</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 1: LISTADO DE ACTAS                              -->
      <!-- ======================================================== -->
      <div v-if="activeTab === 'actas'" class="space-y-6">

        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <FolderOpenIcon class="h-5 w-5 text-sky-600" />
            Actas Reglamentarias Disponibles
          </h2>
          <span class="text-xs text-gray-500 font-medium">
            {{ actas.length }} actas generadas para este evento
          </span>
        </div>

        <!-- Tarjetas de Actas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="acta in actas"
            :key="acta.id"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group"
          >
            <!-- Cabecera de la tarjeta -->
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-start justify-between gap-3 mb-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800">
                  {{ acta.codigo }}
                </span>
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold',
                    acta.estado === 'Disponible' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' :
                    acta.estado === 'En Curso' ? 'bg-amber-100 text-amber-800 border border-amber-200' :
                    'bg-gray-100 text-gray-700 border border-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'w-2 h-2 rounded-full',
                      acta.estado === 'Disponible' ? 'bg-emerald-500' :
                      acta.estado === 'En Curso' ? 'bg-amber-500 animate-pulse' : 'bg-gray-400'
                    ]"
                  ></span>
                  {{ acta.estado }}
                </span>
              </div>

              <div class="text-xs font-semibold uppercase tracking-wider text-sky-600 mb-1">
                {{ acta.categoria }}
              </div>
              <h3 class="text-lg font-bold text-gray-900 group-hover:text-sky-700 transition-colors">
                {{ acta.nombre }}
              </h3>
              <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                {{ acta.descripcion }}
              </p>
            </div>

            <!-- Cuerpo con Metadatos automáticos -->
            <div class="px-6 py-4 bg-gray-50/70 border-b border-gray-100 text-xs space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Fecha oficial detectada:</span>
                <span class="font-bold text-gray-900">{{ acta.fecha_texto }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Fuente del cronograma:</span>
                <span class="font-medium text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200">
                  {{ acta.origen_fecha }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Firmas requeridas:</span>
                <span class="font-semibold text-gray-700">
                  Secretario de las TIC + {{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Dependencia Evento' }}
                </span>
              </div>
            </div>

            <!-- Acciones -->
            <div class="p-4 bg-white flex items-center justify-between gap-3">
              <a
                :href="route('actas-comision.show', { tipo: acta.id, id_evento: evento.id })"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-700 hover:bg-sky-800 text-white font-semibold text-sm rounded-xl shadow-sm transition-colors duration-150"
              >
                <EyeIcon class="h-4 w-4" />
                Ver Acta Oficial
              </a>

              <a
                :href="route('actas-comision.pdf', { tipo: acta.id, id_evento: evento.id })"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-xl shadow-sm transition-colors duration-150"
                v-tooltip.top="'Descargar PDF oficial membretado'"
              >
                <ArrowDownTrayIcon class="h-4 w-4" />
                PDF
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 2: GESTIÓN DE FIRMANTES                           -->
      <!-- ======================================================== -->
      <div v-else-if="activeTab === 'firmantes'" class="space-y-6">

        <!-- Banner Explicativo de la Política de Firmas -->
        <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="space-y-1">
            <div class="flex items-center gap-2 text-sky-900 font-bold text-sm">
              <ShieldCheckIcon class="h-5 w-5 text-sky-600" />
              Política Institucional de Firmas en Actas
            </div>
            <p class="text-xs text-sky-800 max-w-3xl">
              1. En <strong>TODAS</strong> las actas oficiales se estampa obligatoriamente la firma digital del <strong>Secretario de las TIC</strong>.<br>
              2. Como segundo firmante, si el evento tiene un secretario registrado en este panel se estampará su firma; de lo contrario, se genera automáticamente el espacio oficial con el nombre de la dependencia del evento: <strong>{{ evento?.dependencias && evento?.dependencias !== 'NA' ? evento?.dependencias : 'Dependencia Responsable' }}</strong>.
            </p>
          </div>

          <button
            @click="abrirModalNuevoFirmante"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap flex items-center gap-1.5"
          >
            <UserPlusIcon class="h-4 w-4" />
            + Asignar Secretario a este Evento
          </button>
        </div>

        <!-- Tabla de Firmantes del Evento -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">
              Firmantes que aparecerán en las actas de: [ID {{ evento?.id }}] {{ evento?.nombre }}
            </h3>
            <span class="text-xs text-gray-500 font-medium">
              {{ firmantes.length }} firmante(s) configurado(s)
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                  <th class="py-3.5 px-3 text-center w-24">Orden</th>
                  <th class="py-3.5 px-4">Firmante / Funcionario</th>
                  <th class="py-3.5 px-4">Identificación</th>
                  <th class="py-3.5 px-4">Cargo Institucional</th>
                  <th class="py-3.5 px-4">Firma Digital</th>
                  <th class="py-3.5 px-4 text-center">Tipo de Firmante</th>
                  <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <tr v-for="(f, idx) in listaFirmantes" :key="f.id || idx" class="hover:bg-sky-50/40 transition-colors">
                  <!-- Columna Reordenar Posición -->
                  <td class="py-3.5 px-3 text-center">
                    <div class="inline-flex items-center gap-1">
                      <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 text-xs font-black flex items-center justify-center border border-slate-200">
                        {{ idx + 1 }}
                      </span>
                      <div v-if="f.id > 0 && !f.is_dependencia" class="flex flex-col gap-0.5 ml-1">
                        <button
                          type="button"
                          @click="subirFirmante(idx)"
                          :disabled="idx === 0"
                          class="p-0.5 rounded text-slate-500 hover:text-sky-700 hover:bg-sky-100 disabled:opacity-20 disabled:hover:bg-transparent"
                          title="Subir posición"
                        >
                          <ChevronUpIcon class="h-3.5 w-3.5" />
                        </button>
                        <button
                          type="button"
                          @click="bajarFirmante(idx)"
                          :disabled="idx === listaFirmantes.length - 1"
                          class="p-0.5 rounded text-slate-500 hover:text-sky-700 hover:bg-sky-100 disabled:opacity-20 disabled:hover:bg-transparent"
                          title="Bajar posición"
                        >
                          <ChevronDownIcon class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </div>
                  </td>

                  <td class="py-3.5 px-4 font-semibold text-gray-900">
                    <div class="flex items-center gap-2.5">
                      <div
                        :class="[
                          'w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs',
                          f.is_tic ? 'bg-indigo-100 text-indigo-700' :
                          f.is_dependencia ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700'
                        ]"
                      >
                        {{ f.nombre ? f.nombre.substring(0, 2).toUpperCase() : 'SE' }}
                      </div>
                      <div>
                        <div>{{ f.nombre }}</div>
                        <span class="text-xs text-gray-500">
                          {{ f.is_tic ? 'Firma Obligatoria Institucional' : f.is_dependencia ? 'Espacio por Dependencia del Evento' : 'Secretario Delegado' }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-gray-600 font-mono text-xs">
                    {{ f.identificacion }}
                  </td>
                  <td class="py-3.5 px-4 text-gray-700 font-medium text-xs">
                    {{ f.cargo }}
                  </td>
                  <td class="py-3.5 px-4">
                    <div
                      v-if="f.firma"
                      class="border border-gray-200 rounded-lg p-1 bg-white inline-block shadow-sm cursor-pointer hover:border-sky-500"
                      @click="verFirmaGrande(getFirmaUrl(f.firma))"
                    >
                      <img
                        :src="getFirmaUrl(f.firma)"
                        alt="Firma"
                        class="h-12 w-28 object-contain"
                      />
                    </div>
                    <span v-else-if="f.is_dependencia" class="text-xs text-amber-700 font-medium bg-amber-50 px-2 py-1 rounded border border-amber-200">
                      Espacio Oficial de Dependencia
                    </span>
                    <span v-else class="text-xs text-gray-400 italic">
                      Sin firma cargada
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      v-if="f.is_tic"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200"
                    >
                      Secretario TIC
                    </span>
                    <span
                      v-else-if="f.is_dependencia"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"
                    >
                      Dependencia Evento
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                    >
                      Secretario Asignado
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div v-if="!f.is_dependencia && f.id > 0" class="inline-flex items-center gap-1.5">
                      <button
                        @click="editarFirmante(f)"
                        class="p-1.5 text-sky-700 hover:bg-sky-100 rounded-lg transition-colors"
                        v-tooltip.top="'Editar firmante'"
                      >
                        <PencilIcon class="h-4 w-4" />
                      </button>
                      <button
                        @click="eliminarFirmante(f.id)"
                        class="p-1.5 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors"
                        v-tooltip.top="'Eliminar firmante'"
                      >
                        <TrashIcon class="h-4 w-4" />
                      </button>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">
                      Automático
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL: CREAR / EDITAR SECRETARIO FIRMANTE                 -->
    <!-- ======================================================== -->
    <div
      v-if="modalFirmanteVisible"
      class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-gray-100 overflow-hidden animate-fadeIn">
        <!-- Header Modal con Identidad Institucional -->
        <div class="bg-slate-900 border-b-2 border-[#C20E1A] px-6 py-4 text-white flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 rounded-lg bg-[#C20E1A]/20 text-[#FFB800] border border-[#C20E1A]/40">
              <PencilSquareIcon class="h-5 w-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white">
                {{ modoEdicion ? 'Editar Secretario Firmante' : 'Nuevo Secretario Firmante' }}
              </h3>
              <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">Alcaldía de Pereira · VOTA YA</span>
            </div>
          </div>
          <button
            @click="cerrarModalFirmante"
            class="text-slate-400 hover:text-white text-2xl font-bold leading-none cursor-pointer"
          >
            &times;
          </button>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="guardarFirmanteSubmit" class="p-6 space-y-4">
          <!-- Búsqueda por Identificación (C.C.) -->
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold text-slate-800 uppercase">
                Identificación (C.C.) (*)
              </label>
              <span v-if="buscandoCedula" class="text-[11px] text-sky-700 font-semibold flex items-center gap-1">
                <span class="animate-spin inline-block w-3 h-3 border-2 border-sky-600 border-t-transparent rounded-full"></span>
                Buscando registros previos...
              </span>
            </div>
            <div class="flex gap-2">
              <input
                type="text"
                v-model="formFirmante.identificacion"
                @keydown.enter.prevent="buscarPorCedula"
                required
                placeholder="Ej: 1088234567"
                class="flex-1 text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm bg-white"
              />
              <button
                type="button"
                @click="buscarPorCedula"
                :disabled="buscandoCedula || !formFirmante.identificacion"
                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition-all disabled:opacity-50 flex items-center gap-1.5"
                title="Buscar si el funcionario ya existe en otro evento"
              >
                <MagnifyingGlassIcon class="h-4 w-4" />
                <span>Buscar C.C.</span>
              </button>
            </div>

            <!-- Alerta: Secretario Encontrado en Otro Evento -->
            <div
              v-if="secretarioEncontradoInfo"
              class="mt-2.5 p-3 bg-emerald-50 border border-emerald-300 rounded-xl flex items-start gap-2.5 text-xs text-emerald-900"
            >
              <CheckCircleIcon class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
              <div class="space-y-1">
                <div class="font-bold text-emerald-800">
                  ¡Funcionario encontrado en el sistema!
                </div>
                <p class="leading-relaxed">
                  Se autocompletaron sus datos y firma registrada previamente
                  <span v-if="secretarioEncontradoInfo.evento_origen"> (en <em>"{{ secretarioEncontradoInfo.evento_origen }}"</em>)</span>.
                  Al guardar, se le asignará a este nuevo evento <strong>sin desasignarlo ni afectar sus eventos anteriores</strong>.
                </p>
              </div>
            </div>

            <!-- Alerta: Cédula no registrada previamente -->
            <div
              v-else-if="cedulaBuscadaSinResultado"
              class="mt-2 p-2.5 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-800 flex items-center gap-2"
            >
              <InformationCircleIcon class="h-4 w-4 text-sky-600 shrink-0" />
              <span>No se encontró firma previa con esta cédula. Complete los datos y firme a continuación.</span>
            </div>
          </div>

          <!-- Nombre Completo y Cargo -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre Completo (*)</label>
              <input
                type="text"
                v-model="formFirmante.nombre"
                required
                placeholder="Ej: Dr. Carlos Alberto Gómez"
                class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo Institucional (*)</label>
              <input
                type="text"
                v-model="formFirmante.cargo"
                required
                placeholder="Ej: Secretario de Gestión Administrativa"
                class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
              />
            </div>
          </div>

          <!-- Evento Asignado -->
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Evento al que se asignará (*)</label>
            <select
              v-model="formFirmante.id_evento"
              class="w-full text-sm rounded-xl border-gray-300 focus:border-sky-600 focus:ring-sky-600 shadow-sm"
            >
              <option v-for="ev in eventos_disponibles" :key="ev.id" :value="ev.id">
                [ID {{ ev.id }}] {{ ev.nombre }}
              </option>
            </select>
          </div>

          <!-- SELECCIÓN Y CREACIÓN DE FIRMA -->
          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-gray-700 uppercase">
                Firma Digital {{ modoEdicion ? '(Opcional cambiar)' : '(*)' }}
              </label>
              <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                <button
                  type="button"
                  @click="modoFirma = 'canvas'"
                  :class="[
                    'px-2.5 py-1 text-xs font-bold rounded-md transition-colors',
                    modoFirma === 'canvas' ? 'bg-sky-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  Dibujar en Pantalla
                </button>
                <button
                  type="button"
                  @click="modoFirma = 'imagen'"
                  :class="[
                    'px-2.5 py-1 text-xs font-bold rounded-md transition-colors',
                    modoFirma === 'imagen' ? 'bg-sky-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  Subir Imagen
                </button>
              </div>
            </div>

            <!-- Canvas para Dibujar Firma -->
            <div v-show="modoFirma === 'canvas'" class="space-y-2">
              <div class="border-2 border-dashed border-gray-300 rounded-xl bg-white p-1 relative flex justify-center">
                <canvas
                  ref="canvasRef"
                  width="420"
                  height="140"
                  class="cursor-crosshair w-full max-w-[420px] h-[140px] touch-none bg-white rounded-lg"
                  @mousedown="startDrawing"
                  @mousemove="draw"
                  @mouseup="stopDrawing"
                  @mouseleave="stopDrawing"
                  @touchstart="startDrawingTouch"
                  @touchmove="drawTouch"
                  @touchend="stopDrawing"
                ></canvas>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-500 italic">Dibuje su trazo en el recuadro</span>
                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="limpiarCanvas"
                    class="px-2.5 py-1 text-gray-600 hover:text-rose-600 font-semibold"
                  >
                    Borrar trazo
                  </button>
                  <button
                    type="button"
                    @click="aplicarFirmaCanvas"
                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg"
                  >
                    Confirmar Firma
                  </button>
                </div>
              </div>
            </div>

            <!-- Subir Archivo de Imagen -->
            <div v-show="modoFirma === 'imagen'" class="space-y-2">
              <input
                type="file"
                accept="image/*"
                @change="onFileFirmaChange"
                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100"
              />
            </div>

            <!-- Vista Previa de la Firma -->
            <div v-if="firmaPreview" class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center gap-3">
              <div class="text-xs font-semibold text-gray-500">Vista previa:</div>
              <img :src="firmaPreview" alt="Vista previa de firma" class="h-14 max-w-[200px] object-contain border bg-white rounded p-1" />
              <button
                type="button"
                @click="firmaPreview = null; formFirmante.firma = null"
                class="text-xs text-rose-600 font-semibold ml-auto hover:underline"
              >
                Quitar
              </button>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="cerrarModalFirmante"
              class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="procesando"
              class="px-5 py-2.5 bg-sky-700 hover:bg-sky-800 text-white font-bold text-sm rounded-xl shadow-md transition-all disabled:opacity-50 flex items-center gap-2"
            >
              <CheckIcon class="h-4 w-4" />
              {{ modoEdicion ? 'Actualizar Firmante' : 'Guardar Firmante' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  DocumentTextIcon,
  DocumentDuplicateIcon,
  PencilSquareIcon,
  FolderOpenIcon,
  EyeIcon,
  ArrowDownTrayIcon,
  UserPlusIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  ShieldCheckIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  modo: {
    type: String,
    default: 'comision'
  },
  titulo_seccion: {
    type: String,
    default: 'Actas Oficiales'
  },
  evento: Object,
  es_comision: Boolean,
  fechas: Object,
  actas: Array,
  firmantes: Array,
  secretario_tic: Object,
  estadisticas: Object,
  eventos_disponibles: Array
});

const breadcrumbLinks = props.modo === 'comision'
  ? [
      { url: '/dashboard', text: 'Inicio' },
      { url: route('postulaciones-candidatos.index'), text: 'Comisión de Personal' },
      { url: '', text: 'Actas Comisión de Personal' },
    ]
  : [
      { url: '/dashboard', text: 'Inicio' },
      { url: route('eventos.index'), text: 'Eventos' },
      { url: '', text: 'Actas Generales de Votación' },
    ];

const activeTab = ref('actas');
const selectedEventoId = ref(props.evento?.id || 25);

// Lista reactiva de firmantes para reordenamiento
const listaFirmantes = ref([...(props.firmantes || [])]);

watch(() => props.firmantes, (nuevos) => {
  listaFirmantes.value = [...(nuevos || [])];
});

// Reordenamiento de firmantes
const guardandoOrden = ref(false);

const guardarNuevoOrden = () => {
  const ordenes = listaFirmantes.value
    .filter(item => item.id > 0)
    .map((item, index) => ({
      id: item.id,
      orden: index + 1
    }));

  if (ordenes.length === 0) return;

  guardandoOrden.value = true;
  router.post(route('actas-comision.firmantes.reordenar'), { ordenes }, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      guardandoOrden.value = false;
    }
  });
};

const subirFirmante = (idx) => {
  if (idx <= 0) return;
  const temp = listaFirmantes.value[idx];
  listaFirmantes.value[idx] = listaFirmantes.value[idx - 1];
  listaFirmantes.value[idx - 1] = temp;
  guardarNuevoOrden();
};

const bajarFirmante = (idx) => {
  if (idx >= listaFirmantes.value.length - 1) return;
  const temp = listaFirmantes.value[idx];
  listaFirmantes.value[idx] = listaFirmantes.value[idx + 1];
  listaFirmantes.value[idx + 1] = temp;
  guardarNuevoOrden();
};

const cambiarEvento = () => {
  const rutaDestino = props.modo === 'comision' ? 'actas-comision.index' : 'actas-generales.index';
  router.get(
    route(rutaDestino),
    { id_evento: selectedEventoId.value },
    { preserveState: false }
  );
};

// Estado Modal Firmantes
const modalFirmanteVisible = ref(false);
const modoEdicion = ref(false);
const firmanteIdEditar = ref(null);
const modoFirma = ref('canvas');
const canvasRef = ref(null);
const firmaPreview = ref(null);
const procesando = ref(false);

// Búsqueda de secretario por cédula
const buscandoCedula = ref(false);
const secretarioEncontradoInfo = ref(null);
const cedulaBuscadaSinResultado = ref(false);

const formFirmante = ref({
  nombre: '',
  identificacion: '',
  cargo: '',
  id_evento: props.evento?.id || 25,
  firma: null
});

const buscarPorCedula = async () => {
  const cedula = formFirmante.value.identificacion ? formFirmante.value.identificacion.trim() : '';
  if (!cedula) return;

  buscandoCedula.value = true;
  secretarioEncontradoInfo.value = null;
  cedulaBuscadaSinResultado.value = false;

  try {
    const response = await axios.get(route('actas-comision.firmantes.buscar-cedula'), {
      params: { identificacion: cedula }
    });

    if (response.data && response.data.encontrado) {
      const f = response.data.firmante;
      formFirmante.value.nombre = f.nombre;
      formFirmante.value.cargo = f.cargo;
      formFirmante.value.firma = f.firma; // Reutiliza el archivo de firma física
      firmaPreview.value = f.firma_url;
      secretarioEncontradoInfo.value = f;
    } else {
      cedulaBuscadaSinResultado.value = true;
    }
  } catch (err) {
    console.error('Error buscando firmante por cédula:', err);
  } finally {
    buscandoCedula.value = false;
  }
};

// Canvas Drawing Logic
let isDrawing = false;
let ctx = null;

const initCanvas = () => {
  if (canvasRef.value) {
    ctx = canvasRef.value.getContext('2d');
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }
};

const getCanvasCoordinates = (e) => {
  const rect = canvasRef.value.getBoundingClientRect();
  const scaleX = canvasRef.value.width / rect.width;
  const scaleY = canvasRef.value.height / rect.height;
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY
  };
};

const startDrawing = (e) => {
  initCanvas();
  isDrawing = true;
  const pos = getCanvasCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
  if (!isDrawing || !ctx) return;
  const pos = getCanvasCoordinates(e);
  ctx.lineTo(pos.x, pos.y);
  ctx.stroke();
};

const stopDrawing = () => {
  isDrawing = false;
};

const startDrawingTouch = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  startDrawing(touch);
};

const drawTouch = (e) => {
  e.preventDefault();
  const touch = e.touches[0];
  draw(touch);
};

const limpiarCanvas = () => {
  if (ctx && canvasRef.value) {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  }
  firmaPreview.value = null;
  formFirmante.value.firma = null;
};

const aplicarFirmaCanvas = () => {
  if (canvasRef.value) {
    const dataUrl = canvasRef.value.toDataURL('image/png');
    firmaPreview.value = dataUrl;
    formFirmante.value.firma = dataUrl;
  }
};

const onFileFirmaChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    formFirmante.value.firma = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      firmaPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const abrirModalNuevoFirmante = () => {
  modoEdicion.value = false;
  firmanteIdEditar.value = null;
  secretarioEncontradoInfo.value = null;
  cedulaBuscadaSinResultado.value = false;
  formFirmante.value = {
    nombre: '',
    identificacion: '',
    cargo: '',
    id_evento: props.evento?.id || 25,
    firma: null
  };
  firmaPreview.value = null;
  modalFirmanteVisible.value = true;
  setTimeout(initCanvas, 100);
};

const editarFirmante = (f) => {
  modoEdicion.value = true;
  firmanteIdEditar.value = f.id;
  secretarioEncontradoInfo.value = null;
  cedulaBuscadaSinResultado.value = false;
  formFirmante.value = {
    nombre: f.nombre,
    identificacion: f.identificacion,
    cargo: f.cargo,
    id_evento: f.id_evento || props.evento?.id || 25,
    firma: null
  };
  firmaPreview.value = f.firma ? getFirmaUrl(f.firma) : null;
  modalFirmanteVisible.value = true;
  setTimeout(initCanvas, 100);
};

const cerrarModalFirmante = () => {
  modalFirmanteVisible.value = false;
  secretarioEncontradoInfo.value = null;
  cedulaBuscadaSinResultado.value = false;
  limpiarCanvas();
};

const guardarFirmanteSubmit = () => {
  if (modoFirma.value === 'canvas' && !formFirmante.value.firma && canvasRef.value) {
    aplicarFirmaCanvas();
  }

  if (!modoEdicion.value && !formFirmante.value.firma) {
    alert('Por favor dibuje o cargue la firma digital del secretario.');
    return;
  }

  procesando.value = true;

  if (modoEdicion.value) {
    router.post(route('actas-comision.firmantes.update', firmanteIdEditar.value), formFirmante.value, {
      forceFormData: true,
      onSuccess: () => {
        procesando.value = false;
        cerrarModalFirmante();
      },
      onError: () => {
        procesando.value = false;
      }
    });
  } else {
    router.post(route('actas-comision.firmantes.store'), formFirmante.value, {
      forceFormData: true,
      onSuccess: () => {
        procesando.value = false;
        cerrarModalFirmante();
      },
      onError: () => {
        procesando.value = false;
      }
    });
  }
};

const eliminarFirmante = (id) => {
  if (confirm('¿Está seguro de eliminar este secretario firmante? Esta acción no se puede deshacer.')) {
    router.delete(route('actas-comision.firmantes.destroy', id));
  }
};

const getFirmaUrl = (filename) => {
  if (!filename) return '';
  return `/storage/uploads/delegado/${filename}`;
};

const verFirmaGrande = (url) => {
  window.open(url, '_blank');
};
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
