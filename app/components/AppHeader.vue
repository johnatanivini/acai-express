<template>
  <header class="bg-gray-900 text-white p-4 flex justify-between items-center border-b border-gray-800 sticky top-0 z-10">
    <h1 class="text-2xl font-bold text-purple-500">Açaí Express</h1>
    <button @click="isCartModalOpen = true" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded font-semibold flex items-center gap-2 transition">
      🛒 Carrinho ({{ cart.length }})
    </button>
  </header>
</template>

<script setup>
const { cart, isCartModalOpen } = useCart()
</script>