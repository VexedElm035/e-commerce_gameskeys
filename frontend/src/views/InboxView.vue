<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import { useToastStore } from '@/stores/toast';

const router = useRouter();
const toast = useToastStore();
const messages = ref([]);
const unreadCount = ref(0);
const loading = ref(true);
const activeFilter = ref('all');
const currentPage = ref(1);
const expandedMessageId = ref(null);

const filters = [
  { type: 'all', label: 'Todos' },
];

const fetchMessages = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/messages', {
      params: {
        page: currentPage.value,
        type: activeFilter.value === 'all' ? null : activeFilter.value
      }
    });
    messages.value = response.data.messages.data;
    unreadCount.value = response.data.unread_count;
  } catch (err) {
    console.error(err);
    toast.trigger('Error al cargar los mensajes', 'error');
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (messageId) => {
  try {
    await axios.put(`/messages/${messageId}/read`);
    const message = messages.value.find(m => m.id === messageId);
    if (message && !message.is_read) {
      message.is_read = true;
      unreadCount.value--;
      window.dispatchEvent(new CustomEvent('unread-count-updated'));
    }
  } catch (err) {
    console.error('Error al marcar como leído:', err);
    toast.trigger('Error al actualizar estado del mensaje', 'error');
  }
};

const changeFilter = (filter) => {
  activeFilter.value = filter;
  currentPage.value = 1;
  fetchMessages();
};

const toggleExpand = (messageId) => {
  expandedMessageId.value = expandedMessageId.value === messageId ? null : messageId;
};

onMounted(fetchMessages);
</script>

<template>
  <section class="bg-gray-900 text-white min-h-screen">
    <div class="bg-gray-800 py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <button @click="router.go(-1)" class="flex items-center text-gray-300 hover:text-white transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Volver
        </button>
        <h1 class="text-2xl md:text-3xl font-bold text-center">Bandeja de Entrada</h1>
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <span class="bg-yellow-500 text-gray-900 rounded-full px-2 py-1 text-xs font-bold">
            {{ unreadCount }} no leídos
          </span>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-500 mb-4"></div>
        <p class="text-lg">Cargando tus mensajes...</p>
      </div>

      <div v-else-if="error" class="bg-red-900/30 border border-red-700 rounded-lg p-6 mb-8">
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500 mr-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-xl font-semibold mb-1">Error al cargar</h3>
            <p class="text-gray-300">{{ error }}</p>
            <button @click="fetchMessages" class="mt-3 text-yellow-400 hover:underline">
              Reintentar
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="messages.length === 0" class="text-center py-16">
        <div class="max-w-md mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500 mx-auto mb-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <h2 class="text-2xl font-bold mb-4">No hay mensajes</h2>
          <p class="text-gray-300">Tu bandeja de entrada está vacía</p>
        </div>
      </div>

      <div v-else class="bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="border-b border-gray-700 flex overflow-x-auto">
          <button v-for="filter in filters" :key="filter.type" @click="changeFilter(filter.type)"
            class="px-6 py-4 font-medium whitespace-nowrap transition-colors" :class="{
              'text-yellow-400 border-b-2 border-yellow-400': activeFilter === filter.type,
              'text-gray-400 hover:text-white': activeFilter !== filter.type
            }">
            {{ filter.label }}
          </button>
        </div>

        <div class="divide-y divide-gray-700">
          <div v-for="message in messages" :key="message.id" class="p-6 hover:bg-gray-700/50 transition-colors"
            :class="{ 'bg-gray-700/30': !message.is_read }">
            <div @click="markAsRead(message.id)" class="cursor-pointer">
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 pt-1">
                  <div class="h-3 w-3 rounded-full" :class="message.is_read ? 'bg-gray-500' : 'bg-yellow-400'"></div>
                </div>
                <div class="flex-grow">
                  <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-lg mb-1">{{ message.subject }}</h3>
                    <span class="text-xs text-gray-400">
                      {{ new Date(message.created_at).toLocaleDateString() }}
                    </span>
                  </div>
                  <p class="text-gray-300 mb-2">{{ message.content }}</p>

                  <div class="flex items-center gap-3 mt-3">
                    <span class="text-xs px-2 py-1 rounded-full" :class="{
                      'bg-blue-900/50 text-blue-300': message.type === 'purchase',
                      'bg-green-900/50 text-green-300': message.type === 'sale',
                      'bg-purple-900/50 text-purple-300': message.type === 'review'
                    }">
                      {{ message.type }}
                    </span>

                    <span class="text-xs text-gray-400 flex items-center">
                      <svg v-if="message.sender?.role === 'admin'" xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-yellow-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                      De: {{ message.sender?.username || 'Sistema' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="message.type === 'purchase' && message.purchase_id" class="mt-4">
              <div class="flex gap-3">
                <button @click="toggleExpand(message.id)"
                  class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm">
                  {{ expandedMessageId === message.id ? 'Ocultar clave' : 'Inspeccionar compra' }}
                </button>

                <RouterLink :to="'/orders'"
                  class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-sm">
                  Ver todas mis compras
                </RouterLink>
              </div>

              <div v-if="expandedMessageId === message.id" class="mt-4 bg-gray-700/50 p-4 rounded-lg">
                <div v-if="message.purchase?.gameKey" class="space-y-3">
                  <div>
                    <h4 class="font-medium text-yellow-400 mb-1">Tu clave de juego:</h4>
                    <div class="bg-gray-800 p-3 rounded-lg font-mono text-lg tracking-wider select-all">
                      {{ message.purchase.gameKey.key || 'Clave no disponible' }}
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                      Plataforma: {{ message.purchase.gameKey.platform }}
                    </p>
                  </div>

                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p class="text-gray-400">Fecha compra:</p>
                      <p>{{ new Date(message.purchase.created_at).toLocaleString() }}</p>
                    </div>
                    <div>
                      <p class="text-gray-400">Estado:</p>
                      <p class="capitalize">{{ message.purchase.state }}</p>
                    </div>
                  </div>
                </div>

                <div v-else class="text-gray-400">
                  Cargando detalles de la compra...
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-800 px-6 py-4 border-t border-gray-700 flex justify-between items-center">
          <button @click="currentPage--; fetchMessages()" :disabled="currentPage === 1"
            class="px-4 py-2 rounded-lg border border-gray-600 disabled:opacity-50">
            Anterior
          </button>
          <span class="text-sm text-gray-400">Página {{ currentPage }}</span>
          <button @click="currentPage++; fetchMessages()" :disabled="messages.length < 10"
            class="px-4 py-2 rounded-lg border border-gray-600 disabled:opacity-50">
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.message-unread-indicator {
  box-shadow: 0 0 0 0 rgba(234, 179, 8, 1);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.7);
  }

  70% {
    box-shadow: 0 0 0 6px rgba(234, 179, 8, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(234, 179, 8, 0);
  }
}

.select-all {
  user-select: all;
  -webkit-user-select: all;
  -moz-user-select: all;
  -ms-user-select: all;
}
</style>