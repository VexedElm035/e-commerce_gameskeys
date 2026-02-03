<script setup>
import HorizontalKeyCard from '@/components/HorizontalKeyCard.vue';
import router from '@/router';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth'; // Ensure you have an auth store or similar

import { useToastStore } from '@/stores/toast';
import { useImage } from '@/composables/useImage';

const route = useRoute();
const toast = useToastStore();
const { resolve, getPlaceholder } = useImage();
const gameId = route.params.id;
const game = ref({}); // Initialize as object not array
const keys = ref([]);
const genres = ref([]);
const reviews = ref([]);
const authStore = useAuthStore();

// Review Form State
const showReviewForm = ref(false);
const newReview = ref({
    rate: 5,
    rate_ux: 5,
    rate_time: 5,
    commentary: ''
});
const submittingReview = ref(false);
const reviewError = ref(null);

const gameKeys = computed(() => {
    return keys.value.filter(k => k.game_id == gameId);
});

const canReview = computed(() => {
    return authStore.isAuthenticated;
});

onMounted(async () => {
    try {
        const [gameRes, keysRes, genresRes, reviewsRes] = await Promise.all([
            axios.get(`/games/${gameId}`),
            axios.get('/gamekeys'), 
            axios.get('/genres'),   
            axios.get(`/reviews?game_id=${gameId}`) 
        ]);
        game.value = gameRes.data;
        keys.value = keysRes.data.filter(key => key.state === 'disponible');
        genres.value = genresRes.data;
        reviews.value = reviewsRes.data;

    } catch (err) {
        console.error(err);
        toast.trigger('Error al cargar los detalles del juego.', 'error');
        router.push('/error');
    }
})

const submitReview = async () => {
    if (!authStore.isAuthenticated) {
        toast.trigger('Debes iniciar sesión para escribir una reseña.', 'warning');
        return;
    }

    submittingReview.value = true;
    reviewError.value = null;

    try {
        const payload = {
            game_id: gameId,
            seller_id: gameKeys.value.length > 0 ? gameKeys.value[0].seller_id : 1, 
            ...newReview.value
        };
        
        if(gameKeys.value.length > 0) {
             payload.seller_id = gameKeys.value[0].seller.id;
        } else {
             payload.seller_id = 1; 
        }

        const response = await axios.post('/reviews', payload);
        reviews.value.unshift(response.data);
        showReviewForm.value = false;
        newReview.value = { rate: 5, rate_ux: 5, rate_time: 5, commentary: '' };
        toast.trigger('Reseña publicada con éxito!', 'success');
    } catch (err) {
        console.error(err);
        const msg = err.response?.data?.message || 'Error al enviar la reseña.';
        reviewError.value = msg;
        toast.trigger(msg, 'error');
    } finally {
        submittingReview.value = false;
    }
};

</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen flex-1">
        <section class="bg-yellow-500 text-gray-900 text-center py-10">
            <h2 class="text-3xl font-bold">Detalles del Juego</h2>
            <p class="mt-2">{{ game.name }}</p>
        </section>

        <section class="container mx-auto p-6 flex-grow ">
            <div class="bg-gray-800 p-6 rounded-lg flex flex-col md:flex-row gap-6">

                <div class="md:w-1/2 flex justify-center">
                    <img :src="resolve(game.img, 'game')" alt="Game Image"
                        class="max-w-md w-full h-64 md:h-96 object-cover rounded-lg mx-auto shadow-lg"
                        @error="(e) => e.target.src = getPlaceholder('game')">
                </div>

                <div class="md:w-1/2">
                    <h3 class="text-2xl font-bold mb-4">{{ game.name }}</h3>
                    <p class="mb-4">{{ game.description }}</p>
                    <ul class="mb-4">
                        <!-- <li><strong>Género:</strong> RPG, Acción</li> -->
                        <li><strong>Desarrollador:</strong> {{ game.publisher }}</li>
                        <li><strong>Fecha de lanzamiento:</strong> {{ game.launch_date }}</li>
                        <li><strong>Plataforma:</strong> {{ game.available_platforms }}</li>
                    </ul>
                    <div class="flex gap-4">
                        <RouterLink to="/" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Volver
                            a la Tienda</RouterLink>
                    </div>

                    <div class="bg-gray-900 text-white flex flex-col gap-4 mt-6 p-3 rounded-lg">
                        <h3 class="text-xl font-semibold mb-2">Llaves disponibles:</h3>
                        <HorizontalKeyCard v-for="key in gameKeys" :key="key.id" :id="key.id" :platform="key.platform"
                            :price="`$${key.price}`" :region="key.region" :seller="key.seller.username"
                            :seller_img="key.seller.avatar" :rate="key.rate" :deliverytime="key.delivery_time" />
                    </div>
                    <p v-if="gameKeys.length === 0">No hay llaves disponibles para este juego.</p>
                </div>

            </div>

             <!-- Reviews Section -->
             <div class="bg-gray-800 p-6 rounded-lg mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-yellow-500">Reseñas de Usuarios</h3>
                    <button v-if="canReview && !showReviewForm" @click="showReviewForm = true" class="bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-yellow-400">
                        Escribir reseña
                    </button>
                </div>

                <!-- Review Form -->
                <div v-if="showReviewForm" class="bg-gray-700 p-4 rounded-lg mb-6 border border-yellow-500">
                    <h4 class="text-lg font-bold mb-3">Tu Opinión</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Puntuación General</label>
                            <select v-model="newReview.rate" class="w-full bg-gray-800 rounded px-3 py-2 text-white">
                                <option :value="5">5 - Excelente</option>
                                <option :value="4">4 - Muy Bueno</option>
                                <option :value="3">3 - Bueno</option>
                                <option :value="2">2 - Regular</option>
                                <option :value="1">1 - Malo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Experiencia (UX)</label>
                            <select v-model="newReview.rate_ux" class="w-full bg-gray-800 rounded px-3 py-2 text-white">
                                <option :value="5">5 - Excelente</option>
                                <option :value="4">4 - Muy Bueno</option>
                                <option :value="3">3 - Bueno</option>
                                <option :value="2">2 - Regular</option>
                                <option :value="1">1 - Malo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Tiempo de Entrega/Uso</label>
                            <select v-model="newReview.rate_time" class="w-full bg-gray-800 rounded px-3 py-2 text-white">
                                <option :value="5">5 - Rápido</option>
                                <option :value="4">4 - Normal</option>
                                <option :value="3">3 - Aceptable</option>
                                <option :value="2">2 - Lento</option>
                                <option :value="1">1 - Muy Lento</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-1">Comentario</label>
                        <textarea v-model="newReview.commentary" rows="3" class="w-full bg-gray-800 rounded px-3 py-2 text-white" placeholder="Comparte tu experiencia..."></textarea>
                    </div>

                    <div v-if="reviewError" class="text-red-500 text-sm mb-3">
                        {{ reviewError }}
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="showReviewForm = false" class="text-gray-400 hover:text-white px-4 py-2">Cancelar</button>
                        <button @click="submitReview" :disabled="submittingReview" class="bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-yellow-400 disabled:opacity-50">
                            {{ submittingReview ? 'Enviando...' : 'Publicar Reseña' }}
                        </button>
                    </div>
                </div>
                
                <div v-if="reviews.length > 0" class="space-y-4">
                    <div v-for="review in reviews" :key="review.id" class="bg-gray-700 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="bg-gray-600 w-10 h-10 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-bold">{{ review.user?.username?.charAt(0).toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="font-bold">{{ review.user?.username || 'Usuario' }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ new Date(review.created_at).toLocaleDateString() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center bg-gray-900 px-3 py-1 rounded-full">
                                <span class="text-yellow-400 mr-1">★</span>
                                <span class="font-bold">{{ review.rate }}/5</span>
                            </div>
                        </div>
                        
                        <p class="mt-3 text-gray-300">{{ review.commentary }}</p>
                        
                        <div class="flex gap-4 mt-3 text-xs text-gray-400 border-t border-gray-600 pt-2">
                             <span>UX: {{ review.rate_ux }}/5</span>
                             <span>Tiempo: {{ review.rate_time }}/5</span>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-gray-400">
                    <p>Aún no hay reseñas para este juego.</p>
                </div>
            </div>

        </section>
    </section>

</template>