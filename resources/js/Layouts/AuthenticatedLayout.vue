<template>
  <div class="flex h-screen bg-gray-200 font-roboto">
    <Navigation />

    <div class="flex flex-1 flex-col overflow-hidden">
      <Header />

      <main class="flex-1 overflow-y-auto mb-4 overflow-x-hidden bg-gray-200">
        <!-- titulo -->
        <div
          class="w-full flex justify-between items-center h-auto p-2 px-4 bg-azul shadow-lg"
        >
          <!-- titulo text -->
          <h3
            class="min-[300px]:text-sm md:text-md lg:text-lg font-medium text-white"
          >
            <slot name="header" />
          </h3>
          <!-- return button -->
          <div class="">
            <!-- si tiene mas de 2 -->
            <div v-if="breadCrumbLinks.length == 2">
              <SecondaryButtonReturn class="!bg-transparent text-white" />
            </div>
            <!-- si tiene solo 1 -->
            <a v-else-if="breadCrumbLinks.length == 1" href="/">
              <HomeIcon class="h-6 w-6 text-white cursor-pointer" />
            </a>
            <!-- Dashboard -->
            <div v-else>
              <HomeModernIcon class="h-6 w-6 text-white" />
            </div>
          </div>
        </div>
        <!-- breadcrumb -->
        <div class="mx-auto px-6 pt-4">
          <BreadCrumb :links="breadCrumbLinks" />
        </div>
        <!-- main part -->
        <div class="mx-auto px-6 py-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from "@/Layouts/Header.vue";
import Navigation from "@/Layouts/Navigation.vue";
import BreadCrumb from '@/Components/BreadCrumb.vue';
import SecondaryButtonReturn from '@/Components/SecondaryButtonReturn.vue';
import { HomeIcon, HomeModernIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    breadCrumbLinks: {
        type: Array,
        default: () => []
    }
});

console.log(props);



</script>
