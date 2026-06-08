<template>
  <div v-if="isCartModalOpen" class="fixed inset-0 bg-black/80 flex justify-center items-end sm:items-center z-50 p-0 sm:p-4 transition-opacity">
    <div class="bg-[#120822] sm:rounded-2xl rounded-t-2xl max-w-lg w-full h-[90vh] flex flex-col text-white shadow-2xl overflow-hidden border border-[#2a1c44]">
      
      <div class="p-5 flex justify-between items-center border-b border-[#2a1c44] bg-[#1a0f2e] shrink-0">
        <h2 class="text-xl font-bold flex items-center gap-2">🛍️ Meu Carrinho</h2>
        <button @click="isCartModalOpen = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>

      <div v-if="cart.length === 0" class="flex-grow flex flex-col justify-center items-center text-gray-400 p-8">
        <span class="text-5xl mb-4">🛒</span>
        <p>Seu carrinho está vazio.</p>
      </div>

      <div v-else class="p-5 overflow-y-auto flex-grow scrollbar-thin space-y-4">
        <div v-for="item in cart" :key="item.cartId" class="bg-[#1c1132] border border-[#2a1c44] rounded-xl p-4 flex gap-4 relative">
          
          <button @click="removeFromCart(item.cartId)" class="absolute top-4 right-4 text-gray-500 hover:text-red-400">
            🗑️
          </button>

          <div class="text-3xl bg-[#2a1c44] w-12 h-12 flex items-center justify-center rounded-lg shrink-0">{{ item.icon }}</div>
          
          <div class="flex-grow pr-6">
            <h3 class="font-bold text-sm">{{ item.name }} <span v-if="item.size">({{ item.size?.name }})</span></h3>
            <p class="text-xs text-gray-400 mt-1 leading-relaxed line-clamp-2" v-if="item.extras.length">
              {{ item.extras.map(e => e.name).join(', ') }}
            </p>
            
            <div class="flex justify-between items-end mt-3">
              <span class="text-[#d92794] font-bold">R$ {{ (item.unitPrice * item.quantity).toFixed(2).replace('.', ',') }}</span>
              
              <div class="flex items-center gap-3 bg-[#120822] rounded-lg p-1 border border-[#2a1c44]">
                <button @click="updateQuantity(item.cartId, -1)" class="w-6 h-6 rounded-md bg-[#2a1c44] flex items-center justify-center hover:bg-[#4a3275] transition-colors">-</button>
                <span class="text-sm font-bold w-4 text-center">{{ item.quantity }}</span>
                <button @click="updateQuantity(item.cartId, 1)" class="w-6 h-6 rounded-md bg-[#2a1c44] flex items-center justify-center hover:bg-[#4a3275] transition-colors">+</button>
              </div>
            </div>
          </div>
        </div>

       <div v-if="address" class="mt-6 border-t border-[#2a1c44] pt-6">
          <h3 class="font-bold text-lg mb-4 flex items-center gap-2">📍 Onde vamos entregar?</h3>
          <div class="space-y-3">
            <input 
              v-model="address.rua" 
              type="text" 
              placeholder="Rua / Avenida" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
            <div class="flex gap-3">
              <input 
                v-model="address.numero" 
                type="text" 
                placeholder="Número" 
                class="w-1/3 bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
              >
              <input 
                v-model="address.bairro" 
                type="text" 
                placeholder="Bairro" 
                class="w-2/3 bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
              >
            </div>
            <input 
              v-model="address.complemento" 
              type="text" 
              placeholder="Complemento (Apto, Bloco, etc - Opcional)" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
          </div>
        </div>
      </div>

      <div class="p-5 border-t border-[#2a1c44] bg-[#1a0f2e] shrink-0">
        <div class="flex justify-between items-center mb-4">
          <span class="text-gray-400">Total</span>
          <span class="text-2xl font-bold">R$ {{ cartTotal.toFixed(2).replace('.', ',') }}</span>
        </div>
        <button 
          @click="checkoutWhatsApp" 
          :disabled="cart.length === 0 || !isAddressValid" 
          class="w-full bg-[#c31f75] hover:bg-[#a1165e] disabled:bg-gray-700 disabled:text-gray-400 py-4 rounded-xl font-bold text-lg transition-colors flex justify-center items-center gap-2 shadow-lg"
        >
          {{ isAddressValid ? 'Finalizar Pedido' : 'Preencha o endereço' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const { cart, cartTotal, isCartModalOpen, removeFromCart, updateQuantity, checkoutWhatsApp, address } = useCart()

// Regra para liberar o botão: Rua, Número e Bairro precisam estar preenchidos
const isAddressValid = computed(() => {
  if (!address.value) return false // Proteção extra
  
  return address.value.rua?.trim() !== '' && 
         address.value.numero?.trim() !== '' && 
         address.value.bairro?.trim() !== ''
})
</script>