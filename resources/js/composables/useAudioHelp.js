import { ref } from "vue";

export function useAudioHelp() {
  const speaking = ref(false);
  const paused = ref(false);
  const lastSpokenText = ref("");

  const leerTexto = (texto) => {
    lastSpokenText.value = texto;

    window.speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(texto);
    speech.lang = "es-CO";
    speech.rate = 0.95;
    speech.pitch = 1.05;

    speech.onend = () => {
      speaking.value = false;
      paused.value = false;
    };
    speech.onerror = () => {
      speaking.value = false;
      paused.value = false;
    };

    window.speechSynthesis.speak(speech);
    speaking.value = true;
    paused.value = false;
  };

  const toggleReproducir = () => {
    if (paused.value) {
      window.speechSynthesis.resume();
      paused.value = false;
      speaking.value = true;
    } else if (lastSpokenText.value) {
      leerTexto(lastSpokenText.value);
    }
  };

  const pausarLectura = () => {
    if (window.speechSynthesis.speaking && !paused.value) {
      window.speechSynthesis.pause();
      paused.value = true;
      speaking.value = false;
    }
  };

  const detenerLectura = () => {
    window.speechSynthesis.cancel();
    speaking.value = false;
    paused.value = false;
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