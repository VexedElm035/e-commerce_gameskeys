<script setup>
import HorizontalKeyCard from '@/components/HorizontalKeyCard.vue';
import router from '@/router';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const keyId = route.params.id;
const mainKey = ref(null);
const relatedKeys = ref([]);
const game = ref(null);

const auth = useAuthStore();
const cart = useCartStore();

const isAdding = ref(false);
const addError = ref(null);

onMounted(async () => {
  try {
    const { data: keyData } = await axios.get(`/gamekeys/${keyId}`);
    mainKey.value = keyData;

    const { data: gameData } = await axios.get(`/games/${keyData.game_id}`);
    game.value = gameData;

    const { data: allKeys } = await axios.get('/gamekeys');

    relatedKeys.value = allKeys.filter(k =>
      k.id !== keyData.id &&
      k.game_id === keyData.game_id &&
      k.region === keyData.region &&
      k.platform === keyData.platform &&
      k.state === 'disponible'
    );

  } catch (error) {
    router.push('/error');
  }
});

const handleAddToCart = async () => {
  if (!auth.isLoggedIn) {
    addError.value = 'Debes iniciar sesión para añadir al carrito';
    return;
  }

  if (!mainKey.value) return;

  isAdding.value = true;
  addError.value = null;

  const success = await cart.addToCart(mainKey.value.id);

  if (success) {
    console.log('Añadido al carrito correctamente');
  } else {
    addError.value = cart.error || 'Error al añadir al carrito';
  }

  isAdding.value = false;
};
</script>

<template>
  <section class="bg-gray-900 text-white flex flex-col min-h-screen flex-1">
    <section class="bg-yellow-500 text-gray-900 text-center py-10">
      <h2 class="text-3xl font-bold">Detalles de la key</h2>
      <p class="mt-2">{{ game?.name }}</p>
    </section>
    
    <div class="p-5">
      <RouterLink to="/" class=" text-gray-300 hover:text-yellow-400 transition-colors duration-300">
        < Volver a la Tienda</RouterLink>
    </div>

    <section class="container mx-auto pr-6 pl-6 flex-grow">
      <div class="bg-gray-800 p-6 rounded-lg flex flex-col md:flex-row gap-6">
        <div class="md:w-1/2 flex justify-center">
          <img v-if="game?.img" :src="`${game.img}`" class="max-w-md h-96 object-cover rounded-lg" />
        </div>

        <div class="md:w-1/2">
          <div class="flex flex-row">
            <h3 class="text-2xl font-bold mb-2">{{ game?.name }}</h3>
            <RouterLink :to="`/game/${game?.id}`"
              class="bg-none border-2 border-yellow-400 text-white ml-5 h-10 px-4 py-2 rounded-lg hover:text-yellow-400">
              Ver juego</RouterLink>
          </div>

          <p class="text-lg mb-4">Plataforma: {{ mainKey?.platform }}</p>
          <p class="text-lg mb-4">Región: {{ mainKey?.region }}</p>
          <p class="text-lg mb-4">Precio: ${{ mainKey?.price }}</p>

          <div v-if="mainKey?.seller" class="flex items-center mb-4">
            <img v-if="mainKey?.seller?.img" :src="`${$img_url}/${mainKey.seller.img}`" alt="avatar"
              class="w-10 h-10 rounded-full mr-2" />
            <span class="font-semibold">{{ mainKey.seller.username }}</span>
          </div>

          <div class="flex gap-4 mt-4">
            <RouterLink :to="`/purchase/${keyId}`"
              class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-lg">
              Comprar ahora
            </RouterLink>
            <button 
              @click="handleAddToCart" 
              :disabled="isAdding"
              class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-lg disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <span v-if="!isAdding">Agregar al carrito</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                  fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Añadiendo...
              </span>
            </button>
          </div>

          <div v-if="addError" class="text-red-400 text-sm mt-2">
            {{ addError }}
          </div>

          <div class="bg-gray-900 text-white flex flex-col gap-4 mt-6 p-3 rounded-lg">
            <h3 class="text-xl font-semibold mb-2">Llaves similares:</h3>

            <template v-if="relatedKeys.length > 0">
              <HorizontalKeyCard v-for="key in relatedKeys" :key="key.id" :id="key.id" :platform="key.platform"
                :price="`$${key.price}`" :region="key.region" :seller="key.seller?.username"
                :seller_img="key.seller?.avatar" :rate="key.rate" :deliverytime="key.delivery_time" />
            </template>
            <p v-else>No hay llaves similares para este juego.</p>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>