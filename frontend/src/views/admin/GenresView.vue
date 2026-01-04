<script setup>
import { ref } from 'vue'
import axios from 'axios'

let genres = ref([]);
let action = ref('add');
let id_genre = ref('');
let name = ref('');

(async function listGenres() {
    try {
        const response = await axios.get("/genres");
        genres.value = response.data;
    } catch {
        console.error('Error al obtener los géneros');
    }
})();

async function deleteGenre(id) {
    try {
        await axios.delete(`/genres/${id}`);
        genres.value = genres.value.filter(genre => genre.id !== id);
    } catch {
        console.error('Error al eliminar el género');
    }
}

async function addGenre() {
    try {
        const response = await axios.post('/genres', {
            name: name.value
        });
        genres.value.push(response.data);
        name.value = '';
    } catch {
        console.error('Error al agregar el género');
    }
}

async function selectGenre(id) {
    try {
        const response = await axios.get(`/genres/${id}`);
        name.value = response.data.name;
        id_genre.value = id;
        action.value = 'edit';
    } catch {
        console.error('Error al obtener el género');
    }
}

async function editGenre() {
    try {
        const response = await axios.put(`/genres/${id_genre.value}`, {
            name: name.value
        });
        genres.value = genres.value.map(genre => genre.id === id_genre.value ? response.data : genre);
        name.value = '';
        action.value = 'add';
        id_genre.value = '';
    } catch {
        console.error('Error al editar el género');
    }
}
</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen">
        <RouterLink to="/admin" class="hover:text-yellow-400">
            < Volver</RouterLink>
                <main class="container mx-auto p-6 flex-grow">
                    <button @click="action = 'add'; name = ''; id_genre = '';"
                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Agregar</button>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-bold mb-4">Géneros</h3>
                            <ul class="space-y-2">
                                <div v-for="genre in genres" :key="genre.id">
                                    <li>{{ genre.name }}</li>
                                    <button @click="selectGenre(genre.id)"
                                        class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Editar</button>
                                    <button @click="deleteGenre(genre.id)"
                                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Eliminar</button>
                                </div>
                            </ul>
                        </div>
                    </div>
                </main>
                <div>
                    <form @submit.prevent="action === 'add' ? addGenre() : editGenre()"
                        class="bg-gray-800 p-6 rounded-lg">
                        <label for="name">Nombre del género</label>
                        <input v-model="name" id="name" type="text" class="w-full p-2 mb-3 rounded-lg text-white-900"
                            placeholder="e.g RPG">
                        <button type="submit"
                            class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">
                            {{ action === 'add' ? 'Agregar' : 'Editar' }}
                        </button>
                    </form>
                </div>
    </section>
</template>