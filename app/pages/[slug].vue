<!-- pages/[slug].vue -->
<template>
  <div class="min-h-screen bg-[#0b0514] text-white font-sans pb-10">
    
    <!-- 1. Estado de Carregamento (Loading Skeleton) -->
    <div v-if="pending" class="flex flex-col items-center justify-center min-h-screen gap-4">
      <span class="animate-spin inline-block w-8 h-8 border-4 border-[#c31f75] border-t-transparent rounded-full"></span>
      <p class="text-xs text-gray-400 tracking-widest uppercase">A carregar o cardápio...</p>
    </div>

    <!-- 2. Estado de Erro (Caso o slug não exista no banco) -->
    <div v-else-if="error || !catalog" class="flex flex-col items-center justify-center min-h-screen text-center px-4">
      <div class="text-5xl mb-4">⚠️</div>
      <h2 class="text-xl font-bold text-red-500">Cardápio indisponível</h2>
      <p class="text-gray-400 text-sm mt-1 max-w-xs mx-auto">Esta loja não foi encontrada ou o endereço digitado está incorreto.</p>
      <NuxtLink to="/login" class="mt-6 text-xs text-purple-400 underline">Aceder ao painel do lojista</NuxtLink>
    </div>

    <!-- 3. Aplicação Pronta (Dados Carregados) -->
    <div v-else>
      <!-- Passamos os dados da loja dinâmica para o Header se necessário -->
      <AppHeader :store-name="catalog.store.name" />

      <main class="container mx-auto max-w-3xl px-4 pt-4">
        
        <!-- Info de Entrega fixado no topo -->
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs text-gray-300 mb-6 bg-[#1a0f2e] p-4 rounded-2xl border border-[#2a1c44]">
          <span class="flex items-center gap-1.5">⏱️ 30–45 min</span>
          <span class="flex items-center gap-1.5">🛵 Taxa: R$ 5,00</span>
          <span class="flex items-center gap-1.5 text-yellow-400 font-semibold">⭐ Pedido mínimo R$ 15,00</span>
        </div>

        <!-- Banner de Destaque: "Monte seu Açaí" -->
        <!-- Pega o primeiro produto disponível do menu se houver -->
        <div 
          v-if="firstProduct"
          @click="openBuilder(firstProduct)" 
          class="bg-gradient-to-r from-[#8e2de2] to-[#c31f75] rounded-2xl p-6 flex justify-between items-center cursor-pointer hover:opacity-95 transition-all mb-8 shadow-lg shadow-purple-900/20 active:scale-[0.99]"
        >
          <div class="flex gap-4 items-center">
             <div class="text-4xl">🍇</div>
             <div>
               <h2 class="text-2xl font-bold text-white mb-1">Monte seu Açaí</h2>
               <p class="text-purple-100 text-sm">Escolha o tamanho e os complementos</p>
             </div>
          </div>
          <span class="text-2xl font-bold text-white tracking-widest">&gt;</span>
        </div>

        <!-- Renderização por Categorias (Agrupamento Whitelabel) -->
        <div v-for="category in catalog.menu" :key="category.id" class="mb-8">
          
          <!-- Título da Categoria Dinâmica -->
          <h2 class="text-lg font-bold mb-4 ml-1 text-purple-400 tracking-wide flex items-center gap-2">
            <span>#</span> {{ category.name }}
          </h2>
          
          <!-- Lista de Produtos desta Categoria -->
          <div class="space-y-4">
            <div 
              v-for="product in category.products" 
              :key="product.id" 
              class="bg-[#1c1132] border border-[#2a1c44] rounded-2xl p-4 flex gap-4 hover:border-[#4a3275] transition-colors"
            >
              <!-- Ícone/Imagem vindo da API ou Fallback de segurança -->
              <div class="bg-[#2a1c44] w-16 h-16 rounded-xl flex justify-center items-center text-3xl shrink-0">
                {{ product.image_url || getIconFallback(category.name) }}
              </div>
              
              <div class="flex-grow flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="text-base font-bold">{{ product.name }}</h3>
                  
                  <!-- Tag Condicional Dinâmica -->
                  <span 
                    v-if="product.is_active" 
                    class="text-[9px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full bg-purple-950 text-purple-300 border border-purple-800"
                  >
                    Disponível
                  </span>
                </div>
                
                <p class="text-xs text-gray-400 mb-2 leading-relaxed line-clamp-2">
                  {{ product.description || 'Sem descrição cadastrada para este produto.' }}
                </p>
                
                <div class="flex justify-between items-center mt-auto">
                  <span class="text-[#d92794] font-bold text-sm">
                    R$ {{ parseFloat(product.price).toFixed(2).replace('.', ',') }}
                  </span>
                  <button 
                    @click="openBuilder(product)" 
                    class="bg-[#c31f75] hover:bg-[#a1165e] w-8 h-8 rounded-full font-bold flex items-center justify-center shadow-lg transition-transform active:scale-95 text-white text-lg"
                  >
                    +
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
      
      <!-- Modais que gerenciam o carrinho -->
      <BuilderModal />
      <CartModal />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const route = useRoute()
const { isBuilderModalOpen, currentProduct } = useCart()

// Captura o parâmetro dinâmico da URL ([slug])
const slug = route.params.slug

console.log('Slug da loja:', slug) // Debug: Verifique se o slug está correto

// Consome a API do Laravel de forma assíncrona e blindada com o Header Secreto
const { data: catalog, pending, error } = await useApi(`/tenant/${slug}/catalog`)
console.log('Error:', error) // Debug: Verifique os dados retornados
// Helper de conveniência: pega o primeiro produto encontrado para o botão "Monte seu Açaí"
const firstProduct = computed(() => {
  if (!catalog.value || !catalog.value.menu.length) return null
  const firstCat = catalog.value.menu[0]
  return firstCat.products.length ? firstCat.products[0] : null
})

// Abre o modal injetando os dados reais obtidos da API
const openBuilder = (product) => {
  currentProduct.value = product
  isBuilderModalOpen.value = true
}

// Pequeno utilitário visual para injetar emojis baseado no nome da categoria enquanto não colocamos uploads reais
const getIconFallback = (categoryName) => {
  const name = categoryName.toLowerCase()
  if (name.includes('bebida') || name.includes('suco')) return '🥤'
  if (name.includes('adicional') || name.includes('complemento')) return '🍫'
  if (name.includes('sorvete')) return '🍦'
  return '🫐'
}
</script>

<style>
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #2a1c44;
  border-radius: 20px;
}
</style>