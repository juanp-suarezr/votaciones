<template>
  <Head title="Migrar / Asignar Votantes entre Eventos" />

  <AuthenticatedLayout :breadCrumbLinks="breadcrumbLinks">
    <template #header>
      Migrar o Asignar Usuarios entre Eventos
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Banner explicativo principal -->
      <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-md">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-white/10 rounded-lg">
            <ArrowPathIcon class="w-8 h-8 text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold">Asignación y Migración de Censo Electoral</h2>
            <p class="text-blue-100 text-sm mt-1 leading-relaxed">
              Esta herramienta le permite vincular masivamente los usuarios de un evento anterior a un nuevo evento de votación sin necesidad de volver a importar archivos Excel.
              Todos los filtros vienen configurados por defecto en <b>"Todos"</b> para facilitar la clonación completa del censo electoral.
            </p>
          </div>
        </div>
      </div>

      <!-- Alerta de resumen exitoso post-ejecución -->
      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-50 border border-green-300 rounded-xl p-5 shadow-sm">
        <div class="flex items-start gap-3">
          <CheckCircleIcon class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <h3 class="font-semibold text-green-900">Operación Completada Exitosamente</h3>
            <p class="text-green-800 text-sm mt-1">{{ $page.props.flash.success }}</p>
            <div v-if="$page.props.flash.resumen" class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-gray-500 uppercase font-medium">Procesados</span>
                <span class="text-lg font-bold text-gray-800">{{ $page.props.flash.resumen.total }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-green-600 uppercase font-medium">Asignados</span>
                <span class="text-lg font-bold text-green-700">{{ $page.props.flash.resumen.insertados }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-amber-600 uppercase font-medium">Omitidos</span>
                <span class="text-lg font-bold text-amber-700">{{ $page.props.flash.resumen.omitidos }}</span>
              </div>
              <div class="bg-white rounded-lg p-2.5 border border-green-200 text-center">
                <span class="block text-xs text-blue-600 uppercase font-medium">Actualizados</span>
                <span class="text-lg font-bold text-blue-700">{{ $page.props.flash.resumen.actualizados }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="confirmarEnvio">
        <!-- Paso 1 y 2: Selección de Eventos -->
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Tarjeta Evento Origen -->
          <div class="bg-white border rounded-xl shadow-sm p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between pb-3 border-b mb-4">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm">1</span>
                  <h3 class="text-base font-bold text-gray-800">Evento de Origen</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">Desde donde se copian</span>
              </div>

              <div>
                <InputLabel for="evento_origen_id" value="Selecciona el evento origen *" />
                <select
                  id="evento_origen_id"
                  v-model="form.evento_origen_id"
                  @change="cargarEstadisticas"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                  <option value="">-- Seleccionar evento origen --</option>
                  <option v-for="ev in eventos" :key="'orig-' + ev.id" :value="ev.id">
                    #{{ ev.id }} - {{ ev.nombre }} ({{ ev.estado }})
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.evento_origen_id" />
              </div>

              <!-- Estadísticas del evento de origen -->
              <div v-if="cargandoStats" class="mt-4 p-4 text-center text-gray-500 bg-gray-50 rounded-lg border">
                <ArrowPathIcon class="w-5 h-5 mx-auto animate-spin text-blue-600 mb-1" />
                <span class="text-xs">Consultando censo y tipos del evento...</span>
              </div>

              <div v-else-if="statsOrigen" class="mt-4 bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-3">
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                  <UserGroupIcon class="w-4 h-4 text-slate-500" />
                  Censo detectado en origen
                </p>

                <div class="grid grid-cols-3 gap-2 text-center">
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-gray-500 font-medium">Total</span>
                    <span class="text-base font-bold text-gray-800">{{ statsOrigen.total }}</span>
                  </div>
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-green-600 font-medium">Activos</span>
                    <span class="text-base font-bold text-green-700">{{ statsOrigen.activos }}</span>
                  </div>
                  <div class="bg-white p-2 rounded border border-slate-200">
                    <span class="block text-xs text-amber-600 font-medium">Pendientes</span>
                    <span class="text-base font-bold text-amber-700">{{ statsOrigen.pendientes }}</span>
                  </div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-600 pt-1 border-t border-slate-200">
                  <span>Solo Votantes: <b>{{ statsOrigen.solo_votantes }}</b></span>
                  <span>Candidatos: <b>{{ statsOrigen.candidatos }}</b></span>
                  <span>Rechazados: <b>{{ statsOrigen.rechazados }}</b></span>
                </div>

                <!-- Aviso sobre comunas -->
                <div v-if="!statsOrigen.tiene_comunas" class="bg-amber-50 border border-amber-200 rounded p-2 text-[11px] text-amber-800 flex items-start gap-1.5">
                  <InformationCircleIcon class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                  <span>Este evento no maneja división por comunas. Todos los votantes se transferirán sin restricción territorial.</span>
                </div>
              </div>
            </div>

            <div v-if="eventoOrigenSeleccionado" class="mt-4 pt-3 border-t text-xs text-gray-500 space-y-1">
              <div><b>Fechas:</b> {{ eventoOrigenSeleccionado.fecha_inicio }} &rarr; {{ eventoOrigenSeleccionado.fecha_fin }}</div>
              <div><b>Tipos configurados:</b> {{ eventoOrigenSeleccionado.tipos || 'Sin tipos específicos' }}</div>
            </div>
          </div>

          <!-- Tarjeta Evento Destino -->
          <div class="bg-white border rounded-xl shadow-sm p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between pb-3 border-b mb-4">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm">2</span>
                  <h3 class="text-base font-bold text-gray-800">Evento de Destino</h3>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full">Hacia donde se asignan</span>
              </div>

              <div>
                <div class="flex items-center justify-between">
                  <InputLabel for="evento_destino_id" value="Selecciona el nuevo evento *" />
                  <Link :href="route('eventos.create')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    + Crear nuevo evento
                  </Link>
                </div>
                <select
                  id="evento_destino_id"
                  v-model="form.evento_destino_id"
                  @change="cargarEstadisticas"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                  <option value="">-- Seleccionar evento destino --</option>
                  <option
                    v-for="ev in eventos"
                    :key="'dest-' + ev.id"
                    :value="ev.id"
                    :disabled="ev.id === form.evento_origen_id"
                  >
                    #{{ ev.id }} - {{ ev.nombre }} ({{ ev.estado }})
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.evento_destino_id" />
              </div>

              <!-- Impacto en vivo -->
              <div v-if="statsOrigen && statsOrigen.ya_en_destino !== null && form.evento_destino_id" class="mt-4 bg-indigo-50/60 border border-indigo-200 rounded-lg p-4 space-y-3">
                <p class="text-xs font-bold text-indigo-900 uppercase tracking-wide flex items-center gap-1.5">
                  <DocumentDuplicateIcon class="w-4 h-4 text-indigo-600" />
                  Previsualización en destino
                </p>
                <div class="grid grid-cols-2 gap-2 text-center">
                  <div class="bg-white p-2.5 rounded border border-indigo-200">
                    <span class="block text-xs text-amber-600 font-medium">Ya registrados en destino</span>
                    <span class="text-base font-bold text-amber-700">{{ statsOrigen.ya_en_destino }}</span>
                  </div>
                  <div class="bg-white p-2.5 rounded border border-indigo-200">
                    <span class="block text-xs text-indigo-700 font-medium">Nuevos a asignar</span>
                    <span class="text-base font-bold text-indigo-800">{{ statsOrigen.nuevos }}</span>
                  </div>
                </div>

                <!-- Comparativa de Tipos coincidentes -->
                <div v-if="statsOrigen.tipos_origen && statsOrigen.tipos_origen.length > 0" class="pt-2 border-t border-indigo-200 text-xs">
                  <div class="font-semibold text-indigo-950 mb-1">Compatibilidad de Tipos:</div>
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="tipo in statsOrigen.tipos_origen"
                      :key="'badge-' + tipo"
                      class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                      :class="esTipoCompatible(tipo) ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                    >
                      {{ tipo }} {{ esTipoCompatible(tipo) ? '✓' : '' }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-else-if="form.evento_origen_id && !form.evento_destino_id" class="mt-4 p-4 text-center text-gray-400 bg-gray-50 rounded-lg border border-dashed text-xs">
                Selecciona el evento de destino para calcular votantes duplicados y verificar compatibilidad de tipos.
              </div>
            </div>

            <div v-if="eventoDestinoSeleccionado" class="mt-4 pt-3 border-t text-xs text-gray-500 space-y-1">
              <div><b>Fechas:</b> {{ eventoDestinoSeleccionado.fecha_inicio }} &rarr; {{ eventoDestinoSeleccionado.fecha_fin }}</div>
              <div><b>Tipos permitidos:</b> {{ eventoDestinoSeleccionado.tipos || 'Sin tipos específicos (acepta todos)' }}</div>
            </div>
          </div>
        </div>

        <!-- Paso 3: Filtros Explicados -->
        <div class="bg-white border rounded-xl shadow-sm p-6 mt-6">
          <div class="flex items-center justify-between pb-3 border-b mb-6">
            <div class="flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-sm">3</span>
              <div>
                <h3 class="text-base font-bold text-gray-800">Filtros y Reglas de Asignación</h3>
                <p class="text-xs text-gray-500">Cada filtro viene por defecto en <b>"Todos"</b> y está explicado a continuación.</p>
              </div>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- 1. MODO DE OPERACIÓN -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                  1. Modo de Operación
                </label>
              </div>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> Define si los usuarios se duplican para que voten en ambos eventos, o si se transfieren quitándolos del evento anterior.
              </p>

              <label class="flex items-start gap-3 p-3 bg-white border rounded-lg cursor-pointer transition shadow-xs"
                :class="form.modo === 'copiar' ? 'border-indigo-500 ring-1 ring-indigo-400 bg-indigo-50/30' : 'border-gray-200 hover:bg-gray-50'">
                <input type="radio" value="copiar" v-model="form.modo" class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                <div>
                  <span class="text-xs font-bold text-gray-900 block">Copiar / Asignar (Recomendado)</span>
                  <span class="text-[11px] text-gray-500">Mantiene el censo en el evento origen y vincula a los mismos usuarios al nuevo evento.</span>
                </div>
              </label>

              <label class="flex items-start gap-3 p-3 bg-white border rounded-lg cursor-pointer transition shadow-xs"
                :class="form.modo === 'mover' ? 'border-amber-500 ring-1 ring-amber-400 bg-amber-50/30' : 'border-gray-200 hover:bg-gray-50'">
                <input type="radio" value="mover" v-model="form.modo" class="mt-1 text-amber-600 focus:ring-amber-500" />
                <div>
                  <span class="text-xs font-bold text-gray-900 block">Mover / Desvincular</span>
                  <span class="text-[11px] text-gray-500">Traslada al nuevo evento. Si algún votante ya sufragó en el origen, se conserva en el origen por auditoría.</span>
                </div>
              </label>
            </div>

            <!-- 2. FILTRO POR ESTADO -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                2. Filtro por Estado en Origen
              </label>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> Permite pasar todo el censo o únicamente a los que ya tienen el visto bueno administrativo ("Activo").
              </p>

              <div>
                <select
                  id="filtro_estado"
                  v-model="form.filtro_estado"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="todos">Todos los estados (Activos, Pendientes, Rechazados) [Por defecto]</option>
                  <option value="Activo">Solo usuarios con estado 'Activo'</option>
                  <option value="Pendiente">Solo usuarios con estado 'Pendiente'</option>
                  <option value="Rechazado">Solo usuarios con estado 'Rechazado'</option>
                </select>
              </div>

              <div class="p-2.5 bg-blue-50 border border-blue-200 rounded text-[11px] text-blue-900 leading-snug">
                <b>Efecto:</b> Al seleccionar <i>Todos</i>, ningún usuario quedará por fuera. Si solo deseas que voten los que ya fueron validados, escoge <i>Solo Activo</i>.
              </div>
            </div>

            <!-- 3. FILTRO POR TIPO DE VOTANTE -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                3. Filtro por Tipo de Votante
              </label>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> En elecciones por estamentos (estudiantes, profesores, egresados, etc.), permite migrar solo una categoría de usuario siempre que el evento destino también la tenga.
              </p>

              <div>
                <select
                  id="filtro_tipo"
                  v-model="form.filtro_tipo"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="todos">Todos los tipos de votante [Por defecto]</option>
                  <option v-if="statsOrigen && statsOrigen.tipos_coincidentes && statsOrigen.tipos_coincidentes.length > 0" value="solo_coincidentes">
                    Solo tipos compatibles con el evento destino ({{ statsOrigen.tipos_coincidentes.join(', ') }})
                  </option>
                  <option
                    v-for="tipo in listaTiposOrigen"
                    :key="'tipo-opt-' + tipo"
                    :value="tipo"
                  >
                    Tipo: {{ tipo }} {{ esTipoCompatible(tipo) ? ' (✓ Compatible)' : ' (⚠ No en destino)' }}
                  </option>
                </select>
              </div>

              <div v-if="form.filtro_tipo !== 'todos' && !esTipoCompatible(form.filtro_tipo) && form.filtro_tipo !== 'solo_coincidentes' && form.evento_destino_id" class="p-2 bg-red-50 border border-red-200 rounded text-[11px] text-red-800">
                ⚠ <b>Atención:</b> El tipo seleccionado '{{ form.filtro_tipo }}' no está en la lista de tipos del evento destino. El sistema validará que sea admitido antes de procesar.
              </div>
              <div v-else class="p-2 bg-emerald-50 border border-emerald-200 rounded text-[11px] text-emerald-900">
                <b>Efecto:</b> Si ambos eventos manejan tipos de estamento, se garantiza la correspondencia de roles entre los dos comicios.
              </div>
            </div>

            <!-- 4. FILTRO POR COMUNA / SECTOR -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                4. Filtro por Comuna / Sector (Opcional)
              </label>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> Exclusivo para eventos territoriales o comunales (Presupuesto Participativo). En elecciones generales, cerradas o institucionales este filtro no se utiliza.
              </p>

              <div>
                <select
                  id="filtro_comuna"
                  v-model="form.filtro_comuna"
                  class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-xs font-medium focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Todas las comunas / Sin filtro territorial [Por defecto]</option>
                  <option v-for="c in comunas" :key="'com-' + c.id" :value="c.id">
                    {{ c.detalle }}
                  </option>
                </select>
              </div>

              <div class="p-2 bg-slate-100 border border-slate-200 rounded text-[11px] text-slate-700">
                <b>Efecto:</b> Déjalo en <i>"Todas las comunas"</i> si tu evento no requiere delimitación territorial o si deseas pasar a todos sin excepción.
              </div>
            </div>

            <!-- 5. INCLUIR CANDIDATOS -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                5. Usuarios Candidatos
              </label>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> Los candidatos también tienen usuario en el sistema. Puedes elegir si se vinculan al nuevo evento como electores o si solo se migra la ciudadanía regular.
              </p>

              <div class="pt-2">
                <label class="flex items-center gap-2.5 p-3 bg-white border border-gray-300 rounded-lg cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.incluir_candidatos"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span class="text-xs text-gray-800 font-semibold">Incluir candidatos en la asignación [Por defecto: Sí]</span>
                </label>
              </div>

              <div class="p-2 bg-slate-100 border border-slate-200 rounded text-[11px] text-slate-700">
                <b>Efecto:</b> Al estar marcado, ningún usuario se quedará por fuera independientemente de si postuló candidatura en el evento anterior.
              </div>
            </div>

            <!-- 6. ESTADO, DUPLICADOS Y RESET DE INTENTOS -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
              <label class="block text-xs font-bold text-gray-800 uppercase tracking-wide">
                6. Reglas en el Nuevo Evento
              </label>
              <p class="text-xs text-gray-500 leading-snug">
                <b>¿Por qué existe?</b> Garantiza que los usuarios queden habilitados de inmediato y con su contador de sufragio limpio.
              </p>

              <div class="space-y-2">
                <div>
                  <span class="text-[11px] font-semibold text-gray-700 block">Estado en destino:</span>
                  <select
                    id="estado_destino"
                    v-model="form.estado_destino"
                    class="mt-0.5 block w-full border-gray-300 rounded-lg shadow-sm text-xs"
                  >
                    <option value="mantener">Mantener el mismo estado del origen [Por defecto]</option>
                    <option value="Activo">Activo (Habilitados para votar de inmediato)</option>
                    <option value="Pendiente">Pendiente (Requerirá aprobación previa)</option>
                  </select>
                </div>

                <div>
                  <span class="text-[11px] font-semibold text-gray-700 block">Si ya existe en destino:</span>
                  <select
                    id="accion_duplicados"
                    v-model="form.accion_duplicados"
                    class="mt-0.5 block w-full border-gray-300 rounded-lg shadow-sm text-xs"
                  >
                    <option value="omitir">Omitir (No alterar el registro que ya existe) [Por defecto]</option>
                    <option value="actualizar">Actualizar (Sobrescribir con los nuevos datos)</option>
                  </select>
                </div>

                <label class="flex items-center gap-2 pt-1 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="form.resetear_intentos"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span class="text-[11px] text-gray-700 font-medium">Reiniciar contador de intentos a 0 [Por defecto: Sí]</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white border rounded-xl p-5 shadow-sm">
          <SecondaryLink :href="route('eventos.index')">
            Regresar al Listado de Eventos
          </SecondaryLink>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <PrimaryButton
              type="submit"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing || !form.evento_origen_id || !form.evento_destino_id || form.evento_origen_id === form.evento_destino_id"
              class="w-full sm:w-auto justify-center py-2.5 px-6 text-sm"
            >
              <ArrowPathIcon v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
              <span>{{ form.modo === 'mover' ? 'Ejecutar Migración (Mover Votantes)' : 'Asignar Votantes al Nuevo Evento' }}</span>
            </PrimaryButton>
          </div>
        </div>
      </form>

      <!-- MODAL DE ERROR CENTRALIZADO (NUNCA PASA DESAPERCIBIDO) -->
      <div
        v-if="mostrarModalErrorDialog"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-red-200 transform transition-all text-center">
          <div class="w-14 h-14 mx-auto rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-4">
            <ExclamationTriangleIcon class="w-8 h-8" />
          </div>

          <h3 class="text-lg font-bold text-gray-900 mb-2">
            {{ tituloErrorModal || 'No fue posible completar la asignación' }}
          </h3>

          <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-xs text-red-800 text-left mb-6 whitespace-pre-line leading-relaxed">
            {{ mensajeErrorModal }}
          </div>

          <button
            type="button"
            @click="cerrarModalError"
            class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition shadow-sm"
          >
            Entendido, corregir selección
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import {
  ArrowPathIcon,
  UserGroupIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  DocumentDuplicateIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/solid';

const swal = inject('$swal');
const page = usePage();

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  comunas: {
    type: Array,
    default: () => [],
  },
  tipos: {
    type: Object,
    default: () => ({}),
  },
  origen_inicial: {
    type: Number,
    default: null,
  },
  destino_inicial: {
    type: Number,
    default: null,
  },
});

const breadcrumbLinks = [
  { url: '/eventos', text: 'Listado de eventos' },
  { url: '', text: 'Migrar / Asignar Votantes' },
];

// Formulario con TODOS los filtros activados / en "todos" por defecto
const form = useForm({
  evento_origen_id: props.origen_inicial || '',
  evento_destino_id: props.destino_inicial || '',
  modo: 'copiar',
  filtro_estado: 'todos',          // <-- Por defecto TODOS
  filtro_tipo: 'todos',            // <-- Por defecto TODOS
  filtro_comuna: '',               // <-- Por defecto TODAS
  incluir_candidatos: true,        // <-- Por defecto TODOS (votantes y candidatos)
  estado_destino: 'mantener',      // <-- Por defecto Mantener estado original
  accion_duplicados: 'omitir',     // <-- Por defecto Omitir duplicados
  resetear_intentos: true,         // <-- Por defecto Reiniciar intentos
});

const cargandoStats = ref(false);
const statsOrigen = ref(null);

// Control de Modal de Error
const mostrarModalErrorDialog = ref(false);
const tituloErrorModal = ref('');
const mensajeErrorModal = ref('');

const abrirModalError = (titulo, mensaje) => {
  tituloErrorModal.value = titulo;
  mensajeErrorModal.value = mensaje;
  mostrarModalErrorDialog.value = true;
};

const cerrarModalError = () => {
  mostrarModalErrorDialog.value = false;
};

// Observar errores flash o de validación de Inertia para desplegar el modal inmediatamente
watch(
  () => page.props.flash?.error,
  (errorFlash) => {
    if (errorFlash) {
      abrirModalError('Atención al procesar la asignación', errorFlash);
    }
  },
  { immediate: true }
);

watch(
  () => page.props.errors,
  (errores) => {
    if (errores && Object.keys(errores).length > 0) {
      const lista = Object.values(errores).join('\n• ');
      abrirModalError('Errores de validación', '• ' + lista);
    }
  },
  { deep: true }
);

const eventoOrigenSeleccionado = computed(() => {
  return props.eventos.find((e) => e.id === Number(form.evento_origen_id));
});

const eventoDestinoSeleccionado = computed(() => {
  return props.eventos.find((e) => e.id === Number(form.evento_destino_id));
});

// Lista de tipos presentes en el evento origen
const listaTiposOrigen = computed(() => {
  if (statsOrigen.value?.tipos_origen && statsOrigen.value.tipos_origen.length > 0) {
    return statsOrigen.value.tipos_origen;
  }
  if (eventoOrigenSeleccionado.value?.tipos && eventoOrigenSeleccionado.value.tipos !== 'NA') {
    return eventoOrigenSeleccionado.value.tipos.split('|').map((t) => t.trim()).filter(Boolean);
  }
  return Object.values(props.tipos || {});
});

// Comprobar si un tipo de votante existe en el evento destino
const esTipoCompatible = (tipo) => {
  if (!tipo || !eventoDestinoSeleccionado.value) return true;
  const tiposDestinoRaw = eventoDestinoSeleccionado.value.tipos;
  if (!tiposDestinoRaw || tiposDestinoRaw === 'NA' || tiposDestinoRaw.trim() === '') {
    return true; // El evento destino no tiene restricción de tipos
  }
  const tiposDestinoArr = tiposDestinoRaw.split('|').map((t) => t.trim().toLowerCase());
  return tiposDestinoArr.includes(tipo.trim().toLowerCase());
};

const cargarEstadisticas = async () => {
  if (!form.evento_origen_id) {
    statsOrigen.value = null;
    return;
  }

  cargandoStats.value = true;
  try {
    const params = {};
    if (form.evento_destino_id && form.evento_destino_id !== form.evento_origen_id) {
      params.destino_id = form.evento_destino_id;
    }
    const response = await axios.get(route('eventos.estadisticas.votantes', form.evento_origen_id), { params });
    statsOrigen.value = response.data;
  } catch (error) {
    console.error('Error cargando estadísticas del evento', error);
  } finally {
    cargandoStats.value = false;
  }
};

onMounted(() => {
  if (form.evento_origen_id) {
    cargarEstadisticas();
  }
});

const confirmarEnvio = () => {
  if (!form.evento_origen_id || !form.evento_destino_id) {
    abrirModalError(
      'Selección de eventos requerida',
      'Debes seleccionar tanto el evento de origen como el evento de destino para poder continuar.'
    );
    return;
  }

  if (form.evento_origen_id === form.evento_destino_id) {
    abrirModalError(
      'Eventos idénticos',
      'El evento de origen y de destino no pueden ser el mismo evento de votación. Selecciona eventos distintos.'
    );
    return;
  }

  // Validación de compatibilidad de tipos
  if (form.filtro_tipo !== 'todos' && form.filtro_tipo !== 'solo_coincidentes' && !esTipoCompatible(form.filtro_tipo)) {
    abrirModalError(
      'Tipo de votante incompatible',
      `El tipo '${form.filtro_tipo}' no está configurado en el evento de destino '${eventoDestinoSeleccionado.value?.nombre}'.\n\nTipos permitidos en destino: ${eventoDestinoSeleccionado.value?.tipos || 'Ninguno'}.\n\nPara continuar, escoge 'Todos' o un tipo que coincida.`
    );
    return;
  }

  const nombreOrigen = eventoOrigenSeleccionado.value ? eventoOrigenSeleccionado.value.nombre : `ID ${form.evento_origen_id}`;
  const nombreDestino = eventoDestinoSeleccionado.value ? eventoDestinoSeleccionado.value.nombre : `ID ${form.evento_destino_id}`;
  const verboModo = form.modo === 'mover' ? 'Mover / Desvincular' : 'Copiar / Asignar';

  swal({
    title: '¿Confirmar migración de censo?',
    html: `
      <div class="text-left text-xs space-y-2">
        <p>Vas a procesar el censo electoral con los siguientes parámetros:</p>
        <div class="bg-gray-100 p-2.5 rounded text-gray-800">
          <b>Origen:</b> ${nombreOrigen}<br>
          <b>Destino:</b> ${nombreDestino}<br>
          <b>Modo:</b> ${verboModo}
        </div>
        <ul class="list-disc pl-4 text-gray-600 space-y-1">
          <li>Filtro estado: <b>${form.filtro_estado}</b></li>
          <li>Filtro tipo: <b>${form.filtro_tipo}</b></li>
          <li>Filtro comuna: <b>${form.filtro_comuna || 'Todas las comunas (sin filtro)'}</b></li>
          <li>Candidatos: <b>${form.incluir_candidatos ? 'Incluidos' : 'Solo votantes'}</b></li>
          <li>Estado en destino: <b>${form.estado_destino}</b></li>
          <li>Duplicados: <b>${form.accion_duplicados === 'omitir' ? 'Omitir' : 'Actualizar'}</b></li>
          <li>Reiniciar intentos: <b>${form.resetear_intentos ? 'Sí (a 0)' : 'No'}</b></li>
        </ul>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, ejecutar asignación',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#4338ca',
  }).then((result) => {
    if (result.isConfirmed) {
      form.post(route('eventos.migrar.ejecutar'), {
        preserveScroll: true,
        onSuccess: () => {
          cargarEstadisticas();
        },
        onError: (errores) => {
          const mensaje = Object.values(errores).join('\n• ');
          abrirModalError('Error al ejecutar la asignación', '• ' + mensaje);
        },
      });
    }
  });
};
</script>
