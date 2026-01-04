<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';

const authStore = useAuthStore();
const cartStore = useCartStore();
const route = useRoute();
const router = useRouter();

const keys = ref([]);
const loading = ref(true);
const error = ref(null);
const processingPayment = ref(false);
const paymentSuccess = ref(false);

// Determinar tipo de compra
const isSinglePurchase = computed(() => !!route.params.id);

// Cálculos de precios
const subtotal = computed(() => {
  return keys.value.reduce((sum, key) => sum + Number(key.price || 0), 0);
});



const total = computed(() => subtotal.value);

// Obtener items a comprar
const fetchPurchaseItems = async () => {
  try {
    loading.value = true;
    error.value = null;

    if (isSinglePurchase.value) {
      // Compra individual
      const { data } = await axios.get(`/gamekeys/${route.params.id}`);
      keys.value = [data];
    } else {
      // Compra desde carrito
      if (cartStore.items.length === 0) {
        router.push('/cart');
        return;
      }

      // Obtener detalles de cada key en el carrito
      const responses = await Promise.all(
        cartStore.items.map(item => axios.get(`/gamekeys/${item.game_key?.id || item.id}`))
      );
      keys.value = responses.map(r => r.data);

    }
  } catch (err) {
    error.value = 'No se pudo cargar la información de los productos';
    console.error('Error fetching purchase items:', err);
  } finally {
    loading.value = false;
  }
};

// Procesar la compra
// Procesar la compra
const realizarCompra = async () => {
  processingPayment.value = true;
  error.value = null;

  try {
    // 1. Procesar todas las compras
    const purchasePromises = keys.value.map(key =>
      axios.post('/purchases', {
        user_id: authStore.userId,
        key_id: key.id,
        tax: Number(key.tax || 0),
        pay_method: 'Tarjeta',
        state: 'pendiente',
        total: Number(key.price || 0),
      }).then(() => {
        return axios.put(`/gamekeys/${key.id}`, {
          state: 'vendida'
        });
      })
    );

    await Promise.all(purchasePromises);

    // 2. Si es compra desde carrito, limpiarlo
    if (!isSinglePurchase.value) {
      await cartStore.clearCart();
    }

    // 3. Actualizar UI y notificar
    paymentSuccess.value = true;
    window.dispatchEvent(new CustomEvent('unread-count-updated'));
    window.dispatchEvent(new CustomEvent('cart-updated'));

  } catch (err) {
    error.value = err.response?.data?.message || 'Error al procesar el pago. Por favor intenta nuevamente.';
    console.error('Error processing payment:', err);
  } finally {
    processingPayment.value = false;
  }
};

// Navegación
const goBack = () => {
  isSinglePurchase.value ? router.push('/') : router.push('/cart');
};

// Inicialización
onMounted(() => {
  fetchPurchaseItems();
});
</script>

<template>
  <section class="bg-gray-900 text-white min-h-screen">
    <!-- Header -->
    <div class="bg-gray-800 py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <button @click="goBack" class="flex items-center text-gray-300 hover:text-white transition-colors">
          <ArrowLeftIcon class="h-5 w-5 mr-2" />
          Volver
        </button>
        <h1 class="text-2xl md:text-3xl font-bold text-center">Resumen de tu compra</h1>
        <div class="w-10"></div> <!-- Spacer para alinear el título -->
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-500 mb-4"></div>
        <p class="text-lg">Cargando tu pedido...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-900/30 border border-red-700 rounded-lg p-6 mb-8">
        <div class="flex items-center">
          <XCircleIcon class="h-10 w-10 text-red-500 mr-4" />
          <div>
            <h3 class="text-xl font-semibold mb-1">Error al cargar</h3>
            <p class="text-gray-300">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div v-else-if="paymentSuccess" class="text-center py-16">
        <div class="max-w-md mx-auto">
          <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-6" />
          <h2 class="text-2xl font-bold mb-4">¡Compra realizada con éxito!</h2>
          <p class="text-gray-300 mb-8">Hemos enviado los detalles de tu compra a tu buzón</p>
          <RouterLink to="/"
            class="inline-flex items-center px-6 py-3 bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-medium rounded-lg transition-colors">
            Volver a la tienda
          </RouterLink>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-700">
              <h2 class="text-xl font-semibold">
                {{ isSinglePurchase ? 'Tu pedido' : `Tu carrito (${keys.length} ${keys.length === 1 ? 'producto' :
                'productos'})` }}
              </h2>
            </div>

            <div v-for="key in keys" :key="key.id" class="p-6 border-b border-gray-700 last:border-b-0">
              <div class="flex flex-col sm:flex-row gap-6">
                <div class="flex-shrink-0">
                  <img :src="key.game?.img || '/placeholder-game.jpg'" :alt="key.game?.name"
                    class="w-32 h-32 object-cover rounded-lg">
                </div>
                <div class="flex-grow">
                  <h3 class="text-lg font-semibold mb-2">{{ key.game?.name || 'Nombre no disponible' }}</h3>
                  <div class="grid grid-cols-2 gap-4 text-sm text-gray-300 mb-4">
                    <div>
                      <p class="font-medium">Plataforma</p>
                      <p>{{ key.platform || 'N/A' }}</p>
                    </div>
                    <div>
                      <p class="font-medium">Región</p>
                      <p>{{ key.region || 'N/A' }}</p>
                    </div>
                    <div>
                      <p class="font-medium">Vendedor</p>
                      <p>{{ key.seller?.username || 'Tienda oficial' }}</p>
                    </div>
                    <div>
                      <p class="font-medium">Entrega</p>
                      <p>{{ key.delivery_time || 'Instantánea' }}</p>
                    </div>
                  </div>
                </div>
                <div class="flex-shrink-0 text-right">
                  <p class="text-lg font-bold text-yellow-400">${{ (key.price || 0) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-800 rounded-xl shadow-lg mt-6 p-6">
            <h2 class="text-xl font-semibold mb-6">Método de pago</h2>
            <div class="bg-gray-700 rounded-lg p-4">
              <div class="flex items-center">
                <CreditCardIcon class="h-6 w-6 text-gray-400 mr-3" />
                <div>
                  <h3 class="font-medium">Tarjeta de crédito/débito</h3>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Número de tarjeta</label>
                  <div class="bg-gray-800 rounded px-4 py-3">•••• •••• •••• 4242</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Fecha de expiración</label>
                  <div class="bg-gray-800 rounded px-4 py-3">••/••</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Código de seguridad</label>
                  <div class="bg-gray-800 rounded px-4 py-3">•••</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-1">Nombre en la tarjeta</label>
                  <div class="bg-gray-800 rounded px-4 py-3">•••••• ••••••</div>
                </div>
              </div>

              <p class="text-xs text-gray-500 mt-4">*pago simulado</p>
            </div>
          </div>
        </div>

        <!-- Order Total -->
        <div class="lg:col-span-1">
          <div class="bg-gray-800 rounded-xl shadow-lg p-6 sticky top-6">
            <h2 class="text-xl font-semibold mb-6">Resumen</h2>

            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-400">Subtotal</span>
                <span>${{ subtotal }}</span>
              </div>

              <div class="border-t border-gray-700 pt-4 flex justify-between font-bold text-lg">
                <span>Total</span>
                <span class="text-yellow-400">${{ total }}</span>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mt-6 text-red-400 text-sm flex items-start">
              <XCircleIcon class="h-5 w-5 mr-2 flex-shrink-0" />
              <span>{{ error }}</span>
            </div>

            <button @click="realizarCompra" :disabled="processingPayment || keys.length === 0"
              class="mt-6 w-full bg-green-600 hover:bg-green-500 text-white py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center"
              :class="{
                'opacity-70 cursor-not-allowed': processingPayment || keys.length === 0,
                'bg-yellow-500 hover:bg-yellow-400 text-gray-900': keys.length === 0
              }">
              <span v-if="!processingPayment && keys.length > 0">Confirmar y pagar</span>
              <span v-else-if="keys.length === 0">No hay productos</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                Procesando...
              </span>
            </button>

            <p class="text-xs text-gray-500 mt-4 text-center">
              Al completar tu compra, aceptas nuestros <a href="#" class="text-yellow-400 hover:underline">Términos de
                servicio</a> y <a href="#" class="text-yellow-400 hover:underline">Política de privacidad</a>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>