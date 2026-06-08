export const useCart = () => {
  // Estado global para o carrinho e modais
  const cart = useState('cart', () => [])
  const isCartModalOpen = useState('isCartModalOpen', () => false)
  const isBuilderModalOpen = useState('isBuilderModalOpen', () => false)
  const currentProduct = useState('currentProduct', () => null)

  // Cálculo automático do valor total
  const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + item.price, 0)
  })

  // Adicionar ao carrinho
  const addToCart = (item) => {
    cart.value.push(item)
    isBuilderModalOpen.value = false
  }

  // Redirecionar para o WhatsApp
  const checkoutWhatsApp = () => {
    let text = "Olá! Gostaria de fazer o seguinte pedido:\n\n"
    cart.value.forEach((item, index) => {
      text += `${index + 1}. ${item.name} - R$ ${item.price.toFixed(2)}\n`
    })
    text += `\n*Total: R$ ${cartTotal.value.toFixed(2)}*`
    
    const phone = "5511999999999" // Substitua pelo número da loja
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
    checkoutWhatsApp 
  }
}