<template>
  <div v-if="isBuilderModalOpen" class="fixed inset-0 bg-black/80 flex justify-center items-end sm:items-center z-50 p-0 sm:p-4 transition-opacity">
    <div class="bg-[#120822] sm:rounded-2xl rounded-t-2xl max-w-lg w-full h-[85vh] sm:h-auto sm:max-h-[90vh] flex flex-col text-white shadow-2xl overflow-hidden border border-[#2a1c44]">
      
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
      
      <div class="p-5 overflow-y-auto flex-grow scrollbar-thin">
        
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

        <h3 class="font-bold text-lg mb-3">Complementos</h3>
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div 
            v-for="comp in complements" :key="comp.name"
            @click="toggleComplement(comp)"
            class="p-3 rounded-xl border-2 cursor-pointer transition-all bg-[#1c1132] flex items-center gap-3"
            :class="isComplementSelected(comp) ? 'border-[#d92794]' : 'border-[#2a1c44] hover:border-[#4a3275]'"
          >
            <span class="text-2xl">{{ comp.icon }}</span>
            <div class="flex-grow">
              <span class="font-semibold text-sm block">{{ comp.name }}</span>
              <span class="text-xs text-gray-400 block">{{ comp.price === 0 ? 'Grátis' : `+R$ ${comp.price.toFixed(2).replace('.', ',')}` }}</span>
            </div>
            <span v-if="isComplementSelected(comp)" class="text-[#d92794]">✓</span>
          </div>
        </div>
      </div>

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

// Dados simulados baseados na imagem
const sizes = [
  { name: 'Pequeno', desc: '300ml', price: 12.90 },
  { name: 'Médio', desc: '500ml', price: 18.90 },
  { name: 'Grande', desc: '700ml', price: 24.90 },
  { name: 'Família', desc: '1000ml', price: 34.90 }
]

const complements = [
  { name: 'Granola', price: 0, icon: '🌾' },
  { name: 'Leite Ninho', price: 1.50, icon: '🥛' },
  { name: 'Morango', price: 1.50, icon: '🍓' },
  { name: 'Banana', price: 1.00, icon: '🍌' },
  { name: 'Kiwi', price: 2.00, icon: '🥝' },
  { name: 'Mel', price: 1.00, icon: '🍯' },
  { name: 'Nutella', price: 3.00, icon: '🍫' },
  { name: 'Amendoim', price: 1.00, icon: '🥜' }
]

const selectedSize = ref(sizes[0])
const selectedComplements = ref([])

// Reseta os estados toda vez que o modal abre
watch(isBuilderModalOpen, (newVal) => {
  if (newVal) {
    selectedSize.value = sizes[0]
    selectedComplements.value = []
  }
})

const isComplementSelected = (comp) => selectedComplements.value.some(c => c.name === comp.name)

const toggleComplement = (comp) => {
  if (isComplementSelected(comp)) {
    selectedComplements.value = selectedComplements.value.filter(c => c.name !== comp.name)
  } else {
    selectedComplements.value.push(comp)
  }
}

const currentTotal = computed(() => {
  const compTotal = selectedComplements.value.reduce((sum, item) => sum + item.price, 0)
  return selectedSize.value.price + compTotal
})

const handleAddToCart = () => {
  const customizedItem = {
    cartId: Date.now().toString(), // ID único
    name: currentProduct.value.name,
    icon: currentProduct.value.icon,
    size: selectedSize.value,
    extras: selectedComplements.value,
    unitPrice: currentTotal.value,
    quantity: 1
  }
  addToCart(customizedItem)
}
</script>