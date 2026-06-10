import tailwindcss from "@tailwindcss/vite";
// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
 css: ['~/assets/css/main.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  runtimeConfig: {
    // Variáveis privadas (rodam apenas no servidor Node do Nuxt)
    apiSecret: '', 
    
    public: {
      // Variáveis públicas (vão para o navegador do cliente)
      apiBaseUrl:'',
      storageUrl: '',
      frontendSecret: '',
    }
  },
  debug: true,
})


