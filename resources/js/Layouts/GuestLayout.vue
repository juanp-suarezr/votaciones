<template>
    <div class="flex h-screen items-center justify-center bg-gray-200 px-6">
        <div class="w-full rounded-md bg-white p-6 shadow-md" :class="isRegister ? 'max-w-2xl' :'max-w-sm '">
            <slot />
        </div>
    </div>
</template>

<script setup>

defineProps(['isRegister']);

</script>
