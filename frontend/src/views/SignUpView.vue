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

const avatars = Array.from({ length: 10 }, (_, i) => `../img/pf${i + 1}.jpeg`)

async function signupUser() {
  try {
    if (password.value !== password_confirm.value) {
      error.value = '¡Las contraseñas no coinciden!'
      return
    }

    if (!selectedAvatar.value) {
      error.value = 'Por favor selecciona un avatar'
      return
    }

    await axios.post('/users', {
      username: username.value,
      email: email.value,
      password: password.value,
      role: role.value === 'true' ? 'seller' : 'user',
      avatar: selectedAvatar.value, // Solo el nombre, no la URL completa
    })

    router.push('/login')
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Error inesperado'
    }
  }
}
</script>

<template>
  <div class="bg-gray-900 text-white flex flex-col min-h-screen">
    <section class="container mx-auto p-6 flex-grow flex justify-center items-center">
      <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Registro</h2>
        <form @submit.prevent="signupUser">
          <label for="username">Nombre de usuario:</label>
          <input v-model="username" type="text" placeholder="user1234"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">

          <label for="email">Correo electrónico:</label>
          <input v-model="email" type="email" placeholder="e.g user_1234@mail.com"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">

          <label for="password">Contraseña:</label>
          <input v-model="password" type="password" placeholder="******"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">

          <label for="password_confirm">Confirmar Contraseña:</label>
          <input v-model="password_confirm" type="password" placeholder="******"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">

          <label class="block mb-2">Selecciona un avatar:</label>
          <div class="grid grid-cols-5 gap-2 mb-4">
            <div v-for="avatar in avatars" :key="avatar" class="cursor-pointer border-2 rounded-lg p-1"
              :class="selectedAvatar === avatar ? 'border-yellow-500' : 'border-transparent'"
              @click="selectedAvatar = avatar">
              <img :src="`${avatar}`" alt="avatar" class="w-full rounded" />
            </div>
          </div>

          <label class="block mb-2">¿Deseas ser vendedor?</label>
          <div class="flex gap-4 mb-4">
            <label class="flex items-center gap-2">
              <input type="radio" v-model="role" value="true" />
              Sí
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="role" value="false" />
              No
            </label>
          </div>

          <button
            class="w-full bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Registrarse</button>
          <p v-if="error" class="text-red-400 mt-2 text-sm">{{ error }}</p>
        </form>

        <div class="mt-6 flex text-gray-400">
          <RouterLink to="/login">¿Ya tienes una cuenta? Inicia sesión aquí</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>