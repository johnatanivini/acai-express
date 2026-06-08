<template>
  <div class="min-h-screen bg-black text-white flex flex-col font-sans">
    <AppHeader />

    <main class="flex-grow container mx-auto px-4 py-8">
      <h2 class="text-3xl font-bold mb-8 text-center">Nosso Cardápio</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="product in products" :key="product.id" class="bg-gray-900 border border-gray-800 rounded-xl p-5 hover:border-purple-500 transition shadow-lg">
          <h3 class="text-xl font-bold mb-2">{{ product.name }}</h3>
          <p class="text-gray-400 mb-4 h-12">{{ product.description }}</p>
          <div class="flex justify-between items-center mt-auto">
            <span class="text-xl font-bold text-green-400">R$ {{ product.price.toFixed(2) }}</span>
            <button @click="openBuilder(product)" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded font-semibold transition">
              Montar
            </button>
          </div>
        </div>
      </div>
    </main>

    <AppFooter />
    
    <BuilderModal />
    <CartModal />
  </div>
</template>

<script setup>
const { isBuilderModalOpen, currentProduct } = useCart()

// Lista de produtos mockada simulando seu banco de dados
const products = [
  { id: 1, name: 'Açaí Tradicional 300ml', description: 'Açaí puro batido na hora.', price: 15.00 },
  { id: 2, name: 'Açaí Supremo 500ml', description: 'Açaí trufado com camadas generosas.', price: 22.00 },
  { id: 3, name: 'Barca de Açaí', description: 'Ideal para dividir com a galera.', price: 45.00 }
]

const openBuilder = (product) => {
  currentProduct.value = product
  isBuilderModalOpen.value = true
}
</script>