<script setup>
import HorizontalKeyCard from '@/components/HorizontalKeyCard.vue';
import router from '@/router';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
const route = useRoute();
const gameId = route.params.id;
const game = ref([]);
const keys = ref([]);
const genres = ref([]);

const gameKeys = computed(() => {
    return keys.value.filter(k => k.game_id == gameId);
});

onMounted(async () => {
    try {
        const [gameRes, keysRes, genresRes] = await Promise.all([
            axios.get(`/games/${gameId}`),
            axios.get('/gamekeys'),
            axios.get('/genres')
        ]);
        game.value = gameRes.data;
        keys.value = keysRes.data.filter(key => key.state === 'disponible');
        genres.value = genresRes.data;

    } catch {
        router.push('/error')
    }
})

</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen flex-1">
        <section class="bg-yellow-500 text-gray-900 text-center py-10">
            <h2 class="text-3xl font-bold">Detalles del Juego</h2>
            <p class="mt-2">{{ game.name }}</p>
        </section>

        <section class="container mx-auto p-6 flex-grow ">
            <div class="bg-gray-800 p-6 rounded-lg flex flex-col md:flex-row gap-6">

                <div class="md:w-1/2 flex justify-center">
                    <img :src="`${game.img}`" alt="Cyberpunk 2077"
                        class="max-w-md h-96 object-cover rounded-lg mx-auto">
                </div>

                <div class="md:w-1/2">
                    <h3 class="text-2xl font-bold mb-4">{{ game.name }}</h3>
                    <p class="mb-4">{{ game.description }}</p>
                    <ul class="mb-4">
                        <!-- <li><strong>Género:</strong> RPG, Acción</li> -->
                        <li><strong>Desarrollador:</strong> {{ game.publisher }}</li>
                        <li><strong>Fecha de lanzamiento:</strong> {{ game.launch_date }}</li>
                        <li><strong>Plataforma:</strong> {{ game.available_platforms }}</li>
                    </ul>
                    <div class="flex gap-4">
                        <RouterLink to="/" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Volver
                            a la Tienda</RouterLink>
                    </div>

                    <div class="bg-gray-900 text-white flex flex-col gap-4 mt-6 p-3 rounded-lg">
                        <h3 class="text-xl font-semibold mb-2">Llaves disponibles:</h3>
                        <HorizontalKeyCard v-for="key in gameKeys" :key="key.id" :id="key.id" :platform="key.platform"
                            :price="`$${key.price}`" :region="key.region" :seller="key.seller.username"
                            :seller_img="key.seller.avatar" :rate="key.rate" :deliverytime="key.delivery_time" />
                    </div>
                    <p v-if="gameKeys.length === 0">No hay llaves disponibles para este juego.</p>
                </div>

            </div>
        </section>
    </section>

</template>