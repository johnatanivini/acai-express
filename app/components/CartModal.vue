<template>
  <div v-if="isCartModalOpen" class="fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center p-4 z-50">
    <div class="bg-gray-800 rounded-lg max-w-md w-full p-6 text-white shadow-xl">
      <div class="flex justify-between items-center mb-4 border-b border-gray-700 pb-2">
        <h2 class="text-xl font-bold">Seu Pedido</h2>
        <button @click="isCartModalOpen = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>

      <div v-if="cart.length === 0" class="text-center py-8 text-gray-400">
        Seu carrinho está vazio.
      </div>

      <ul v-else class="space-y-4 max-h-60 overflow-y-auto mb-4 pr-2">
        <li v-for="item in cart" :key="item.cartId" class="flex justify-between border-b border-gray-700 pb-2">
          <div>
            <p class="font-semibold">{{ item.name }}</p>
            <p class="text-xs text-gray-400" v-if="item.extras?.length">
              Com: {{ item.extras.join(', ') }}
            </p>
          </div>
          <span class="text-green-400">R$ {{ item.price.toFixed(2) }}</span>
        </li>
      </ul>

      <div class="mt-6 pt-4 border-t border-gray-700">
        <div class="flex justify-between font-bold text-lg mb-4">
          <span>Total:</span>
          <span class="text-green-400">R$ {{ cartTotal.toFixed(2) }}</span>
        </div>
        <button @click="checkoutWhatsApp" :disabled="cart.length === 0" class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 px-4 py-3 rounded font-bold text-lg transition flex justify-center items-center gap-2">
          Finalizar no WhatsApp
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const { cart, cartTotal, isCartModalOpen, checkoutWhatsApp } = useCart()
</script>