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
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL,
      storageUrl: process.env.NUXT_PUBLIC_STORAGE_URL,
    }
  }
})
