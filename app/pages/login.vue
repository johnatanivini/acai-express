<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-950 text-white font-sans px-4">
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-md">
      
      <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-purple-500 tracking-tight">Açaí SaaS</h1>
        <p class="text-gray-400 mt-2 text-sm">Painel do Lojista • Entre com as suas credenciais</p>
      </div>

      <div v-if="errorMessage" class="mb-4 p-3 bg-red-950 border border-red-800 text-red-400 text-sm rounded-lg text-center font-medium">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">E-mail Corporativo</label>
          <input 
            v-model="form.email" 
            type="email" 
            placeholder="exemplo@loja.com" 
            required
            class="w-full p-3 rounded-xl bg-gray-950 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all text-sm"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Palavra-passe</label>
          <input 
            v-model="form.password" 
            type="password" 
            placeholder="••••••••" 
            required
            class="w-full p-3 rounded-xl bg-gray-950 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all text-sm"
          />
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-purple-800 disabled:opacity-60 text-white p-3 rounded-xl font-bold transition-all shadow-lg shadow-purple-900/30 flex items-center justify-center gap-2 text-sm mt-2"
        >
          <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ loading ? 'A autenticar...' : 'Entrar no Painel' }}
        </button>
      </form>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

// Vincula esta página ao middleware de convidado
definePageMeta({
  middleware: 'guest'
})

const { login } = useAuth()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: ''
})

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  
  try {
    const success = await login(form.email, form.password)
    
    if (success) {
      // Login com sucesso! O useAuth já guardou o Cookie, agora vamos para o dashboard
      navigateTo('/admin/orders')
    } else {
      errorMessage.value = 'E-mail ou senha incorretos. Tente novamente.'
    }
  } catch (err) {
    errorMessage.value = 'Erro ao conectar com o servidor de autenticação.'
  } finally {
    loading.value = false
  }
}
</script>