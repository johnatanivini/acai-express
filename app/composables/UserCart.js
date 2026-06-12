// composables/useCart.js
import { useState } from '#app'
import { computed, watch, onMounted } from 'vue'

export const useCart = () => {
  const route = useRoute()
  const cart = useState('cart', () => [])
  const isCartModalOpen = useState('isCartModalOpen', () => false)
  const isBuilderModalOpen = useState('isBuilderModalOpen', () => false)
  const currentProduct = useState('currentProduct', () => null)
  
  // Dados do cliente coletados no checkout
  const customerName = useState('customerName', () => '')
  const customerWhatsapp = useState('customerWhatsapp', () => '')
  const paymentMethod = useState('paymentMethod', () => 'pix') // pix, money, card

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
    // Garante um ID único temporário para o item no carrinho se não existir
    if (!customizedItem.cartId) {
      customizedItem.cartId = Date.now() + Math.random().toString(36).substr(2, 9)
    }

    const existingItemIndex = cart.value.findIndex(item => 
      item.id === customizedItem.id &&
      JSON.stringify(item.extras) === JSON.stringify(customizedItem.extras)
    )

    if (existingItemIndex > -1) {
      cart.value[existingItemIndex].quantity += customizedItem.quantity
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

  /**
   * LIMPA O CARRINHO APÓS SUCESSO
   */
  const clearCart = () => {
    cart.value = []
    customerName.value = ''
    customerWhatsapp.value = ''
    address.value = { rua: '', numero: '', bairro: '', complemento: '' }
    isCartModalOpen.value = false
  }

  /**
   * EXECUTA O CHECKOUT INTEGRADO (BANCO DE DADOS + WHATSAPP)
   */
  const executeCheckout = async (storeData) => {
    if (cart.value.length === 0) return alert('Seu carrinho está vazio!')
    if (!customerName.value || !address.value.rua) return alert('Por favor, preencha o nome e o endereço de entrega!')

    const slug = route.params.slug // Pega o slug da URL atual dinamicamente

    // 1. Monta o payload exato que o nosso Laravel espera receber
   const payload = {
      customer_name: customerName.value,
      customer_whatsapp: customerWhatsapp.value,
      payment_method: paymentMethod.value,
      delivery_address: `${address.value.rua}, ${address.value.numero} - ${address.value.bairro} ${address.value.complemento ? '(' + address.value.complemento + ')' : ''}`,
      
      // ATUALIZAÇÃO AQUI: Passando as informações do tamanho escolhido
      items: cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        size_name: item.size ? item.size.name : null,  // ex: 'Médio'
        size_price: item.size ? item.size.price : 0,  // ex: 18.90
        extras: item.extras ? item.extras.map(e => e.id) : []
      }))
    }

    try {
      // 2. Dispara a gravação persistente na cozinha do Laravel
      const { data, error } = await useApi(`/tenant/${slug}/orders`, {
        method: 'POST',
        body: payload
      })

      if (error.value) {
        alert('Erro ao registrar pedido na cozinha: ' + (error.value.data?.message || 'Tente novamente.'))
        return false
      }

      // 3. Se gravou no banco de dados com sucesso, dispara o texto do WhatsApp para o lojista
      const lines = []
      lines.push(`✅ *PEDIDO REGISTRADO COZINHA #${data.value.order_id}*`)
      lines.push("📢 *Novo Pedido vindo do Cardápio Online*")
      lines.push("")
      lines.push(`👤 *Cliente:* ${customerName.value}`)
      lines.push(`📞 *WhatsApp:* ${customerWhatsapp.value}`)
      lines.push(`💳 *Forma de Pagamento:* ${paymentMethod.value.toUpperCase()}`)
      lines.push("")

      cart.value.forEach((item) => {
        lines.push(`*${item.quantity}x ${item.name}*`)
        if (item.extras && item.extras.length > 0) {
          const extraNames = item.extras.map(e => e.name).join(', ')
          lines.push(`   ↳ Adicionais: ${extraNames}`)
        }
        lines.push(`   ↳ Subtotal: R$ ${(item.unitPrice * item.quantity).toFixed(2).replace('.', ',')}`)
        lines.push("")
      })

      lines.push("📍 *Endereço de Entrega:*")
      lines.push(`${address.value.rua}, ${address.value.numero}`)
      lines.push(`Bairro: ${address.value.bairro}`)
      if (address.value.complemento) lines.push(`Comp: ${address.value.complemento}`)
      lines.push("")
      lines.push(`*Total Geral: R$ ${cartTotal.value.toFixed(2).replace('.', ',')}*`)

      // Pega o número do WhatsApp dinâmico da loja que veio da API do catálogo
      const storePhone = storeData.whatsapp_number || "5511999999999"
      
      const text = encodeURIComponent(lines.join('\n'))
      const whatsappUrl = `https://wa.me/${storePhone}?text=${text}`
      
      // Abre o app do WhatsApp
      window.open(whatsappUrl, '_blank')

     
      // Dentro do método de checkout após o sucesso da API:
      if (data.value && data.value.order_hash) {
        const hash = data.value.order_hash;

        // Dispara o WhatsApp...

        // Limpa o carrinho
        clearCart();

        // Redireciona para a nova URL limpa e segura
        navigateTo(`/order/${hash}`);
      }

      clearCart()
      return true

    } catch (err) {
      console.error('Erro crítico no checkout:', err)
      alert('Erro de conexão ao enviar o pedido.')
      return false
    }
  }

  return { 
    cart, 
    cartTotal, 
    isCartModalOpen, 
    isBuilderModalOpen, 
    currentProduct,
    address,
    customerName,
    customerWhatsapp,
    paymentMethod,
    addToCart,
    removeFromCart,
    updateQuantity,
    executeCheckout 
  }
}