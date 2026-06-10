<script setup>
// Protege a tela: se não estiver logado, o Nuxt joga para o /login antes de renderizar o HTML
definePageMeta({
  middleware: 'auth'
})

// Aqui você chama a API de pedidos normalmente
const { data: orders } = await useApi('/admin/orders')
</script>