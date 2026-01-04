<script setup>
import { RouterLink, useRouter } from 'vue-router';
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();
const currentRoute = ref('');

const userId = computed(() => auth.userId);
const isSeller = computed(() => auth.userRole === 'seller');

// Actualizar la ruta actual cuando cambie
onMounted(() => {
  currentRoute.value = router.currentRoute.value.name;
});

const navigateToSection = (sectionId) => {
  if (router.currentRoute.value.name !== 'home') {
    router.push({ name: 'home' }).then(() => {
      setTimeout(() => {
        scrollToSection(sectionId);
      }, 300);
    });
  } else {
    scrollToSection(sectionId);
  }
};

const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    
    // Agregamos clase de highlight temporal
    element.classList.add('highlight-section');
    setTimeout(() => {
      element.classList.remove('highlight-section');
    }, 2000);
  }
};
</script>
<template>
  <aside>
    <h3 class="text-xl text-white font-bold mb-4 whitespace-nowrap">Menú</h3>
    <ul class="text-gray-300">
      <li>
        <RouterLink to="/" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Inicio</RouterLink>
      </li>
    <!--<li>
        <RouterLink to="/sales" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Ofertas</RouterLink>
      </li>-->  
      <li>
        <RouterLink to="/catalog" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Catálogo</RouterLink>
      </li>
      <li class="pt-4 mt-4 border-t border-gray-700"></li>
      <li>
        <RouterLink to="/orders" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Mis compras</RouterLink>
      </li>
      <li>
        <RouterLink to="/inbox" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Mi buzón</RouterLink>
      </li>
      <li>
        <RouterLink :to="`/profile/${userId}`" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Mi cuenta
        </RouterLink>
      </li>

      <li class="pt-4 mt-4 border-t border-gray-700 whitespace-nowrap">
        <h4 class="font-semibold">Plataformas</h4>
        <ul class="mt-2 space-y-1 ml-2">
          
          <li>
          <img src="./../assets/steam.svg" alt="logo" class="w-4 h-4 inline-block mr-2">  
            <button @click="navigateToSection('Steam')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              Steam
            </button>
          </li>
          
          <li>
            <img src="./../assets/ps.svg" alt="logo" class="w-4 h-4 inline-block mr-2">
            <button @click="navigateToSection('PlayStation')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              PS4
            </button>
          </li>
          <li>
            <img src="./../assets/ps.svg" alt="logo" class="w-4 h-4 inline-block mr-2">
            <button @click="navigateToSection('PlayStation')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              PS5
            </button>
          </li>
          <li>
            <img src="./../assets/xbox.svg" alt="logo" class="w-4 h-4 inline-block mr-2">
            <button @click="navigateToSection('Xbox')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              Xbox One
            </button>
          </li>
          <li>
            <img src="./../assets/xbox.svg" alt="logo" class="w-4 h-4 inline-block mr-2">
            <button @click="navigateToSection('Xbox')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              Xbox Series X
            </button>
          </li>
          <li>
            <img src="./../assets/switch.svg" alt="logo" class="w-4 h-4 inline-block mr-2">
            <button @click="navigateToSection('Nintendo')" class="w-full text-left hover:bg-gray-700 p-2 rounded whitespace-nowrap">
              Nintendo Switch
            </button>
          </li>
        </ul>
      </li>

      <li class="pt-4 mt-4 border-t border-gray-700"></li>

      <template v-if="isSeller">
        <li>
          <RouterLink to="/dashboard" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Panel de ventas
          </RouterLink>
        </li>
        <li>
          <RouterLink to="/dashboard/sell" class="block hover:bg-gray-700 p-2 rounded whitespace-nowrap">Vender
          </RouterLink>
        </li>
        <li class="pt-4 mt-4 border-t border-gray-700"></li>
      </template>

    </ul>
  </aside>
</template>

<style scoped>
.highlight-section {
  animation: highlight 2s ease;
}

@keyframes highlight {
  0% { background-color: rgba(234, 179, 8, 0.1); }
  50% { background-color: rgba(234, 179, 8, 0.3); }
  100% { background-color: transparent; }
}
</style>