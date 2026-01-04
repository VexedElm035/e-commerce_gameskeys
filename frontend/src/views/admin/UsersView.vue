<script setup>
import { ref } from 'vue'
import axios from 'axios'

let users = ref([]);

(async function listusers() {
    try {
        const response = await axios.get("/users");
        users.value = response.data;
    } catch {
        console.error('Error al obtener los usuarios');
    }
})();

</script>

<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen">
        <RouterLink to="/admin" class="hover:text-yellow-400">
            < Volver</RouterLink>
                <main class="container mx-auto p-6 flex-grow">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-800 p-6 rounded-lg">
                            <h3 class="text-xl font-bold mb-4">Usuarios</h3>
                            <ul class="space-y-2">
                                <div v-for="user in users" :key="user.id">
                                    <li>{{ user.username }}</li>
                                    <li>{{ user.email }}</li>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <div class="card flex flex-col gap-6 items-center justify-center">

                    </div>

                </main>
    </section>
</template>
