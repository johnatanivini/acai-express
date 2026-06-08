import { useState } from '#app'
import { computed, watch, onMounted } from 'vue'

export const useCart = () => {
  const cart = useState('cart', () => [])
  const isCartModalOpen = useState('isCartModalOpen', () => false)
  const isBuilderModalOpen = useState('isBuilderModalOpen', () => false)
  const currentProduct = useState('currentProduct', () => null)

  // 1. Carrega o carrinho do cache do navegador quando o app inicia no cliente
  onMounted(() => {
    const savedCart = localStorage.getItem('acai_express_cart')
    if (savedCart) {
      cart.value = JSON.parse(savedCart)
    }
  })

  // 2. Fica escutando qualquer mudança no carrinho e salva no cache automaticamente
  // O { deep: true } é essencial para ele perceber mudanças dentro dos objetos (como quantidade)
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
    let text = "🛵 *Novo Pedido - Açaí Express*\n\n"
    cart.value.forEach((item) => {
      text += `*${item.quantity}x ${item.name} (${item.size.name})*\n`
      if (item.extras && item.extras.length > 0) {
        const extraNames = item.extras.map(e => e.name).join(', ')
        text += `   ↳ Extras: ${extraNames}\n`
      }
      text += `   ↳ Valor: R$ ${(item.unitPrice * item.quantity).toFixed(2).replace('.', ',')}\n\n`
    })
    text += `*Total do Pedido: R$ ${cartTotal.value.toFixed(2).replace('.', ',')}*`
    
    const phone = "5511999999999" // Substitua pelo seu número
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`
    window.open(url, '_blank')

    // 3. Limpa o carrinho e fecha o modal após enviar o pedido
    // Como o 'watch' está ativo, o localStorage também será esvaziado automaticamente
    cart.value = []
    isCartModalOpen.value = false
  }

  return { 
    cart, 
    cartTotal, 
    isCartModalOpen, 
    isBuilderModalOpen, 
    currentProduct,
    addToCart,
    removeFromCart,
    updateQuantity,
    checkoutWhatsApp 
  }
}