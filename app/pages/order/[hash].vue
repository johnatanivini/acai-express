<template>
  <div class="min-h-screen bg-[#0b0514] text-white font-sans pb-10 flex flex-col justify-between">
    
    <div v-if="pending && !order" class="flex-grow flex flex-col items-center justify-center gap-4">
      <span class="animate-spin inline-block w-8 h-8 border-4 border-[#c31f75] border-t-transparent rounded-full"></span>
      <p class="text-xs text-gray-400 uppercase tracking-widest">Localizando seu pedido...</p>
    </div>

    <div v-else-if="error || !order" class="flex-grow flex flex-col items-center justify-center text-center px-4">
      <span class="text-5xl mb-4">🔍</span>
      <h2 class="text-xl font-bold text-red-500">Pedido não encontrado</h2>
      <p class="text-gray-400 text-sm mt-1">Verifique o link enviado ou tente novamente.</p>
      <button @click="navigateTo(`/${slug}`)" class="mt-6 bg-[#c31f75] px-6 py-2 rounded-xl text-xs font-bold">
        Voltar para o Cardápio
      </button>
    </div>

    <div v-else class="flex-grow container mx-auto max-w-xl px-4 pt-6">
      
      <div class="bg-[#1a0f2e] border border-[#2a1c44] rounded-2xl p-6 text-center mb-6 shadow-xl">
        <span class="text-xs font-bold uppercase tracking-widest text-purple-400 block mb-1">Acompanhamento</span>
        <h1 class="text-2xl font-black">Pedido #{{ order.id }}</h1>
        <p class="text-sm text-gray-400 mt-1">Olá, <span class="text-white font-medium">{{ order.customer_name }}</span>! Veja o andamento do seu açaí:</p>
      </div>

      <div class="bg-[#1c1132] border border-[#2a1c44] rounded-2xl p-6 space-y-6 mb-6 shadow-lg">
        
        <div class="flex gap-4 items-start relative">
          <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-[#2a1c44]" :class="{ 'bg-gradient-to-b from-[#d92794] to-[#2a1c44]': isStepActive('preparing') }"></div>
          <div :class="getStatusCircleClass(['pending', 'preparing', 'ready', 'delivered'])">
            <span>⏳</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" :class="getStatusTextClass(['pending', 'preparing', 'ready', 'delivered'])">Pedido Recebido</h3>
            <p class="text-xs text-gray-400 mt-0.5">O lojista recebeu seu pedido na cozinha.</p>
          </div>
        </div>

        <div class="flex gap-4 items-start relative">
          <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-[#2a1c44]" :class="{ 'bg-gradient-to-b from-[#d92794] to-[#2a1c44]': isStepActive('ready') }"></div>
          <div :class="getStatusCircleClass(['preparing', 'ready', 'delivered'])">
            <span>🧑‍🍳</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" :class="getStatusTextClass(['preparing', 'ready', 'delivered'])">Em Preparo</h3>
            <p class="text-xs text-gray-400 mt-0.5">Seu açaí está sendo montado com capricho.</p>
          </div>
        </div>

        <div class="flex gap-4 items-start relative">
          <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-[#2a1c44]" :class="{ 'bg-gradient-to-b from-[#d92794] to-[#2a1c44]': isStepActive('delivered') }"></div>
          <div :class="getStatusCircleClass(['ready', 'delivered'])">
            <span>🛵</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" :class="getStatusTextClass(['ready', 'delivered'])">Saiu para Entrega</h3>
            <p class="text-xs text-gray-400 mt-0.5">O entregador já está a caminho do seu endereço.</p>
          </div>
        </div>

        <div class="flex gap-4 items-start">
          <div :class="getStatusCircleClass(['delivered'])">
            <span>🎉</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" :class="getStatusTextClass(['delivered'])">Entregue</h3>
            <p class="text-xs text-gray-400 mt-0.5">Bom apetite! Não esqueça de nos avaliar.</p>
          </div>
        </div>

      </div>

      <div class="bg-[#120822] border border-[#2a1c44] rounded-2xl p-5 space-y-3 text-sm text-gray-300 font-light">
        <h4 class="font-bold text-white mb-2 flex items-center gap-2">📝 Detalhes da Entrega</h4>
        <p><strong class="text-gray-400 font-normal">📍 Endereço:</strong> {{ formattedAddress }}</p>
        <p><strong class="text-gray-400 font-normal">💳 Pagamento:</strong> {{ order.payment_method.toUpperCase() }}</p>
        <p><strong class="text-gray-400 font-normal">💰 Valor Pago:</strong> <span class="text-[#d92794] font-bold">R$ {{ order.total?.toFixed(2).replace('.', ',') }}</span></p>
      </div>

    </div>

    <footer class="text-center text-[10px] text-gray-600 py-4 shrink-0">
      Atualizando automaticamente • Plataforma Whitelabel Açaí
    </footer>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, computed } from 'vue'

const route = useRoute()
const slug = route.params.slug

// Captura o hash direto da URL (/order/abc123xyz)
const orderHash = route.params.hash

// Consome a nova rota limpa do Laravel
const { data: order, pending, error, refresh } = await useApi(`/orders/${orderHash}`)

// Mantém o Short Polling atualizando o status a cada 10 segundos
let pollingTimer = null
// Função dedicada a desligar o cronômetro com segurança
const stopPolling = () => {
  if (pollingTimer) {
    clearInterval(pollingTimer)
    pollingTimer = null
    console.log('🛑 Polling parado. O pedido atingiu um status final.')
  }
}
onMounted(() => {
  // 2. Só inicia o cronômetro se o pedido NÃO estiver entregue ou cancelado logo de cara
  if (order.value?.status !== 'delivered' && order.value?.status !== 'cancelled') {
    
    pollingTimer = setInterval(async () => {
      // Executa a busca leve no Laravel
      await refresh()
      
      // 3. REGRA DE OURO: Se virou 'delivered' ou 'cancelled' após a atualização, desliga na hora!
      if (order.value?.status === 'delivered' || order.value?.status === 'cancelled') {
        stopPolling()
      }
    }, 10000) // 10 segundos
  }
})

// Desliga o timer quando o cliente fecha a página para não gastar processamento à toa
onUnmounted(() => {
  stopPolling()
})



// HELPERS ESTÉTICOS PARA CONTROLAR AS CORES DO STEPPER DINAMICAMENTE
const isStepActive = (requiredStatus) => {
  if (!order.value) return false
  const statusHierarchy = ['pending', 'preparing', 'ready', 'delivered']
  const currentIdx = statusHierarchy.indexOf(order.value.status)
  const targetIdx = statusHierarchy.indexOf(requiredStatus)
  return currentIdx >= targetIdx
}

const getStatusCircleClass = (validStatuses) => {
  const isActive = order.value && validStatuses.includes(order.value.status)
  return `w-9 h-9 rounded-full flex items-center justify-center text-sm border-2 z-10 transition-all shrink-0 ${
    isActive 
      ? 'bg-[#d92794]/20 border-[#d92794] shadow-[0_0_10px_rgba(217,39,148,0.4)] text-white' 
      : 'bg-[#120822] border-[#2a1c44] text-gray-600'
  }`
}

const getStatusTextClass = (validStatuses) => {
  const isActive = order.value && validStatuses.includes(order.value.status)
  return isActive ? 'text-white' : 'text-gray-500'
}

// Computed inteligente para tratar o endereço em qualquer formato
const formattedAddress = computed(() => {
  if (!order.value || !order.value.address) return 'Não informado'

  let addr = order.value.address

  // Se o Laravel mandou como String JSON, vamos tentar converter para Objeto
  if (typeof addr === 'string' && (addr.startsWith('{') || addr.startsWith('['))) {
    try {
      addr = JSON.parse(addr)
    } catch (e) {
      return order.value.address // Fallback caso falhe o parse
    }
  }

  // Se for um Objeto estruturado, monta a string amigável para o cliente
  if (typeof addr === 'object' && addr !== null) {
    const { address } = addr
    let text = `${address || ''}`
    return text
  }

  // Se já for uma String simples, retorna direto
  return addr
})
</script>