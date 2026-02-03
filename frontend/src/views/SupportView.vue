<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

import { useToastStore } from '@/stores/toast';

const authStore = useAuthStore();
const toast = useToastStore();
const tickets = ref([]);
const loading = ref(true);
const showCreateForm = ref(false);

const newTicket = ref({
    issue: '',
    description: '',
    game_id: null
});
const creatingTicket = ref(false);

const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/support');
        tickets.value = response.data;
    } catch (err) {
        console.error(err);
        toast.trigger('No se pudieron cargar los tickets de soporte.', 'error');
    } finally {
        loading.value = false;
    }
};

const createTicket = async () => {
    creatingTicket.value = true;
    try {
        // We need a seller_id. For now, let's assume general support goes to admin (ID 1)
        const payload = {
            ...newTicket.value,
            user_id_seller: 1 
        };

        const response = await axios.post('/support', payload);
        tickets.value.unshift(response.data);
        showCreateForm.value = false;
        newTicket.value = { issue: '', description: '', game_id: null };
        toast.trigger('Ticket creado correctamente', 'success');
    } catch (err) {
        console.error(err);
        toast.trigger(err.response?.data?.message || 'Error al crear el ticket.', 'error');
    } finally {
        creatingTicket.value = false;
    }
};

onMounted(fetchTickets);
</script>

<template>
    <section class="bg-gray-900 text-white min-h-screen">
        <div class="bg-gray-800 py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <h1 class="text-2xl font-bold">Soporte Técnico</h1>
                <button @click="showCreateForm = !showCreateForm" class="bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-yellow-400">
                    {{ showCreateForm ? 'Cancelar' : 'Nuevo Ticket' }}
                </button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Create Form -->
            <div v-if="showCreateForm" class="bg-gray-800 p-6 rounded-lg shadow-lg mb-8 border border-yellow-500">
                <h2 class="text-xl font-bold mb-4">Crear Nuevo Ticket</h2>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1">Asunto</label>
                        <input v-model="newTicket.issue" type="text" class="w-full bg-gray-700 rounded px-3 py-2 text-white" placeholder="Ej: Problema con una clave">
                    </div>
                     <div>
                        <label class="block text-gray-400 mb-1">Descripción Detallada</label>
                        <textarea v-model="newTicket.description" rows="4" class="w-full bg-gray-700 rounded px-3 py-2 text-white" placeholder="Describe tu problema..."></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button @click="createTicket" :disabled="creatingTicket" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-500 disabled:opacity-50">
                        {{ creatingTicket ? 'Enviando...' : 'Enviar Ticket' }}
                    </button>
                </div>
            </div>

            <!-- Ticket List -->
            <div v-if="loading" class="text-center py-10">Cargando...</div>
            <div v-else-if="tickets.length === 0" class="text-center py-10 text-gray-400">No tienes tickets de soporte recientes.</div>
            
            <div v-else class="space-y-4">
                <div v-for="ticket in tickets" :key="ticket.id" class="bg-gray-800 p-4 rounded-lg shadow hover:bg-gray-700 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-yellow-500">{{ ticket.issue }}</h3>
                            <p class="text-sm text-gray-400">ID: #{{ ticket.id }} | Creado: {{ new Date(ticket.created_at).toLocaleDateString() }}</p>
                            <p class="mt-2 text-gray-300">{{ ticket.description }}</p>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="px-3 py-1 rounded-full text-xs uppercase font-bold"
                                :class="{
                                    'bg-green-900 text-green-300': ticket.state === 'abierto',
                                    'bg-red-900 text-red-300': ticket.state === 'cerrado',
                                    'bg-yellow-900 text-yellow-300': ticket.state === 'pendiente'
                                }">
                                {{ ticket.state }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
