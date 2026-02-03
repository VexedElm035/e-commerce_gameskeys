<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios'

const purchases = ref([]);
const error = ref(null);

const fetchPurchases = async () => {
  try {
    const response = await axios.get('/purchases');
    purchases.value = response.data;
  } catch (err) {
    console.error('Error fetching purchases:', err);
    error.value = err.response?.data?.message || 'Error al cargar los datos';
  }
};

onMounted(() => {
  fetchPurchases();
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
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Comprador</th>
                    <th class="px-4 py-3 text-left">Juego</th>
                    <th class="px-4 py-3 text-left">Método</th>
                    <th class="px-4 py-3 text-left">Total</th>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                  <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-700/50">
                    <td class="px-4 py-3 text-gray-400">#{{ purchase.id }}</td>
                    <td class="px-4 py-3 font-semibold">{{ purchase.user?.username || 'Usuario eliminado' }}</td>
                    <td class="px-4 py-3">{{ purchase.game_key?.game?.name || 'Juego desconocido' }}</td>
                    <td class="px-4 py-3">{{ purchase.pay_method }}</td>
                    <td class="px-4 py-3 text-yellow-400">${{ purchase.total }}</td>
                    <td class="px-4 py-3">{{ new Date(purchase.created_at).toLocaleDateString() }}</td>
                    <td class="px-4 py-3">
                      <span class="px-2 py-1 rounded text-xs" :class="{
                        'bg-green-500/20 text-green-400': purchase.state === 'completado',
                        'bg-yellow-500/20 text-yellow-400': purchase.state === 'pendiente',
                        'bg-red-500/20 text-red-400': purchase.state === 'cancelado'
                      }">
                        {{ purchase.state }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </main>
  </section>
</template>
