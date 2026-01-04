<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const apiUrl = import.meta.env.VITE_API_URL;
const stats = ref({
  totalEarnings: 0,
  totalPurchases: 0,
  averageTax: 0
});

const isLoading = ref(true);
const error = ref(null);
const router = useRouter()
const banners = ref([]);
const fileInput = ref(null);
const previewUrl = ref(null);
const showPreview = ref(false);
const currentPreview = ref('');

const fetchStats = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get('/admin/earnings');
    stats.value = {
      totalEarnings: response.data.total_earnings,
      totalPurchases: response.data.total_purchases,
      averageTax: response.data.average_tax
    };
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar las estadísticas';
  } finally {
    isLoading.value = false;
  }
};

const fetchBanners = async () => {
  try {
    const response = await axios.get('/uploads');
    banners.value = response.data;
  } catch (err) {
    console.error('Error al cargar los banners:', err);
  }
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    previewUrl.value = URL.createObjectURL(file);
  }
};

const uploadBanner = async () => {
  if (!fileInput.value.files[0]) return;

  const formData = new FormData();
  formData.append('file', fileInput.value.files[0]);

  try {
    await axios.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    await fetchBanners();
    fileInput.value.value = '';
    previewUrl.value = null;
  } catch (error) {
    console.error('Error al subir el banner:', error);
  }
};

const showBannerPreview = (filename) => {
  currentPreview.value = `${apiUrl}/uploads/${filename}`;
  showPreview.value = true;
};

const deleteBanner = async (filename) => {
  if (!confirm('¿Estás seguro de eliminar este banner?')) return;

  try {
    await axios.delete(`/uploads/${filename}`);
    await fetchBanners();
  } catch (error) {
    console.error('Error al eliminar el banner:', error);
  }
};

onMounted(() => {
  fetchStats();
  fetchBanners();
});

function logout() {
  auth.logout()
  router.push('/login')
  showMenu.value = false
}

</script>

<template>

  <section class="bg-gray-900 text-white flex flex-col min-h-screen">

    <section class="bg-yellow-500 text-gray-900 text-center py-10">
      <h2 class="text-3xl font-bold">Panel de Administrador</h2>
      <p class="mt-2">Gestiona tu tienda de manera eficiente</p>
    </section>
    <li>
      <button @click="logout" class="block w-full text-left px-4 py-2 hover:bg-gray-600">Cerrar Sesión</button>
    </li>
    <main class="container mx-auto p-6 flex-grow">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 p-6 rounded-lg border-l-4 border-yellow-500">
          <h3 class="text-xl font-bold mb-4">Ganancias por Impuestos</h3>

          <div v-if="isLoading" class="animate-pulse space-y-3">
            <div class="h-8 bg-gray-700 rounded w-3/4"></div>
            <div class="h-4 bg-gray-700 rounded w-1/2"></div>
            <div class="h-4 bg-gray-700 rounded w-2/3"></div>
          </div>

          <div v-else-if="error" class="text-red-400 text-sm">
            {{ error }}
          </div>

          <div v-else class="space-y-2">
            <div>
              <p class="text-3xl font-bold text-yellow-400">
                ${{ stats.totalEarnings }}
              </p>
              <p class="text-gray-400 text-sm">Total en impuestos</p>
            </div>

            <div class="pt-2 mt-2 border-t border-gray-700">
              <p class="text-white">
                <span class="text-gray-400">Total compras:</span>
                {{ stats.totalPurchases }}
              </p>
              <p class="text-white">
                <span class="text-gray-400">Promedio por compra:</span>
                ${{ stats.averageTax }}
              </p>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700">
              <RouterLink to="/admin/purchases"
                class="text-sm text-yellow-400 hover:text-yellow-300 flex items-center gap-1">
                Ver todas las compras
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </RouterLink>
            </div>
          </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg">
          <h3 class="text-xl font-bold mb-4">Gestión de Productos</h3>
          <ul class="space-y-2">
            <li>
              <RouterLink to="/admin/games" class="hover:text-yellow-400">Gestionar juegos</RouterLink>
            </li>
            <!-- <li>
              <RouterLink to="/admin/genres" class="hover:text-yellow-400">Gestionar generos</RouterLink>
            </li> -->
            <li>
              <RouterLink to="/admin/keys" class="hover:text-yellow-400">Gestionar llaves</RouterLink>
            </li>
            <li>
              <RouterLink to="/admin/users" class="hover:text-yellow-400">Gestionar usuarios</RouterLink>
            </li>
          </ul>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg">
          <h3 class="text-xl font-bold mb-4">Cambiar Banners</h3>

          <div class="mb-4">
            <h4 class="text-lg font-semibold mb-2">Banners actuales</h4>
            <ul class="space-y-2">
              <li v-for="(banner, index) in banners" :key="index" class="flex justify-between items-center">
                <span class="truncate">{{ banner }}</span>
                <div class="flex space-x-2">
                  <button @click="showBannerPreview(banner)"
                    class="text-sm bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-400">
                    Ver
                  </button>
                  <button @click="deleteBanner(banner)"
                    class="text-sm bg-red-500 text-white px-2 py-1 rounded hover:bg-red-400">
                    Eliminar
                  </button>
                </div>
              </li>
            </ul>
          </div>

          <div v-if="banners.length < 5" class="mt-4">
            <h4 class="text-lg font-semibold mb-2">Subir nuevo banner</h4>
            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="w-full mb-2 text-sm text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded file:border-0
                                file:text-sm file:font-semibold
                                file:bg-yellow-500 file:text-gray-900
                                hover:file:bg-yellow-400">

            <div v-if="previewUrl" class="mb-3">
              <p class="text-sm mb-1">Vista previa:</p>
              <img :src="previewUrl" class="max-h-32 rounded">
            </div>

            <button @click="uploadBanner" :disabled="!fileInput?.files?.length"
              class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-400 disabled:bg-gray-600 disabled:cursor-not-allowed">
              Subir Banner
            </button>
          </div>
          <div v-else class="text-yellow-400 text-sm mt-4">
            Has alcanzado el límite de 5 banners. Elimina uno para poder subir otro.
          </div>
        </div>

        <div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
          <div class="bg-gray-800 rounded-lg max-w-4xl max-h-[90vh] p-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">Vista previa del banner</h3>
              <button @click="showPreview = false" class="text-gray-400 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <img :src="currentPreview" class="max-w-full max-h-[80vh] mx-auto">
          </div>
        </div>

      </div>
    </main>
  </section>
</template>