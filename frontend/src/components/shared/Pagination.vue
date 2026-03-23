<template>
  <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <p class="text-sm text-gray-700">
        Mostrando
        <span class="font-medium">{{ from }}</span>
        a
        <span class="font-medium">{{ to }}</span>
        de
        <span class="font-medium">{{ total }}</span>
        resultados
      </p>
      <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        <button
          :disabled="currentPage <= 1"
          class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
          @click="emit('page-change', currentPage - 1)"
        >
          Anterior
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="relative inline-flex items-center border px-4 py-2 text-sm font-medium"
          :class="
            page === currentPage
              ? 'z-10 border-indigo-500 bg-indigo-50 text-indigo-600'
              : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'
          "
          @click="emit('page-change', page)"
        >
          {{ page }}
        </button>
        <button
          :disabled="currentPage >= lastPage"
          class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
          @click="emit('page-change', currentPage + 1)"
        >
          Siguiente
        </button>
      </nav>
    </div>
    <!-- Mobile pagination -->
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        :disabled="currentPage <= 1"
        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        @click="emit('page-change', currentPage - 1)"
      >
        Anterior
      </button>
      <span class="flex items-center text-sm text-gray-700">{{ currentPage }} / {{ lastPage }}</span>
      <button
        :disabled="currentPage >= lastPage"
        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        @click="emit('page-change', currentPage + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: number
  lastPage: number
  perPage: number
  total: number
}>()

const emit = defineEmits<{
  'page-change': [page: number]
}>()

const from = computed(() => (props.currentPage - 1) * props.perPage + 1)
const to = computed(() => Math.min(props.currentPage * props.perPage, props.total))

const visiblePages = computed(() => {
  const pages: number[] = []
  const maxVisible = 7
  let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2))
  const end = Math.min(props.lastPage, start + maxVisible - 1)
  start = Math.max(1, end - maxVisible + 1)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})
</script>
