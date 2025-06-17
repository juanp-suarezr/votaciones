<template>
    <div class="flex h-full min-h-screen items-center justify-center bg-black px-6 py-6 relative">
        <!-- Fondo con la palabra REGISTRO -->
        <div class="absolute inset-0 bg-pattern h-full min-h-screen"></div>
        <!-- Contenido del slot -->
        <slot />
    </div>
</template>

<script setup>
defineProps(['isRegister']);
</script>

<style scoped>
/* Fondo con la palabra REGISTRO repetida */
.bg-pattern {
    background: radial-gradient(transparent 34%, #870909 35%, #870909 45%, transparent 46%), radial-gradient(circle at left, transparent 39%, #87090980 40%, #87090980 45%, transparent 46%), radial-gradient(circle at right, transparent 39%, #87090980 40%, #87090980 45%, transparent 46%);
        background-size: 6em 6em;
        background-color: #E20613;
        opacity: 0.11

}
</style>
