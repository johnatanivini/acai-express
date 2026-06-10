// app/middleware/guest.js

export default defineNuxtRouteMiddleware((to, from) => {
  const token = useCookie('auth_token')

  // Se o lojista já tiver um token válido, manda ele direto pro Dashboard
  if (token.value) {
    return navigateTo('/admin/orders')
  }
})