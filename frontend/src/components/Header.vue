<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import axios from 'axios'
import { useImage } from '@/composables/useImage';

const cart = useCartStore();
const { resolve, getPlaceholder } = useImage();

const props = defineProps({
  hasSidebar: {
    type: Boolean,
    default: true
  },
  isMenuVisible: Boolean
})

const auth = useAuthStore()
const router = useRouter()
const showMenu = ref(false)
const unreadCount = ref(0)
const loadingUnread = ref(true)

const userId = computed(() => auth.userId)
const avatar = computed(() => auth.avatar)

const fetchUnreadCount = async () => {
  try {
    if (!auth.isLoggedIn) return

    const response = await axios.get('/messages/unread/count')
    unreadCount.value = response.data.count
  } catch (error) {
    console.error('Error fetching unread count:', error)
  } finally {
    loadingUnread.value = false
  }
}

onMounted(() => {
  fetchUnreadCount()
  window.addEventListener('unread-count-updated', fetchUnreadCount)
  cart.fetchCart();
  window.addEventListener('cart-updated', cart.fetchCart);
})

onUnmounted(() => {
  window.removeEventListener('unread-count-updated', fetchUnreadCount)
})

function toggleMenu() {
  showMenu.value = !showMenu.value
}

function logout() {
  auth.logout()
  router.push('/login')
  showMenu.value = false
}
</script>

<template>
  <header class="bg-gray-800 p-4 flex justify-between items-center relative">

    <button v-if="hasSidebar" @click="$emit('toggle-menu')" class="text-white focus:outline-none cursor-pointer"
      aria-label="Toggle menu">
      <div class="w-5 h-[20px] relative hover:w-6 transition-all duration-300 ease-in-out">
        <!-- Barra superior -->
        <div class="bg-white w-full h-[2px] absolute transition-all duration-300 ease-in-out"
          :class="{ 'rotate-45 translate-y-[6px]': isMenuVisible, 'top-0': !isMenuVisible }"></div>
        <!-- Barra del medio -->
        <div class="bg-white w-full h-[2px] absolute top-[8px] transition-all duration-300 ease-in-out"
          :class="{ 'opacity-0': isMenuVisible }"></div>
        <!-- Barra inferior -->
        <div class="bg-white w-full h-[2px] absolute transition-all duration-300 ease-in-out"
          :class="{ '-rotate-45 translate-y-[6px]': isMenuVisible, 'top-[16px]': !isMenuVisible }"></div>
      </div>
    </button>

    <RouterLink to="/" class="text-2xl text-white font-bold">GameKeys</RouterLink>

    <nav>
      <ul class="flex gap-4 items-center relative">

        <template v-if="!auth.isLoggedIn">
          <li class="relative group">
            <RouterLink to="/login" class="text-gray-300 hover:text-yellow-400 transition-colors duration-300">Iniciar
              Sesión</RouterLink>
            <div
              class="bg-yellow-400 h-[1px] absolute bottom-0 left-1/2 transform -translate-x-1/2 transition-all duration-300 ease-in-out w-0 group-hover:w-full">
            </div>
          </li>
          <li>
            <RouterLink to="/signup"
              class="text-gray-200 hover:bg-blue-600 hover:text-white bg-blue-500 p-2 rounded-lg transition-all duration-300 ease-in-out">
              Registrarse</RouterLink>
          </li>
        </template>

        <template v-else>
          <li class="relative">
            <RouterLink to="/inbox"
              class="text-gray-300 hover:text-yellow-400 transition-all duration-300 ease-in-out relative">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                <path d="M3 7l9 6l9 -6" />
              </svg>

              <span v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-white text-xs animate-pulse">
                {{ unreadCount }}
              </span>
            </RouterLink>
          </li>

          <li class="relative">
            <RouterLink to="/cart" class="text-gray-300 hover:text-yellow-400 transition-colors relative">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path
                  d="M6 2a1 1 0 0 1 .993 .883l.007 .117v1.068l13.071 .935a1 1 0 0 1 .929 1.024l-.01 .114l-1 7a1 1 0 0 1 -.877 .853l-.113 .006h-12v2h10a3 3 0 1 1 -2.995 3.176l-.005 -.176l.005 -.176c.017 -.288 .074 -.564 .166 -.824h-5.342a3 3 0 1 1 -5.824 1.176l-.005 -.176l.005 -.176a3.002 3.002 0 0 1 1.995 -2.654v-12.17h-1a1 1 0 0 1 -.993 -.883l-.007 -.117a1 1 0 0 1 .883 -.993l.117 -.007h2zm0 16a1 1 0 1 0 0 2a1 1 0 0 0 0 -2zm11 0a1 1 0 1 0 0 2a1 1 0 0 0 0 -2z">
                </path>
              </svg>
              <span v-if="cart.count > 0"
                class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-white text-xs">
                {{ cart.count }}
              </span>
            </RouterLink>
          </li>


          <li class="relative">
            <button @click="toggleMenu"
              class="text-gray-300 hover:text-yellow-400 focus:outline-none cursor-pointer w-6 h-6">
              <img :src="resolve(avatar, 'user')" alt="avatar" class="w-full h-full rounded-full object-cover"
                   @error="(e) => e.target.src = getPlaceholder('user')">
            </button>

            <ul v-if="showMenu" class="absolute right-0 mt-2 w-48 bg-gray-700 text-white rounded-lg shadow-lg z-50">
              <li>
                <RouterLink :to="`/profile/${userId}`" class="block px-4 py-2 hover:bg-gray-600">Perfil</RouterLink>
              </li>
              <li>
                <RouterLink to="/orders" class="block px-4 py-2 hover:bg-gray-600">Historial de pedidos</RouterLink>
              </li>
              <li>
                <button @click="logout" class="block w-full text-left px-4 py-2 hover:bg-gray-600">Cerrar
                  Sesión</button>
              </li>
            </ul>
          </li>
        </template>
      </ul>
    </nav>
  </header>
</template>

<style scoped>

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-pulse {
  animation: pulse 1.5s infinite;
}
</style>