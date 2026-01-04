<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Header from '../components/Header.vue';
import SideBar from '../components/SideBar.vue';
import Footer from '../components/Footer.vue';
import { RouterView } from 'vue-router';

const isMenuVisible = ref(true);
const userToggled = ref(false);
const lastWindowWidth = ref(window.innerWidth);

const checkScreenSize = () => {
  if (userToggled.value) return;

  const mediaQuery = window.matchMedia('(max-width: 900px)');
  isMenuVisible.value = !mediaQuery.matches;
};

const toggleMenu = () => {
  isMenuVisible.value = !isMenuVisible.value;
  userToggled.value = true;
};

const handleResize = () => {
  const currentWidth = window.innerWidth;
  const wasMobile = lastWindowWidth.value <= 900;
  const isNowDesktop = currentWidth > 900;

  if (wasMobile && isNowDesktop) {
    userToggled.value = false;
  }

  lastWindowWidth.value = currentWidth;
  checkScreenSize();
};

onMounted(() => {
  lastWindowWidth.value = window.innerWidth;
  checkScreenSize();
  window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Header :hasSidebar="true" :is-menu-visible="isMenuVisible" @toggle-menu="toggleMenu"
      class="sticky top-0 z-50 w-full shadow-xl" />
    <div class="flex">
      <div
        :class="['bg-gray-800 transition-all duration-300 ease-in-out', isMenuVisible ? 'w-55 md:w-64' : 'w-0 overflow-hidden']">
        <SideBar class="p-4" />
      </div>
      <main
        :class="['bg-gray-900 transition-all duration-300 ease-in-out', isMenuVisible ? 'w-[calc(100%-13rem)] md:w-[calc(100%-16rem)]' : 'w-full']">
        <router-view />
      </main>
    </div>
    <Footer />
  </div>
</template>
