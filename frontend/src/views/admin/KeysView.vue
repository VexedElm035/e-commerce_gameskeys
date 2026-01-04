<script setup>
import { ref } from 'vue'
import axios from 'axios'

let keys = ref([]);

(async function listkeys() {
    try {
        const response = await axios.get("/gamekeys");
        keys.value = response.data;
    } catch {
        console.error('Error al obtener las llaves');
    }
})();

</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen">
        <RouterLink to="/admin" class="hover:text-yellow-400 p-4">
            < Volver</RouterLink>
                <main class="container mx-auto p-6 flex-grow">
                    <h2 class="text-2xl font-bold mb-6">Listado de Claves de Juegos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="key in keys" :key="key.id" class="bg-gray-800 p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                            <h3 class="text-xl font-bold mb-2 text-yellow-400">{{ key.game?.name || 'Juego no disponible' }}</h3>
                            <div class="space-y-2">
                                
                                <p><span class="font-semibold">Vendedor:</span> {{ key.seller?.username || 'N/A' }}</p>
                                <p><span class="font-semibold">Plataforma:</span> {{ key.platform || 'N/A' }}</p>
                                <p><span class="font-semibold">Region:</span> {{ key.region || 'N/A' }}</p>
                                <p><span class="font-semibold">Precio:</span> ${{ key.price }}</p>
                                <p><span class="font-semibold">Estado:</span> 
                                    <span :class="{
                                        'text-green-400': key.status === 'disponible',
                                        'text-red-400': key.status === 'vendida',
                                    }">
                                        {{ key.status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </main>
    </section>
</template>