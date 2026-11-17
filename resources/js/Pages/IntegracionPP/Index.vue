<template>
  <Head title="Integración Presupuesto Participativo (GREEN)" />

  <AuthenticatedLayout :breadcrumbs="[
    { url: '/dashboard', text: 'Inicio' },
    { url: route('eventos.index'), text: 'Eventos' },
    { url: '', text: 'Integración Presupuesto Participativo' }
  ]">

    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Encabezado Institucional -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl shadow-xl overflow-hidden border-b-4 border-[#00A859]">
        <div class="p-6 md:p-8 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00A859]/20 text-emerald-300 border border-[#00A859]/30">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              API REST v1 · GREEN / Gobtech
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
              Integración Presupuesto Participativo
            </h1>
            <p class="text-sm text-slate-300 max-w-2xl">
              Sincronización bidireccional entre <strong>Voto Electrónico (VOTA YA)</strong> y el módulo de <strong>Presupuesto Participativo (GREEN)</strong>.
            </p>
          </div>

          <!-- Tarjeta de Estado de Conexión -->
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 w-full md:w-auto text-xs space-y-2">
            <div class="flex items-center justify-between gap-3">
              <span class="text-slate-300 font-semibold">Estado de Conexión:</span>
              <span
                :class="[
                  'px-2.5 py-0.5 rounded-full font-bold',
                  conexion_ok ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'
                ]"
              >
                {{ conexion_ok ? 'Conectado a GREEN' : 'Sin Conexión' }}
              </span>
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
              <div>Base: {{ config.base_url }}</div>
              <div>Token: {{ config.token_resumen }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas de Éxito / Error -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
        <CheckCircleIcon class="h-5 w-5 text-emerald-600 shrink-0" />
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <div v-if="$page.props.errors?.error" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-center gap-2">
        <ExclamationTriangleIcon class="h-5 w-5 text-rose-600 shrink-0" />
        <span>{{ $page.props.errors.error }}</span>
      </div>

      <!-- Navegación por Pestañas -->
      <div class="flex border-b border-gray-200 gap-2 overflow-x-auto pb-1 text-sm font-semibold">
        <button
          @click="activeTab = 'convocatorias'"
          :class="[
            'px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap',
            activeTab === 'convocatorias' ? 'bg-emerald-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <BuildingLibraryIcon class="h-4 w-4" />
          Convocatorias GREEN ({{ convocatorias.length }})
        </button>

        <button
          @click="activeTab = 'proyectos'"
          :class="[
            'px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap',
            activeTab === 'proyectos' ? 'bg-emerald-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <DocumentDuplicateIcon class="h-4 w-4" />
          Proyectos Sincronizados ({{ proyectos_sincronizados.total || 0 }})
        </button>

        <button
          @click="activeTab = 'logs'"
          :class="[
            'px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap',
            activeTab === 'logs' ? 'bg-emerald-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <ClockIcon class="h-4 w-4" />
          Logs y Auditoría de Despachos
        </button>

        <button
          @click="activeTab = 'homologacion'"
          :class="[
            'px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap',
            activeTab === 'homologacion' ? 'bg-emerald-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <MapPinIcon class="h-4 w-4" />
          Homologación Territorial ({{ homologaciones.length }})
        </button>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 1: CONVOCATORIAS DISPONIBLES EN GREEN            -->
      <!-- ======================================================== -->
      <div v-if="activeTab === 'convocatorias'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-gray-900">Convocatorias Expuestas por GREEN</h2>
              <p class="text-xs text-gray-500">API de solo lectura consumida mediante GET con token Bearer Sanctum.</p>
            </div>
            <button
              @click="recargarConvocatorias"
              class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center gap-1.5"
            >
              <ArrowPathIcon class="h-4 w-4" />
              Refrescar API
            </button>
          </div>

          <div v-if="convocatorias.length === 0" class="p-8 text-center text-gray-500 text-sm">
            <InformationCircleIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p>No se encontraron convocatorias devueltas por GREEN o el endpoint está en preparación.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                <tr>
                  <th class="py-3 px-4">ID</th>
                  <th class="py-3 px-4">Convocatoria</th>
                  <th class="py-3 px-4">Tipo</th>
                  <th class="py-3 px-4">Estado</th>
                  <th class="py-3 px-4">Fechas Apertura / Cierre</th>
                  <th class="py-3 px-4 text-center">Comunas</th>
                  <th class="py-3 px-4 text-center">Postulaciones</th>
                  <th class="py-3 px-4 text-right">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr v-for="c in convocatorias" :key="c.id" class="hover:bg-gray-50/60">
                  <td class="py-3 px-4 font-mono font-bold text-slate-900">#{{ c.id }}</td>
                  <td class="py-3 px-4 font-semibold text-gray-900">
                    <div>{{ c.nombre }}</div>
                    <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ c.descripcion || 'Sin descripción' }}</div>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">
                      {{ c.tipo?.nombre || c.tipo?.codigo || 'N/A' }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                        c.estado?.codigo === 'ABIERTA' ? 'bg-emerald-100 text-emerald-800' :
                        c.estado?.codigo === 'PUBLICADA' ? 'bg-blue-100 text-blue-800' :
                        'bg-gray-100 text-gray-700'
                      ]"
                    >
                      {{ c.estado?.nombre || c.estado?.codigo || 'N/A' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-mono text-[11px]">
                    <div>Inicio: {{ c.fecha_apertura || 'N/A' }}</div>
                    <div>Cierre: {{ c.fecha_cierre || 'N/A' }}</div>
                  </td>
                  <td class="py-3 px-4 text-center font-bold">
                    {{ c.comunas ? c.comunas.length : 0 }}
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="font-black text-emerald-700 text-sm">{{ c.total_postulaciones || 0 }}</span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="abrirModalSincronizar(c)"
                      class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 ml-auto"
                    >
                      <ArrowDownTrayIcon class="h-3.5 w-3.5" />
                      Sincronizar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 2: PROYECTOS SINCRONIZADOS                       -->
      <!-- ======================================================== -->
      <div v-else-if="activeTab === 'proyectos'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">
              Proyectos con Identificador Original de GREEN
            </h2>
            <span class="text-xs text-gray-500 font-medium">
              Total: {{ proyectos_sincronizados.total || 0 }} proyectos
            </span>
          </div>

          <div v-if="!proyectos_sincronizados.data || proyectos_sincronizados.data.length === 0" class="p-8 text-center text-gray-500 text-sm">
            <p>Aún no se han importado proyectos desde la API de GREEN. Use la pestaña <strong>Convocatorias GREEN</strong> para sincronizar.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                <tr>
                  <th class="py-3 px-4">ID Local</th>
                  <th class="py-3 px-4">ID GREEN</th>
                  <th class="py-3 px-4">Tarjetón #</th>
                  <th class="py-3 px-4">Detalle del Proyecto</th>
                  <th class="py-3 px-4">Comuna / Subtipo</th>
                  <th class="py-3 px-4">Estado GREEN</th>
                  <th class="py-3 px-4">Estado Vota Ya</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr v-for="p in proyectos_sincronizados.data" :key="p.id" class="hover:bg-gray-50">
                  <td class="py-3 px-4 font-mono font-bold text-gray-900">#{{ p.id }}</td>
                  <td class="py-3 px-4 font-mono font-black text-emerald-700">
                    #{{ p.id_postulacion_green }}
                  </td>
                  <td class="py-3 px-4 font-mono font-bold text-center">
                    {{ p.numero_tarjeton }}
                  </td>
                  <td class="py-3 px-4 font-semibold text-gray-900">
                    <div>{{ p.detalle }}</div>
                    <div class="text-[11px] text-gray-400 truncate max-w-sm">{{ p.descripcion }}</div>
                  </td>
                  <td class="py-3 px-4 text-gray-600">
                    {{ p.parametro_detalle?.detalle || ('Comuna ' + p.subtipo) }}
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                      {{ p.estado_green || 'N/A' }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <span :class="['px-2 py-0.5 rounded text-[10px] font-bold', p.estado == 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800']">
                      {{ p.estado == 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 3: LOGS Y AUDITORÍA DE DESPACHOS                 -->
      <!-- ======================================================== -->
      <div v-else-if="activeTab === 'logs'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-gray-900">Auditoría de Transacciones con GREEN</h2>
              <p class="text-xs text-gray-500">Historial de ingesta, webhooks y envíos de resultados con control de reintentos.</p>
            </div>
            <button
              @click="abrirModalEnviarGanadores"
              class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-1.5"
            >
              <PaperAirplaneIcon class="h-3.5 w-3.5" />
              Probar Envío de Ganadores
            </button>
          </div>

          <div v-if="!logs.data || logs.data.length === 0" class="p-8 text-center text-gray-500 text-sm">
            <p>No se registran transacciones aún.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                <tr>
                  <th class="py-3 px-4">Fecha</th>
                  <th class="py-3 px-4">Tipo</th>
                  <th class="py-3 px-4">Convocatoria</th>
                  <th class="py-3 px-4">Método / Endpoint</th>
                  <th class="py-3 px-4 text-center">Status</th>
                  <th class="py-3 px-4 text-center">Estado</th>
                  <th class="py-3 px-4 text-center">Intentos</th>
                  <th class="py-3 px-4 text-right">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr v-for="l in logs.data" :key="l.id" class="hover:bg-gray-50">
                  <td class="py-3 px-4 font-mono text-[11px] text-gray-500">
                    {{ formatearFecha(l.created_at) }}
                  </td>
                  <td class="py-3 px-4">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold',
                        l.tipo === 'OUTBOUND_RESULTADOS' ? 'bg-purple-100 text-purple-800' :
                        l.tipo === 'WEBHOOK' ? 'bg-amber-100 text-amber-800' :
                        'bg-sky-100 text-sky-800'
                      ]"
                    >
                      {{ l.tipo }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-mono font-bold">
                    {{ l.id_convocatoria_green ? '#' + l.id_convocatoria_green : 'N/A' }}
                  </td>
                  <td class="py-3 px-4 font-mono text-[11px]">
                    <span class="font-bold text-slate-800">{{ l.metodo }}</span>
                    <span class="text-gray-500 ml-1">{{ l.endpoint }}</span>
                  </td>
                  <td class="py-3 px-4 text-center font-mono font-bold">
                    <span :class="l.http_status === 200 ? 'text-emerald-700' : 'text-rose-600'">
                      {{ l.http_status || 'TIMEOUT' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                        l.estado === 'EXITOSO' ? 'bg-emerald-100 text-emerald-800' :
                        l.estado === 'REINTENTANDO' ? 'bg-amber-100 text-amber-800 animate-pulse' :
                        'bg-rose-100 text-rose-800'
                      ]"
                    >
                      {{ l.estado }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center font-bold">
                    {{ l.intentos }} / {{ l.max_intentos }}
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      v-if="l.estado !== 'EXITOSO' && l.tipo === 'OUTBOUND_RESULTADOS'"
                      @click="reintentarLog(l.id)"
                      class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-bold shadow-sm"
                    >
                      Reintentar
                    </button>
                    <span v-else class="text-gray-400 italic text-[11px]">N/A</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PESTAÑA 4: HOMOLOGACIÓN TERRITORIAL                      -->
      <!-- ======================================================== -->
      <div v-else-if="activeTab === 'homologacion'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Mapeo de Comunas y Corregimientos</h2>
            <p class="text-xs text-gray-500">Relación entre la división territorial de GREEN (dir_comunas) y VOTA YA (parametros_detalle com01).</p>
          </div>

          <div v-if="homologaciones.length === 0" class="p-8 text-center text-gray-500 text-sm">
            <p>Las comunas se registrarán automáticamente a medida que se sincronicen convocatorias de GREEN.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                <tr>
                  <th class="py-3 px-4">ID GREEN</th>
                  <th class="py-3 px-4">Comuna GREEN</th>
                  <th class="py-3 px-4">Tipo GREEN</th>
                  <th class="py-3 px-4">Grupo</th>
                  <th class="py-3 px-4">ID VOTA YA</th>
                  <th class="py-3 px-4">Comuna VOTA YA</th>
                  <th class="py-3 px-4 text-center">Estado Homologación</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr v-for="h in homologaciones" :key="h.id" class="hover:bg-gray-50">
                  <td class="py-3 px-4 font-mono font-bold text-slate-800">#{{ h.green_comuna_id }}</td>
                  <td class="py-3 px-4 font-semibold text-gray-900">{{ h.green_nombre }}</td>
                  <td class="py-3 px-4">{{ h.green_tipo || 'Urbano' }}</td>
                  <td class="py-3 px-4 font-mono">{{ h.green_grupo || 1 }}</td>
                  <td class="py-3 px-4 font-mono font-bold text-emerald-700">
                    {{ h.vota_comuna_id ? '#' + h.vota_comuna_id : 'Pendiente' }}
                  </td>
                  <td class="py-3 px-4 font-medium text-gray-900">
                    {{ h.comuna_vota?.detalle || h.vota_nombre || 'Sin asociar' }}
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase',
                        h.vota_comuna_id ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                      ]"
                    >
                      {{ h.vota_comuna_id ? 'Homologada' : 'Por Homologar' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL: SINCRONIZAR CONVOCATORIA -->
    <div v-if="modalSincronizarVisible" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden">
        <div class="bg-slate-900 p-4 text-white flex justify-between items-center border-b-2 border-emerald-500">
          <h3 class="font-bold text-sm">Sincronizar Convocatoria GREEN #{{ convocatoriaSeleccionada?.id }}</h3>
          <button @click="modalSincronizarVisible = false" class="text-gray-400 hover:text-white font-bold">&times;</button>
        </div>
        <form @submit.prevent="ejecutarSincronizacion" class="p-6 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Nombre Convocatoria</label>
            <div class="p-2.5 bg-gray-50 border rounded-xl text-gray-800 font-semibold">
              {{ convocatoriaSeleccionada?.nombre }}
            </div>
          </div>

          <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Vincular a Evento Hijo (Vigencia de PP)</label>
            <select v-model="formSincronizar.id_evento_padre" class="w-full text-xs rounded-xl border-gray-300">
              <option :value="null">-- Crear/Ubicar Automáticamente la Vigencia en hash_eventos_hijos --</option>
              <option v-for="ev in eventos_padre" :key="ev.id" :value="ev.id">
                [ID {{ ev.id }}] {{ ev.nombre }} ({{ ev.estado }})
              </option>
            </select>
            <p class="text-[11px] text-gray-500 mt-1">
              Los proyectos se vincularán a este Evento Hijo de la Vigencia a través de la tabla puente <code>hash_proyectos</code>, y la comuna se asignará en el campo <code>subtipo</code> de cada proyecto.
            </p>
          </div>

          <div class="pt-3 border-t flex justify-end gap-2">
            <button type="button" @click="modalSincronizarVisible = false" class="px-4 py-2 text-gray-600 font-bold">Cancelar</button>
            <button type="submit" :disabled="procesando" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-md disabled:opacity-50">
              {{ procesando ? 'Sincronizando...' : 'Iniciar Sincronización' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ENVIAR GANADORES A GREEN -->
    <div v-if="modalEnviarGanadoresVisible" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden">
        <div class="bg-slate-900 p-4 text-white flex justify-between items-center border-b-2 border-emerald-500">
          <h3 class="font-bold text-sm">Enviar Proyectos Ganadores a GREEN</h3>
          <button @click="modalEnviarGanadoresVisible = false" class="text-gray-400 hover:text-white font-bold">&times;</button>
        </div>
        <form @submit.prevent="ejecutarEnvioGanadores" class="p-6 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">ID Convocatoria GREEN (*)</label>
            <input type="number" v-model="formEnvio.id_convocatoria" required placeholder="Ej: 12" class="w-full text-xs rounded-xl border-gray-300" />
          </div>

          <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">ID Evento Local VOTA YA (*)</label>
            <input type="number" v-model="formEnvio.id_evento" required placeholder="Ej: 26" class="w-full text-xs rounded-xl border-gray-300" />
          </div>

          <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Número de Proyectos Ganadores (Top)</label>
            <input type="number" min="1" max="5" v-model="formEnvio.top_ganadores" class="w-full text-xs rounded-xl border-gray-300" />
            <p class="text-[11px] text-gray-500 mt-1">Se seleccionarán los más votados del evento que posean ID original de GREEN.</p>
          </div>

          <div class="pt-3 border-t flex justify-end gap-2">
            <button type="button" @click="modalEnviarGanadoresVisible = false" class="px-4 py-2 text-gray-600 font-bold">Cancelar</button>
            <button type="submit" :disabled="procesando" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-md disabled:opacity-50">
              {{ procesando ? 'Enviando...' : 'Despachar Resultados' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  BuildingLibraryIcon,
  DocumentDuplicateIcon,
  ClockIcon,
  MapPinIcon,
  ArrowPathIcon,
  ArrowDownTrayIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  conexion_ok: Boolean,
  conexion_error: String,
  convocatorias: Array,
  proyectos_sincronizados: Object,
  logs: Object,
  homologaciones: Array,
  eventos_padre: Array,
  config: Object,
});

const activeTab = ref('convocatorias');
const modalSincronizarVisible = ref(false);
const modalEnviarGanadoresVisible = ref(false);
const convocatoriaSeleccionada = ref(null);
const procesando = ref(false);

const formSincronizar = ref({
  id_convocatoria: null,
  id_evento_padre: null,
});

const formEnvio = ref({
  id_convocatoria: null,
  id_evento: null,
  top_ganadores: 1,
});

const recargarConvocatorias = () => {
  router.reload({ only: ['convocatorias', 'conexion_ok', 'conexion_error'] });
};

const abrirModalSincronizar = (c) => {
  convocatoriaSeleccionada.value = c;
  formSincronizar.value.id_convocatoria = c.id;
  formSincronizar.value.id_evento_padre = null;
  modalSincronizarVisible.value = true;
};

const ejecutarSincronizacion = () => {
  procesando.value = true;
  router.post(route('pp-integracion.sincronizar'), formSincronizar.value, {
    preserveScroll: true,
    onFinish: () => {
      procesando.value = false;
      modalSincronizarVisible.value = false;
    }
  });
};

const abrirModalEnviarGanadores = () => {
  formEnvio.value = {
    id_convocatoria: null,
    id_evento: null,
    top_ganadores: 1,
  };
  modalEnviarGanadoresVisible.value = true;
};

const ejecutarEnvioGanadores = () => {
  procesando.value = true;
  router.post(route('pp-integracion.enviar-ganadores'), formEnvio.value, {
    preserveScroll: true,
    onFinish: () => {
      procesando.value = false;
      modalEnviarGanadoresVisible.value = false;
    }
  });
};

const reintentarLog = (idLog) => {
  router.post(route('pp-integracion.reintentar', idLog), {}, {
    preserveScroll: true,
  });
};

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return '';
  return new Date(fechaStr).toLocaleString('es-CO');
};
</script>

