<template>
  <div class="bg-[#1c1132] border border-[#2a1c44] rounded-2xl p-6 shadow-xl mt-6">
    <div v-if="submitted" class="text-center py-4">
      <span class="text-4xl">⭐</span>
      <h3 class="text-lg font-bold mt-2">Obrigado pela avaliação!</h3>
      <p class="text-xs text-gray-400 mt-1">Sua opinião ajuda o estabelecimento a melhorar o serviço.</p>
    </div>

    <div v-else class="space-y-5">
      <div>
        <h3 class="text-base font-bold flex items-center gap-2 text-purple-400">⭐ Avalie sua experiência</h3>
        <p class="text-xs text-gray-400 mt-0.5">Sua nota compõe a reputação geral da loja.</p>
      </div>

      <div v-for="criterion in criteria" :key="criterion.key" class="flex justify-between items-center bg-[#120822] p-3 rounded-xl border border-[#2a1c44]/40">
        <div class="flex flex-col">
          <span class="text-sm font-semibold text-gray-200">{{ criterion.label }}</span>
          <span class="text-[10px] text-gray-500">Peso {{ criterion.weight }}</span>
        </div>
        
        <div class="flex gap-1">
          <button 
            v-for="star in 5" :key="star"
            @click="form[criterion.key] = star"
            type="button"
            class="text-xl transition-transform active:scale-95 focus:outline-none"
          >
            <span :class="star <= form[criterion.key] ? 'text-yellow-400' : 'text-gray-700'">★</span>
          </button>
        </div>
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-gray-400">Deseja deixar um comentário? (Opcional)</label>
        <textarea 
          v-model="form.comment"
          placeholder="Conte o que achou do açaí, da entrega..." 
          rows="3"
          class="w-full bg-[#120822] border border-[#2a1c44] rounded-xl p-3 text-xs text-white placeholder-gray-600 focus:outline-none focus:border-[#d92794] transition-colors resize-none"
        ></textarea>
      </div>

      <button 
        @click="sendReview" 
        :disabled="isSubmitting"
        class="w-full bg-gradient-to-r from-[#8e2de2] to-[#c31f75] hover:opacity-95 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-lg flex justify-center items-center gap-2"
      >
        <span v-if="isSubmitting" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
        {{ isSubmitting ? 'Enviando...' : 'Enviar Avaliação' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
  orderHash: { type: String, required: true }
})

const isSubmitting = ref(false)
const submitted = ref(false)

const criteria = [
  { key: 'rating_quality', label: '🫐 Qualidade do Açaí', weight: 3 },
  { key: 'rating_delivery', label: '⏱️ Tempo de Entrega', weight: 2 },
  { key: 'rating_packaging', label: '📦 Embalagem', weight: 1 },
  { key: 'rating_service', label: '🧑‍🍳 Atendimento', weight: 1 },
  { key: 'rating_value', label: '💳 Custo-Benefício', weight: 1 }
]

const form = reactive({
  rating_quality: 5,
  rating_delivery: 5,
  rating_packaging: 5,
  rating_service: 5,
  rating_value: 5,
  comment: ''
})

const sendReview = async () => {
  isSubmitting.value = true
  try {
    const { data, error } = await useApi(`/orders/${props.orderHash}/review`, {
      method: 'POST',
      body: form
    })

    if (error.value) {
      alert(error.value.data?.message || 'Erro ao enviar avaliação.')
    } else {
      submitted.value = true
    }
  } catch (err) {
    console.error(err)
  } finally {
    isSubmitting.value = false
  }
}
</script>