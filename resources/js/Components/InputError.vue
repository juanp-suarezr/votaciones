<template>
    <div v-show="message">
        <p class="text-[8px] md:text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>

<script setup>
defineProps(['message']);
</script>
