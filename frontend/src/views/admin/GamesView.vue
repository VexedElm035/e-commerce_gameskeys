<script setup>
import { ref } from 'vue'
import axios from 'axios'
import FileUpload from 'primevue/fileupload';

let games = ref([]);
let action = ref('add');
let id_game = ref('');
let name = ref('');
let description = ref('');
let launch_date = ref('');
let publisher = ref('');
let available_platforms = ref('');
let genre_id = ref('');
let filename = ref('');


const file = ref(null);

const onFileSelect = (event) => {
    file.value = event.files[0];
};

const upload = async () => {
    if (!file.value) {
        console.error('No file selected');
        return;
    };
    const formData = new FormData();
    formData.append('file', file.value);
    try {
        const response = await axios.post('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        console.log('File uploaded successfully', response.data);
        return response.data.path;


    } catch (error) {
        console.error('error uploading file', error);
    };
};

(async function listGames() {
    try {
        const response = await axios.get("/games");
        games.value = response.data;
    } catch {
        console.error('Error al obtener los juegos');
    }
})();

async function deleteGame(id) {
    try {
        await axios.delete(`/games/${id}`);
        games.value = games.value.filter(game => game.id !== id);
    } catch {
        console.error('Error al eliminar el género');
    }
}

async function addGame() {
    try {
        filename.value = await upload();
        const response = await axios.post('/games', {
            name: name.value,
            description: description.value,
            launch_date: launch_date.value,
            publisher: publisher.value,
            available_platforms: available_platforms.value,
            img: filename.value
        });
        games.value.push(response.data);
        name.value = '';
    } catch {
        console.error('Error al agregar el género');
    }
}

async function selectGame(id) {
    try {
        const response = await axios.get(`/games/${id}`);
        name.value = response.data.name;
        description.value = response.data.description
        launch_date.value = response.data.launch_date
        publisher.value = response.data.publisher
        available_platforms.value = response.data.available_platforms
        genre_id.value = response.data.genre_id
        filename.value = response.data.img
        id_game.value = id;
        action.value = 'edit';
    } catch {
        console.error('Error al obtener el género');
    }
}

async function editGame() {
    try {
        filename.value = await upload();
        const response = await axios.put(`/games/${id_game.value}`, {
            name: name.value,
            description: description.value,
            launch_date: launch_date.value,
            publisher: publisher.value,
            available_platforms: available_platforms.value,
            img: filename.value
        });
        games.value = games.value.map(game => game.id === id_game.value ? response.data : game);
        name.value = '';
        action.value = 'add';
        id_game.value = '';
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
                    <button @click="action = 'add'; name = ''; id_game = '';"
                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Agregar</button>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-bold mb-4">Juegos</h3>
                            <ul class="space-y-2">
                                <div v-for="game in games" :key="game.id">
                                    <li>{{ game.name }}</li>
                                    <button @click="selectGame(game.id)"
                                        class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Editar</button>
                                    <button @click="deleteGame(game.id)"
                                        class="text-sm flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Eliminar</button>
                                </div>
                            </ul>
                        </div>
                    </div>

                    <div class="card flex flex-col gap-6 items-center justify-center">

                    </div>

                </main>
                <div>
                    <form @submit.prevent="action === 'add' ? addGame() : editGame()"
                        class="bg-gray-800 p-6 rounded-lg">
                        <label for="name">Nombre:</label>
                        <input v-model="name" id="name" type="text" class="w-full p-2 mb-3 rounded-lg text-white-900"
                            placeholder="e.g RPG">

                        <label for="img">Imagen</label>
                        <FileUpload mode="basic" name="demo[]" accept="image/*" :maxFileSize="1000000"
                            @select="onFileSelect" />


                        <label for="description">Descripcion:</label>
                        <textarea v-model="description" id="description" type="fieldset"
                            class="w-full p-2 mb-3 rounded-lg text-white-900" placeholder="e.g RPG"></textarea>

                        <label for="launch_date">Fecha de lanzamiento</label>
                        <input v-model="launch_date" id="launch_date" type="date"
                            class="w-full p-2 mb-3 rounded-lg text-white-900" placeholder="e.g RPG">

                        <label for="publisher">Desarrollador</label>
                        <input v-model="publisher" id="publisher" type="text"
                            class="w-full p-2 mb-3 rounded-lg text-white-900" placeholder="e.g RPG">

                        <label for="available_platforms">Plataformas disponibles</label>
                        <input v-model="available_platforms" id="available_platforms" type="text"
                            class="w-full p-2 mb-3 rounded-lg text-white-900" placeholder="e.g RPG">


                        <button type="submit"
                            class="text-center text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">
                            {{ action === 'add' ? 'Agregar' : 'Editar' }}
                        </button>
                    </form>
                </div>
    </section>
</template>
