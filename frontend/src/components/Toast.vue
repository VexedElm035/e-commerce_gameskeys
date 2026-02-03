<script setup>
import { computed } from 'vue';

const props = defineProps({
  message: String,
  type: {
    type: String,
    default: 'info', // info, success, error, warning
  },
  show: Boolean,
});

const emit = defineEmits(['close']);

const bgColor = computed(() => {
  switch (props.type) {
    case 'success': return 'bg-green-600';
    case 'error': return 'bg-red-600';
    case 'warning': return 'bg-yellow-600';
    default: return 'bg-blue-600';
  }
});
</script>

<template>
  <transition name="toast">
    <div v-if="show" class="fixed bottom-5 right-5 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 cursor-pointer"
         :class="bgColor"
         @click="emit('close')">
      <span>{{ message }}</span>
      <span class="text-sm opacity-75 hover:opacity-100">✕</span>
    </div>
  </transition>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
