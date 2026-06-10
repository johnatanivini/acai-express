<!-- components/BuilderModal.vue -->
<template>
  <div v-if="isBuilderModalOpen" class="fixed inset-0 bg-black/80 flex justify-center items-end sm:items-center z-50 p-0 sm:p-4 transition-opacity">
    <div class="bg-[#120822] sm:rounded-2xl rounded-t-2xl max-w-lg w-full h-[85vh] sm:h-auto sm:max-h-[90vh] flex flex-col text-white shadow-2xl overflow-hidden border border-[#2a1c44]">
      
      <!-- Cabeçalho do Modal -->
      <div class="p-5 flex justify-between items-start border-b border-[#2a1c44] bg-[#1a0f2e]">
        <div class="flex gap-3 items-center">
          <span class="text-3xl">{{ currentProduct?.icon || '🫐' }}</span>
          <div>
            <h2 class="text-xl font-bold">Monte seu {{ currentProduct?.name.replace('Açaí ', '') }}</h2>
            <p class="text-sm text-gray-400">Escolha o tamanho e os complementos</p>
          </div>
        </div>
        <button @click="isBuilderModalOpen = false" class="text-gray-400 hover:text-white text-2xl p-1">&times;</button>
      </div>
      
      <!-- Corpo com Scroll -->
      <div class="p-5 overflow-y-auto flex-grow scrollbar-thin">
        
        <!-- Seleção de Tamanhos -->
        <h3 class="font-bold text-lg mb-3">Tamanho</h3>
        <div class="grid grid-cols-2 gap-3 mb-6">
          <div 
            v-for="size in sizes" :key="size.name"
            @click="selectedSize = size"
            class="p-4 rounded-xl border-2 cursor-pointer transition-all bg-[#1c1132]"
            :class="selectedSize.name === size.name ? 'border-[#d92794] shadow-[0_0_10px_rgba(217,39,148,0.2)]' : 'border-[#2a1c44] hover:border-[#4a3275]'"
          >
            <div class="flex justify-between items-start">
              <span class="font-bold">{{ size.name }}</span>
              <span v-if="selectedSize.name === size.name" class="text-[#d92794]">✓</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ size.desc }}</p>
            <p class="text-[#d92794] font-bold mt-2">R$ {{ size.price.toFixed(2).replace('.', ',') }}</p>
          </div>
        </div>

        <!-- Seleção de Complementos (Dinâmicos vindos do Laravel!) -->
        <div v-if="currentProduct?.extras?.length">
          <h3 class="font-bold text-lg mb-3">Complementos</h3>
          <div class="grid grid-cols-2 gap-3 mb-4">
            <div 
              v-for="comp in currentProduct.extras" :key="comp.id"
              @click="toggleComplement(comp)"
              class="p-3 rounded-xl border-2 cursor-pointer transition-all bg-[#1c1132] flex items-center gap-3"
              :class="isComplementSelected(comp) ? 'border-[#d92794]' : 'border-[#2a1c44] hover:border-[#4a3275]'"
            >
              <!-- Ícone visual baseado no nome do adicional -->
              <span class="text-2xl">{{ getEmojiFallback(comp.name) }}</span>
              
              <div class="flex-grow">
                <span class="font-semibold text-sm block">{{ comp.name }}</span>
                <span class="text-xs text-gray-400 block">
                  {{ parseFloat(comp.price) === 0 ? 'Grátis' : `+ R$ ${parseFloat(comp.price).toFixed(2).replace('.', ',')}` }}
                </span>
              </div>
              <span v-if="isComplementSelected(comp)" class="text-[#d92794]">✓</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Rodapé com o Totalizador e Botão Salvar -->
      <div class="p-5 border-t border-[#2a1c44] bg-[#1a0f2e]">
        <div class="flex justify-between items-center mb-4">
          <span class="text-gray-400">Total</span>
          <span class="text-2xl font-bold">R$ {{ currentTotal.toFixed(2).replace('.', ',') }}</span>
        </div>
        <button @click="handleAddToCart" class="w-full bg-[#c31f75] hover:bg-[#a1165e] py-4 rounded-xl font-bold text-lg transition-colors flex justify-center items-center gap-2 shadow-lg shadow-pink-600/20">
          + Adicionar ao Carrinho
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const { isBuilderModalOpen, currentProduct, addToCart } = useCart()

// Mantemos a tabela de tamanhos mockada até você decidir controlá-la no painel
const sizes = [
  { name: 'Pequeno', desc: '300ml', price: 12.90 },
  { name: 'Médio', desc: '500ml', price: 18.90 },
  { name: 'Grande', desc: '700ml', price: 24.90 },
  { name: 'Família', desc: '1000ml', price: 34.90 }
]

const selectedSize = ref(sizes[0])
const selectedComplements = ref([])

// Reseta as seleções toda vez que o modal abre para um novo açaí
watch(isBuilderModalOpen, (newVal) => {
  if (newVal) {
    selectedSize.value = sizes[0]
    selectedComplements.value = []
  }
})

// Modificado para validar pelo ID único do banco de dados!
const isComplementSelected = (comp) => selectedComplements.value.some(c => c.id === comp.id)

const toggleComplement = (comp) => {
  if (isComplementSelected(comp)) {
    selectedComplements.value = selectedComplements.value.filter(c => c.id !== comp.id)
  } else {
    selectedComplements.value.push(comp)
  }
}

// Calcula o total somando o tamanho escolhido + adicionais vindos da API
const currentTotal = computed(() => {
  const compTotal = selectedComplements.value.reduce((sum, item) => sum + parseFloat(item.price), 0)
  return selectedSize.value.price + compTotal
})

// Junta tudo e dispara o salvamento estruturado com os IDs reais do banco
const handleAddToCart = () => {
  const customizedItem = {
    cartId: Date.now() + Math.random().toString(36).substr(2, 9), // Garante ID único no carrinho
    id: currentProduct.value.id, // <-- CRUCIAL: Vincula o ID real do produto para o Laravel saber quem é
    name: currentProduct.value.name,
    icon: currentProduct.value.icon || '🫐',
    size: selectedSize.value,
    unitPrice: currentTotal.value,
    quantity: 1,
    
    // Mapeia salvando as referências cruciais com ID numérico
    extras: selectedComplements.value.map(extra => ({
      id: extra.id, // <-- Enviando o ID real do banco agora!
      name: extra.name,
      price: parseFloat(extra.price)
    }))
  }
  
  addToCart(customizedItem)
}

// Utilitário estético para injetar emojis nos complementos do banco
const getEmojiFallback = (name) => {
  const text = name.toLowerCase()
  if (text.includes('leite') || text.includes('ninho')) return '🥛'
  if (text.includes('morango')) return '🍓'
  if (text.includes('banana')) return '🍌'
  if (text.includes('nutella') || text.includes('chocolate')) return '🍫'
  if (text.includes('granola')) return '🌾'
  if (text.includes('kiwi')) return '🥝'
  if (text.includes('mel')) return '🍯'
  if (text.includes('amendoim') || text.includes('paçoca')) return '🥜'
  return '✨'
}
</script>