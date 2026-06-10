// composables/useAuth.js

export const useAuth = () => {
  const token = useCookie('auth_token') // Ligação ao cookie do browser
  
  // useState mantém os dados do utilizador disponíveis em toda a app (Vuex/Pinia simplificado)
  const user = useState('auth_user', () => null) 

  // Função de Login
  const login = async (email, password) => {
    // Usamos o nosso useApi customizado (não precisa passar URL completa nem headers)
    const { data, error } = await useApi('/auth/login', {
      method: 'POST',
      body: { email, password }
    })

    if (error.value) {
      console.error('Erro no login:', error.value.data.message)
      return false
    }

    if (data.value) {
      // Guarda o token no Cookie (o useApi vai apanhá-lo automaticamente na próxima requisição)
      token.value = data.value.access_token
      // Guarda os dados do lojista no estado global
      user.value = data.value.user
      return true
    }
  }

  // Função de Logout
  const logout = async () => {
    if (token.value) {
      await useApi('/auth/logout', { method: 'POST' })
    }
    // Limpa a sessão no frontend
    token.value = null
    user.value = null
    
    // Redireciona para o login
    navigateTo('/login')
  }

  return {
    login,
    logout,
    user,
    isAuthenticated: computed(() => !!token.value)
  }
}