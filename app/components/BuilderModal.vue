<template>
  <div v-if="isBuilderModalOpen" class="fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center p-4 z-50">
    <div class="bg-gray-800 rounded-lg max-w-md w-full p-6 text-white shadow-xl">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Monte seu {{ currentProduct?.name }}</h2>
        <button @click="isBuilderModalOpen = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>
      
      <div class="space-y-4 my-6">
        <p class="text-gray-300">Selecione os complementos (Exemplo prático):</p>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="extras" value="Leite Condensado" class="accent-purple-500 w-5 h-5"> 
          Leite Condensado
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="extras" value="Morango" class="accent-purple-500 w-5 h-5"> 
          Morango Fresco
        </label>
      </div>

      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-700">
        <span class="text-lg font-bold text-green-400">R$ {{ currentProduct?.price.toFixed(2) }}</span>
        <button @click="handleAddToCart" class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded font-bold transition">
          Adicionar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const { isBuilderModalOpen, currentProduct, addToCart } = useCart()
const extras = ref([])

const handleAddToCart = () => {
  // Cria uma cópia do produto com os extras selecionados
  const finalProduct = {
    ...currentProduct.value,
    extras: extras.value,
    cartId: Date.now() // ID único para o item no carrinho
  }
  addToCart(finalProduct)
  extras.value = [] // limpa os extras para a próxima vez
}
</script>