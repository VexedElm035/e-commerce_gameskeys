<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const purchases = ref([]);
const isLoading = ref(true);
const error = ref(null);
const expandedPurchase = ref(null); // Para controlar qué compra está expandida


const fetchPurchases = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get('/purchases');
    console.log('Datos recibidos:', response.data); // Para depuración
    purchases.value = response.data;
  } catch (err) {
    console.error('Error fetching purchases:', err);
    error.value = err.response?.data?.message || 'Error al cargar las compras';
  } finally {
    isLoading.value = false;
  }
};

const togglePurchase = (purchaseId) => {
  expandedPurchase.value = expandedPurchase.value === purchaseId ? null : purchaseId;
};

onMounted(() => {
  fetchPurchases();
});
</script>

<template>
  <div class="container mx-auto p-6 max-w-6xl">
    <h1 class="text-2xl font-bold mb-6 text-white">Mis Compras</h1>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="n in 3" :key="n" class="bg-gray-800 p-6 rounded-lg animate-pulse">
        <div class="flex gap-4">
          <div class="w-24 h-24 bg-gray-700 rounded-lg"></div>
          <div class="flex-1 space-y-3">
            <div class="h-6 bg-gray-700 rounded w-3/4"></div>
            <div class="h-4 bg-gray-700 rounded w-1/2"></div>
            <div class="h-4 bg-gray-700 rounded w-1/3"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensaje de error -->
    <div v-else-if="error" class="bg-red-500/90 text-white p-4 rounded-lg">
      {{ error }}
    </div>

    <!-- Sin compras -->
    <div v-else-if="purchases.length === 0" class="bg-gray-800/50 p-8 rounded-lg text-center">
      <p class="text-gray-300 text-lg">No tienes compras registradas</p>
      <RouterLink to="/"
        class="mt-4 inline-block bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-medium px-4 py-2 rounded transition-colors">
        Explorar juegos
      </RouterLink>
    </div>

    <!-- Lista de compras -->
    <div v-else class="grid gap-4">
      <div v-for="purchase in purchases" :key="purchase.id"
        class="bg-gray-800/70 rounded-lg overflow-hidden border border-gray-700/50 transition-all">
        <!-- Encabezado de la compra -->
        <div class="p-4 flex flex-col md:flex-row gap-4">
          <!-- Imagen del juego -->
          <img :src="purchase.game_key?.game?.img || 'https://via.placeholder.com/150'"
            :alt="purchase.game_key?.game?.name" class="w-full md:w-32 h-32 object-cover rounded-lg">

          <!-- Información principal -->
          <div class="flex-1">
            <div class="flex justify-between items-start">
              <div>
                <h2 class="text-xl font-bold text-white">{{ purchase.game_key?.game?.name || 'Juego desconocido' }}</h2>
                <div class="flex gap-3 mt-2 text-sm text-gray-300">
                  <span class="bg-gray-700/50 px-2 py-1 rounded">{{ purchase.game_key?.platform }}</span>
                  <span class="bg-gray-700/50 px-2 py-1 rounded">{{ purchase.game_key?.region }}</span>
                </div>
              </div>

              <span class="px-3 py-1 rounded-full text-sm font-medium" :class="{
                'bg-yellow-500/20 text-yellow-400': purchase.state === 'pendiente',
                'bg-green-500/20 text-green-400': purchase.state === 'completado',
                'bg-red-500/20 text-red-400': purchase.state === 'cancelado'
              }">
                {{ purchase.state }}
              </span>
            </div>

            <div class="mt-4 flex flex-wrap justify-between items-center gap-4">
              <div>
                <p class="text-gray-400 text-sm">Fecha: {{ new Date(purchase.created_at).toLocaleDateString() }}</p>
                <p class="text-white font-bold mt-1">${{ purchase.total }}</p>
              </div>

              <button @click="togglePurchase(purchase.id)"
                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span>{{ expandedPurchase === purchase.id ? 'Ocultar' : 'Inspeccionar' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                  :class="{ 'rotate-180': expandedPurchase === purchase.id }" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Detalles expandidos -->
        <div v-if="expandedPurchase === purchase.id"
          class="bg-gray-900/50 border-t border-gray-700/50 p-4 transition-all">
          <div class="grid md:grid-cols-2 gap-6">
            <!-- Sección de información de pago -->
            <div>
              <h3 class="font-medium text-gray-300 mb-3">Información de pago</h3>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-400">Método:</span>
                  <span class="text-white">{{ purchase.pay_method }}</span>
                </div>

                <div class="flex justify-between pt-2 border-t border-gray-700/50">
                  <span class="text-gray-400 font-medium">Total:</span>
                  <span class="text-white font-bold">${{ purchase.total }}</span>
                </div>
              </div>
            </div>

            <!-- Sección de la llave del juego -->
            <div>
              <h3 class="font-medium text-gray-300 mb-3">Tu llave de juego</h3>
              <div class="bg-gray-800/70 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-gray-400 text-sm">Código:</span>
                  <span class="text-yellow-400 font-mono tracking-wider">{{ purchase.game_key?.key || 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400 text-sm">Tiempo de entrega:</span>
                  <span class="text-white">{{ purchase.game_key?.delivery_time }}</span>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-700/50">
                  <p class="text-gray-400 text-sm mb-2">Instrucciones:</p>
                  <ol class="text-gray-300 text-sm space-y-1 list-decimal list-inside">
                    <li>Inicia sesión en la plataforma {{ purchase.game_key?.platform }}</li>
                    <li>Ve a la sección de "Canjear código"</li>
                    <li>Ingresa el código mostrado arriba</li>
                    <li>¡Disfruta de tu juego!</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>