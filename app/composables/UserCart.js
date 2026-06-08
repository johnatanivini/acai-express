import { useState } from '#app'
import { computed, watch, onMounted } from 'vue'

export const useCart = () => {
  const cart = useState('cart', () => [])
  const isCartModalOpen = useState('isCartModalOpen', () => false)
  const isBuilderModalOpen = useState('isBuilderModalOpen', () => false)
  const currentProduct = useState('currentProduct', () => null)
  
  // Novo estado para o endereço
  const address = useState('address', () => ({
    rua: '',
    numero: '',
    bairro: '',
    complemento: ''
  }))

  onMounted(() => {
    const savedCart = localStorage.getItem('acai_express_cart')
    if (savedCart) {
      cart.value = JSON.parse(savedCart)
    }
  })

  watch(cart, (newCart) => {
    localStorage.setItem('acai_express_cart', JSON.stringify(newCart))
  }, { deep: true })

  const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.unitPrice * item.quantity), 0)
  })

  const addToCart = (customizedItem) => {
    const existingItemIndex = cart.value.findIndex(item => 
      item.name === customizedItem.name &&
      item.size?.name === customizedItem.size?.name &&
      JSON.stringify(item.extras) === JSON.stringify(customizedItem.extras)
    )

    if (existingItemIndex > -1) {
      cart.value[existingItemIndex].quantity += 1
    } else {
      cart.value.push(customizedItem)
    }
    isBuilderModalOpen.value = false
  }

  const removeFromCart = (cartId) => {
    cart.value = cart.value.filter(item => item.cartId !== cartId)
  }

  const updateQuantity = (cartId, delta) => {
    const item = cart.value.find(item => item.cartId === cartId)
    if (item) {
      item.quantity += delta
      if (item.quantity <= 0) removeFromCart(cartId)
    }
  }

  const checkoutWhatsApp = () => {
    // Usar um Array garante que as quebras de linha sejam processadas corretamente
    const lines = []
    
    lines.push("🛵 *Novo Pedido - Açaí Express*")
    lines.push("") // Linha em branco
    
    cart.value.forEach((item) => {
      // Proteção extra com ?. no tamanho caso algum item antigo esteja no cache
      lines.push(`*${item.quantity}x ${item.name} (${item.size?.name || ''})*`)
      
      if (item.extras && item.extras.length > 0) {
        const extraNames = item.extras.map(e => e.name).join(', ')
        lines.push(`   ↳ Extras: ${extraNames}`)
      }
      lines.push(`   ↳ Valor: R$ ${(item.unitPrice * item.quantity).toFixed(2).replace('.', ',')}`)
      lines.push("") // Linha em branco separando os produtos
    })
    
    // Adiciona o endereço
    lines.push("📍 *Endereço de Entrega:*")
    lines.push(`${address.value.rua}, ${address.value.numero}`)
    lines.push(`Bairro: ${address.value.bairro}`)
    if (address.value.complemento) {
      lines.push(`Complemento: ${address.value.complemento}`)
    }
    
    lines.push("") // Linha em branco
    lines.push(`*Total do Pedido: R$ ${cartTotal.value.toFixed(2).replace('.', ',')}*`)
    
    const phone = "5511999999999" // Não esqueça de colocar seu número aqui
    
    // Junta tudo com quebra de linha e converte no formato perfeito para URLs
    const text = encodeURIComponent(lines.join('\n'))
    const url = `https://wa.me/${phone}?text=${text}`
    
    window.open(url, '_blank')

    // Limpa o estado e o carrinho
    cart.value = []
    isCartModalOpen.value = false
  }

  return { 
    cart, 
    cartTotal, 
    isCartModalOpen, 
    isBuilderModalOpen, 
    currentProduct,
    address, // Exportando o endereço para o Modal usar
    addToCart,
    removeFromCart,
    updateQuantity,
    checkoutWhatsApp 
  }
}