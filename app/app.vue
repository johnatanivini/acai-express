<template>
  <div class="min-h-screen bg-[#0b0514] text-white font-sans pb-10">
    <AppHeader />

    <main class="container mx-auto max-w-3xl px-4 pt-4">
      
      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-300 mb-6 bg-[#1a0f2e] p-3 rounded-lg border border-[#2a1c44]">
        <span class="flex items-center gap-1">⏱️ 30–45 min</span>
        <span class="flex items-center gap-1">🛵 Taxa: R$ 5,00</span>
        <span class="flex items-center gap-1 text-yellow-400 font-semibold">⭐ Pedido mínimo R$ 15,00</span>
      </div>

      <div @click="openBuilder(products[0])" class="bg-gradient-to-r from-[#8e2de2] to-[#c31f75] rounded-2xl p-6 flex justify-between items-center cursor-pointer hover:opacity-90 transition-opacity mb-8 shadow-lg shadow-purple-900/20">
        <div class="flex gap-4 items-center">
           <div class="text-4xl">🫐</div>
           <div>
             <h2 class="text-2xl font-bold text-white mb-1">Monte seu Açaí</h2>
             <p class="text-purple-100 text-sm">Escolha o tamanho e os complementos</p>
           </div>
        </div>
        <span class="text-2xl font-bold text-white">></span>
      </div>

      <h2 class="text-xl font-bold mb-4 ml-1">Cardápio</h2>
      
      <div class="space-y-4">
        <div v-for="product in products" :key="product.id" class="bg-[#1c1132] border border-[#2a1c44] rounded-2xl p-4 flex gap-4 hover:border-[#4a3275] transition-colors">
          
          <div class="bg-[#2a1c44] w-16 h-16 rounded-xl flex justify-center items-center text-3xl shrink-0">
            {{ product.icon }}
          </div>
          
          <div class="flex-grow flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="text-base font-bold">{{ product.name }}</h3>
              <span :class="`text-[10px] font-bold px-2 py-0.5 rounded-full ${product.tagColor} text-white`">
                {{ product.tag }}
              </span>
            </div>
            <p class="text-xs text-gray-400 mb-2 leading-relaxed">{{ product.description }}</p>
            
            <div class="flex justify-between items-center mt-auto">
              <span class="text-[#d92794] font-bold text-sm">A partir de R$ {{ product.price.toFixed(2).replace('.', ',') }}</span>
              <button @click="openBuilder(product)" class="bg-[#c31f75] hover:bg-[#a1165e] w-8 h-8 rounded-full font-bold flex items-center justify-center shadow-lg transition-transform active:scale-95">
                +
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <BuilderModal />
    <CartModal />
  </div>
</template>

<script setup>
const { isBuilderModalOpen, currentProduct } = useCart()

// Mock de produtos idênticos à sua imagem
const products = [
  { id: 1, name: 'Açaí Tradicional', tag: 'Mais vendido', tagColor: 'bg-pink-600', description: 'Açaí puro batido no ponto certo, sem adição de açúcar', price: 12.90, icon: '🫐' },
  { id: 2, name: 'Açaí com Leite', tag: 'Especial', tagColor: 'bg-purple-500', description: 'Açaí cremoso batido com leite condensado', price: 14.90, icon: '🍦' },
  { id: 3, name: 'Açaí Proteico', tag: 'Fitness', tagColor: 'bg-cyan-500', description: 'Com whey protein, banana e amendoim. Perfeito pós-treino', price: 18.90, icon: '💪' },
  { id: 4, name: 'Açaí com Frutas', tag: 'Fresco', tagColor: 'bg-green-500', description: 'Açaí com mix de frutas frescas da estação', price: 16.90, icon: '🍓' },
  { id: 5, name: 'Açaí Nutella Lover', tag: 'Premium', tagColor: 'bg-yellow-600', description: 'Açaí cremoso com Nutella, morango e granola crocante', price: 21.90, icon: '🍯' }
]

const openBuilder = (product) => {
  currentProduct.value = product
  isBuilderModalOpen.value = true
}
</script>

<style>
/* Remove a barra de rolagem mas permite o scroll (Opcional, melhora a estética) */
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