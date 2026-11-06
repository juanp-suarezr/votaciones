<template>
  <Head :title="'Detalle del Evento: ' + evento.nombre" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Detalle del Evento
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Encabezado con datos principales y acciones -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-mono font-bold text-xs border border-slate-200">
              ID #{{ evento.id }}
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
              :class="{
                'bg-green-100 text-green-800 border-green-200': evento.estado === 'Activo',
                'bg-amber-100 text-amber-800 border-amber-200': evento.estado === 'Pendiente',
                'bg-gray-100 text-gray-700 border-gray-200': evento.estado === 'Cerrado',
              }"
            >
              {{ evento.estado }}
            </span>
            <span
              v-if="evento.evento_padre"
              class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200"
            >
              Evento Padre
            </span>
          </div>

          <h1 class="text-2xl font-bold text-gray-900">{{ evento.nombre }}</h1>
          <p class="text-xs text-gray-500 flex items-center gap-1.5">
            <BuildingOfficeIcon class="w-4 h-4 text-gray-400" />
            <span>Dependencia: <b>{{ evento.dependencias || 'Sin dependencia especificada' }}</b></span>
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <!-- BOTÓN PRINCIPAL DESTACADO PARA VER USUARIOS -->
          <button
            type="button"
            @click="irAUsuarios"
            class="inline-flex items-center gap-2 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition shadow-sm ring-2 ring-blue-300"
          >
            <UserGroupIcon class="w-4 h-4 text-white" />
            <span>Ver Usuarios Asignados ({{ conteo_votantes.total }})</span>
          </button>

          <button
            type="button"
            @click="abrirModalVotantes"
            class="inline-flex items-center gap-1.5 py-2 px-3 border border-blue-300 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg text-xs transition"
            title="Abrir ventana modal flotante con los usuarios"
          >
            <ArrowsPointingOutIcon class="w-4 h-4" />
            <span>Modal Flotante</span>
          </button>

          <SecondaryLink :href="route('eventos.migrar.view', { destino: evento.id })" class="inline-flex items-center gap-1.5 shadow-xs">
            <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
            <span>Asignar Votantes</span>
          </SecondaryLink>

          <SecondaryLink :href="route('eventos.edit', evento.id)" class="inline-flex items-center gap-1.5 shadow-xs">
            <PencilIcon class="w-4 h-4 text-gray-600" />
            <span>Editar</span>
          </SecondaryLink>

          <SecondaryLink :href="route('eventos.index')" class="inline-flex items-center gap-1.5 shadow-xs">
            <ArrowLeftIcon class="w-4 h-4 text-gray-500" />
            <span>Volver</span>
          </SecondaryLink>
        </div>
      </div>

      <!-- Métricas Clave (KPIs) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Votantes Asignados (Click baja a la tabla de usuarios) -->
        <div
          @click="irAUsuarios"
          class="bg-white p-5 rounded-xl border border-blue-200 shadow-sm flex items-start gap-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition group ring-1 ring-blue-100"
          title="Haga clic para ver la tabla de usuarios asignados"
        >
          <div class="p-3 bg-blue-600 text-white rounded-lg shadow-xs group-hover:scale-105 transition transform">
            <UserGroupIcon class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="block text-xs font-bold text-blue-900 uppercase tracking-wide">Censo Asignado</span>
              <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded">Ver tabla ↓</span>
            </div>
            <span class="text-2xl font-black text-gray-900">{{ conteo_votantes.total }}</span>
            <span class="block text-[11px] text-green-700 font-medium mt-0.5">
              {{ conteo_votantes.activos }} activos • {{ conteo_votantes.pendientes }} pendientes
            </span>
          </div>
        </div>

        <!-- Votos Emitidos -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
            <CheckCircleIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Votos Emitidos</span>
            <span class="text-2xl font-black text-gray-900">{{ conteo_votos.total }}</span>
            <span class="block text-[11px] text-gray-500 font-medium mt-0.5">
              {{ conteo_votos.virtuales }} virtuales • {{ conteo_votos.presenciales }} presenciales
            </span>
          </div>
        </div>

        <!-- Acta de Inicio -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 rounded-lg" :class="actas_inicio.length > 0 ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-400'">
            <DocumentTextIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Acta de Inicio</span>
            <span class="text-lg font-bold" :class="actas_inicio.length > 0 ? 'text-purple-700' : 'text-gray-400'">
              {{ actas_inicio.length > 0 ? 'Registrada' : 'Sin Registrar' }}
            </span>
            <span class="block text-[11px] text-gray-500 mt-0.5">
              {{ actas_inicio.length > 0 ? actas_inicio.length + ' acta(s)' : 'Pendiente apertura' }}
            </span>
          </div>
        </div>

        <!-- Acta de Fin / Cierre -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-start gap-4">
          <div class="p-3 rounded-lg" :class="actas_fin.length > 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-400'">
            <DocumentCheckIcon class="w-6 h-6" />
          </div>
          <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Acta de Cierre</span>
            <span class="text-lg font-bold" :class="actas_fin.length > 0 ? 'text-indigo-700' : 'text-gray-400'">
              {{ actas_fin.length > 0 ? 'Registrada' : 'Sin Registrar' }}
            </span>
            <span class="block text-[11px] text-gray-500 mt-0.5">
              {{ actas_fin.length > 0 ? actas_fin.length + ' acta(s)' : 'Pendiente cierre' }}
            </span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN VISIBLE DIRECTA: TABLA DE USUARIOS ASIGNADOS AL EVENTO -->
      <div id="seccion-usuarios" class="bg-white rounded-xl border-2 border-blue-200 shadow-md p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-200">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
              <UserGroupIcon class="w-6 h-6" />
            </div>
            <div>
              <h2 class="text-base font-bold text-gray-900">
                Usuarios Asignados al Evento ({{ votantesPaginados.total }} en total)
              </h2>
              <p class="text-xs text-gray-500">
                Listado completo del censo con búsqueda, verificación de sufragio previo y paginación.
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="abrirModalVotantes"
              class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs"
            >
              <ArrowsPointingOutIcon class="w-4 h-4 text-gray-500" />
              <span>Ver en Modal Flotante</span>
            </button>

            <SecondaryLink :href="route('eventos.migrar.view', { destino: evento.id })" class="inline-flex items-center gap-1.5 text-xs py-1.5 px-3">
              <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
              <span>+ Migrar Más Votantes</span>
            </SecondaryLink>
          </div>
        </div>

        <!-- Botones de Acceso Rápido por Rol -->
        <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
          <button
            type="button"
            @click="filtroVotantesRol = 'todos'; cargarVotantes(1)"
            :class="filtroVotantesRol === 'todos' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300'"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
          >
            <UserGroupIcon class="w-4 h-4" />
            <span>Todos los Asignados ({{ conteo_votantes.total }})</span>
          </button>

          <button
            type="button"
            @click="filtroVotantesRol = 'candidatos'; cargarVotantes(1)"
            :class="filtroVotantesRol === 'candidatos' ? 'bg-amber-500 text-white shadow-md ring-2 ring-amber-300' : 'bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300'"
            class="px-3.5 py-1.5 rounded-lg text-xs font-black transition flex items-center gap-1.5"
          >
            <StarIcon class="w-4 h-4 text-amber-300 fill-amber-300" />
            <span>Ver Solo Candidatos ({{ conteo_votantes.candidatos }})</span>
          </button>

          <button
            type="button"
            @click="filtroVotantesRol = 'votantes'; cargarVotantes(1)"
            :class="filtroVotantesRol === 'votantes' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200'"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
          >
            <span>Solo Votantes Regulares ({{ conteo_votantes.solo_votantes }})</span>
          </button>
        </div>

        <!-- Controles de Filtro y Búsqueda -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs">
          <!-- Buscador -->
          <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <MagnifyingGlassIcon class="w-4 h-4" />
            </div>
            <input
              type="text"
              v-model="filtroVotantesSearch"
              @keyup.enter="cargarVotantes(1)"
              placeholder="Buscar por nombre, cédula o email..."
              class="pl-9 pr-8 py-2 w-full text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
            />
            <button
              v-if="filtroVotantesSearch"
              type="button"
              @click="limpiarFiltroSearch"
              class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- Filtros de Rol, Estado y Cantidad -->
          <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <!-- Filtro de Rol -->
            <div class="flex items-center gap-1.5">
              <span class="text-gray-500 font-medium">Rol:</span>
              <select
                v-model="filtroVotantesRol"
                @change="cargarVotantes(1)"
                class="py-1.5 pl-2.5 pr-7 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs font-medium"
              >
                <option value="todos">Todos (Votantes y Candidatos)</option>
                <option value="candidatos">★ Solo Candidatos ({{ conteo_votantes.candidatos }})</option>
                <option value="votantes">Solo Votantes ({{ conteo_votantes.solo_votantes }})</option>
              </select>
            </div>

            <!-- Filtro de Estado -->
            <div class="flex items-center gap-1.5">
              <span class="text-gray-500 font-medium">Estado:</span>
              <select
                v-model="filtroVotantesEstado"
                @change="cargarVotantes(1)"
                class="py-1.5 pl-2.5 pr-7 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
              >
                <option value="todos">Todos los estados</option>
                <option value="Activo">Solo Activos</option>
                <option value="Pendiente">Solo Pendientes</option>
                <option value="Rechazado">Solo Rechazados</option>
              </select>
            </div>

            <div class="flex items-center gap-1.5">
              <span class="text-gray-500 font-medium">Por pág.:</span>
              <select
                v-model="perPageVotantes"
                @change="cargarVotantes(1)"
                class="py-1.5 pl-2.5 pr-7 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
              >
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
              </select>
            </div>

            <button
              type="button"
              @click="cargarVotantes(votantesPaginados.current_page)"
              class="p-1.5 rounded-lg border border-gray-300 hover:bg-white text-gray-600 transition shadow-xs"
              title="Actualizar tabla"
            >
              <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': cargandoVotantes }" />
            </button>
          </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-xs">
          <div v-if="cargandoVotantes" class="py-16 text-center text-gray-500">
            <ArrowPathIcon class="w-8 h-8 mx-auto animate-spin text-blue-600 mb-2" />
            <p class="text-xs font-semibold">Consultando usuarios asignados...</p>
          </div>

          <div v-else-if="votantesPaginados.data && votantesPaginados.data.length > 0" class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead class="bg-gray-50 text-[11px] font-bold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                <tr>
                  <th class="py-3 px-3.5">#</th>
                  <th class="py-3 px-3.5">Nombre / Votante</th>
                  <th class="py-3 px-3.5">Identificación</th>
                  <th class="py-3 px-3.5">Tipo / Estamento</th>
                  <th class="py-3 px-3.5">Comuna / Sector</th>
                  <th class="py-3 px-3.5 text-center">Rol / Candidatura</th>
                  <th class="py-3 px-3.5 text-center">Estado</th>
                  <th class="py-3 px-3.5 text-center">Intentos</th>
                  <th class="py-3 px-3.5 text-center">¿Votó?</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="(item, idx) in votantesPaginados.data"
                  :key="'vot-main-' + item.id"
                  :class="item.candidato || item.es_candidato
                    ? 'bg-amber-50/70 hover:bg-amber-100/70 border-l-4 border-l-amber-500'
                    : 'hover:bg-slate-50 border-l-4 border-l-transparent'"
                  class="transition"
                >
                  <td class="py-3 px-3.5 font-mono text-gray-400 font-medium whitespace-nowrap">
                    <span v-if="item.candidato || item.es_candidato" class="text-amber-600 font-bold mr-1">★</span>
                    {{ (votantesPaginados.current_page - 1) * votantesPaginados.per_page + idx + 1 }}
                  </td>

                  <td class="py-3 px-3.5">
                    <div class="flex items-center gap-2">
                      <div class="font-bold text-gray-900">{{ item.votante?.nombre || 'Sin nombre' }}</div>
                      <span
                        v-if="item.candidato || item.es_candidato"
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-200 text-amber-900 border border-amber-300 shadow-2xs"
                      >
                        <StarIcon class="w-3 h-3 text-amber-700" />
                        <span>CANDIDATO</span>
                      </span>
                    </div>
                    <div class="text-[11px] text-gray-400">{{ item.votante?.email || 'Sin correo' }}</div>
                  </td>

                  <td class="py-3 px-3.5 font-mono text-slate-700 whitespace-nowrap">
                    <span class="text-[11px] text-gray-400 mr-1">{{ item.votante?.tipo_documento || 'CC' }}:</span>
                    <b>{{ item.votante?.identificacion || 'N/A' }}</b>
                  </td>

                  <td class="py-3 px-3.5 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                      {{ item.tipo || 'Votante' }}
                    </span>
                  </td>

                  <td class="py-3 px-3.5 whitespace-nowrap text-gray-600">
                    {{ item.comuna_nombre || 'N/A' }}
                  </td>

                  <!-- Rol y Evento Específico de Candidatura -->
                  <td class="py-3 px-3.5 text-center">
                    <div v-if="item.candidato || item.es_candidato" class="flex flex-col items-center gap-1 py-0.5">
                      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-400 shadow-xs">
                        <StarIcon class="w-3 h-3 text-amber-600 fill-amber-500" />
                        <span>Candidato</span>
                      </span>

                      <!-- Evento específico del que es candidato -->
                      <div class="flex flex-col items-center gap-0.5 mt-0.5 max-w-[220px]">
                        <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider">Candidato en:</span>
                        <template v-if="item.eventos_candidato && item.eventos_candidato.length > 0">
                          <span
                            v-for="(evNom, evIdx) in item.eventos_candidato"
                            :key="evIdx"
                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs truncate max-w-full"
                            :title="evNom"
                          >
                            {{ evNom }}
                          </span>
                        </template>
                        <span
                          v-else
                          class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 truncate max-w-full"
                        >
                          {{ evento.nombre }}
                        </span>
                      </div>
                    </div>

                    <div v-else class="flex flex-col items-center">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                        Votante
                      </span>
                      <span class="text-[10px] text-gray-300 mt-0.5">—</span>
                    </div>
                  </td>

                  <td class="py-3 px-3.5 text-center whitespace-nowrap">
                    <span
                      class="px-2 py-0.5 rounded-full text-[11px] font-bold border"
                      :class="{
                        'bg-green-100 text-green-800 border-green-200': item.estado === 'Activo',
                        'bg-amber-100 text-amber-800 border-amber-200': item.estado === 'Pendiente',
                        'bg-red-100 text-red-800 border-red-200': item.estado === 'Rechazado',
                      }"
                    >
                      {{ item.estado }}
                    </span>
                  </td>

                  <td class="py-3 px-3.5 text-center font-mono font-semibold text-gray-700">
                    {{ item.intentos ?? 0 }}
                  </td>

                  <td class="py-3 px-3.5 text-center whitespace-nowrap">
                    <span
                      class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                      :class="item.ya_voto ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'"
                    >
                      {{ item.ya_voto ? '✓ Sí votó' : 'Pendiente' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="py-14 text-center text-gray-400 bg-gray-50/50">
            <UserGroupIcon class="w-10 h-10 mx-auto text-gray-300 mb-2" />
            <p class="font-bold text-gray-700 text-sm">No se encontraron usuarios</p>
            <p class="text-xs text-gray-400 mt-1">
              No hay usuarios asignados a este evento o no coinciden con la búsqueda.
            </p>
          </div>

          <!-- Paginador -->
          <div class="px-5 py-3 bg-gray-50/80 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
            <div>
              Mostrando del <b>{{ votantesPaginados.from || 0 }}</b> al <b>{{ votantesPaginados.to || 0 }}</b> de <b>{{ votantesPaginados.total || 0 }}</b> usuarios
            </div>

            <div class="flex items-center gap-1.5">
              <button
                type="button"
                :disabled="!votantesPaginados.prev_page_url"
                @click="cargarVotantes(1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                « Primera
              </button>

              <button
                type="button"
                :disabled="!votantesPaginados.prev_page_url"
                @click="cargarVotantes(votantesPaginados.current_page - 1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                ‹ Anterior
              </button>

              <span class="px-3 py-1.5 font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-md">
                Pág. {{ votantesPaginados.current_page || 1 }} / {{ votantesPaginados.last_page || 1 }}
              </span>

              <button
                type="button"
                :disabled="!votantesPaginados.next_page_url"
                @click="cargarVotantes(votantesPaginados.current_page + 1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                Siguiente ›
              </button>

              <button
                type="button"
                :disabled="!votantesPaginados.next_page_url"
                @click="cargarVotantes(votantesPaginados.last_page)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                Última »
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Fila: Información Detallada y Censo General -->
      <div class="grid md:grid-cols-3 gap-6">
        <!-- Columna 1 y 2: Información General del Evento -->
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
          <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider pb-3 border-b border-gray-100 flex items-center gap-2">
            <InformationCircleIcon class="w-5 h-5 text-indigo-600" />
            Información General del Proceso
          </h2>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <span class="block text-xs font-semibold text-gray-400">Fecha y Hora de Inicio</span>
              <span class="text-sm font-bold text-gray-800 block mt-0.5">
                {{ formatearFechaLarga(evento.fecha_inicio) }}
              </span>
            </div>

            <div>
              <span class="block text-xs font-semibold text-gray-400">Fecha y Hora de Cierre</span>
              <span class="text-sm font-bold text-gray-800 block mt-0.5">
                {{ formatearFechaLarga(evento.fecha_fin) }}
              </span>
            </div>
          </div>

          <div>
            <span class="block text-xs font-semibold text-gray-400">Descripción del Evento</span>
            <p class="text-xs text-gray-700 mt-1 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-200">
              {{ evento.descripcion || 'Sin descripción registrada.' }}
            </p>
          </div>

          <div class="grid sm:grid-cols-2 gap-4 pt-2">
            <div>
              <span class="block text-xs font-semibold text-gray-400">Tipos / Estamentos Habilitados</span>
              <div class="flex flex-wrap gap-1 mt-1">
                <span
                  v-for="t in desglosarTipos(evento.tipos)"
                  :key="t"
                  class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200"
                >
                  {{ t }}
                </span>
                <span v-if="!evento.tipos || evento.tipos === 'NA'" class="text-xs text-gray-400">
                  Sin estamentos específicos (Abierto a todos)
                </span>
              </div>
            </div>

            <div>
              <span class="block text-xs font-semibold text-gray-400">Estructura Jerárquica</span>
              <div class="mt-1 text-xs">
                <span v-if="evento.evento_padre" class="text-purple-700 font-semibold">
                  ★ Es un Evento Padre (agrupa sub-eventos)
                </span>
                <span v-else-if="evento.padre" class="text-gray-700">
                  Sub-evento perteneciente al padre: <b>{{ evento.padre.nombre }}</b>
                </span>
                <span v-else class="text-gray-500">
                  Evento independiente
                </span>
              </div>
            </div>
          </div>

          <!-- Eventos Hijos si los tiene -->
          <div v-if="eventos_hijos && eventos_hijos.length > 0" class="pt-3 border-t border-gray-100">
            <span class="block text-xs font-bold text-gray-700 uppercase mb-2">Eventos Hijos Vinculados ({{ eventos_hijos.length }}):</span>
            <div class="grid sm:grid-cols-2 gap-2">
              <Link
                v-for="hijo in eventos_hijos"
                :key="'hijo-' + hijo.id"
                :href="route('eventos.show', hijo.id)"
                class="p-2.5 rounded-lg border border-purple-200 bg-purple-50/40 hover:bg-purple-100/60 transition flex items-center justify-between text-xs"
              >
                <span class="font-semibold text-purple-950">#{{ hijo.id }} - {{ hijo.nombre }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-white text-purple-700 border border-purple-200">
                  {{ hijo.estado }}
                </span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Columna 3: Desglose Estadístico del Censo -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
              <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                <UserGroupIcon class="w-5 h-5 text-blue-600" />
                Desglose Estadístico
              </h2>
              <button
                type="button"
                @click="irAUsuarios"
                class="text-xs text-blue-600 hover:text-blue-800 font-bold underline"
              >
                Ver tabla
              </button>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                <span class="text-gray-600">Votantes Regulares:</span>
                <span class="font-bold text-gray-800">{{ conteo_votantes.solo_votantes }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                <span class="text-gray-600">Candidatos Inscritos:</span>
                <span class="font-bold text-indigo-700">{{ conteo_votantes.candidatos }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs">
                <span class="text-emerald-800 font-medium">Usuarios 'Activos' (Habilitados):</span>
                <span class="font-bold text-emerald-800">{{ conteo_votantes.activos }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs">
                <span class="text-amber-800 font-medium">Usuarios 'Pendientes':</span>
                <span class="font-bold text-amber-800">{{ conteo_votantes.pendientes }}</span>
              </div>

              <div class="flex items-center justify-between p-2.5 rounded-lg bg-red-50 border border-red-200 text-xs">
                <span class="text-red-800 font-medium">Usuarios 'Rechazados':</span>
                <span class="font-bold text-red-800">{{ conteo_votantes.rechazados }}</span>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 space-y-2">
            <button
              type="button"
              @click="irAUsuarios"
              class="w-full justify-center inline-flex items-center gap-1.5 text-xs py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-xs"
            >
              <EyeIcon class="w-4 h-4" />
              <span>Ver Lista de Usuarios Asignados</span>
            </button>

            <SecondaryLink
              :href="route('eventos.migrar.view', { destino: evento.id })"
              class="w-full justify-center inline-flex items-center gap-1.5 text-xs py-2.5"
            >
              <ArrowPathIcon class="w-4 h-4 text-indigo-600" />
              <span>Migrar más votantes a este evento</span>
            </SecondaryLink>
          </div>
        </div>
      </div>

      <!-- Sección de Actas Registradas -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
          <div class="flex items-center gap-2">
            <DocumentDuplicateIcon class="w-5 h-5 text-indigo-600" />
            <h2 class="text-base font-bold text-gray-800">Historial y Registro de Actas</h2>
          </div>
          <span class="text-xs text-gray-500">
            Apertura, Cierre y Escrutinio electoral
          </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <!-- Actas de Inicio -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-purple-600"></span>
              Acta(s) de Inicio (Apertura)
            </h3>

            <div v-if="actas_inicio && actas_inicio.length > 0" class="space-y-2.5">
              <div
                v-for="acta in actas_inicio"
                :key="'inicio-' + acta.id"
                class="p-4 rounded-xl border border-purple-200 bg-purple-50/30 text-xs space-y-1.5"
              >
                <div class="flex items-center justify-between font-bold text-purple-900">
                  <span>Acta #{{ acta.id }} ({{ acta.modalidad || 'virtual' }})</span>
                  <span class="text-gray-500 font-normal">{{ formatearFechaLarga(acta.fecha_inicio) }}</span>
                </div>
                <div class="text-gray-600">
                  <b>Jurado/Responsable:</b> {{ acta.jurado?.nombre || 'Automática / Sistema' }}
                </div>
                <div v-if="acta.puesto_votacion" class="text-gray-600">
                  <b>Puesto de votación:</b> {{ acta.puesto_votacion }}
                </div>
                <div v-if="acta.comunas" class="text-gray-500 text-[11px]">
                  <b>Comunas:</b> {{ acta.comunas }}
                </div>
              </div>
            </div>
            <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
              No se ha registrado acta de inicio para este evento.
            </div>
          </div>

          <!-- Actas de Cierre -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
              Acta(s) de Fin (Cierre)
            </h3>

            <div v-if="actas_fin && actas_fin.length > 0" class="space-y-2.5">
              <div
                v-for="acta in actas_fin"
                :key="'fin-' + acta.id"
                class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 text-xs space-y-1.5"
              >
                <div class="flex items-center justify-between font-bold text-indigo-900">
                  <span>Acta #{{ acta.id }} ({{ acta.modalidad || 'virtual' }})</span>
                  <span class="text-gray-500 font-normal">{{ formatearFechaLarga(acta.fecha_cierre) }}</span>
                </div>
                <div class="text-gray-600">
                  <b>Jurado/Responsable:</b> {{ acta.jurado?.nombre || 'Automática / Sistema' }}
                </div>
                <div v-if="acta.puesto_votacion" class="text-gray-600">
                  <b>Puesto de votación:</b> {{ acta.puesto_votacion }}
                </div>
                <div v-if="acta.comunas" class="text-gray-500 text-[11px]">
                  <b>Comunas:</b> {{ acta.comunas }}
                </div>
              </div>
            </div>
            <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
              No se ha registrado acta de cierre para este evento.
            </div>
          </div>
        </div>

        <!-- Actas de Escrutinio -->
        <div class="pt-4 border-t border-gray-200 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
              Actas de Escrutinio ({{ actas_escrutinio ? actas_escrutinio.length : 0 }})
            </h3>
          </div>

          <div v-if="actas_escrutinio && actas_escrutinio.length > 0" class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-600 border-b border-gray-200">
                <tr>
                  <th class="py-2.5 px-3">ID</th>
                  <th class="py-2.5 px-3">Puesto / Comuna</th>
                  <th class="py-2.5 px-3">Fecha</th>
                  <th class="py-2.5 px-3 text-center">Total Votos</th>
                  <th class="py-2.5 px-3 text-center">Nulos</th>
                  <th class="py-2.5 px-3 text-center">Blanco</th>
                  <th class="py-2.5 px-3">Jurado / Coordinador</th>
                  <th class="py-2.5 px-3">Observaciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="acta in actas_escrutinio" :key="'escrutinio-' + acta.id" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-mono font-bold text-gray-700">#{{ acta.id }}</td>
                  <td class="py-2.5 px-3">
                    <span class="font-semibold block">{{ acta.puesto_votacion || 'General' }}</span>
                    <span class="text-[11px] text-gray-500">Comuna: {{ acta.comuna || 'N/A' }}</span>
                  </td>
                  <td class="py-2.5 px-3 whitespace-nowrap text-gray-600">
                    {{ formatearFechaLarga(acta.created_at || acta.fecha_evento) }}
                  </td>
                  <td class="py-2.5 px-3 text-center font-bold text-gray-900">
                    {{ acta.total_votantes ?? acta.total_ciudadanos ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-center text-red-600 font-semibold">
                    {{ acta.votos_nulos ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-center text-gray-600">
                    {{ acta.votos_blanco ?? 0 }}
                  </td>
                  <td class="py-2.5 px-3 text-gray-700">
                    {{ acta.jurado?.nombre || acta.cordinador?.name || 'N/A' }}
                  </td>
                  <td class="py-2.5 px-3 text-gray-500 max-w-xs truncate" :title="acta.observaciones">
                    {{ acta.observaciones || 'Sin observaciones' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-4 rounded-xl border border-dashed border-gray-300 text-center text-xs text-gray-400 bg-gray-50">
            No se han registrado actas de escrutinio para este evento de votación.
          </div>
        </div>
      </div>

      <!-- MODAL FLOTANTE (OPCIONAL EN VENTANA COMPLETA) -->
      <div
        v-if="modalVotantesAbierto"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="bg-white rounded-2xl max-w-6xl w-full shadow-2xl border border-gray-200 overflow-hidden flex flex-col max-h-[92vh]">
          <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/10 rounded-lg">
                <UserGroupIcon class="w-6 h-6 text-blue-300" />
              </div>
              <div>
                <h3 class="text-base font-bold">Censo de Usuarios Asignados</h3>
                <p class="text-xs text-blue-200">
                  Evento: <span class="font-semibold text-white">{{ evento.nombre }}</span> (Total: {{ votantesPaginados.total }} usuarios)
                </p>
              </div>
            </div>

            <button
              type="button"
              @click="cerrarModalVotantes"
              class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition"
              title="Cerrar ventana"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <div class="p-4 bg-slate-50 border-b border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="relative w-full sm:w-80">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <MagnifyingGlassIcon class="w-4 h-4" />
              </div>
              <input
                type="text"
                v-model="filtroVotantesSearch"
                @keyup.enter="cargarVotantes(1)"
                placeholder="Buscar por nombre, identificación..."
                class="pl-9 pr-8 py-2 w-full text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
              />
              <button
                v-if="filtroVotantesSearch"
                type="button"
                @click="limpiarFiltroSearch"
                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
              <!-- Filtro de Rol en Modal -->
              <select
                v-model="filtroVotantesRol"
                @change="cargarVotantes(1)"
                class="py-2 pl-3 pr-8 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs font-medium"
              >
                <option value="todos">Todos (Votantes y Candidatos)</option>
                <option value="candidatos">★ Solo Candidatos ({{ conteo_votantes.candidatos }})</option>
                <option value="votantes">Solo Votantes ({{ conteo_votantes.solo_votantes }})</option>
              </select>

              <select
                v-model="filtroVotantesEstado"
                @change="cargarVotantes(1)"
                class="py-2 pl-3 pr-8 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
              >
                <option value="todos">Todos los estados</option>
                <option value="Activo">Solo Activos</option>
                <option value="Pendiente">Solo Pendientes</option>
                <option value="Rechazado">Solo Rechazados</option>
              </select>

              <select
                v-model="perPageVotantes"
                @change="cargarVotantes(1)"
                class="py-2 pl-3 pr-8 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-xs"
              >
                <option :value="10">10 por pág.</option>
                <option :value="20">20 por pág.</option>
                <option :value="50">50 por pág.</option>
              </select>

              <button
                type="button"
                @click="cargarVotantes(votantesPaginados.current_page)"
                class="p-2 rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-600 transition"
                title="Recargar listado"
              >
                <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': cargandoVotantes }" />
              </button>
            </div>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div v-if="cargandoVotantes" class="py-16 text-center text-gray-500">
              <ArrowPathIcon class="w-8 h-8 mx-auto animate-spin text-blue-600 mb-2" />
              <p class="text-xs font-semibold">Consultando usuarios asignados...</p>
            </div>

            <div v-else-if="votantesPaginados.data && votantesPaginados.data.length > 0" class="overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-gray-100/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider sticky top-0 border-b border-gray-200">
                  <tr>
                    <th class="py-3 px-3.5">#</th>
                    <th class="py-3 px-3.5">Nombre / Votante</th>
                    <th class="py-3 px-3.5">Identificación</th>
                    <th class="py-3 px-3.5">Tipo / Estamento</th>
                    <th class="py-3 px-3.5">Comuna / Sector</th>
                    <th class="py-3 px-3.5 text-center">Rol / Candidatura</th>
                    <th class="py-3 px-3.5 text-center">Estado</th>
                    <th class="py-3 px-3.5 text-center">Intentos</th>
                    <th class="py-3 px-3.5 text-center">¿Votó?</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr
                    v-for="(item, idx) in votantesPaginados.data"
                    :key="'vot-modal-' + item.id"
                    :class="item.candidato || item.es_candidato
                      ? 'bg-amber-50/70 hover:bg-amber-100/70 border-l-4 border-l-amber-500'
                      : 'hover:bg-slate-50 border-l-4 border-l-transparent'"
                    class="transition"
                  >
                    <td class="py-3 px-3.5 font-mono text-gray-400 font-medium whitespace-nowrap">
                      <span v-if="item.candidato || item.es_candidato" class="text-amber-600 font-bold mr-1">★</span>
                      {{ (votantesPaginados.current_page - 1) * votantesPaginados.per_page + idx + 1 }}
                    </td>

                    <td class="py-3 px-3.5">
                      <div class="flex items-center gap-2">
                        <div class="font-bold text-gray-900">{{ item.votante?.nombre || 'Sin nombre' }}</div>
                        <span
                          v-if="item.candidato || item.es_candidato"
                          class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-200 text-amber-900 border border-amber-300 shadow-2xs"
                        >
                          <StarIcon class="w-3 h-3 text-amber-700" />
                          <span>CANDIDATO</span>
                        </span>
                      </div>
                      <div class="text-[11px] text-gray-400">{{ item.votante?.email || 'Sin correo' }}</div>
                    </td>

                    <td class="py-3 px-3.5 font-mono text-slate-700 whitespace-nowrap">
                      <span class="text-[11px] text-gray-400 mr-1">{{ item.votante?.tipo_documento || 'CC' }}:</span>
                      <b>{{ item.votante?.identificacion || 'N/A' }}</b>
                    </td>

                    <td class="py-3 px-3.5 whitespace-nowrap">
                      <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                        {{ item.tipo || 'Votante' }}
                      </span>
                    </td>

                    <td class="py-3 px-3.5 whitespace-nowrap text-gray-600">
                      {{ item.comuna_nombre || 'N/A' }}
                    </td>

                    <!-- Rol y Evento Específico de Candidatura en Modal -->
                    <td class="py-3 px-3.5 text-center">
                      <div v-if="item.candidato || item.es_candidato" class="flex flex-col items-center gap-1 py-0.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-400 shadow-xs">
                          <StarIcon class="w-3 h-3 text-amber-600 fill-amber-500" />
                          <span>Candidato</span>
                        </span>

                        <div class="flex flex-col items-center gap-0.5 mt-0.5 max-w-[220px]">
                          <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider">Candidato en:</span>
                          <template v-if="item.eventos_candidato && item.eventos_candidato.length > 0">
                            <span
                              v-for="(evNom, evIdx) in item.eventos_candidato"
                              :key="evIdx"
                              class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs truncate max-w-full"
                              :title="evNom"
                            >
                              {{ evNom }}
                            </span>
                          </template>
                          <span
                            v-else
                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 truncate max-w-full"
                          >
                            {{ evento.nombre }}
                          </span>
                        </div>
                      </div>

                      <div v-else class="flex flex-col items-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                          Votante
                        </span>
                        <span class="text-[10px] text-gray-300 mt-0.5">—</span>
                      </div>
                    </td>

                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                      <span
                        class="px-2 py-0.5 rounded-full text-[11px] font-bold border"
                        :class="{
                          'bg-green-100 text-green-800 border-green-200': item.estado === 'Activo',
                          'bg-amber-100 text-amber-800 border-amber-200': item.estado === 'Pendiente',
                          'bg-red-100 text-red-800 border-red-200': item.estado === 'Rechazado',
                        }"
                      >
                        {{ item.estado }}
                      </span>
                    </td>

                    <td class="py-3 px-3.5 text-center font-mono font-semibold text-gray-700">
                      {{ item.intentos ?? 0 }}
                    </td>

                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                      <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                        :class="item.ya_voto ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'"
                      >
                        {{ item.ya_voto ? '✓ Sí votó' : 'Pendiente' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="py-16 text-center text-gray-400 bg-gray-50/50">
              <UserGroupIcon class="w-10 h-10 mx-auto text-gray-300 mb-2" />
              <p class="font-bold text-gray-700 text-sm">No se encontraron usuarios</p>
              <p class="text-xs text-gray-400 mt-1">
                No hay usuarios que coincidan con la búsqueda.
              </p>
            </div>
          </div>

          <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
            <div>
              Mostrando del <b>{{ votantesPaginados.from || 0 }}</b> al <b>{{ votantesPaginados.to || 0 }}</b> de <b>{{ votantesPaginados.total || 0 }}</b> usuarios
            </div>

            <div class="flex items-center gap-1.5">
              <button
                type="button"
                :disabled="!votantesPaginados.prev_page_url"
                @click="cargarVotantes(1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                « Primera
              </button>

              <button
                type="button"
                :disabled="!votantesPaginados.prev_page_url"
                @click="cargarVotantes(votantesPaginados.current_page - 1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                ‹ Anterior
              </button>

              <span class="px-3 py-1.5 font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-md">
                Página {{ votantesPaginados.current_page || 1 }} de {{ votantesPaginados.last_page || 1 }}
              </span>

              <button
                type="button"
                :disabled="!votantesPaginados.next_page_url"
                @click="cargarVotantes(votantesPaginados.current_page + 1)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                Siguiente ›
              </button>

              <button
                type="button"
                :disabled="!votantesPaginados.next_page_url"
                @click="cargarVotantes(votantesPaginados.last_page)"
                class="px-2.5 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition"
              >
                Última »
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import axios from 'axios';
import {
  UserGroupIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  DocumentCheckIcon,
  InformationCircleIcon,
  BuildingOfficeIcon,
  PencilIcon,
  ArrowPathIcon,
  ArrowLeftIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  XMarkIcon,
  MagnifyingGlassIcon,
  ArrowsPointingOutIcon,
  StarIcon,
} from '@heroicons/vue/24/solid';

const props = defineProps({
  evento: {
    type: Object,
    required: true,
  },
  conteo_votantes: {
    type: Object,
    default: () => ({
      total: 0,
      activos: 0,
      pendientes: 0,
      rechazados: 0,
      candidatos: 0,
      solo_votantes: 0,
    }),
  },
  conteo_votos: {
    type: Object,
    default: () => ({
      total: 0,
      virtuales: 0,
      presenciales: 0,
    }),
  },
  actas_inicio: {
    type: Array,
    default: () => [],
  },
  actas_fin: {
    type: Array,
    default: () => [],
  },
  actas_escrutinio: {
    type: Array,
    default: () => [],
  },
  eventos_hijos: {
    type: Array,
    default: () => [],
  },
  votantes_iniciales: {
    type: Object,
    default: () => ({
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10,
      from: 0,
      to: 0,
    }),
  },
});

const breadcrumbLinks = [
  { url: '/eventos', text: 'Listado de eventos' },
  { url: '', text: 'Detalle: ' + props.evento.nombre },
];

// Estado de los Votantes Asignados (inicializado con la primera página enviada por el servidor)
const modalVotantesAbierto = ref(false);
const cargandoVotantes = ref(false);
const votantesPaginados = ref(props.votantes_iniciales);
const filtroVotantesSearch = ref('');
const filtroVotantesEstado = ref('todos');
const filtroVotantesRol = ref('todos');
const perPageVotantes = ref(10);

const irAUsuarios = () => {
  const el = document.getElementById('seccion-usuarios');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};

const abrirModalVotantes = () => {
  modalVotantesAbierto.value = true;
  document.body.style.overflow = 'hidden';
};

const cerrarModalVotantes = () => {
  modalVotantesAbierto.value = false;
  document.body.style.overflow = null;
};

const limpiarFiltroSearch = () => {
  filtroVotantesSearch.value = '';
  cargarVotantes(1);
};

const cargarVotantes = async (pagina = 1) => {
  cargandoVotantes.value = true;
  try {
    const response = await axios.get(route('eventos.votantes.listado', props.evento.id), {
      params: {
        page: pagina,
        search: filtroVotantesSearch.value,
        estado: filtroVotantesEstado.value,
        rol: filtroVotantesRol.value,
        per_page: perPageVotantes.value,
      },
    });
    votantesPaginados.value = response.data;
  } catch (error) {
    console.error('Error cargando usuarios del evento:', error);
  } finally {
    cargandoVotantes.value = false;
  }
};

const manejarEscape = (e) => {
  if (e.key === 'Escape' && modalVotantesAbierto.value) {
    cerrarModalVotantes();
  }
};

onMounted(() => {
  document.addEventListener('keydown', manejarEscape);
});

onUnmounted(() => {
  document.removeEventListener('keydown', manejarEscape);
  document.body.style.overflow = null;
});

const desglosarTipos = (tipos) => {
  if (!tipos || tipos === 'NA') return [];
  return tipos.split('|').map((t) => t.trim()).filter(Boolean);
};

const formatearFechaLarga = (fecha) => {
  if (!fecha) return 'Sin fecha';
  try {
    const d = new Date(fecha);
    if (isNaN(d.getTime())) return fecha;
    return d.toLocaleDateString('es-CO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch (e) {
    return fecha;
  }
};
</script>
