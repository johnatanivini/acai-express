<template>
  <header class="bg-[#0b0514] text-white p-4 sticky top-0 z-10 shadow-md">
    <div class="container mx-auto max-w-3xl flex justify-between items-center">
      <div class="flex items-center gap-3">
        <div class="bg-pink-600 w-12 h-12 rounded-full flex justify-center items-center text-2xl shadow-lg shadow-pink-600/30">
           💨
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-wide">AçaíExpress</h1>
          <p class="text-xs text-yellow-400 flex items-center gap-1">
            ⭐ 4.9 <span class="text-gray-400">• Aberto agora</span>
          </p>
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
</script>