<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const banners = ref([]);
const activeIndex = ref(0);
const interval = ref(null);
const isLoading = ref(true);
const apiUrl = import.meta.env.VITE_API_URL;

const fetchBanners = async () => {
  try {
    const response = await axios.get('/uploads');
    banners.value = response.data.map((filename, index) => ({
      id: index + 1,
      title: "",
      img: `${apiUrl}/uploads/${filename}`
    }));

    if (banners.value.length === 0) {
      banners.value = [{
        id: 1,
        title: "No hay banners disponibles",
        img: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 400'%3E%3Crect width='800' height='400' fill='%232d3748'/%3E%3Ctext x='50%' y='50%' font-family='Arial' font-size='24' fill='%23fff' text-anchor='middle' dominant-baseline='middle'%3ENo hay banners disponibles%3C/text%3E%3C/svg%3E"
      }];
    }
  } catch (error) {
    console.error('Error al cargar banners:', error);
  } finally {
    isLoading.value = false;
  }
};

const nextSlide = () => {
  if (banners.value.length > 0) {
    activeIndex.value = (activeIndex.value + 1) % banners.value.length;
  }
};

const prevSlide = () => {
  if (banners.value.length > 0) {
    activeIndex.value = (activeIndex.value - 1 + banners.value.length) % banners.value.length;
  }
};

const startCarousel = () => {
  if (banners.value.length > 1) {
    interval.value = setInterval(nextSlide, 5000);
  }
};

const getCardStyle = (index) => {
  if (banners.value.length === 0) return {};

  const totalCards = banners.value.length;
  const adjustedIndex = (index - activeIndex.value + totalCards) % totalCards;
  const distance = adjustedIndex <= totalCards / 2 ? adjustedIndex : adjustedIndex - totalCards;

  const scale = 1 - Math.abs(distance) * (1 / Math.max(totalCards, 3));
  const zIndex = 10 - Math.abs(distance);
  const translateX = distance * (totalCards > 3 ? 70 : 50);
  const blur = Math.min(Math.abs(distance) * 2, 4);

  return {
    transform: `translateX(${translateX}%) scale(${scale})`,
    zIndex,
    filter: `blur(${blur}px)`,
    transition: 'transform 0.6s ease, filter 0.6s ease',
    opacity: scale < 0.8 ? 0.7 : 1
  };
};

onMounted(() => {
  fetchBanners().then(() => {
    startCarousel();
  });
});

onUnmounted(() => {
  if (interval.value) clearInterval(interval.value);
});
</script>

<template>
  <div class="relative max-w-500 flex items-center justify-center overflow-hidden min-h-[300px]">

    <div v-if="isLoading" class="flex items-center justify-center w-full h-[300px]">
      <div class="animate-pulse bg-gray-700 w-full h-full rounded-lg"></div>
    </div>

    <template v-else>
      <button v-if="banners.length > 1" @click="prevSlide"
        class="absolute left-4 bg-gray-900 bg-opacity-70 p-2 rounded-full text-white hover:bg-gray-700 z-20 transition-all">
        &#9665;
      </button>

      <div class="relative flex items-center justify-center w-fit h-70">
        <div v-for="(banner, index) in banners" :key="banner.id"
          class="absolute w-[600px] h-[300px] bg-gray-800 p-1 rounded-lg text-center shadow-lg"
          :style="getCardStyle(index)">
          <img :src="banner.img" :alt="banner.title" class="w-full h-full object-cover rounded-lg"
            @error="banner.img = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 800 400\'%3E%3Crect width=\'800\' height=\'400\' fill=\'%232d3748\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' font-family=\'Arial\' font-size=\'24\' fill=\'%23fff\' text-anchor=\'middle\' dominant-baseline=\'middle\'%3EBanner no disponible%3C/text%3E%3C/svg%3E'">
          <h3 class="text-lg font-bold text-white mt-2">{{ banner.title }}</h3>
        </div>
      </div>

      <button v-if="banners.length > 1" @click="nextSlide"
        class="absolute right-4 bg-gray-900 bg-opacity-70 p-2 rounded-full text-white hover:bg-gray-700 z-20 transition-all">
        &#9655;
      </button>
    </template>
  </div>
</template>

<style scoped>
button:hover {
  transform: scale(1.1);
}

img {
  transition: opacity 0.3s ease;
}
</style>