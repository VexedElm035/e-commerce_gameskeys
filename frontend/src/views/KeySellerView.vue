<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import getUnicodeFlagIcon from 'country-flag-icons/unicode';
import { useAuthStore } from '@/stores/auth';
import { debounce } from 'lodash-es';

const authStore = useAuthStore();
const sellerId = computed(() => authStore.userId);

let games = ref([]);
let searchGame = ref('');
let selectedGame = ref(null);
let availablePlatforms = ref([]);
let platform = ref('');
let gameKey = ref('');
let expirationDate = ref('');
let price = ref('');
let tax = computed(() => (price.value * 0.08).toFixed(2));
let deliveryTime = ref('');
let region = ref('');
let state = ref('disponible');
let isLoading = ref(false);
let abortController = ref(null);

deliveryTime.value = 'Instantáneo'; // Valor por defecto

const regions = [
  { name: 'Global', code: 'GL' },
  { name: 'Norte América (NA)', code: 'US' },
  { name: 'México', code: 'MX' },
  { name: 'Europa (EU)', code: 'EU' },
  { name: 'Latinoamérica (LATAM)', code: 'BR' },
  { name: 'Reino Unido (UK)', code: 'GB' },
  { name: 'Alemania', code: 'DE' },
  { name: 'Francia', code: 'FR' },
  { name: 'España', code: 'ES' },
  { name: 'Italia', code: 'IT' },
  { name: 'Polonia', code: 'PL' },
  { name: 'Rusia', code: 'RU' },
  { name: 'Turquía', code: 'TR' },
  { name: 'Australia', code: 'AU' },
  { name: 'Japón', code: 'JP' },
  { name: 'China', code: 'CN' },
  { name: 'Sudeste Asiático (SEA)', code: 'SG' },
  { name: 'Canadá', code: 'CA' },
  { name: 'Argentina', code: 'AR' },
  { name: 'Chile', code: 'CL' },
  { name: 'Colombia', code: 'CO' },
  { name: 'India', code: 'IN' },
  { name: 'Sudáfrica', code: 'ZA' }
];

// Debounced search
const debouncedSearch = debounce((value) => {
  searchGame.value = value;
}, 500);

// Fetch games from IGDB API
async function fetchGames() {
  try {
    if (abortController.value) {
      abortController.value.abort();
    }

    abortController.value = new AbortController();
    isLoading.value = true;

    const response = await axios.get('/igdb/search-games', {
      params: {
        limit: 50, // Aumentamos el límite para mejores resultados de búsqueda
        search: searchGame.value
      },
      signal: abortController.value.signal
    });

    // Transformamos los datos de IGDB al formato que espera tu formulario
    games.value = response.data.map(game => {
      // Si el juego ya viene transformado del backend (por si acaso)
      if (game.img && typeof game.img === 'string') {
        return game;
      }

      // Transformación de datos de IGDB
      return {
        id: game.id,
        name: game.name,
        img: game.cover
          ? `https:${game.cover.url.replace('t_thumb', 't_cover_big')}`
          : null,
        available_platforms: game.platforms
          ? game.platforms.map(p => p.abbreviation || p.name).join(' ')
          : 'PC', // Default a PC si no hay plataformas
        description: game.summary || 'Descripción no disponible',
        first_release_date: game.first_release_date
          ? new Date(game.first_release_date * 1000).toISOString().split('T')[0]
          : null
      };
    });
  } catch (err) {
    if (!axios.isCancel(err)) {
      console.error('Error al obtener los juegos de IGDB', err);
    }
  } finally {
    isLoading.value = false;
  }
}

// Actualizamos para buscar cuando cambia searchGame
watch(searchGame, (newVal) => {
  if (newVal && newVal.length >= 1) { // Solo buscamos si hay al menos 3 caracteres
    fetchGames();
  } else {
    games.value = [];
  }
});

// Resto del código permanece exactamente igual...
// Lifecycle hooks
onMounted(() => {
  window.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
  if (abortController.value) {
    abortController.value.abort();
  }
  window.removeEventListener('keydown', handleEscape);
});

// Handle escape key to clear search
function handleEscape(event) {
  if (event.key === 'Escape') {
    searchGame.value = '';
  }
}

const filteredGames = computed(() => {
  if (!searchGame.value) return [];
  return games.value.filter(game =>
    game.name.toLowerCase().includes(searchGame.value.toLowerCase())
  );
});

watch(selectedGame, (newGame) => {
  if (newGame?.available_platforms) {
    availablePlatforms.value = newGame.available_platforms.split(' ');
    platform.value = availablePlatforms.value[0] || '';
  } else {
    availablePlatforms.value = [];
    platform.value = '';
  }
});

function formatKeyInput(event) {
  let raw = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
  raw = raw.substring(0, 15);
  gameKey.value = raw.match(/.{1,5}/g)?.join('-') || '';
}

function chooseGame(game) {
  searchGame.value = "";
  selectedGame.value = game;
}

async function addKey() {
  try {
    // Primero sincroniza el juego
    const syncResponse = await axios.post('/igdb/sync-game', {
      igdb_id: selectedGame.value.id
    });

    if (!syncResponse.data.success) {
      throw new Error('Error al sincronizar el juego');
    }

    const localGame = syncResponse.data.game;

    // Luego crea la game_key con el ID local
    const response = await axios.post('/gamekeys', {
      game_id: localGame.id, // Usa el ID local
      platform: platform.value,
      key: gameKey.value,
      expiration: expirationDate.value,
      price: price.value,
      tax: tax.value,
      delivery_time: deliveryTime.value,
      region: region.value,
      seller_id: sellerId.value,
      state: state.value
    });

    // Reset form after successful submission
    selectedGame.value = null;
    gameKey.value = '';
    expirationDate.value = '';
    price.value = '';
    deliveryTime.value = '';
    region.value = '';
  } catch (err) {
    console.error('Error al agregar el juego', err);
  }
}
</script>

<!-- El template permanece EXACTAMENTE igual -->
<template>
  <section class="bg-gray-900 text-white min-h-screen flex flex-col">
    <section class="mx-auto mt-6">
      <h3 class="text-xl font-semibold mb-4">Vender una Key</h3>
      <form @submit.prevent="addKey()">

        <div v-if="!selectedGame" class="bg-gray-800 p-6 rounded-lg mt-6 w-full max-w-2xl">

          <label class="block mb-2">Juego</label>
          <div class="relative mb-2">
            <input type="text" :value="searchGame" @input="(e) => debouncedSearch(e.target.value)"
              placeholder="Buscar juego..." class="w-full p-2 rounded text-gray-400" @keydown.esc="searchGame = ''" />
            <ul v-if="searchGame && filteredGames.length"
              class="absolute z-10 bg-gray-700 w-full rounded max-h-64 overflow-auto shadow-lg">
              <li v-for="game in filteredGames" :key="game.id" @click="chooseGame(game)"
                class="flex items-center gap-2 px-2 py-1 hover:bg-gray-600 cursor-pointer">
                <img :src="`${game.img}`" alt="game" class="w-8 h-8 rounded object-cover" loading="lazy" />
                <span>{{ game.name }}</span>
              </li>
            </ul>
            <div v-if="isLoading" class="absolute right-2 top-2">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            </div>
          </div>
        </div>


        <div v-if="selectedGame" class="bg-gray-800 p-6 rounded-lg mt-6 w-full max-w-2xl mx-auto">
          <button @click="selectedGame = null" class=''>Seleccionar otro juego</button>
          <div class="flex items-center gap-2 my-2">
            <img :src="`${selectedGame.img}`" alt="selected" class="w-10 h-10 object-cover rounded" loading="lazy" />
            <span>{{ selectedGame.name }}</span>
          </div>
        </div>
        <div v-if="selectedGame" class="bg-gray-800 p-6 rounded-lg mt-6 w-full max-w-2xl">
          <label class="block mb-2">Plataforma</label>
          <select v-model="platform" class="w-full p-2 rounded text-gray-400 mb-2 bg-gray-800" :disabled="!selectedGame">
            <option v-for="p in availablePlatforms" :key="p" :value="p">
              {{ p.toUpperCase() }}
            </option>
          </select>
         
          <label class="block mb-2">Key</label>
          <input type="text" :value="gameKey" @input="formatKeyInput" placeholder="XXXXX-YYYYY-ZZZZZ"
            class="w-full p-2 rounded text-gray-400 mb-2" :disabled="!selectedGame" maxlength="17" />

          <label class="block mb-2">Fecha de caducidad</label>
          <input type="date" v-model="expirationDate" class="w-full p-2 rounded text-gray-400 mb-2"
            :disabled="!selectedGame" />

          <label class="block mb-2">Precio</label>
          <input type="number" v-model.number="price" placeholder="$0.00" class="w-full p-2 rounded text-gray-400 mb-2"
            :disabled="!selectedGame" min="0" step="0.01" />
          <div class="text-red-400 text-sm mb-4">Tarifa (8%): ${{ tax }}</div>

          <!-- <label class="block mb-2">Tipo de entrega</label>
          <select v-model="deliveryTime" class="w-full p-2 rounded text-gray-100 mb-2 bg-gray-800" :disabled="!selectedGame">
            <option>Instantaneo</option>
            <option>Menos de 24 horas</option>
          </select> -->

          <label class="block mb-2">Región</label>
          <select v-model="region" class="w-full p-2 rounded text-gray-400 mb-4 bg-gray-800" :disabled="!selectedGame">
            <option v-for="r in regions" :key="r.code" :value="r.name">
              {{ getUnicodeFlagIcon(r.code) }} {{ r.name }}
            </option>
          </select>

          <button type="submit"
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-400 disabled:bg-gray-500"
            :disabled="!selectedGame || !gameKey || !price">
            Publicar Key
          </button>
        </div>

      </form>
    </section>
  </section>
</template>