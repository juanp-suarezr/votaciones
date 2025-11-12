function metricsByEndpoint_beforeRequest(requestParams, context, ee, next) {
    console.log("Ejecutando antes de la solicitud:", requestParams.url);
    return next();
}

module.exports = {
    metricsByEndpoint_beforeRequest
};
