<script setup>
import router from '@/router';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute } from 'vue-router';

const authStore = useAuthStore();
const token = computed(() => authStore.token);
const route = useRoute();
const userId = route.params.id;
let user = ref({});
const isCurrentUser = computed(() => authStore.userId === userId);
const isSeller = computed(() => user.value.role === 'seller');

onMounted(async () => {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  try {
    const response = await axios.get(`/users/${userId}`);
    user.value = response.data;
  } catch (error) {
    console.error('Error al obtener el perfil:', error);
    if (error.response && error.response.status === 401) {
      router.push('/login');
    }
  }
});

async function becomeSeller() {
  if (!confirm('¿Estás seguro que quieres convertirte en vendedor? Podrás publicar y vender claves de juegos.')) {
    return;
  }

  try {
    console.log('Token:', token.value);
    const response = await axios.put(`/users/${userId}`, {
      role: 'seller'
    });
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

    user.value.role = 'seller';
    authStore.setRole('seller');

    alert('¡Felicidades! Ahora eres un vendedor. Puedes comenzar a publicar tus productos.');
  } catch (error) {
    console.error('Error al actualizar el rol:', error);
    alert('Ocurrió un error al intentar convertirte en vendedor.');
  }
}
</script>

<template>
  <section class="bg-gray-900 text-white min-h-screen">
    <div class="container mx-auto px-4 py-8">
      <!-- Header del perfil -->
      <div class="flex flex-col md:flex-row gap-8 mb-8">
        <!-- Avatar y datos básicos -->
        <div class="bg-gray-800 p-6 rounded-lg w-full md:w-1/3">
          <div class="flex flex-col items-center">
            <img :src="user.avatar ? `${user.avatar}` : '../img/def.jpg'"
              class="w-32 h-32 rounded-full object-cover mb-4 border-2 border-yellow-500" />
            <h2 class="text-2xl font-bold">{{ user.username }}</h2>
            <p class="text-gray-400">{{ user.email }}</p>

            <div class="mt-4 px-4 py-2 rounded-full text-sm font-semibold" :class="{
              'bg-purple-600': user.role === 'admin',
              'bg-yellow-600': user.role === 'seller',
              'bg-blue-600': user.role === 'user'
            }">
              {{
                user.role === 'admin' ? 'Administrador' :
                  user.role === 'seller' ? 'Vendedor' : 'Usuario'
              }}
            </div>
          </div>
        </div>

        <!-- Información detallada -->
        <div class="bg-gray-800 p-6 rounded-lg w-full md:w-2/3">
          <h3 class="text-xl font-semibold mb-4 border-b border-gray-700 pb-2">Información de la cuenta</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-400 mb-1">Nombre de usuario</label>
              <input type="text" :value="user.username" class="w-full p-2 rounded bg-gray-700 text-white mb-4"
                disabled />
            </div>

            <div>
              <label class="block text-gray-400 mb-1">Correo electrónico</label>
              <input type="email" :value="user.email" class="w-full p-2 rounded bg-gray-700 text-white" disabled />
            </div>

            <div>
              <label class="block text-gray-400 mb-1">Fecha de registro</label>
              <input type="text" :value="new Date(user.register_date).toLocaleDateString()"
                class="w-full p-2 rounded bg-gray-700 text-white" disabled />
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de acciones -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Mis Compras -->
        <div class="bg-gray-800 p-6 rounded-lg">
          <h3 class="text-xl font-semibold mb-4 border-b border-gray-700 pb-2">Mis Compras</h3>
          <p class="text-gray-400 mb-4">Revisa el historial de tus compras y descargas.</p>
          <RouterLink to="/orders"
            class="inline-block bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-medium px-4 py-2 rounded transition duration-200">
            Ver mis compras
          </RouterLink>
        </div>

        <!-- Mis Ventas / Convertirse en vendedor -->
        <div class="bg-gray-800 p-6 rounded-lg">
          <h3 class="text-xl font-semibold mb-4 border-b border-gray-700 pb-2">
            {{ isSeller ? 'Mis Ventas' : 'Vender Juegos' }}
          </h3>

          <template v-if="isSeller">
            <p class="text-gray-400 mb-4">Administra tus productos y ventas como vendedor.</p>
            <RouterLink to="/dashboard"
              class="inline-block bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-medium px-4 py-2 rounded transition duration-200">
              Panel de vendedor
            </RouterLink>
          </template>

          <template v-else>
            <p class="text-gray-400 mb-4">Conviértete en vendedor y comienza a publicar tus claves de juegos.</p>
            <button @click="becomeSeller"
              class="inline-block bg-green-600 hover:bg-green-500 text-white font-medium px-4 py-2 rounded transition duration-200">
              Quiero ser vendedor
            </button>
          </template>

          <!-- <template v-else>
            <p class="text-gray-400">Este usuario no es vendedor.</p>
          </template> -->
        </div>
      </div>
    </div>
  </section>
</template>