<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

import { useToastStore } from '@/stores/toast';
import { useImage } from '@/composables/useImage';

const route = useRoute();
const toast = useToastStore();
const { resolve, getPlaceholder } = useImage();
const searchQuery = ref(route.query.q || '');
const gameKeys = ref([]);
const filteredKeys = ref([]);
const isLoading = ref(true);
const showMobileFilters = ref(false);
const filters = ref({
  platforms: [],
  regions: [],
  priceRange: [0, 100],
  originalPriceRange: [0, 100],
  deliveryTime: '',
  availablePlatforms: [],
  availableRegions: []
});

const fetchGameKeys = async () => {
  try {
    isLoading.value = true;
    // Use server-side filtering for state
    const response = await axios.get('/gamekeys', {
      params: { state: 'disponible' }
    });
    gameKeys.value = response.data;
    console.log('Game keys fetched:', gameKeys.value);
    extractUniqueFilters();
    applyFilters();
  } catch (error) {
    console.error('Error fetching game keys:', error);
    toast.trigger('Error al cargar el catálogo de juegos.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const extractUniqueFilters = () => {
  const uniquePlatforms = new Set();
  const uniqueRegions = new Set();
  const prices = [];

  gameKeys.value.forEach(key => {
    uniquePlatforms.add(key.platform);
    uniqueRegions.add(key.region);
    const price = parseFloat(key.price);
    if (!isNaN(price)) {
      prices.push(price);
    }
  });

  filters.value.availablePlatforms = Array.from(uniquePlatforms);
  filters.value.availableRegions = Array.from(uniqueRegions);

  if (prices.length > 0) {
    const minPrice = Math.floor(Math.min(...prices));
    const maxPrice = Math.ceil(Math.max(...prices));
    filters.value.priceRange = [minPrice, maxPrice];
    filters.value.originalPriceRange = [minPrice, maxPrice];
  } else {
    filters.value.priceRange = [0, 100];
    filters.value.originalPriceRange = [0, 100];
  }
};

const applyFilters = () => {
  let results = [...gameKeys.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    results = results.filter(key =>
      key.game.name.toLowerCase().includes(query) ||
      (key.game.description && key.game.description.toLowerCase().includes(query))
    );
  }

  if (filters.value.platforms.length > 0) {
    results = results.filter(key =>
      filters.value.platforms.includes(key.platform)
    );
  }

  if (filters.value.regions.length > 0) {
    results = results.filter(key =>
      filters.value.regions.includes(key.region)
    );
  }

  results = results.filter(key => {
    const price = parseFloat(key.price);
    return price >= filters.value.priceRange[0] &&
      price <= filters.value.priceRange[1];
  });

  if (filters.value.deliveryTime) {
    results = results.filter(key =>
      key.delivery_time.toLowerCase().includes(filters.value.deliveryTime.toLowerCase())
    );
  }

  filteredKeys.value = results;
};

const resetFilters = () => {
  filters.value.platforms = [];
  filters.value.regions = [];
  filters.value.priceRange = [...filters.value.originalPriceRange];
  filters.value.deliveryTime = '';
  applyFilters();
};

const toggleMobileFilters = () => {
  showMobileFilters.value = !showMobileFilters.value;
};

onMounted(() => {
  fetchGameKeys();
});

watch(
  () => ({
    query: route.query.q,
    ...filters.value
  }),
  () => {
    searchQuery.value = route.query.q || '';
    applyFilters();
  },
  { deep: true }
);

watch(
  () => filters.value.priceRange,
  (newRange) => {
    if (newRange[0] > newRange[1]) {
      filters.value.priceRange = [newRange[1], newRange[1]];
    }
    if (newRange[1] < newRange[0]) {
      filters.value.priceRange = [newRange[0], newRange[0]];
    }
    if (newRange[0] < filters.value.originalPriceRange[0]) {
      filters.value.priceRange[0] = filters.value.originalPriceRange[0];
    }
    if (newRange[1] > filters.value.originalPriceRange[1]) {
      filters.value.priceRange[1] = filters.value.originalPriceRange[1];
    }
  },
  { deep: true }
);
</script>

<template>
  <section class="bg-gray-900 text-white flex flex-col min-h-screen">
    <section class="container mx-auto p-6">
      <div class="flex justify-center">
        <div class="relative w-full max-w-2xl">
          <input v-model="searchQuery" @keypress.enter="applyFilters" type="text"
            placeholder="Buscar juegos por nombre o descripción..."
            class="w-full p-2 pl-10 rounded-lg text-gray-100 bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      
      <!-- Mobile Filter Toggle -->
      <div class="md:hidden mt-4">
        <button @click="toggleMobileFilters" 
          class="w-full bg-gray-800 p-2 rounded text-center font-bold text-yellow-500 border border-gray-700">
          {{ showMobileFilters ? 'Ocultar Filtros' : 'Mostrar Filtros' }}
        </button>
      </div>
    </section>

    <section class="container mx-auto p-6 flex-grow flex flex-col md:flex-row">
      <!-- Sidebar / Filters -->
      <aside :class="`${showMobileFilters ? 'block' : 'hidden'} md:block w-full md:w-64 bg-gray-800 p-4 rounded-lg md:mr-6 mb-6 md:mb-0 transform transition-all`">
        <h2 class="text-xl font-bold mb-4">Filtros</h2>

        <div class="mb-6">
          <h3 class="font-semibold mb-2">Plataforma</h3>
          <div class="space-y-2">
            <label v-for="platform in filters.availablePlatforms" :key="platform" class="flex items-center">
              <input type="checkbox" v-model="filters.platforms" :value="platform" @change="applyFilters"
                class="mr-2 rounded text-yellow-500 focus:ring-yellow-500">
              {{ platform }}
            </label>
          </div>
        </div>

        <div class="mb-6">
          <h3 class="font-semibold mb-2">Región</h3>
          <div class="space-y-2">
            <label v-for="region in filters.availableRegions" :key="region" class="flex items-center">
              <input type="checkbox" v-model="filters.regions" :value="region" @change="applyFilters"
                class="mr-2 rounded text-yellow-500 focus:ring-yellow-500">
              {{ region }}
            </label>
          </div>
        </div>

        <div class="mb-6">
          <h3 class="font-semibold mb-2">Rango de precio (${{ filters.priceRange[0] }} - ${{ filters.priceRange[1] }})
          </h3>
          <div class="flex space-x-4 mb-2">
            <input type="number" v-model="filters.priceRange[0]" :min="filters.originalPriceRange[0]"
              :max="filters.priceRange[1]" @change="applyFilters"
              class="w-1/2 p-1 rounded bg-gray-700 text-white border border-gray-600 focus:border-yellow-500 focus:ring-yellow-500">
            <input type="number" v-model="filters.priceRange[1]" :min="filters.priceRange[0]"
              :max="filters.originalPriceRange[1]" @change="applyFilters"
              class="w-1/2 p-1 rounded bg-gray-700 text-white border border-gray-600 focus:border-yellow-500 focus:ring-yellow-500">
          </div>
          <input type="range" v-model="filters.priceRange[1]" :min="filters.originalPriceRange[0]"
            :max="filters.originalPriceRange[1]" step="1" @change="applyFilters" class="w-full accent-yellow-500">
          <div class="flex justify-between text-sm mt-1">
            <span>${{ filters.originalPriceRange[0] }}</span>
            <span>${{ filters.originalPriceRange[1] }}</span>
          </div>
        </div>

        <div class="mb-6">
          <h3 class="font-semibold mb-2">Tiempo de entrega</h3>
          <select v-model="filters.deliveryTime" @change="applyFilters"
            class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600 focus:border-yellow-500 focus:ring-yellow-500">
            <option value="">Todos</option>
            <option value="Instantaneo">Instantáneo</option>
            <option value="24 horas">24 horas</option>
            <option value="1-3 días">1-3 días</option>
          </select>
        </div>

        <button @click="resetFilters"
          class="w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded transition-colors">
          Limpiar filtros
        </button>
      </aside>

      <section class="flex-grow">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">
            {{ searchQuery ? `Resultados para "${searchQuery}"` : 'Todas las llaves disponibles' }}
            <span class="text-sm font-normal text-gray-400 ml-2">
              ({{ filteredKeys.length }} resultados)
            </span>
          </h2>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 6" :key="n" class="bg-gray-800 p-4 rounded-lg animate-pulse h-72"></div>
        </div>

        <div v-else-if="filteredKeys.length === 0" class="bg-gray-800 p-8 rounded-lg text-center">
          <p class="text-gray-300 text-lg">No se encontraron llaves que coincidan con los filtros</p>
          <button @click="resetFilters"
            class="mt-4 inline-block bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-medium px-4 py-2 rounded transition-colors">
            Mostrar todas
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="key in filteredKeys" :key="key.id"
            class="bg-gray-800 p-4 rounded-lg hover:shadow-lg transition-all border border-gray-700 hover:border-yellow-500/30">
            <img :src="resolve(key.game.img, 'game')" :alt="key.game.name" class="w-full h-40 object-cover rounded-lg mb-3"
              @error="(e) => e.target.src = getPlaceholder('game')">
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-bold">{{ key.game.name }}</h3>
              <span class="px-2 py-1 bg-gray-700 rounded text-xs">{{ key.platform }}</span>
            </div>

            <div class="flex justify-between items-center mb-3">
              <span class="text-yellow-400 font-bold">${{ parseFloat(key.price) }}</span>
              <span class="text-sm bg-gray-700 px-2 py-1 rounded">{{ key.region }}</span>
            </div>

            <div class="text-sm space-y-1 text-gray-400 mb-4">
              <p class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Entrega: {{ key.delivery_time }}
              </p>
              <p class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Vendedor: {{ key.seller.username }}
              </p>
            </div>

            <RouterLink :to="{ name: 'keyDetails', params: { id: key.id } }"
              class="w-full block text-center bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-medium py-2 rounded transition-colors">
              Ver detalles
            </RouterLink>
          </div>
        </div>
      </section>
    </section>
  </section>
</template>