<template>
  <Head title="Validación de Candidatos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Validación de Postulaciones de Candidatos</h1>
          <p class="text-xs text-slate-500">
            Revisión de requisitos legales, soportes documentales y aprobación de candidatos para elecciones.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            :href="route('postulaciones-candidatos.historial')"
            class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
          >
            <ClockIcon class="w-4 h-4" />
            <span>Ver Historial de Auditoría</span>
          </Link>
          <a
            :href="route('candidatos.inscripcion.publica')"
            target="_blank"
            class="px-3.5 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
          >
            <ArrowTopRightOnSquareIcon class="w-4 h-4" />
            <span>Formulario Público</span>
          </a>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- TARJETAS DE MÉTRICAS -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          @click="filtrarPorEstado('Todos')"
          class="cursor-pointer bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-indigo-400 transition"
          :class="{ 'ring-2 ring-indigo-500 bg-indigo-50/20': currentEstado === 'Todos' }"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Postulaciones</span>
            <span class="p-2 bg-slate-100 text-slate-600 rounded-lg">
              <UserGroupIcon class="w-5 h-5" />
            </span>
          </div>
          <p class="text-2xl font-black text-slate-900 mt-2">{{ metrics.total }}</p>
          <p class="text-[11px] text-slate-500 mt-1">Registradas en el sistema</p>
        </div>

        <div
          @click="filtrarPorEstado('Pendiente')"
          class="cursor-pointer bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-amber-400 transition"
          :class="{ 'ring-2 ring-amber-500 bg-amber-50/20': currentEstado === 'Pendiente' }"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Por Validar</span>
            <span class="p-2 bg-amber-100 text-amber-600 rounded-lg">
              <ClockIcon class="w-5 h-5" />
            </span>
          </div>
          <p class="text-2xl font-black text-amber-700 mt-2">{{ metrics.pendientes }}</p>
          <p class="text-[11px] text-amber-600 font-semibold mt-1">Requieren revisión documental</p>
        </div>

        <div
          @click="filtrarPorEstado('Aprobado')"
          class="cursor-pointer bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-400 transition"
          :class="{ 'ring-2 ring-emerald-500 bg-emerald-50/20': currentEstado === 'Aprobado' }"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Aprobadas</span>
            <span class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
              <CheckBadgeIcon class="w-5 h-5" />
            </span>
          </div>
          <p class="text-2xl font-black text-emerald-700 mt-2">{{ metrics.aprobadas }}</p>
          <p class="text-[11px] text-emerald-600 font-semibold mt-1">Habilitadas como candidatos</p>
        </div>

        <div
          @click="filtrarPorEstado('Rechazado')"
          class="cursor-pointer bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-red-400 transition"
          :class="{ 'ring-2 ring-red-500 bg-red-50/20': currentEstado === 'Rechazado' }"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-red-700">Rechazadas</span>
            <span class="p-2 bg-red-100 text-red-600 rounded-lg">
              <XCircleIcon class="w-5 h-5" />
            </span>
          </div>
          <p class="text-2xl font-black text-red-700 mt-2">{{ metrics.rechazadas }}</p>
          <p class="text-[11px] text-red-600 font-semibold mt-1">Con motivos registrados</p>
        </div>
      </div>

      <!-- BARRA DE FILTROS Y BÚSQUEDA -->
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-4">
        <!-- TABS DE ESTADOS -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <button
              v-for="pestana in pestañasEstado"
              :key="pestana.key"
              @click="filtrarPorEstado(pestana.key)"
              type="button"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shrink-0"
              :class="currentEstado === pestana.key
                ? 'bg-indigo-600 text-white shadow-xs'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            >
              <span>{{ pestana.nombre }}</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px]"
                :class="currentEstado === pestana.key ? 'bg-indigo-800 text-white' : 'bg-slate-200 text-slate-700'"
              >
                {{ pestana.conteo }}
              </span>
            </button>
          </div>

          <div class="text-xs text-slate-500 font-medium">
            Mostrando <span class="font-bold text-slate-800">{{ postulaciones.data.length }}</span> de
            <span class="font-bold text-slate-800">{{ postulaciones.total }}</span> registros
          </div>
        </div>

        <!-- FILTROS SECUNDARIOS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
          <!-- Búsqueda -->
          <div class="relative md:col-span-2">
            <input
              type="text"
              v-model="filtroSearch"
              @keyup.enter="aplicarFiltros"
              placeholder="Buscar por cédula, nombre, radicado o dependencia..."
              class="w-full text-xs border border-slate-300 rounded-lg pl-9 pr-8 py-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            <button
              v-if="filtroSearch"
              @click="filtroSearch = ''; aplicarFiltros()"
              class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- Filtro Evento -->
          <div>
            <select
              v-model="filtroEvento"
              @change="aplicarFiltros"
              class="w-full text-xs border border-slate-300 rounded-lg py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Todos los Eventos</option>
              <option v-for="ev in eventos" :key="ev.id" :value="ev.id">
                {{ ev.nombre }} ({{ ev.estado }})
              </option>
            </select>
          </div>

          <!-- Filtro Planta -->
          <div>
            <select
              v-model="filtroPlanta"
              @change="aplicarFiltros"
              class="w-full text-xs border border-slate-300 rounded-lg py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Todas las Plantas</option>
              <option value="PLANTA CENTRAL">Planta Central</option>
              <option value="SGP">SGP</option>
            </select>
          </div>
        </div>
      </div>

      <!-- TABLA DE POSTULACIONES -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-extrabold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3 px-4">Radicado / Fecha</th>
                <th class="py-3 px-4">Candidato / Funcionario</th>
                <th class="py-3 px-4">Evento de Elección</th>
                <th class="py-3 px-4">Planta / Dependencia</th>
                <th class="py-3 px-4 text-center">Soportes</th>
                <th class="py-3 px-4 text-center">Estado</th>
                <th class="py-3 px-4 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr
                v-for="postulacion in postulaciones.data"
                :key="postulacion.id"
                class="hover:bg-slate-50/80 transition"
              >
                <!-- Radicado y Fecha -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="font-mono font-bold text-indigo-700 text-xs">
                    {{ postulacion.radicado }}
                  </div>
                  <div class="text-[11px] text-slate-400 mt-0.5">
                    {{ formatearFecha(postulacion.fecha_postulacion || postulacion.created_at) }}
                  </div>
                </td>

                <!-- Datos del Candidato -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                      <img
                        v-if="postulacion.foto"
                        :src="'/storage/uploads/candidatos/' + postulacion.foto"
                        :alt="postulacion.nombre"
                        class="w-full h-full object-cover"
                      />
                      <UserIcon v-else class="w-4 h-4 text-slate-400" />
                    </div>
                    <div>
                      <div class="font-extrabold text-slate-900 uppercase">
                        {{ postulacion.nombre }}
                      </div>
                      <div class="text-[11px] font-mono text-slate-500">
                        {{ postulacion.tipo_documento || 'CC' }}: {{ postulacion.identificacion }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Evento -->
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-800 max-w-xs truncate" :title="postulacion.evento ? postulacion.evento.nombre : ''">
                    {{ postulacion.evento ? postulacion.evento.nombre : 'No asignado' }}
                  </div>
                  <span
                    v-if="postulacion.evento"
                    class="inline-block text-[10px] font-bold px-1.5 py-0.2 rounded mt-0.5"
                    :class="postulacion.evento.estado === 'Pendiente' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                  >
                    Evento {{ postulacion.evento.estado }}
                  </span>
                </td>

                <!-- Planta y Dependencia -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-1">
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                      :class="postulacion.tipo_planta === 'SGP' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                    >
                      {{ postulacion.tipo_planta || 'PLANTA CENTRAL' }}
                    </span>
                  </div>
                  <div class="text-[11px] text-slate-600 font-medium truncate max-w-xs mt-1" :title="postulacion.dependencia">
                    {{ postulacion.dependencia || 'Sin dependencia' }}
                  </div>
                  <div class="text-[10px] text-slate-400 italic" v-if="postulacion.provision">
                    Prov: {{ postulacion.provision }}
                  </div>
                </td>

                <!-- Soportes / Documentos -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Certificado Procuraduría -->
                    <button
                      type="button"
                      @click="abrirVisorDocumento('procuraduria', postulacion)"
                      class="p-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md border border-indigo-200 transition"
                      title="Ver Certificado de Procuraduría"
                    >
                      <DocumentTextIcon class="w-4 h-4" />
                    </button>

                    <!-- Fotocopia Cédula -->
                    <button
                      type="button"
                      @click="abrirVisorDocumento('cedula', postulacion)"
                      class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md border border-slate-300 transition"
                      title="Ver Fotocopia de Cédula"
                    >
                      <IdentificationIcon class="w-4 h-4" />
                    </button>

                    <!-- Foto (si cargó) -->
                    <button
                      v-if="postulacion.foto"
                      type="button"
                      @click="abrirVisorDocumento('foto', postulacion)"
                      class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-md border border-emerald-200 transition"
                      title="Ver Fotografía"
                    >
                      <PhotoIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>

                <!-- Estado -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1"
                    :class="{
                      'bg-amber-100 text-amber-800 border border-amber-300': postulacion.estado === 'Pendiente',
                      'bg-emerald-100 text-emerald-800 border border-emerald-300': postulacion.estado === 'Aprobado',
                      'bg-red-100 text-red-800 border border-red-300': postulacion.estado === 'Rechazado',
                    }"
                  >
                    <ClockIcon v-if="postulacion.estado === 'Pendiente'" class="w-3.5 h-3.5 text-amber-600" />
                    <CheckCircleIcon v-else-if="postulacion.estado === 'Aprobado'" class="w-3.5 h-3.5 text-emerald-600" />
                    <XCircleIcon v-else class="w-3.5 h-3.5 text-red-600" />
                    <span>{{ postulacion.estado }}</span>
                  </span>

                  <div v-if="postulacion.validador" class="text-[10px] text-slate-400 mt-1">
                    Por: {{ postulacion.validador.name }}
                  </div>
                </td>

                <!-- Acciones -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1">
                    <!-- Botón Ver Detalles -->
                    <button
                      type="button"
                      @click="abrirDetalles(postulacion)"
                      class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-bold text-xs transition"
                      title="Ver Detalles Completos"
                    >
                      Detalles
                    </button>

                    <!-- Botón Aprobar (si no está aprobado y tiene permiso) -->
                    <button
                      v-if="postulacion.estado !== 'Aprobado' && ($page.props.user.permissions.includes('validador-candidatos-aprobar') || $page.props.user.roles.includes('Administrador'))"
                      type="button"
                      @click="abrirModalAprobar(postulacion)"
                      class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-bold text-xs transition shadow-2xs inline-flex items-center gap-1"
                      title="Aprobar Candidatura"
                    >
                      <CheckIcon class="w-3.5 h-3.5" />
                      <span>Aprobar</span>
                    </button>

                    <!-- Botón Rechazar (si no está rechazado y tiene permiso) -->
                    <button
                      v-if="postulacion.estado !== 'Rechazado' && ($page.props.user.permissions.includes('validador-candidatos-rechazar') || $page.props.user.roles.includes('Administrador'))"
                      type="button"
                      @click="abrirModalRechazar(postulacion)"
                      class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md font-bold text-xs transition shadow-2xs inline-flex items-center gap-1"
                      title="Rechazar Postulación"
                    >
                      <XMarkIcon class="w-3.5 h-3.5" />
                      <span>Rechazar</span>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="postulaciones.data.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <InboxIcon class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                  <p class="font-bold text-slate-600">No se encontraron postulaciones</p>
                  <p class="text-xs text-slate-400 mt-0.5">
                    No existen registros para el filtro seleccionado o el estado indicado.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50">
          <div class="text-xs text-slate-500">
            Página <span class="font-bold text-slate-800">{{ postulaciones.current_page }}</span> de
            <span class="font-bold text-slate-800">{{ postulaciones.last_page }}</span>
          </div>
          <Pagination :links="postulaciones.links" />
        </div>
      </div>
    </div>

    <!-- MODAL 1: VISOR DOCKABLE / SPLIT-SCREEN / MULTI-MODO DE DOCUMENTOS (z-[101]) -->
    <Teleport to="body">
      <!-- MODO MINIMIZADO (PILL FLOTANTE EN ESQUINA INFERIOR) -->
      <div
        v-if="modalVisorAbierto && visorMinimizado"
        class="fixed bottom-5 right-5 z-[101] bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl p-3 border border-slate-700 flex items-center gap-3 transition-all"
      >
        <div class="p-2 bg-indigo-600 rounded-xl text-white shadow-md">
          <DocumentMagnifyingGlassIcon class="w-5 h-5" />
        </div>
        <div class="text-xs">
          <p class="font-extrabold text-white uppercase">{{ visorTitulo }}</p>
          <p class="text-[11px] text-slate-300 font-mono">{{ postulacionActivaVisor?.nombre }}</p>
        </div>
        <div class="flex items-center gap-1 pl-2 border-l border-slate-700">
          <button
            type="button"
            @click="visorMinimizado = false"
            class="p-1.5 hover:bg-slate-800 text-indigo-400 hover:text-white rounded-lg transition"
            title="Restaurar Visor"
          >
            <ArrowsPointingOutIcon class="w-4 h-4" />
          </button>
          <button
            type="button"
            @click="modalVisorAbierto = false"
            class="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded-lg transition"
            title="Cerrar Visor"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- VISOR ACTIVO SEGÚN EL MODO: SPLIT (50% DERECHA), FULLSCREEN O CENTRADO -->
      <div v-else-if="modalVisorAbierto">
        <!-- Backdrop únicamente cuando está en modo modal 'center' -->
        <div
          v-if="visorModo === 'center'"
          class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs transition-opacity z-[101]"
          @click="modalVisorAbierto = false"
        ></div>

        <!-- Contenedor maestro del Visor -->
        <div
          :class="{
            // Modo Dividido (Split Screen 50% Lateral Derecho)
            'fixed top-0 right-0 bottom-0 w-full lg:w-1/2 xl:w-1/2 z-[102] bg-white shadow-2xl border-l-2 border-indigo-200 flex flex-col': visorModo === 'split',
            // Modo Pantalla Completa
            'fixed inset-0 z-[102] bg-white flex flex-col': visorModo === 'full',
            // Modo Modal Centrado
            'fixed inset-0 z-[102] flex items-center justify-center p-3 sm:p-6 pointer-events-none': visorModo === 'center'
          }"
        >
          <div
            :class="{
              'w-full h-full flex flex-col': visorModo === 'split' || visorModo === 'full',
              'w-full max-w-4xl h-[92vh] bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col pointer-events-auto overflow-hidden': visorModo === 'center'
            }"
          >
            <!-- CABECERA DEL VISOR -->
            <div class="px-4 py-3 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 shrink-0 border-b border-slate-800">
              <!-- Información de Documento y Candidato -->
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-600 text-white rounded-xl shadow-xs">
                  <DocumentMagnifyingGlassIcon class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-xs font-black uppercase tracking-wider text-white">
                    {{ visorTitulo }}
                  </h3>
                  <p class="text-[11px] text-slate-300 font-medium truncate max-w-xs sm:max-w-md">
                    {{ postulacionActivaVisor?.nombre }} •
                    <span class="font-mono text-indigo-300">{{ postulacionActivaVisor?.tipo_documento || 'CC' }}: {{ postulacionActivaVisor?.identificacion }}</span>
                  </p>
                </div>
              </div>

              <!-- PESTAÑAS DE CAMBIO RÁPIDO ENTRE SOPORTES -->
              <div class="flex items-center gap-1 bg-slate-800/90 p-1 rounded-xl border border-slate-700">
                <button
                  type="button"
                  @click="cambiarDocVisor('procuraduria')"
                  class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5"
                  :class="docActivoTipo === 'procuraduria' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-700'"
                >
                  <DocumentTextIcon class="w-3.5 h-3.5" />
                  <span>Procuraduría</span>
                </button>

                <button
                  type="button"
                  @click="cambiarDocVisor('cedula')"
                  class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5"
                  :class="docActivoTipo === 'cedula' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-700'"
                >
                  <IdentificationIcon class="w-3.5 h-3.5" />
                  <span>Cédula</span>
                </button>

                <button
                  v-if="postulacionActivaVisor?.foto"
                  type="button"
                  @click="cambiarDocVisor('foto')"
                  class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5"
                  :class="docActivoTipo === 'foto' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-700'"
                >
                  <PhotoIcon class="w-3.5 h-3.5" />
                  <span>Foto</span>
                </button>
              </div>

              <!-- CONTROLES DE PANTALLA Y CIERRE -->
              <div class="flex items-center gap-1 text-slate-300">
                <!-- Modo Dividido 50% Lateral -->
                <button
                  type="button"
                  @click="visorModo = 'split'"
                  class="p-2 rounded-lg hover:bg-slate-800 transition"
                  :class="visorModo === 'split' ? 'text-indigo-400 bg-slate-800' : 'text-slate-400 hover:text-white'"
                  title="Dividir Pantalla (50% Lateral)"
                >
                  <ViewColumnsIcon class="w-4 h-4" />
                </button>

                <!-- Modo Centrado Modal -->
                <button
                  type="button"
                  @click="visorModo = 'center'"
                  class="p-2 rounded-lg hover:bg-slate-800 transition"
                  :class="visorModo === 'center' ? 'text-indigo-400 bg-slate-800' : 'text-slate-400 hover:text-white'"
                  title="Modal Centrado"
                >
                  <WindowIcon class="w-4 h-4" />
                </button>

                <!-- Modo Pantalla Completa -->
                <button
                  type="button"
                  @click="visorModo = visorModo === 'full' ? 'split' : 'full'"
                  class="p-2 rounded-lg hover:bg-slate-800 transition"
                  :class="visorModo === 'full' ? 'text-indigo-400 bg-slate-800' : 'text-slate-400 hover:text-white'"
                  title="Pantalla Completa"
                >
                  <ArrowsPointingOutIcon v-if="visorModo !== 'full'" class="w-4 h-4" />
                  <ArrowsPointingInIcon v-else class="w-4 h-4" />
                </button>

                <!-- Minimizar -->
                <button
                  type="button"
                  @click="visorMinimizado = true"
                  class="p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition"
                  title="Minimizar Visor"
                >
                  <MinusIcon class="w-4 h-4" />
                </button>

                <!-- Cerrar -->
                <button
                  type="button"
                  @click="modalVisorAbierto = false"
                  class="p-2 rounded-lg hover:bg-red-900/60 text-slate-400 hover:text-red-400 transition"
                  title="Cerrar Visor"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- CONTROLES ADICIONALES PARA IMÁGENES (ZOOM Y ROTACIÓN) -->
            <div v-if="!esPdf(visorUrl)" class="px-4 py-2 bg-slate-100 border-b border-slate-200 flex items-center justify-between text-xs">
              <span class="text-slate-600 font-semibold text-[11px]">Herramientas de inspección visual:</span>
              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="zoomIn"
                  class="p-1 px-2.5 bg-white border border-slate-300 rounded-md hover:bg-slate-50 font-bold text-slate-700 shadow-2xs"
                  title="Aumentar Zoom (+)"
                >
                  <PlusIcon class="w-3.5 h-3.5 inline mr-0.5" />
                  <span>Zoom</span>
                </button>
                <span class="text-[11px] font-mono font-bold text-indigo-700 px-1">{{ Math.round(zoomNivel * 100) }}%</span>
                <button
                  type="button"
                  @click="zoomOut"
                  class="p-1 px-2.5 bg-white border border-slate-300 rounded-md hover:bg-slate-50 font-bold text-slate-700 shadow-2xs"
                  title="Reducir Zoom (-)"
                >
                  <MinusIcon class="w-3.5 h-3.5 inline mr-0.5" />
                  <span>Alejar</span>
                </button>
                <button
                  type="button"
                  @click="rotar"
                  class="p-1 px-2.5 bg-white border border-slate-300 rounded-md hover:bg-slate-50 font-bold text-slate-700 shadow-2xs ml-1"
                  title="Girar 90 grados"
                >
                  <ArrowPathRoundedSquareIcon class="w-3.5 h-3.5 inline mr-0.5" />
                  <span>Girar</span>
                </button>
                <button
                  type="button"
                  @click="resetZoom"
                  class="px-2.5 py-1 bg-white border border-slate-300 rounded-md text-[11px] font-bold text-slate-600 hover:bg-slate-50 shadow-2xs"
                >
                  Restablecer
                </button>
              </div>
            </div>

            <!-- ÁREA DE VISUALIZACIÓN DEL DOCUMENTO -->
            <div class="flex-1 bg-slate-200/90 overflow-auto p-3 flex items-center justify-center relative min-h-[350px]">
              <!-- Caso 1: Archivo PDF -->
              <iframe
                v-if="esPdf(visorUrl)"
                :key="visorUrl"
                :src="visorUrl + '#toolbar=1&navpanes=0'"
                class="w-full h-full min-h-[500px] border-none bg-white rounded-xl shadow-md"
                title="Visor de PDF"
              ></iframe>

              <!-- Caso 2: Imagen (con zoom y rotación interactiva) -->
              <div
                v-else
                class="w-full h-full flex items-center justify-center overflow-auto p-4"
              >
                <img
                  :key="visorUrl"
                  :src="visorUrl"
                  :alt="visorTitulo"
                  :style="{
                    transform: `scale(${zoomNivel}) rotate(${rotacion}deg)`,
                    transformOrigin: 'center center',
                    transition: 'transform 0.2s ease',
                  }"
                  class="max-h-[85vh] max-w-full object-contain rounded-xl shadow-lg bg-white"
                />
              </div>
            </div>

            <!-- BARRA INFERIOR DE ACCIONES -->
            <div class="px-4 py-3 bg-white border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 shrink-0">
              <div class="flex items-center gap-3">
                <a
                  :href="visorUrl"
                  target="_blank"
                  class="text-xs font-bold text-indigo-700 hover:text-indigo-900 flex items-center gap-1.5 py-1.5 px-3 rounded-lg hover:bg-indigo-50 border border-indigo-200 transition"
                >
                  <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                  <span>Abrir en nueva pestaña</span>
                </a>

                <a
                  :href="visorUrl"
                  download
                  class="text-xs font-bold text-slate-600 hover:text-slate-800 flex items-center gap-1.5 py-1.5 px-3 rounded-lg hover:bg-slate-100 border border-slate-200 transition"
                >
                  <ArrowDownTrayIcon class="w-4 h-4" />
                  <span>Descargar original</span>
                </a>
              </div>

              <!-- ACCIONES RÁPIDAS DE VALIDACIÓN DIRECTAS EN EL VISOR -->
              <div class="flex items-center gap-2">
                <template v-if="postulacionActivaVisor?.estado === 'Pendiente'">
                  <button
                    v-if="$page.props.user.permissions.includes('validador-candidatos-aprobar') || $page.props.user.roles.includes('Administrador')"
                    type="button"
                    @click="abrirModalAprobar(postulacionActivaVisor)"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow-xs"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span>Aprobar Candidato</span>
                  </button>

                  <button
                    v-if="$page.props.user.permissions.includes('validador-candidatos-rechazar') || $page.props.user.roles.includes('Administrador')"
                    type="button"
                    @click="abrirModalRechazar(postulacionActivaVisor)"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow-xs"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Rechazar</span>
                  </button>
                </template>

                <button
                  type="button"
                  @click="modalVisorAbierto = false"
                  class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-xs font-bold transition"
                >
                  Cerrar Visor
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 2: APROBACIÓN DE CANDIDATURA -->
    <Modal :show="modalAprobarAbierto" @close="modalAprobarAbierto = false" maxWidth="lg">
      <div class="p-6 space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-200 text-emerald-700">
          <div class="p-2 bg-emerald-100 rounded-lg">
            <CheckBadgeIcon class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-base font-extrabold text-slate-900">Aprobar Postulación de Candidato</h3>
            <p class="text-xs text-slate-500">Radicado: {{ seleccionada?.radicado }}</p>
          </div>
        </div>

        <div v-if="seleccionada" class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs space-y-2">
          <div class="flex justify-between">
            <span class="text-slate-500 font-semibold">Candidato:</span>
            <span class="font-bold text-slate-900 uppercase">{{ seleccionada.nombre }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500 font-semibold">Documento:</span>
            <span class="font-mono font-bold text-slate-800">{{ seleccionada.identificacion }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500 font-semibold">Evento Destino:</span>
            <span class="font-bold text-indigo-900">{{ seleccionada.evento?.nombre }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500 font-semibold">Estamento / Planta:</span>
            <span class="font-bold text-slate-800">{{ seleccionada.tipo_planta }}</span>
          </div>
        </div>

        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-900">
          <p class="font-bold flex items-center gap-1.5">
            <InformationCircleIcon class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>Efectos de la Aprobación:</span>
          </p>
          <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px] text-emerald-800">
            <li>El usuario será habilitado oficialmente como <strong>candidato</strong> en el evento electoral.</li>
            <li>Se registrará en el censo con <code>candidato = 1</code> para permitir recibir sufragios.</li>
            <li>Se dejará constancia en la tabla de <strong>auditoría de validaciones de candidatos</strong>.</li>
          </ul>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase">
            Observaciones de la Aprobación (Opcional):
          </label>
          <textarea
            v-model="formAprobar.observaciones"
            rows="3"
            placeholder="Ingrese notas o consideraciones sobre la verificación documental..."
            class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:ring-emerald-500 focus:border-emerald-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
          <button
            type="button"
            @click="modalAprobarAbierto = false"
            class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="confirmarAprobacion"
            :disabled="procesandoAccion"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5"
          >
            <ArrowPathIcon v-if="procesandoAccion" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" />
            <span>Confirmar Aprobación</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- MODAL 3: RECHAZO DE POSTULACIÓN CON CHECKBOXES DE MOTIVOS -->
    <Modal :show="modalRechazarAbierto" @close="modalRechazarAbierto = false" maxWidth="xl">
      <div class="p-6 space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-200 text-red-700">
          <div class="p-2 bg-red-100 rounded-lg">
            <XCircleIcon class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-base font-extrabold text-slate-900">Rechazar Postulación de Candidato</h3>
            <p class="text-xs text-slate-500">Radicado: {{ seleccionada?.radicado }} - {{ seleccionada?.nombre }}</p>
          </div>
        </div>

        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-900">
          <p class="font-bold">Indique los motivos de rechazo documental:</p>
          <p class="text-[11px] text-red-700 mt-0.5">
            Seleccione uno o varios motivos estandarizados. Estos quedarán almacenados en formato estructurado (JSON) en la postulación y en el historial de auditoría.
          </p>
        </div>

        <!-- LISTADO DE MOTIVOS CON CHECKBOXES -->
        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
          <label
            v-for="(motivo, index) in motivos_estandar"
            :key="index"
            class="flex items-start gap-2.5 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer select-none transition"
            :class="{ 'bg-red-50/40 border-red-300': formRechazar.motivos_rechazo.includes(motivo) }"
          >
            <input
              type="checkbox"
              :value="motivo"
              v-model="formRechazar.motivos_rechazo"
              class="mt-0.5 rounded border-2 border-slate-400 text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer"
            />
            <span class="text-xs text-slate-800 font-medium leading-snug">
              {{ motivo }}
            </span>
          </label>
        </div>

        <!-- OBSERVACIONES ADICIONALES -->
        <div class="space-y-1">
          <label class="block text-xs font-bold text-slate-700 uppercase">
            Observaciones o Justificación Detallada:
          </label>
          <textarea
            v-model="formRechazar.observaciones"
            rows="3"
            placeholder="Especifique detalles del rechazo (ej: fecha de expedición del certificado es anterior a 3 meses)..."
            class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:ring-red-500 focus:border-red-500"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
          <button
            type="button"
            @click="modalRechazarAbierto = false"
            class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="confirmarRechazo"
            :disabled="procesandoAccion || formRechazar.motivos_rechazo.length === 0"
            class="px-5 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5"
          >
            <ArrowPathIcon v-if="procesandoAccion" class="w-4 h-4 animate-spin" />
            <XMarkIcon v-else class="w-4 h-4" />
            <span>Confirmar Rechazo</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- MODAL 4: DETALLE COMPLETO DE POSTULACIÓN -->
    <Modal :show="modalDetalleAbierto" @close="modalDetalleAbierto = false" maxWidth="2xl">
      <div class="p-6 space-y-5" v-if="seleccionada">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg">
              <ClipboardDocumentCheckIcon class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">
                Detalle de Postulación: {{ seleccionada.radicado }}
              </h3>
              <p class="text-xs text-slate-500">
                Fecha: {{ formatearFecha(seleccionada.fecha_postulacion || seleccionada.created_at) }}
              </p>
            </div>
          </div>
          <button
            @click="modalDetalleAbierto = false"
            class="text-slate-400 hover:text-slate-600 p-1 rounded-md"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Estado actual y Validador -->
        <div class="flex items-center justify-between p-3 rounded-xl border" :class="{
          'bg-amber-50 border-amber-200 text-amber-900': seleccionada.estado === 'Pendiente',
          'bg-emerald-50 border-emerald-200 text-emerald-900': seleccionada.estado === 'Aprobado',
          'bg-red-50 border-red-200 text-red-900': seleccionada.estado === 'Rechazado',
        }">
          <div class="flex items-center gap-2">
            <span class="font-bold text-xs">Estado:</span>
            <span class="px-2 py-0.5 rounded font-black text-xs uppercase">{{ seleccionada.estado }}</span>
          </div>
          <div v-if="seleccionada.validador" class="text-xs">
            Revisado por: <span class="font-bold">{{ seleccionada.validador.name }}</span>
            ({{ formatearFecha(seleccionada.fecha_validacion) }})
          </div>
        </div>

        <!-- Si fue rechazado: Motivos -->
        <div v-if="seleccionada.estado === 'Rechazado' && seleccionada.motivos_rechazo" class="p-4 bg-red-50 border border-red-200 rounded-xl space-y-2">
          <h4 class="text-xs font-bold text-red-900 uppercase">Motivos del Rechazo:</h4>
          <ul class="list-disc pl-5 text-xs text-red-800 space-y-1">
            <li v-for="(mot, i) in parsearMotivos(seleccionada.motivos_rechazo)" :key="i">
              {{ mot }}
            </li>
          </ul>
          <p v-if="seleccionada.observaciones" class="text-xs text-red-700 pt-1 border-t border-red-200">
            <strong>Observaciones:</strong> {{ seleccionada.observaciones }}
          </p>
        </div>

        <!-- Información General y Laboral -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1.5">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Datos del Candidato</span>
            <p><strong>Nombre:</strong> <span class="uppercase">{{ seleccionada.nombre }}</span></p>
            <p><strong>Documento:</strong> {{ seleccionada.tipo_documento }} - {{ seleccionada.identificacion }}</p>
            <p><strong>Email:</strong> {{ seleccionada.email || 'No registrado' }}</p>
            <p><strong>Celular:</strong> {{ seleccionada.celular || 'No registrado' }}</p>
          </div>

          <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1.5">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Datos Laborales y Evento</span>
            <p><strong>Evento:</strong> {{ seleccionada.evento?.nombre }}</p>
            <p><strong>Planta:</strong> <span class="font-bold text-indigo-700">{{ seleccionada.tipo_planta }}</span></p>
            <p><strong>Dependencia:</strong> {{ seleccionada.dependencia || 'No registrada' }}</p>
            <p><strong>Provisión:</strong> {{ seleccionada.provision || 'No registrada' }}</p>
          </div>
        </div>

        <!-- Soportes Documentales -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
          <h4 class="text-xs font-bold text-slate-800 uppercase">Documentos Suministrados:</h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button
              type="button"
              @click="abrirVisorDocumento('procuraduria', seleccionada)"
              class="p-2.5 bg-white border border-slate-300 hover:border-indigo-500 rounded-lg text-left transition flex items-center gap-2 group"
            >
              <DocumentTextIcon class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition shrink-0" />
              <div class="truncate">
                <span class="block text-xs font-bold text-slate-800">Procuraduría</span>
                <span class="text-[10px] text-indigo-600 font-semibold">Clic para inspeccionar</span>
              </div>
            </button>

            <button
              type="button"
              @click="abrirVisorDocumento('cedula', seleccionada)"
              class="p-2.5 bg-white border border-slate-300 hover:border-indigo-500 rounded-lg text-left transition flex items-center gap-2 group"
            >
              <IdentificationIcon class="w-5 h-5 text-slate-700 group-hover:scale-110 transition shrink-0" />
              <div class="truncate">
                <span class="block text-xs font-bold text-slate-800">Cédula</span>
                <span class="text-[10px] text-indigo-600 font-semibold">Clic para inspeccionar</span>
              </div>
            </button>

            <button
              v-if="seleccionada.foto"
              type="button"
              @click="abrirVisorDocumento('foto', seleccionada)"
              class="p-2.5 bg-white border border-slate-300 hover:border-indigo-500 rounded-lg text-left transition flex items-center gap-2 group"
            >
              <PhotoIcon class="w-5 h-5 text-emerald-600 group-hover:scale-110 transition shrink-0" />
              <div class="truncate">
                <span class="block text-xs font-bold text-slate-800">Foto Tarjetón</span>
                <span class="text-[10px] text-indigo-600 font-semibold">Clic para inspeccionar</span>
              </div>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="modalDetalleAbierto = false"
            class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold"
          >
            Cerrar Detalle
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import {
  UserGroupIcon,
  ClockIcon,
  CheckBadgeIcon,
  XCircleIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  UserIcon,
  DocumentTextIcon,
  IdentificationIcon,
  PhotoIcon,
  CheckIcon,
  InboxIcon,
  ArrowTopRightOnSquareIcon,
  DocumentMagnifyingGlassIcon,
  ArrowDownTrayIcon,
  ClipboardDocumentCheckIcon,
  InformationCircleIcon,
  ArrowPathIcon,
  ArrowsPointingOutIcon,
  ArrowsPointingInIcon,
  ViewColumnsIcon,
  WindowIcon,
  MinusIcon,
  PlusIcon,
  ArrowPathRoundedSquareIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  postulaciones: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  metrics: {
    type: Object,
    required: true,
  },
  eventos: {
    type: Array,
    default: () => [],
  },
  motivos_estandar: {
    type: Array,
    default: () => [],
  },
});

const breadcrumbLinks = [
  { name: 'Inicio', url: route('dashboard') },
  { name: 'Validación Candidatos', url: route('postulaciones-candidatos.index') },
];

// Filtros Reactivos
const currentEstado = ref(props.filters.estado || 'Pendiente');
const filtroEvento = ref(props.filters.id_evento || '');
const filtroPlanta = ref(props.filters.planta || '');
const filtroSearch = ref(props.filters.search || '');

const pestañasEstado = computed(() => [
  { key: 'Pendiente', nombre: 'Por Validar', conteo: props.metrics.pendientes },
  { key: 'Aprobado', nombre: 'Aprobadas', conteo: props.metrics.aprobadas },
  { key: 'Rechazado', nombre: 'Rechazadas', conteo: props.metrics.rechazadas },
  { key: 'Todos', nombre: 'Todas', conteo: props.metrics.total },
]);

const filtrarPorEstado = (estadoKey) => {
  currentEstado.value = estadoKey;
  aplicarFiltros();
};

const aplicarFiltros = () => {
  router.get(
    route('postulaciones-candidatos.index'),
    {
      estado: currentEstado.value,
      id_evento: filtroEvento.value || null,
      planta: filtroPlanta.value || null,
      search: filtroSearch.value || null,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

// Formateador de fecha
const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A';
  try {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-CO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  } catch (e) {
    return fecha;
  }
};

const parsearMotivos = (motivos) => {
  if (!motivos) return [];
  if (Array.isArray(motivos)) return motivos;
  try {
    return JSON.parse(motivos);
  } catch (e) {
    return [motivos];
  }
};

// Modales y Estados de Acción
const seleccionada = ref(null);
const procesandoAccion = ref(false);

// Visor de Documentos Adaptable (Split 50%, Fullscreen, Centrado, Zoom)
const modalVisorAbierto = ref(false);
const visorModo = ref('split'); // 'split' | 'full' | 'center'
const visorMinimizado = ref(false);
const postulacionActivaVisor = ref(null);
const docActivoTipo = ref('procuraduria'); // 'procuraduria' | 'cedula' | 'foto'

// Zoom y Rotación de Imágenes
const zoomNivel = ref(1);
const rotacion = ref(0);

const zoomIn = () => {
  zoomNivel.value = Math.min(Number((zoomNivel.value + 0.25).toFixed(2)), 3);
};

const zoomOut = () => {
  zoomNivel.value = Math.max(Number((zoomNivel.value - 0.25).toFixed(2)), 0.5);
};

const resetZoom = () => {
  zoomNivel.value = 1;
  rotacion.value = 0;
};

const rotar = () => {
  rotacion.value = (rotacion.value + 90) % 360;
};

const abrirVisorDocumento = (tipoDoc, postulacion) => {
  postulacionActivaVisor.value = postulacion;
  docActivoTipo.value = tipoDoc;
  modalVisorAbierto.value = true;
  visorMinimizado.value = false;
  resetZoom();
};

const cambiarDocVisor = (tipoDoc) => {
  docActivoTipo.value = tipoDoc;
  resetZoom();
};

const visorUrl = computed(() => {
  if (!postulacionActivaVisor.value) return '';
  if (docActivoTipo.value === 'procuraduria') {
    return '/storage/uploads/documentos_candidatos/' + postulacionActivaVisor.value.archivo_procuraduria;
  }
  if (docActivoTipo.value === 'cedula') {
    return '/storage/uploads/documentos_candidatos/' + postulacionActivaVisor.value.archivo_cedula;
  }
  if (docActivoTipo.value === 'foto' && postulacionActivaVisor.value.foto) {
    return '/storage/uploads/candidatos/' + postulacionActivaVisor.value.foto;
  }
  return '';
});

const visorTitulo = computed(() => {
  if (docActivoTipo.value === 'procuraduria') return 'Certificado de Procuraduría';
  if (docActivoTipo.value === 'cedula') return 'Fotocopia de Documento de Identidad';
  if (docActivoTipo.value === 'foto') return 'Fotografía para Tarjetón Electoral';
  return 'Soporte Documental';
});

const esPdf = (url) => {
  if (!url) return false;
  return url.toLowerCase().split('?')[0].endsWith('.pdf');
};

// Modal Detalle
const modalDetalleAbierto = ref(false);
const abrirDetalles = (postulacion) => {
  seleccionada.value = postulacion;
  modalDetalleAbierto.value = true;
};

// Modal Aprobar
const modalAprobarAbierto = ref(false);
const formAprobar = ref({
  observaciones: '',
});

const abrirModalAprobar = (postulacion) => {
  seleccionada.value = postulacion;
  formAprobar.value.observaciones = '';
  modalAprobarAbierto.value = true;
};

const confirmarAprobacion = () => {
  if (!seleccionada.value) return;

  procesandoAccion.value = true;
  router.post(
    route('postulaciones-candidatos.aprobar', seleccionada.value.id),
    {
      observaciones: formAprobar.value.observaciones,
    },
    {
      onSuccess: () => {
        modalAprobarAbierto.value = false;
        procesandoAccion.value = false;
        Swal.fire({
          icon: 'success',
          title: 'Candidatura Aprobada',
          text: `La postulación ${seleccionada.value.radicado} ha sido aprobada e inscrita oficialmente en el evento.`,
        });
      },
      onError: (err) => {
        procesandoAccion.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Error al aprobar',
          text: err.error || 'Ocurrió un error al procesar la aprobación.',
        });
      },
    }
  );
};

// Modal Rechazar
const modalRechazarAbierto = ref(false);
const formRechazar = ref({
  motivos_rechazo: [],
  observaciones: '',
});

const abrirModalRechazar = (postulacion) => {
  seleccionada.value = postulacion;
  formRechazar.value.motivos_rechazo = [];
  formRechazar.value.observaciones = '';
  modalRechazarAbierto.value = true;
};

const confirmarRechazo = () => {
  if (!seleccionada.value) return;

  if (formRechazar.value.motivos_rechazo.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Motivo requerido',
      text: 'Por favor marque al menos un motivo de rechazo de la lista.',
    });
    return;
  }

  procesandoAccion.value = true;
  router.post(
    route('postulaciones-candidatos.rechazar', seleccionada.value.id),
    {
      motivos_rechazo: formRechazar.value.motivos_rechazo,
      observaciones: formRechazar.value.observaciones,
    },
    {
      onSuccess: () => {
        modalRechazarAbierto.value = false;
        procesandoAccion.value = false;
        Swal.fire({
          icon: 'info',
          title: 'Postulación Rechazada',
          text: `La postulación ${seleccionada.value.radicado} fue rechazada y registrada en la auditoría.`,
        });
      },
      onError: (err) => {
        procesandoAccion.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Error al rechazar',
          text: err.error || 'Ocurrió un error al procesar el rechazo.',
        });
      },
    }
  );
};
</script>

