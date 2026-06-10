<!-- components/CartModal.vue -->
<template>
  <div v-if="isCartModalOpen" class="fixed inset-0 bg-black/80 flex justify-center items-end sm:items-center z-50 p-0 sm:p-4 transition-opacity">
    <div class="bg-[#120822] sm:rounded-2xl rounded-t-2xl max-w-lg w-full h-[90vh] flex flex-col text-white shadow-2xl overflow-hidden border border-[#2a1c44]">
      
      <!-- Topo do Modal -->
      <div class="p-5 flex justify-between items-center border-b border-[#2a1c44] bg-[#1a0f2e] shrink-0">
        <h2 class="text-xl font-bold flex items-center gap-2">🛍️ Meu Carrinho</h2>
        <button @click="isCartModalOpen = false" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>

      <!-- Estado Vazio -->
      <div v-if="cart.length === 0" class="flex-grow flex flex-col justify-center items-center text-gray-400 p-8">
        <span class="text-5xl mb-4">🛒</span>
        <p>Seu carrinho está vazio.</p>
      </div>

      <!-- Corpo com Scroll dos Itens e Dados de Entrega -->
      <div v-else class="p-5 overflow-y-auto flex-grow scrollbar-thin space-y-6">
        
        <!-- Lista de Produtos -->
        <div class="space-y-4">
          <div v-for="item in cart" :key="item.cartId" class="bg-[#1c1132] border border-[#2a1c44] rounded-xl p-4 flex gap-4 relative">
            
            <button @click="removeFromCart(item.cartId)" class="absolute top-4 right-4 text-gray-500 hover:text-red-400 text-sm">
              🗑️
            </button>

            <div class="text-3xl bg-[#2a1c44] w-12 h-12 flex items-center justify-center rounded-lg shrink-0">
              {{ item.icon || '🫐' }}
            </div>
            
            <div class="flex-grow pr-6">
              <h3 class="font-bold text-sm">{{ item.name }} <span v-if="item.size" class="text-purple-400">({{ item.size?.name }})</span></h3>
              <p class="text-xs text-gray-400 mt-1 leading-relaxed line-clamp-2" v-if="item.extras && item.extras.length">
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
        </div>

        <!-- Seção de Identificação do Cliente (Novo!) -->
        <div class="border-t border-[#2a1c44] pt-5">
          <h3 class="font-bold text-base mb-3 flex items-center gap-2 text-purple-400">👤 Identificação</h3>
          <div class="space-y-3">
            <input 
              v-model="customerName" 
              type="text" 
              placeholder="Seu Nome Completo" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
            <input 
              v-model="customerWhatsapp" 
              type="tel" 
              placeholder="WhatsApp com DDD (ex: 85999998888)" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
          </div>
        </div>

        <!-- Endereço de Entrega -->
        <div class="border-t border-[#2a1c44] pt-5">
          <h3 class="font-bold text-base mb-3 flex items-center gap-2 text-purple-400">📍 Endereço de Entrega</h3>
          <div class="space-y-3">
            <input 
              v-model="address.rua" 
              type="text" 
              placeholder="Rua / Avenida" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
            <div class="flex gap-3">
              <input 
                v-model="address.numero" 
                type="text" 
                placeholder="Número" 
                class="w-1/3 bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
              >
              <input 
                v-model="address.bairro" 
                type="text" 
                placeholder="Bairro" 
                class="w-2/3 bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
              >
            </div>
            <input 
              v-model="address.complemento" 
              type="text" 
              placeholder="Complemento (Apto, Bloco - Opcional)" 
              class="w-full bg-[#1c1132] border border-[#2a1c44] rounded-xl p-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#d92794] transition-colors"
            >
          </div>
        </div>

        <!-- Escolha de Forma de Pagamento (Novo!) -->
        <div class="border-t border-[#2a1c44] pt-5">
          <h3 class="font-bold text-base mb-3 flex items-center gap-2 text-purple-400">💳 Forma de Pagamento</h3>
          <div class="grid grid-cols-3 gap-2">
            <button 
              v-for="method in [{id: 'pix', label: '⚡ PIX'}, {id: 'money', label: '💵 Dinheiro'}, {id: 'card', label: '💳 Cartão'}]"
              :key="method.id"
              @click="paymentMethod = method.id"
              type="button"
              :class="paymentMethod === method.id 
                ? 'border-[#d92794] bg-[#d92794]/10 text-white font-bold' 
                : 'border-[#2a1c44] bg-[#1c1132] text-gray-400'"
              class="border p-3 rounded-xl text-xs transition-all text-center"
            >
              {{ method.label }}
            </button>
          </div>
        </div>

      </div>

      <!-- Rodapé com Valor Total e Botão de Submit -->
      <div class="p-5 border-t border-[#2a1c44] bg-[#1a0f2e] shrink-0">
        <div class="flex justify-between items-center mb-4">
          <span class="text-gray-400">Total</span>
          <span class="text-2xl font-bold text-white">R$ {{ cartTotal.toFixed(2).replace('.', ',') }}</span>
        </div>
        
        <button 
          @click="handleCheckout" 
          :disabled="cart.length === 0 || !isFormValid || isSubmitting" 
          class="w-full bg-[#c31f75] hover:bg-[#a1165e] disabled:bg-gray-700 disabled:text-gray-400 py-4 rounded-xl font-bold text-lg transition-colors flex justify-center items-center gap-2 shadow-lg disabled:opacity-50"
        >
          <span v-if="isSubmitting" class="animate-spin inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full"></span>
          {{ isSubmitting ? 'A enviar pedido...' : (isFormValid ? 'Enviar para a Cozinha' : 'Preencha todos os dados') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const route = useRoute()

// Desestruturando o nosso useCart reativo
const { 
  cart, 
  cartTotal, 
  isCartModalOpen, 
  address, 
  customerName, 
  customerWhatsapp, 
  paymentMethod,
  removeFromCart, 
  updateQuantity, 
  executeCheckout 
} = useCart()

const isSubmitting = ref(false)

// Re-busca dinamicamente os dados do catálogo da loja atual para ler o número de WhatsApp correto
const { data: catalog } = await useApi(`/tenant/${route.params.slug}/catalog`)

// Validação fina do formulário inteiro antes de liberar o clique
const isFormValid = computed(() => {
  return (
    customerName.value.trim().length > 2 &&
    customerWhatsapp.value.trim().length >= 8 &&
    address.value.rua.trim().length > 3 &&
    address.value.numero.trim().length > 0 &&
    address.value.bairro.trim().length > 2
  )
})

const handleCheckout = async () => {
  if (!catalog.value) return
  
  isSubmitting.value = true
  try {
    // Passa os dados da loja corrente (incluindo o ID e o WhatsApp do tenant)
    await executeCheckout(catalog.value.store)
  } finally {
    isSubmitting.value = false
  }
}
</script>