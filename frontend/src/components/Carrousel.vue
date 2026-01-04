<script setup>
import { ref } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  itemWidth: {
    type: String,
    default: '250px'
  },
  placeholderType: {
    type: String,
    default: 'game'
  }
});

const carouselRef = ref(null);

const scrollLeft = () => {
  if (carouselRef.value) {
    const scrollAmount = carouselRef.value.clientWidth * 0.8;
    carouselRef.value.scrollBy({
      left: -scrollAmount,
      behavior: 'smooth'
    });
  }
};

const scrollRight = () => {
  if (carouselRef.value) {
    const scrollAmount = carouselRef.value.clientWidth * 0.8;
    carouselRef.value.scrollBy({
      left: scrollAmount,
      behavior: 'smooth'
    });
  }
};

const getPlaceholderClass = () => {
  return props.placeholderType === 'game'
    ? 'w-64 h-48 bg-gray-700'
    : 'w-48 h-36 bg-gray-700';
};
</script>

<template>
  <div class="relative w-full">
    <button @click="scrollLeft"
      class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-gray-800/80 hover:bg-gray-700/90 text-white p-2 rounded-full shadow-lg transition-all"
      :class="{ 'opacity-50 cursor-not-allowed': loading }" :disabled="loading">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <div ref="carouselRef" class="flex overflow-x-auto scroll-smooth space-x-6 py-2 px-4 scrollbar-hide"
      :class="{ 'animate-pulse': loading }">

      <template v-if="loading">
        <div v-for="n in placeholderType === 'game' ? 6 : 8" :key="`placeholder-${n}`"
          class="flex-shrink-0 rounded-lg overflow-hidden bg-gray-800" :class="getPlaceholderClass()">
          <div class="w-full h-full bg-gray-700 animate-pulse"></div>
        </div>
      </template>

      <template v-else>
        <slot :items="items"></slot>
      </template>
    </div>

    <button @click="scrollRight"
      class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-gray-800/80 hover:bg-gray-700/90 text-white p-2 rounded-full shadow-lg transition-all"
      :class="{ 'opacity-50 cursor-not-allowed': loading }" :disabled="loading">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>