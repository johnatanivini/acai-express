<template>
  <header class="bg-[#0b0514] text-white p-4 sticky top-0 z-10 shadow-md">
    <div class="container mx-auto max-w-3xl flex justify-between items-center">
      <div class="flex items-center gap-3">
        <div class="bg-pink-600 w-12 h-12 rounded-full flex justify-center items-center text-2xl shadow-lg shadow-pink-600/30">
           💨
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-wide">AçaíExpress</h1>
             <div class="flex items-center gap-2 mt-1.5">
              
              <div v-if="catalogData?.store?.rating_average" class="flex items-center gap-1 bg-yellow-400/10 border border-yellow-400/30 px-2 py-0.5 rounded-lg text-xs text-yellow-400 font-bold">
                <span>⭐</span>
                <span>{{ catalogData.store.rating_average.toFixed(1).replace('.', ',') }}</span>
              </div>
              
              <div v-else class="flex items-center gap-1 bg-purple-500/10 border border-purple-500/20 px-2 py-0.5 rounded-lg text-[10px] text-purple-400 font-medium">
                <span>✨</span>
                <span>Nova Loja</span>
              </div>

              <span v-if="catalogData?.store?.rating_count > 0" class="text-[11px] text-gray-400">
                ({{ catalogData.store.rating_count }} {{ catalogData.store.rating_count === 1 ? 'avaliação' : 'avaliações' }})
              </span>
            </div>
            <span class="bg-green-500/10 text-green-400 border border-green-500/30 text-[10px] uppercase font-bold px-2 py-1 rounded-full tracking-wider">Aberto</span>
        </div>
      </div>
      
      <button @click="isCartModalOpen = true" class="bg-[#c31f75] hover:bg-[#a1165e] transition-colors px-4 py-2 rounded-xl font-semibold flex items-center gap-2 shadow-lg">
        🛒 Carrinho <span v-if="cart.length" class="bg-white text-[#c31f75] text-xs px-2 py-0.5 rounded-full">{{ cart.length }}</span>
      </button>
    </div>
  </header>
</template>

<script setup>
const { cart, isCartModalOpen } = useCart()

// Alinhe a variável reativa com o nome que você usa para capturar o useApi do catálogo
const route = useRoute()
const slug = route.params.slug

const { data: catalogData } = await useApi(`/tenant/${slug}/catalog`)

</script>