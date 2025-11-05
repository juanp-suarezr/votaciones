// load-tests/processor.js
module.exports = {
  // Este método puede modificar dinámicamente datos del flujo
  beforeRequest: function (req, context, ee, next) {
    // Ejemplo: inyectar headers personalizados o logs
    console.log(`Ejecutando request a ${req.url}`);
    return next();
  },

  afterResponse: function (req, res, context, ee, next) {
    // Validar código de estado y registrar errores
    if (res.statusCode >= 400) {
      console.error(`Error (${res.statusCode}) en ${req.url}`);
    }
    return next();
  },
};
