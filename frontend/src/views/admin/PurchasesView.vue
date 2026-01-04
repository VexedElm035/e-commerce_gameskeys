<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios'

const soldKeys = ref([]);

const fetchSellerData = async () => {
  try {
    const soldResponse = await axios.get('/gamekeys', {
      params: {
        state: 'vendida'
      }
    });
    soldKeys.value = soldResponse.data;

  } catch (err) {
    console.error('Error fetching seller data:', err);
    error.value = err.response?.data?.message || 'Error al cargar los datos';
  }
};
onMounted(() => {
  fetchSellerData();
});

</script>

<template>
  <section class="bg-gray-900 text-white flex flex-col min-h-screen">
    <RouterLink to="/admin" class="hover:text-yellow-400">
      < Volver</RouterLink>
        <main class="container mx-auto p-6 flex-grow">

          <section class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Llaves Vendidas</h2>


            <div class="overflow-x-auto">
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
                    <td class="px-4 py-3 text-green-400">${{ (key.tax) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </main>
  </section>
</template>
