<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const username = ref('')
const email = ref('')
const password = ref('')
const password_confirm = ref('')
const selectedAvatar = ref('')
const error = ref('')
const role = ref('false')
const isLoading = ref(false)

const avatars = Array.from({ length: 10 }, (_, i) => `../img/pf${i + 1}.jpeg`)

async function signupUser() {
  try {
    error.value = ''
    
    if (password.value !== password_confirm.value) {
      error.value = '¡Las contraseñas no coinciden!'
      return
    }

    if (!selectedAvatar.value) {
      error.value = 'Por favor selecciona un avatar'
      return
    }

    isLoading.value = true

    await axios.post('/users', {
      username: username.value,
      email: email.value,
      password: password.value,
      role: role.value === 'true' ? 'seller' : 'user',
      avatar: selectedAvatar.value, 
    })

    router.push('/login')
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (err.response?.data?.errors) {
        // Laravel validation errors
        error.value = Object.values(err.response.data.errors).flat().join(' ');
    } else {
      error.value = 'Error inesperado al registrarse.'
    }
  } finally {
      isLoading.value = false
  }
}
</script>

<template>
  <div class="bg-gray-900 text-white flex flex-col min-h-screen">
    <section class="container mx-auto p-6 flex-grow flex justify-center items-center my-8">
      <div class="bg-gray-800 p-8 rounded-lg w-full max-w-md shadow-xl border border-gray-700">
        <h2 class="text-3xl font-bold text-center mb-8 text-yellow-500">Registro</h2>
        <form @submit.prevent="signupUser">
          <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Nombre de usuario:</label>
          <input v-model="username" type="text" placeholder="user1234" required
            class="w-full p-3 mb-4 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">

          <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Correo electrónico:</label>
          <input v-model="email" type="email" placeholder="e.g user_1234@mail.com" required
            class="w-full p-3 mb-4 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">

          <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Contraseña:</label>
          <input v-model="password" type="password" placeholder="******" required
            class="w-full p-3 mb-4 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">

          <label for="password_confirm" class="block mb-2 text-sm font-medium text-gray-300">Confirmar Contraseña:</label>
          <input v-model="password_confirm" type="password" placeholder="******" required
            class="w-full p-3 mb-6 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">

          <label class="block mb-3 text-sm font-medium text-gray-300">Selecciona un avatar:</label>
          <div class="grid grid-cols-5 gap-2 mb-6">
            <div v-for="avatar in avatars" :key="avatar" class="cursor-pointer border-2 rounded-lg p-1 transition-all hover:scale-105"
              :class="selectedAvatar === avatar ? 'border-yellow-500 bg-yellow-500/20' : 'border-transparent hover:border-gray-500'"
              @click="selectedAvatar = avatar">
              <img :src="`${avatar}`" alt="avatar" class="w-full rounded" />
            </div>
          </div>

          <label class="block mb-3 text-sm font-medium text-gray-300">¿Deseas ser vendedor?</label>
          <div class="flex gap-6 mb-8 bg-gray-700 p-3 rounded-lg">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="role" value="true" class="text-yellow-500 focus:ring-yellow-500" />
              <span>Sí</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="role" value="false" class="text-yellow-500 focus:ring-yellow-500" />
              <span>No</span>
            </label>
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-yellow-500 text-gray-900 font-bold px-4 py-3 rounded-lg hover:bg-yellow-400 transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center">
             <span v-if="isLoading">
               <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Registrando...
            </span>
            <span v-else>Registrarse</span>
            </button>
          
          <p v-if="error" class="text-red-400 mt-4 text-center bg-red-900/20 p-2 rounded border border-red-500/50 text-sm">{{ error }}</p>
        </form>

        <div class="mt-6 text-center text-gray-400">
          <RouterLink to="/login" class="hover:text-yellow-500 transition-colors">¿Ya tienes una cuenta? Inicia sesión aquí</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>