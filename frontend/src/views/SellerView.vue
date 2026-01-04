<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const userId = computed(() => authStore.userId);

const stats = ref({
  total_earnings: 0,
  total_sales: 0,
  available_keys: 0,
  sold_keys: 0
});
const isLoading = ref(true);
const error = ref(null);

const availableKeys = ref([]);
const soldKeys = ref([]);
const isLoadingKeys = ref(true);

const fetchSellerData = async () => {
  try {
    isLoading.value = true;
    isLoadingKeys.value = true;

    const statsResponse = await axios.get(`/sellers/${userId.value}/stats`);
    console.log('Stats response:', statsResponse.data); 
    stats.value = statsResponse.data;

    const availableResponse = await axios.get('/gamekeys-s', {
      params: {
        seller_id: userId.value,
        state: 'disponible'
      }
    });
    availableKeys.value = availableResponse.data;

    const soldResponse = await axios.get('/gamekeys', {
      params: {
        seller_id: userId.value,
        state: 'vendida'
      }
    });
    soldKeys.value = soldResponse.data.filter(key => key.seller_id === userId.value && key.state === 'vendida');

  } catch (err) {
    console.error('Error fetching seller data:', err);
    error.value = err.response?.data?.message || 'Error al cargar los datos';
  } finally {
    isLoading.value = false;
    isLoadingKeys.value = false;
  }
};

onMounted(() => {
  fetchSellerData();
});
</script>

<template>
  <section class="bg-gray-900 text-white flex flex-col min-h-screen">
    <!-- Encabezado -->
    <section class="bg-blue-600 text-center py-10">
      <h2 class="text-3xl font-bold">Panel de Vendedor</h2>
      <p class="mt-2">Administra tus ventas y ganancias</p>
    </section>

    <main class="container mx-auto p-6 flex-grow">
      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tarjeta Ganancias Totales -->
        <div class="bg-gray-800 p-6 rounded-lg border-l-4 border-blue-500">
          <h3 class="text-xl font-bold mb-4">Ganancias Totales</h3>
          <div v-if="isLoading" class="animate-pulse h-8 bg-gray-700 rounded w-3/4"></div>
          <p v-else class="text-3xl font-bold text-blue-400">
            ${{ stats.total_earnings.toFixed(2) }}
          </p>
        </div>

        <!-- Tarjeta Ventas Totales -->
        <div class="bg-gray-800 p-6 rounded-lg border-l-4 border-green-500">
          <h3 class="text-xl font-bold mb-4">Ventas Totales</h3>
          <div v-if="isLoading" class="animate-pulse h-8 bg-gray-700 rounded w-3/4"></div>
          <p v-else class="text-3xl font-bold text-green-400">
            {{ stats.total_sales }}
          </p>
        </div>

        <!-- Tarjeta Llaves Disponibles -->
        <div class="bg-gray-800 p-6 rounded-lg border-l-4 border-yellow-500">
          <h3 class="text-xl font-bold mb-4">Llaves Disponibles</h3>
          <div v-if="isLoading" class="animate-pulse h-8 bg-gray-700 rounded w-3/4"></div>
          <p v-else class="text-3xl font-bold text-yellow-400">
            {{ stats.available_keys }}
          </p>
        </div>

        <!-- Tarjeta Llaves Vendidas -->
        <div class="bg-gray-800 p-6 rounded-lg border-l-4 border-purple-500">
          <h3 class="text-xl font-bold mb-4">Llaves Vendidas</h3>
          <div v-if="isLoading" class="animate-pulse h-8 bg-gray-700 rounded w-3/4"></div>
          <p v-else class="text-3xl font-bold text-purple-400">
            {{ stats.sold_keys }}
          </p>
        </div>
      </div>

      <!-- Mensaje de error -->
      <div v-if="error" class="bg-red-500/90 text-white p-4 rounded-lg mb-6">
        {{ error }}
      </div>

      <!-- Sección de Llaves Disponibles -->
      <section class="mb-10">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold">Tus Llaves Disponibles</h2>
          <RouterLink to="/dashboard/sell" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
            Agregar Nueva Llave
          </RouterLink>
        </div>

        <div v-if="isLoadingKeys" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 3" :key="n" class="bg-gray-800 p-4 rounded-lg animate-pulse h-48"></div>
        </div>

        <div v-else-if="availableKeys.length === 0" class="bg-gray-800/50 p-8 rounded-lg text-center">
          <p class="text-gray-300 text-lg mb-4">No tienes llaves disponibles actualmente</p>
          <RouterLink to="/dashboard/sell"
            class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
            Vender una llave
          </RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="key in availableKeys" :key="key.id"
            class="bg-gray-800 p-4 rounded-lg hover:shadow-lg transition-all border border-gray-700">
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-bold">{{ key.game?.name }}</h3>
              <span class="px-2 py-1 bg-gray-700 rounded text-xs">{{ key.platform }}</span>
            </div>

            <div class="flex justify-between items-center mb-3">
              <span class="text-yellow-400 font-bold">${{ key.price }}</span>
              <span class="text-sm bg-gray-700 px-2 py-1 rounded">{{ key.region }}</span>
            </div>

            <div class="text-sm space-y-1 text-gray-400 mb-4">
              <p>Publicado: {{ new Date(key.created_at).toLocaleDateString() }}</p>
              <p>Tiempo de entrega: {{ key.delivery_time }}</p>
            </div>

            <RouterLink :to="{ name: 'keyDetails', params: { id: key.id } }"
              class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded transition-colors">
              Ver detalles
            </RouterLink>
          </div>
        </div>
      </section>

      <!-- Sección de Llaves Vendidas -->
      <section>
        <h2 class="text-2xl font-bold mb-4">Tus Llaves Vendidas</h2>

        <div v-if="isLoadingKeys" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 3" :key="n" class="bg-gray-800 p-4 rounded-lg animate-pulse h-48"></div>
        </div>

        <div v-else-if="soldKeys.length === 0" class="bg-gray-800/50 p-8 rounded-lg text-center">
          <p class="text-gray-300 text-lg">No has vendido ninguna llave aún</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
            <thead class="bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left">Juego</th>
                <th class="px-4 py-3 text-left">Plataforma</th>
                <th class="px-4 py-3 text-left">Precio</th>
                <th class="px-4 py-3 text-left">Fecha Venta</th>
                <th class="px-4 py-3 text-left">Ganancia</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
              <tr v-for="key in soldKeys" :key="key.id" class="hover:bg-gray-700/50">
                <td class="px-4 py-3">{{ key.game?.name }}</td>
                <td class="px-4 py-3">{{ key.platform }}</td>
                <td class="px-4 py-3">${{ key.price }}</td>
                <td class="px-4 py-3">{{ new Date(key.updated_at).toLocaleDateString() }}</td>
                <td class="px-4 py-3 text-green-400">${{ (key.price * 0.9) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </section>
</template>