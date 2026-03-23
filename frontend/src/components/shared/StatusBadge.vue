<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="colorClasses"
  >
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  status: string
  label?: string
}>()

const statusConfig: Record<string, { classes: string; label: string }> = {
  draft: { classes: 'bg-gray-100 text-gray-600', label: 'Borrador' },
  pending: { classes: 'bg-yellow-100 text-yellow-800', label: 'Pendiente' },
  paid: { classes: 'bg-green-100 text-green-800', label: 'Pagada' },
  overdue: { classes: 'bg-red-100 text-red-800', label: 'Vencida' },
  cancelled: { classes: 'bg-gray-200 text-gray-500', label: 'Cancelada' },
}

const colorClasses = computed(() => statusConfig[props.status]?.classes ?? 'bg-gray-100 text-gray-600')
const label = computed(() => props.label || statusConfig[props.status]?.label || props.status)
</script>
