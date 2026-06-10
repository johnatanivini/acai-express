// app/middleware/auth.js

export default defineNuxtRouteMiddleware((to, from) => {
  const token = useCookie('auth_token')

  // Se NÃO houver token, barra o acesso e joga o cara de volta pro login
  if (!token.value) {
    return navigateTo('/login')
  }
})