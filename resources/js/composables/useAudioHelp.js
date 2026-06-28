import { ref } from "vue";

export function useAudioHelp() {
  const speaking = ref(false);
  const paused = ref(false);
  const lastSpokenText = ref("");

  // Almacena los segmentos (oraciones) del texto actual
  const segments = ref([]);
  // Índice del segmento que se está reproduciendo actualmente
  const currentSegmentIndex = ref(0);

  // Divide el texto en segmentos por oraciones
  const splitIntoSegments = (text) => {
    const result = text.match(/[^.!?]+[.!?]*/g) || [text];
    return result.map((s) => s.trim()).filter((s) => s.length > 0);
  };

  // Reproduce el segmento actual y encadena el siguiente al terminar
  const speakCurrentSegment = () => {
    // Si ya no hay más segmentos o estamos en pausa, terminamos
    if (currentSegmentIndex.value >= segments.value.length) {
      speaking.value = false;
      paused.value = false;
      return;
    }

    // Si nos pausaron antes de empezar este segmento, no continuamos
    if (paused.value) {
      speaking.value = false;
      return;
    }

    const utterance = new SpeechSynthesisUtterance(
      segments.value[currentSegmentIndex.value]
    );
    utterance.lang = "es-CO";
    utterance.rate = 0.95;
    utterance.pitch = 1.05;

    utterance.onstart = () => {
      speaking.value = true;
    };

    utterance.onend = () => {
      // Solo avanzamos al siguiente segmento si NO estamos en pausa
      // (si estamos en pausa, `cancel()` detuvo la cadena y no debe continuar)
      if (!paused.value && currentSegmentIndex.value < segments.value.length) {
        currentSegmentIndex.value++;
        speakCurrentSegment();
      }
    };

    utterance.onerror = () => {
      speaking.value = false;
      paused.value = false;
    };

    window.speechSynthesis.speak(utterance);
  };

  // Lee un texto completo dividiéndolo en segmentos
  const leerTexto = (texto) => {
    // Cancela cualquier speech en curso
    window.speechSynthesis.cancel();

    lastSpokenText.value = texto;
    segments.value = splitIntoSegments(texto);
    currentSegmentIndex.value = 0;
    paused.value = false;

    speakCurrentSegment();
  };

  // Reanuda la reproducción desde el segmento actual,
  // o reproduce desde el inicio si ya terminó
  const toggleReproducir = () => {
    if (paused.value) {
      // Reanudar desde donde se quedó
      paused.value = false;
      speakCurrentSegment();
    } else if (lastSpokenText.value && !speaking.value) {
      // Ya terminó, reproducir desde el inicio
      leerTexto(lastSpokenText.value);
    }
  };

  // Pausa la lectura: cancela el speech actual pero NO avanza el índice,
  // así al reanudar se retoma desde el mismo segmento
  const pausarLectura = () => {
    if (speaking.value && !paused.value) {
      window.speechSynthesis.cancel();
      paused.value = true;
      speaking.value = false;
    }
  };

  // Detiene completamente la lectura: cancela y reinicia el estado
  const detenerLectura = () => {
    window.speechSynthesis.cancel();
    speaking.value = false;
    paused.value = false;
    currentSegmentIndex.value = segments.value.length; // Marca como terminado
    segments.value = [];
  };

  const estaHablando = () => {
    return speaking.value || paused.value;
  };

  return {
    speaking,
    paused,
    lastSpokenText,
    leerTexto,
    toggleReproducir,
    pausarLectura,
    detenerLectura,
    estaHablando,
  };
}
