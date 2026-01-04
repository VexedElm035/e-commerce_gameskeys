<script setup>
import { RouterLink } from 'vue-router';
import { ref } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const cart = useCartStore();
const isAdding = ref(false);
const addError = ref(null);

const props = defineProps(['id', 'price', 'seller', 'seller_img', 'rate', 'deliverytime']);

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
    <section class="container mx-auto p-6 flex-grow">
        <div class="gap-6">

            <div class="bg-gray-800 p-4 rounded-lg flex flex-row flex-wrap justify-between">
                <div>
                    <img :src="seller_img ? `${seller_img}` : '../img/def.jpg'" alt="Foto de perfil"
                        class="w-12 h-12 rounded-full object-cover">
                    {{ seller }}
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-yellow-400 text-lg">{{ price }}</span>
                </div>
                <div class="mt-3 flex gap-2">

                    
<button @click="handleAddToCart" :disabled="isAdding"
                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400 h-9">
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
                        class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400 h-9">
                        Detalles
                    </RouterLink>
                </div>
                <div v-if="addError" class="text-red-400 text-xs mt-1 text-center">
                        {{ addError }}
                    </div>
            </div>
        </div>
    </section>

</template>
