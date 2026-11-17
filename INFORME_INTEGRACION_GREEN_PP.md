# Informe de Estado: Integración Voto Electrónico (VOTA YA) ↔ Presupuesto Participativo (GREEN)

**Fecha:** 17 de noviembre de 2026  
**Proyecto:** Plataforma de Votaciones VOTA YA  
**Módulo:** Integración Externa con Presupuesto Participativo (GREEN / Gobtech)  
**Estado Actual:** **Desarrollo 100% Completado** · *Pendiente entrega de `baseUrl` por parte de GREEN para pruebas en vivo.*

---

## 1. Objetivo de la Integración

Establecer una comunicación automatizada y bidireccional entre la plataforma **VOTA YA** (Voto Electrónico institucional) y el sistema **GREEN** (módulo de Presupuesto Participativo):

1. **Recepción de Proyectos (Ingesta):** VOTA YA consulta e importa las convocatorias y proyectos viables expuestos por GREEN, asociándolos a la vigencia electoral correspondiente y homologando las comunas y corregimientos.
2. **Envío de Resultados (Egress):** Al concluir la jornada y consolidar el escrutinio oficial, VOTA YA envía a GREEN la lista de proyectos ganadores con su identificación original para actualizar su estado a **`GANADOR`**.

---

## 2. Lo que se Implementó y Dejó Listo en VOTA YA

A partir del esquema y la especificación de API suministrada en la colección de Postman, se construyó toda la infraestructura técnica necesaria en VOTA YA:

### A. Base de Datos y Trazabilidad (Migración e Idempotencia)
- **Trazabilidad directa:** Se incorporó el campo `id_postulacion_green` a la tabla `proyectos` para almacenar el identificador único original de GREEN. Esto garantiza **idempotencia total** (si una convocatoria se sincroniza varias veces, se actualizan los datos sin duplicar proyectos ni tarjetones).
- **Tabla de Auditoría (`pp_integracion_logs`):** Registra cada intento de conexión, payload enviado, código de respuesta HTTP, estado (`EXITOSO`, `FALLIDO`, `REINTENTANDO`), contador de intentos y detalles de error.
- **Tabla de Homologación Territorial (`pp_homologacion_comunas`):** Mapea de manera automática o asistida las comunas de GREEN (`dir_comunas`) con la división territorial local de Pereira en VOTA YA (`parametros_detalle` con código `com01`).

### B. Servicio Cliente HTTP (`GreenPpService.php`)
- Configurado para operar con el token de pruebas entregado:  
  `Authorization: Bearer 1|hrpLFod7R2ppecAdGgN9d1LntnAn9PMDjRycMR2353377cdd`
- Conexión con los endpoints especificados:
  - `GET /api/v1/pp/convocatorias` (listado y filtros).
  - `GET /api/v1/pp/convocatorias/{id}` (detalle y postulaciones).
  - `POST/PUT /api/v1/pp/proyectos/actualizar-estado` (despacho de ganadores).
- Normalizador inteligente de URLs para aceptar formatos con o sin `/api/v1/pp` y prevenir errores por rutas relativas.

### C. Arquitectura Institucional de Eventos y Proyectos en VOTA YA
La integración se alinea estrictamente a la lógica nativa del sistema:
- **Evento Padre Institucional:** Existe un evento general denominado `"Presupuesto Participativo"` (`evento_padre = 1`), que agrupa históricamente todas las vigencias.
- **Eventos Hijos (Vigencias):** Los eventos hijos representan las **vigencias electorales anuales** (ej. *"Presupuesto Participativo 2026"*), relacionados al padre en `hash_eventos_hijos`.
- **Estructura de la tabla `proyectos`:**
  - `id`: Identificador local autoincremental de VOTA YA.
  - `id_postulacion_green`: Identificador original de la postulación en GREEN (ej: 381).
  - `id_convocatoria_green`: Identificador de la convocatoria en GREEN.
  - `subtipo`: Código ID de la **Comuna o Corregimiento** homologado (asociado a `parametros_detalle` donde `codParametro = 'com01'`).
  - `detalle` y `descripcion`: Nombre y contenido del proyecto.
- **Tabla Puente `hash_proyectos`:**
  - Relaciona cada proyecto con el **Evento Hijo de la Vigencia**: `(id_evento = id_evento_vigencia, id_proyecto = proyecto->id)`.
- **Mecánica del Tarjetón al Votar:**
  - Cuando un ciudadano vota en la vigencia (`id_evento`), el sistema detecta automáticamente la comuna del votante (`informacion_votantes.comuna`) y consulta en `hash_proyectos` únicamente los proyectos de esa vigencia cuyo `subtipo` coincida con la comuna del sufragante. **No se crean eventos por comuna; la comuna es un atributo del proyecto y el evento es la vigencia.**

### D. Política de Resiliencia y Reintentos (`EnviarResultadosPpJob.php`)
- El envío de resultados corre mediante colas en segundo plano.
- En caso de intermitencia de red o saturación temporal de la API de GREEN, el sistema reintenta automáticamente hasta **5 veces** con intervalos exponenciales (1 min, 5 min, 15 min, 1 hora).
- Incorpora encabezado de idempotencia (`X-Idempotency-Key`) para evitar dobles radicaciones en GREEN.

### E. Panel Web Administrativo y Comandos por Consola
- **Panel Web en tiempo real:** Accesible en `/pp-integracion` (enlace en el menú lateral bajo *Proyectos*):
  - Semáforo de estado de conexión.
  - Tabla de convocatorias con botón directo **"Sincronizar"**.
  - Listado de proyectos sincronizados.
  - Bitácora de logs con botón para **"Reintentar"** manualmente cualquier despacho fallido.
- **Comando por Consola:**
  ```bash
  # Consultar convocatorias disponibles
  php artisan pp:sincronizar

  # Sincronizar convocatoria específica (ej: ID 12)
  php artisan pp:sincronizar --id=12

  # Probar despacho de ganadores con payload mock
  php artisan pp:sincronizar --probar-envio=12
  ```
- **Webhook de Producción preparado:** Endpoint listo en `/api/v1/integracion/green-pp/webhook` para cuando GREEN active el disparador automático.

---

## 3. Estado Actual y Requerimiento Pendiente

### Diagnóstico de la prueba actual:
Al acceder a `/pp-integracion`, la plataforma reporta actualmente:
```json
{
  "conexion_ok": false,
  "conexion_error": "Error HTTP 404",
  "convocatorias": []
}
```

### Causa:
En la colección de Postman facilitada por GREEN, la variable de entorno **`baseUrl` se encuentra vacía**:
```json
"variable": [
  {
    "key": "baseUrl",
    "value": ""
  }
]
```
Al no tener una URL destino configurada, VOTA YA recurre por defecto a `localhost`, donde obviamente dicha API externa no existe, generando un código `404 Not Found`.

---

## 4. Información requerida al equipo de GREEN para finalizar pruebas

Para realizar las pruebas completas de punta a punta (End-to-End), solicitamos amablemente al equipo de **GREEN / Gobtech** suministrarnos:

1. **URL Base del Entorno de Pruebas (`baseUrl`):**
   - El dominio o IP pública/privada donde está desplegada la API (ejemplo: `https://qa-pp.gobtech.co` o `https://pruebas-pp.pereira.gov.co`).
2. **Confirmación del método de actualización de ganadores:**
   - Confirmar si el endpoint para reportar los proyectos ganadores tras el escrutinio será `POST /api/v1/pp/proyectos/actualizar-estado` o si utilizará otro path/método (ej. `PUT /api/v1/pp/postulaciones/{id}`).

---

## 5. Próximo Paso (Inmediato tras recibir la URL)

Tan pronto el equipo de GREEN nos entregue la URL base:

1. Se registrará en el archivo `.env`:
   ```env
   GREEN_PP_BASE_URL="https://URL-ENTREGADA-POR-GREEN/api/v1/pp"
   GREEN_PP_TOKEN="1|hrpLFod7R2ppecAdGgN9d1LntnAn9PMDjRycMR2353377cdd"
   ```
2. Se limpiará la caché de configuración:
   ```bash
   php artisan config:clear
   ```
3. Se procederá a ejecutar la prueba de importación en vivo y verificación de tarjetones desde el panel `/pp-integracion`.

