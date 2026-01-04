<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useCartStore } from '@/stores/cart';

const cartStore = useCartStore();

const calculateTotal = (items) => {
  return items.reduce((sum, item) => {
    const price = parseFloat(item.game_key?.price) || 0;
    return sum + price;
  }, 0).toFixed(2);
};

const calculateSubtotal = (items) => {
  return items.reduce((sum, item) => {
    return sum + (parseFloat(item.game_key?.price) || 0);
  }, 0).toFixed(2);
};

onMounted(() => {
  cartStore.fetchCart();

  window.addEventListener('cart-updated', cartStore.fetchCart);

  return () => {
    window.removeEventListener('cart-updated', cartStore.fetchCart);
  };
});
</script>

<template>
  <section class="bg-gray-900 text-white flex flex-col min-h-screen flex-1">
    <section class="container mx-auto p-6 flex-grow">
      <button @click="cartStore.clearCart" :disabled="cartStore.clearingCart"
        class="flex items-center text-sm text-red-400 hover:text-red-300 transition-colors mb-4"
        :class="{ 'opacity-50 cursor-not-allowed': cartStore.clearingCart }">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <span v-if="!cartStore.clearingCart">Vaciar carrito</span>
        <span v-else>Eliminando...</span>
      </button>

      <h2 class="text-2xl font-bold text-center mb-6">Carrito de Compras</h2>

      <div v-if="cartStore.isLoading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-500"></div>
        <span class="sr-only">Cargando...</span>
      </div>

      <div v-else-if="cartStore.error" class="bg-red-900/30 border border-red-700 rounded-lg p-6 mb-8">
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500 mr-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-xl font-semibold mb-1">Error</h3>
            <p class="text-gray-300">{{ cartStore.error }}</p>
          </div>
        </div>
      </div>

      <div v-else-if="!cartStore.items || cartStore.items.length === 0" class="bg-gray-800 p-8 rounded-lg text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-500 mb-4" fill="none"
          viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-xl mb-4">Tu carrito está vacío</p>
        <RouterLink to="/"
          class="inline-block bg-yellow-500 hover:bg-yellow-400 text-gray-900 px-6 py-3 rounded-lg font-medium transition-colors">
          Explorar Juegos
        </RouterLink>
      </div>

      <div v-else class="bg-gray-800 rounded-lg overflow-hidden">
        <div class="divide-y divide-gray-700">
          <div v-for="item in cartStore.items" :key="item.id" class="p-6 flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
              <img :src="item.game_key?.game?.img || '/placeholder-game.jpg'"
                :alt="item.game_key?.game?.name || 'Imagen del juego'" class="w-32 h-32 object-cover rounded-lg">
            </div>

            <div class="flex-grow">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-lg font-semibold">{{ item.game_key?.game?.name || 'Nombre no disponible' }}</h3>
                  <div class="mt-2 grid grid-cols-2 gap-4 text-sm text-gray-300">
                    <div>
                      <p class="font-medium">Plataforma</p>
                      <p>{{ item.game_key?.platform || 'N/A' }}</p>
                    </div>
                    <div>
                      <p class="font-medium">Región</p>
                      <p>{{ item.game_key?.region || 'N/A' }}</p>
                    </div>

                    <div>
                      <p class="font-medium">Entrega</p>
                      <p>{{ item.game_key?.delivery_time || 'Instantánea' }}</p>
                    </div>
                  </div>
                </div>

                <div class="flex flex-col items-end">
                  <p class="text-xl font-bold text-yellow-400 mb-4">
                    ${{ parseFloat(item.game_key?.price || 0).toFixed(2) }}
                  </p>
                  <button @click="cartStore.removeFromCart(item.id)"
                    class="text-gray-400 hover:text-red-500 transition-colors" aria-label="Eliminar del carrito">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-gray-700">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold">Resumen del Pedido</h3>
            <p class="text-gray-400">{{ cartStore.items.length }} {{ cartStore.items.length === 1 ? 'producto' :
              'productos' }}</p>
          </div>

          <div class="space-y-3 mb-6">
            <div class="flex justify-between">
              <span class="text-gray-400">Subtotal</span>
              <span>${{ calculateSubtotal(cartStore.items) }}</span>
            </div>

            <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-700">
              <span>Total</span>
              <span class="text-yellow-400">${{ calculateTotal(cartStore.items) }}</span>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-4">
            <RouterLink to="/" class="px-6 py-3 text-center bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
              Seguir Comprando
            </RouterLink>
            <RouterLink to="/purchase"
              class="px-6 py-3 bg-yellow-500 hover:bg-yellow-400 text-gray-900 rounded-lg font-bold transition-colors flex-1 text-center">
              Proceder al Pago
            </RouterLink>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>