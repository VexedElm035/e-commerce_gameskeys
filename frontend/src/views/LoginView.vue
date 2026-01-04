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

async function loginUser() {
  try {
    await axios.get('../sanctum/csrf-cookie')
    const response = await axios.post('../login', {
      email: email.value,
      password: password.value
    })

    const userResponse = await axios.get('../api/user')
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
    error.value = 'Login inválido'
    console.error("Error al iniciar sesión:", err)
  }
}
</script>

<template>
  <div class="bg-gray-900 text-white flex flex-col min-h-screen">
    <section class="container mx-auto p-6 flex-grow flex justify-center items-center">
      <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Iniciar Sesión</h2>
        <form @submit.prevent="loginUser">
          <label for="email">Correo electronico:</label>
          <input v-model="email" type="email" placeholder="e.g user_1234@mail.com"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">
          <label for="password">Contraseña:</label>
          <input v-model="password" type="password" placeholder="******"
            class="w-full p-2 mb-3 rounded-lg text-gray-400">
          <button class="w-full bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Ingresar</button>
          <p v-if="error" class="text-red-400 mt-2 text-sm">{{ error }}</p>
        </form>
        <div class="mt-6 flex text-gray-400">
          <RouterLink to="/signup">No tienes una cuenta? regístrate aquí</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
