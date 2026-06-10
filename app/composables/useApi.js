// composables/useApi.js

export const useApi = (request, opts) => {
  const config = useRuntimeConfig()
  const token = useCookie('auth_token')

  // DEDO DURO: Adicione este console.log temporário aqui!
  // Ele vai printar no terminal do seu VS Code/Docker do Nuxt para vermos se o Nuxt está lendo a senha do .env
  console.log('--- DEBUG NUXT --- Chave lida pelo Nuxt:', config.public.frontendSecret)

  return useFetch(request, {
    baseURL: config.public.apiBaseUrl,
    
    onRequest({ options }) {
      // Garante que options.headers seja um objeto manipulável
      options.headers = options.headers || {}
      
      // Se for uma instância de Headers nativa, usamos o método .set()
      if (options.headers instanceof Headers) {
        options.headers.set('X-Frontend-Secret', config.public.frontendSecret)
        if (token.value) options.headers.set('Authorization', `Bearer ${token.value}`)
      } else {
        // Se for um objeto simples, injetamos direto
        options.headers['X-Frontend-Secret'] = config.public.frontendSecret
        if (token.value) options.headers['Authorization'] = `Bearer ${token.value}`
      }
    },
    ...opts
  })
}