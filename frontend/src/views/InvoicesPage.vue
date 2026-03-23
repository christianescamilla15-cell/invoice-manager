<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Facturas</h1>
          <p class="mt-1 text-sm text-gray-500">Gestion de todas tus facturas</p>
        </div>
        <RouterLink
          to="/invoices/create"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nueva Factura
        </RouterLink>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Estado</label>
          <select
            v-model="store.filters.status"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
            @change="handleFilter"
          >
            <option value="">Todas</option>
            <option value="draft">Borrador</option>
            <option value="pending">Pendiente</option>
            <option value="paid">Pagada</option>
            <option value="overdue">Vencida</option>
            <option value="cancelled">Cancelada</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Proveedor</label>
          <select
            v-model="store.filters.vendor_id"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
            @change="handleFilter"
          >
            <option :value="null">Todos</option>
            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
              {{ vendor.business_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Desde</label>
          <input
            v-model="store.filters.from"
            type="date"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
            @change="handleFilter"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Hasta</label>
          <input
            v-model="store.filters.to"
            type="date"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
            @change="handleFilter"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
              <tr>
                <th class="px-6 py-3">#</th>
                <th class="px-6 py-3">Proveedor</th>
                <th class="px-6 py-3">Numero</th>
                <th class="px-6 py-3">Estado</th>
                <th class="px-6 py-3">Emision</th>
                <th class="px-6 py-3">Vencimiento</th>
                <th class="px-6 py-3 text-right">Total MXN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-if="store.loading">
                <tr v-for="n in 10" :key="n">
                  <td class="px-6 py-4" v-for="c in 7" :key="c">
                    <div class="h-4 w-20 animate-pulse rounded bg-gray-200" />
                  </td>
                </tr>
              </template>
              <template v-else-if="store.invoices.length === 0">
                <tr>
                  <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                    No se encontraron facturas
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr
                  v-for="(invoice, idx) in store.invoices"
                  :key="invoice.id"
                  class="cursor-pointer hover:bg-gray-50"
                  @click="router.push(`/invoices/${invoice.id}`)"
                >
                  <td class="px-6 py-4 text-gray-500">
                    {{ (store.pagination.current_page - 1) * store.pagination.per_page + idx + 1 }}
                  </td>
                  <td class="px-6 py-4 font-medium text-gray-900">
                    {{ invoice.vendor?.business_name ?? '-' }}
                  </td>
                  <td class="px-6 py-4 text-gray-600">{{ invoice.invoice_number }}</td>
                  <td class="px-6 py-4">
                    <StatusBadge :status="invoice.status" :label="invoice.status_label" />
                  </td>
                  <td class="px-6 py-4 text-gray-600">{{ formatDate(invoice.issued_at) }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ formatDate(invoice.due_at) }}</td>
                  <td class="px-6 py-4 text-right font-medium text-gray-900">
                    {{ formatCurrency(invoice.total) }}
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <Pagination
          :current-page="store.pagination.current_page"
          :last-page="store.pagination.last_page"
          :per-page="store.pagination.per_page"
          :total="store.pagination.total"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import Pagination from '@/components/shared/Pagination.vue'
import { useInvoicesStore } from '@/stores/invoices'
import { useVendorsStore } from '@/stores/vendors'
import type { Vendor } from '@/types'

const router = useRouter()
const store = useInvoicesStore()
const vendorsStore = useVendorsStore()

const vendors = ref<Vendor[]>([])

const currencyFormatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
function formatCurrency(value: number): string {
  return currencyFormatter.format(value)
}
function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
}

function handleFilter() {
  store.fetchAll(1)
}

function handlePageChange(page: number) {
  store.fetchAll(page)
}

onMounted(async () => {
  await Promise.all([
    store.fetchAll(1),
    vendorsStore.fetchAll(),
  ])
  vendors.value = vendorsStore.vendors
})
</script>
