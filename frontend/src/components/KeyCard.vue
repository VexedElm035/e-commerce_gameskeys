<script setup>
import { RouterLink } from 'vue-router';
import { ref } from 'vue';
import getUnicodeFlagIcon from 'country-flag-icons/unicode';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const cart = useCartStore();

const props = defineProps({
    id: {
        type: String,
        required: true
    },
    name: String,
    platform: String,
    price: String,
    region: String,
    seller: String,
    img: String,
    rate: Number,
    deliverytime: String
});

import { useImage } from '@/composables/useImage';
const { resolve, getPlaceholder } = useImage();

const isHovered = ref(false);
const isAdding = ref(false);
const addError = ref(null);

const platformImages = {
    'PS4': "../img/cover_ps4.png",
    PS5: "../img/cover_ps5.png",
    XONE: "../img/cover_xboxone.png",
    Series: "../img/cover_xboxseries.png",
    'X|S': "../img/cover_xboxseries.png",
    Switch: "../img/cover_switch.png",
    PC: "../img/cover_steam.png",
};

const handleAddToCart = async () => {
    if (!auth.isLoggedIn) {
        addError.value = 'Debes iniciar sesión para añadir al carrito';
        return;
    }

    isAdding.value = true;
    addError.value = null;

    const success = await cart.addToCart(props.id);

    if (success) {
        console.log('Añadido al carrito correctamente');
    } else {
        addError.value = cart.error || 'Error al añadir al carrito';
    }

    isAdding.value = false;
};
</script>

<template>
    <div id="card" class="bg-gray-800 rounded-lg text-left max-w-60 mb-5 mt-5 overflow-hidden h-[450px]"
        @mouseenter="isHovered = true" @mouseleave="isHovered = false">
        <div class="flex flex-col h-full relative">
            <div class='relative overflow-hidden flex-shrink-0 h-[350px]'>
                <img :src="resolve(img, 'game')" alt="img" class="w-full h-full object-cover rounded-lg transition-all duration-300 mt-4"
                    :class="{ 'scale-105': isHovered, 'brightness-75': isHovered }"
                    @error="(e) => e.target.src = getPlaceholder('game')">

                <div v-if="platformImages[platform]" class="absolute top-0 w-fit h-fit">
                    <img :src="platformImages[platform]" :alt="platform" class="w-100 h-full object-contain">
                </div>
            </div>

            <div class="p-3 bg-gray-800 flex flex-col justify-between min-h-[100px] absolute bottom-0 left-0 right-0 transition-all duration-300 z-10"
                :class="{ 'translate-y-0': !isHovered, '-translate-y-1 bg-gray-700': isHovered }">
                <div>
                    <h3 class="text-sm font-bold mb-1">{{ name }}</h3>
                    <div class="flex">
                        <h4 class="text-sm text-green-500 font-bold uppercase mb-1">{{ region }}</h4>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-white-100 text-l">${{ price }}</span>
                        <span class="text-gray-400 line-through text-sm">${{ price }}</span>
                    </div>
                </div>

                <div class="mt-3 flex gap-2 transition-all duration-300 flex-col"
                    :class="{ 'opacity-0 h-0 overflow-hidden': !isHovered, 'opacity-100 h-auto': isHovered }">
                    <button @click="handleAddToCart" :disabled="isAdding"
                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span v-if="!isAdding">Añadir al Carrito</span>
                        <span v-else class="flex items-center">
                            <svg class="animate-spin h-4 w-4 text-gray-900" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Añadiendo...
                        </span>
                    </button>
                    <RouterLink :to="`/key/${id}`"
                        class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">
                        Detalles
                    </RouterLink>

                    <div v-if="addError" class="text-red-400 text-xs mt-1 text-center">
                        {{ addError }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.vignette {
    position: relative;
}

.vignette::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.7);
    border-radius: 0.5rem;
    pointer-events: none;
}
</style>