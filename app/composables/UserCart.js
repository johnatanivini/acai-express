import { useState } from '#app'
import { computed } from 'vue'

export const useCart = () => {
  const cart = useState('cart', () => [])
  const isCartModalOpen = useState('isCartModalOpen', () => false)
  const isBuilderModalOpen = useState('isBuilderModalOpen', () => false)
  const currentProduct = useState('currentProduct', () => null)

  // Calcula o total do carrinho considerando as quantidades
  const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.unitPrice * item.quantity), 0)
  })

  // Adiciona ou incrementa no carrinho
  const addToCart = (customizedItem) => {
    // Tenta achar um item idêntico (mesmo produto, mesmo tamanho, mesmos extras)
   // Localize a variável existingItemIndex e atualize para ficar assim:
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

  // Gera a mensagem para o WhatsApp
  const checkoutWhatsApp = () => {
    let text = "🛵 *Novo Pedido - Açaí Express*\n\n"
    cart.value.forEach((item, index) => {
      text += `*${item.quantity}x ${item.name} (${item.size.name})*\n`
      if (item.extras && item.extras.length > 0) {
        const extraNames = item.extras.map(e => e.name).join(', ')
        text += `   ↳ Extras: ${extraNames}\n`
      }
      text += `   ↳ Valor: R$ ${(item.unitPrice * item.quantity).toFixed(2).replace('.', ',')}\n\n`
    })
    text += `*Total do Pedido: R$ ${cartTotal.value.toFixed(2).replace('.', ',')}*`
    
    const phone = "5585987543565" // Substitua pelo seu
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`
    window.open(url, '_blank')
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