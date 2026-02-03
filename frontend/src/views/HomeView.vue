<script setup>
import GameCard from '../components/GameCard.vue';
import KeyCard from '@/components/KeyCard.vue';
import FeaturedCarrousel from '../components/FeaturedCarrousel.vue';
import HorizontalCarousel from '@/components/Carrousel.vue';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';

import { useRouter } from 'vue-router';
import { useImage } from '@/composables/useImage';

const router = useRouter();
const { resolve } = useImage();
const searchQuery = ref('');
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.push({
            name: 'catalog',
            query: { q: searchQuery.value.trim() }
        });
    }
};

const onKeyPress = (e) => {
    if (e.key === 'Enter') {
        handleSearch();
    }
};

let games = ref([]);
let keys = ref([]);
let genres = ref([]);
const isLoading = ref(true);
const isLoadingKeys = ref(true);

// Definición de secciones por plataforma
const platformSections = [
  {
    title: "PlayStation",
    platforms: ["PS4", "PS5"],
    icon: "../ps.svg"
  },
  {
    title: "Xbox",
    platforms: ["Series", "XONE", "X|S"],
    icon: "../xbox.svg"
  },
  {
    title: "Nintendo",
    platforms: ["Switch"],
    icon: "../switch.svg"
  },
  {
    title: "Steam",
    platforms: ["PC"],
    icon: "../steam.svg"
  }
];

// Filtramos las keys para cada sección
const filteredKeysByPlatform = computed(() => {
  return platformSections.map(section => ({
    ...section,
    keys: keys.value.filter(key => 
      section.platforms.includes(key.platform) && 
      key.state === 'disponible'
    ).slice(0, 10) // Limitar a 10 items por sección
  }));
});

async function fetchData() {
    try {
        isLoading.value = true;
        isLoadingKeys.value = true;

        const [gamesRes, keysRes, genresRes] = await Promise.all([
            axios.get('/games'),
            axios.get('/gamekeys'),
            axios.get('/genres')
        ]);

        games.value = gamesRes.data.slice(0, 6);
        keys.value = keysRes.data.filter(key => key.state === 'disponible');
        genres.value = genresRes.data;
    } catch (err) {
        console.error('Error fetching data:', err);
    } finally {
        isLoading.value = false;
        isLoadingKeys.value = false;
    }
}

onMounted(() => {
    fetchData();
});
</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen flex-1">
        <section class="bg-yellow-500 text-center py-10 max-h-35">
            <h2 class="text-2xl font-bold">Ofertas Especiales en Juegos Digitales</h2>
        </section>
        
        <section class="container mx-auto p-6">
            <div class="relative">
                <input v-model="searchQuery" @keypress="onKeyPress" type="text" placeholder="Buscar juegos..."
                    class="w-full p-2 pl-10 rounded-lg text-gray-100 bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                <button @click="handleSearch"
                    class="cursor-pointer absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-yellow-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </section>

        <FeaturedCarrousel />

 <section class="container mx-auto p-2 mt-10 flex-grow">
            <h2 class="text-2xl font-bold text-center mb-6">Llaves Destacadas</h2>
            <HorizontalCarousel :items="keys" :loading="isLoadingKeys" placeholder-type="key">
                <template #default="{ items }">
                    <div v-for="key in items" :key="key.id" class="flex-shrink-0 w-48">
                        <KeyCard :id="key.id" :name="key.game.name" :price="key.price" :platform="key.platform"
                            :region="key.region" :seller="key.seller" :img="key.game.img" :rate="key.rate"
                            :deliverytime="key.delivery_time" />
                    </div>
                </template>
            </HorizontalCarousel>
        </section>

<!-- Sección de Juegos Destacados (existente) -->
        <section class="container mx-auto p-2 flex-grow h-120">
            <h2 class="text-2xl font-bold text-center mb-6">Juegos Destacados</h2>
            <HorizontalCarousel :items="games" :loading="isLoading" placeholder-type="game">
                <template #default="{ items }">
                    <div v-for="game in items" :key="game.id"
                        class="flex-shrink-0 w-55 h-85 hover:w-60 hover:h-90 transition-all duration-300 ease-in-out cursor-pointer">
                        <GameCard :id="game.id" :title="game.name" :img="game.img" />
                    </div>
                </template>
            </HorizontalCarousel>
        </section>


        <!-- Sección de Llaves por Plataforma -->
        <template v-for="section in filteredKeysByPlatform" :key="section.title">
            <section v-if="section.keys.length >= 0" class="container mx-auto p-2 mt-10" :id="section.title" >
                <h2 class="text-2xl font-bold text-center mb-6 flex items-center justify-center gap-2">
                   
                    <img :src="resolve(section.icon, 'icon')" alt="icon" class="w-6 h-6">
                    {{ section.title }}
                </h2>
                
                <HorizontalCarousel 
                    :items="section.keys" 
                    :loading="isLoadingKeys" 
                    placeholder-type="key"
                    
                >
                    <template #default="{ items }">
                        <div 
                            v-for="key in items" 
                            :key="key.id" 
                            class="flex-shrink-0 w-48"
                        >
                            <KeyCard 
                                :id="key.id" 
                                :name="key.game.name" 
                                :price="key.price" 
                                :platform="key.platform"
                                :region="key.region" 
                                :seller="key.seller" 
                                :img="key.game.img" 
                                :rate="key.rate"
                                :deliverytime="key.delivery_time" 
                            />
                        </div>
                    </template>
                </HorizontalCarousel>
            </section>
        </template>

        
    </section>
</template>

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes shimmer {
    0% {
        opacity: 0.5;
    }
    50% {
        opacity: 1;
    }
    100% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: shimmer 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>