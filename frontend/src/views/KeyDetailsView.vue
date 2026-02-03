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
const isLoading = ref(true);

const auth = useAuthStore();
const cart = useCartStore();

const isAdding = ref(false);
const addError = ref(null);

onMounted(async () => {
  try {
    isLoading.value = true;
    const { data: keyData } = await axios.get(`/gamekeys/${keyId}`);
    mainKey.value = keyData;

    if (keyData.game_id) {
      const { data: gameData } = await axios.get(`/games/${keyData.game_id}`);
      game.value = gameData;
      
       // Only fetch GameKeys if we have a game ID
       const { data: allKeys } = await axios.get('/gamekeys', {
           params: { game_id: keyData.game_id, state: 'disponible' }
       });
       
       relatedKeys.value = allKeys.filter(k => k.id !== keyData.id && k.region === keyData.region);
    }
  } catch (error) {
    console.error("Error loading key details", error);
    // Optional: router.push('/404'); 
  } finally {
      isLoading.value = false;
  }
});

const handleAddToCart = async () => {
  if (!auth.isLoggedIn) {
    addError.value = 'Debes iniciar sesión para añadir al carrito';
    // redirect to login?
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
    <div v-if="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-500"></div>
    </div>
    
    <template v-else>
        <section class="bg-yellow-500 text-gray-900 text-center py-10">
          <h2 class="text-3xl font-bold">Detalles de la key</h2>
          <p class="mt-2 text-xl font-semibold">{{ game?.name }}</p>
        </section>
        
        <div class="p-5 container mx-auto">
          <RouterLink to="/" class="text-gray-300 hover:text-yellow-400 transition-colors duration-300 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Volver a la Tienda
          </RouterLink>
        </div>

        <section class="container mx-auto px-6 pb-12 flex-grow">
          <div class="bg-gray-800 p-6 rounded-lg flex flex-col lg:flex-row gap-8 shadow-xl">
            <div class="lg:w-1/2 flex justify-center bg-gray-900 rounded-lg p-4 items-center">
              <img v-if="game?.img" :src="game.img" class="max-w-full max-h-125 object-contain rounded-lg shadow-lg" 
                   @error="(e) => e.target.src = 'https://via.placeholder.com/400x600?text=No+Image'"/>
            </div>

            <div class="lg:w-1/2 flex flex-col">
              <div class="flex flex-wrap justify-between items-start mb-6">
                <h3 class="text-3xl font-bold text-yellow-500">{{ game?.name }}</h3>
                <RouterLink :to="`/game/${game?.id}`"
                  class="border border-yellow-500 text-yellow-500 hover:bg-yellow-500 hover:text-gray-900 px-4 py-2 rounded-lg transition-colors text-sm font-semibold uppercase tracking-wider">
                  Ver ficha del juego
                </RouterLink>
              </div>

              <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-700/50 p-4 rounded-lg">
                  <div>
                      <span class="text-gray-400 block text-sm">Plataforma</span>
                      <span class="font-semibold text-lg">{{ mainKey?.platform }}</span>
                  </div>
                  <div>
                      <span class="text-gray-400 block text-sm">Región</span>
                      <span class="font-semibold text-lg">{{ mainKey?.region }}</span>
                  </div>
                  <div>
                      <span class="text-gray-400 block text-sm">Entrega</span>
                      <span class="font-semibold text-lg">{{ mainKey?.delivery_time }}</span>
                  </div>
                   <div>
                      <span class="text-gray-400 block text-sm">Estado</span>
                      <span class="font-semibold text-lg capitalize">{{ mainKey?.state }}</span>
                  </div>
              </div>

              <div class="flex items-center justify-between mb-8 bg-gray-700/30 p-4 rounded-lg border border-gray-600">
                  <div class="flex items-center gap-3">
                       <img v-if="mainKey?.seller?.avatar" :src="mainKey.seller.avatar" alt="avatar"
                        class="w-12 h-12 rounded-full object-cover border-2 border-yellow-500" />
                        <div class="w-12 h-12 rounded-full bg-gray-600 flex items-center justify-center text-xl font-bold text-gray-300 border-2 border-gray-500" v-else>
                            {{ mainKey?.seller?.username?.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <span class="text-gray-400 text-xs block">Vendedor</span>
                            <span class="font-bold text-white">{{ mainKey?.seller?.username }}</span>
                        </div>
                  </div>
                   <div class="text-right">
                       <span class="text-gray-400 text-xs block">Precio</span>
                       <span class="text-3xl font-bold text-green-400">${{ mainKey?.price }}</span>
                   </div>
              </div>

              <div class="flex flex-col gap-3 mt-auto">
                <RouterLink :to="`/purchase/${keyId}`"
                  class="w-full text-center bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded-lg shadow-lg transition-transform hover:scale-[1.02]">
                  Comprar ahora
                </RouterLink>
                
                <button 
                  @click="handleAddToCart" 
                  :disabled="isAdding"
                  class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition-transform hover:scale-[1.02] flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <span v-if="!isAdding">Agregar al carrito</span>
                  <span v-else class="flex items-center">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando...
                  </span>
                </button>
              </div>

              <div v-if="addError" class="bg-red-900/50 border border-red-500 text-red-200 p-3 rounded-lg mt-4 text-center">
                {{ addError }}
              </div>
            </div>
          </div>
          
           <div class="mt-12">
            <h3 class="text-2xl font-bold mb-6 border-b border-gray-700 pb-2">Otras ofertas para este juego</h3>
            <div v-if="relatedKeys.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                 <HorizontalKeyCard v-for="key in relatedKeys" :key="key.id" :id="key.id" :platform="key.platform"
                :price="`$${key.price}`" :region="key.region" :seller="key.seller?.username"
                :seller_img="key.seller?.avatar" :rate="key.rate" :deliverytime="key.delivery_time" />
            </div>
            <p v-else class="text-gray-400 italic">No hay otras ofertas disponibles por el momento.</p>
          </div>
        </section>
    </template>
  </section>
</template>