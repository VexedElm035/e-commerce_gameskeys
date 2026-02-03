<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)

async function loginUser() {
  try {
    isLoading.value = true
    error.value = ''
    
    // Get CSRF cookie from root (assuming API is at /api, so root is parent)
    // We try to construct the root URL from the API URL
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
    const rootUrl = apiUrl.replace('/api', ''); 
    
    // Set CSRF cookie
    await axios.get(`${rootUrl}/sanctum/csrf-cookie`);

    const response = await axios.post('/login', {
      email: email.value,
      password: password.value
    })

    const userResponse = await axios.get('/user')
    const user = userResponse.data

    auth.login(user.username, user.id, user.role)
    auth.token = response.data.token
    auth.avatar = user.avatar

    axios.defaults.headers.common['Authorization'] = `Bearer ${auth.token}`

    console.log("Login exitoso:", user)

    if (user.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/')
    }
  } catch (err) {
    if (err.response?.status === 401) {
        error.value = 'Credenciales incorrectas. Inténtalo de nuevo.'
    } else {
        error.value = 'Ocurrió un error al iniciar sesión.'
    }
    console.error("Error al iniciar sesión:", err)
  } finally {
      isLoading.value = false
  }
}
</script>

<template>
  <div class="bg-gray-900 text-white flex flex-col min-h-screen">
    <section class="container mx-auto p-6 flex-grow flex justify-center items-center">
      <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md shadow-xl border border-gray-700">
        <h2 class="text-3xl font-bold text-center mb-8 text-yellow-500">Iniciar Sesión</h2>
        <form @submit.prevent="loginUser">
          <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Correo electrónico:</label>
          <input v-model="email" type="email" placeholder="e.g user_1234@mail.com" required
            class="w-full p-3 mb-4 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
          
          <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Contraseña:</label>
          <input v-model="password" type="password" placeholder="******" required
            class="w-full p-3 mb-6 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
          
          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full bg-yellow-500 text-gray-900 font-bold px-4 py-3 rounded-lg hover:bg-yellow-400 transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center">
            <span v-if="isLoading">
               <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Ingresando...
            </span>
            <span v-else>Ingresar</span>
          </button>
          
          <p v-if="error" class="text-red-400 mt-4 text-center bg-red-900/20 p-2 rounded border border-red-500/50">{{ error }}</p>
        </form>
        <div class="mt-6 text-center text-gray-400">
          <RouterLink to="/signup" class="hover:text-yellow-500 transition-colors">¿No tienes una cuenta? Regístrate aquí</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
