<template>
    <header class="flex items-center justify-between border-b-4 border-azul bg-white px-6 py-4">
        <div class="flex items-center">
            <button @click="$page.props.showingMobileMenu = !$page.props.showingMobileMenu"
                class="text-gray-500 focus:outline-none lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div class="flex items-center">
            <dropdown>
                <template #trigger>
                    <button title="configuración perfil" @click="dropdownOpen = !dropdownOpen" class="relative flex gap-2 overflow-hidden">
                        {{ $page.props.auth.user.name ? $page.props.auth.user.name : '' }}
                        <BarsArrowDownIcon class="w-6" />
                    </button>
                </template>

                <template #content>
                    <dropdown-link :href="route('profile.edit')">
                        Perfil
                    </dropdown-link>

                    <dropdown-link class="w-full text-left" :href="route('logout')" method="post" as="button">
                        Cerrar Sesión
                    </dropdown-link>
                </template>
            </dropdown>
        </div>
    </header>
</template>

<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { BarsArrowDownIcon } from "@heroicons/vue/24/solid";
</script>
