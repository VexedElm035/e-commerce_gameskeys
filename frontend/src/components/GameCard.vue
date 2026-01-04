<script setup>
import { RouterLink } from 'vue-router';
defineProps(['id', 'title', 'price', 'img'])

</script>

<template>
    <RouterLink :to="`/game/${id}`">
        <div class="bg-gray-800 hover:bg-gray-700 p-1 rounded-lg text-center h-full w-full">
            <img :src="`${img}`" alt="Juego" class="w-[85%] h-[73%] object-cover mx-auto mb-3 rounded-lg">
            <h3 class="text-lg font-bold">{{ title }}</h3>
        </div>
    </RouterLink>
</template>