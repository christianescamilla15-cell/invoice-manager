<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Resumen general de facturacion</p>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="card in kpiCards"
          :key="card.label"
          class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
          <template v-if="loadingKpis">
            <div class="h-4 w-24 animate-pulse rounded bg-gray-200" />
            <div class="mt-3 h-8 w-32 animate-pulse rounded bg-gray-200" />
            <div class="mt-2 h-3 w-20 animate-pulse rounded bg-gray-200" />
          </template>
          <template v-else>
            <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
            <p class="mt-1 text-2xl font-bold" :class="card.textColor">{{ card.value }}</p>
            <p class="mt-1 text-sm text-gray-400">{{ card.sub }}</p>
          </template>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Recent Invoices -->
        <div class="lg:col-span-2">
          <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
              <h2 class="text-base font-semibold text-gray-900">Facturas Recientes</h2>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                  <tr>
                    <th class="px-6 py-3">Numero</th>
                    <th class="px-6 py-3">Proveedor</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-right">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <template v-if="loadingInvoices">
                    <tr v-for="n in 5" :key="n">
                      <td class="px-6 py-3" v-for="c in 4" :key="c">
                        <div class="h-4 w-20 animate-pulse rounded bg-gray-200" />
                      </td>
                    </tr>
                  </template>
                  <template v-else-if="recentInvoices.length === 0">
                    <tr>
                      <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                        No hay facturas recientes
                      </td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr
                      v-for="invoice in recentInvoices"
                      :key="invoice.id"
                      class="cursor-pointer hover:bg-gray-50"
                      @click="router.push(`/invoices/${invoice.id}`)"
                    >
                      <td class="px-6 py-3 font-medium text-gray-900">{{ invoice.invoice_number }}</td>
                      <td class="px-6 py-3 text-gray-600">{{ invoice.vendor?.business_name ?? '-' }}</td>
                      <td class="px-6 py-3">
                        <StatusBadge :status="invoice.status" :label="invoice.status_label" />
                      </td>
                      <td class="px-6 py-3 text-right font-medium text-gray-900">{{ formatCurrency(invoice.total) }}</td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Overdue Alerts -->
        <div>
          <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
              <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <h2 class="text-base font-semibold text-gray-900">Facturas Vencidas</h2>
              </div>
            </div>
            <div class="divide-y divide-gray-100">
              <template v-if="loadingOverdue">
                <div v-for="n in 3" :key="n" class="px-6 py-4">
                  <div class="h-4 w-32 animate-pulse rounded bg-gray-200" />
                  <div class="mt-2 h-3 w-24 animate-pulse rounded bg-gray-200" />
                </div>
              </template>
              <template v-else-if="overdueInvoices.length === 0">
                <div class="px-6 py-8 text-center text-sm text-gray-400">
                  Sin facturas vencidas
                </div>
              </template>
              <template v-else>
                <div
                  v-for="invoice in overdueInvoices"
                  :key="invoice.id"
                  class="cursor-pointer px-6 py-4 hover:bg-gray-50"
                  @click="router.push(`/invoices/${invoice.id}`)"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</span>
                    <span class="text-sm font-semibold text-red-600">{{ formatCurrency(invoice.total) }}</span>
                  </div>
                  <p class="mt-0.5 text-xs text-gray-500">
                    {{ invoice.vendor?.business_name }} — Vencida: {{ formatDate(invoice.due_at) }}
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { useAuthStore } from '@/stores/auth'
import * as dashboardApi from '@/api/dashboard'
import * as invoicesApi from '@/api/invoices'
import type { DashboardKpis, Invoice } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const kpis = ref<DashboardKpis | null>(null)
const recentInvoices = ref<Invoice[]>([])
const overdueInvoices = ref<Invoice[]>([])
const loadingKpis = ref(true)
const loadingInvoices = ref(true)
const loadingOverdue = ref(true)

const currencyFormatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })

function formatCurrency(value: number): string {
  return currencyFormatter.format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
}

const kpiCards = computed(() => {
  const k = kpis.value
  return [
    {
      label: 'Total Facturas',
      value: k ? k.total_invoices.toString() : '0',
      sub: formatCurrency(k?.total_amount ?? 0),
      textColor: 'text-gray-900',
    },
    {
      label: 'Pendientes',
      value: k ? k.total_pending.toString() : '0',
      sub: formatCurrency(k?.total_pending_amount ?? 0),
      textColor: 'text-yellow-600',
    },
    {
      label: 'Vencidas',
      value: k ? k.total_overdue.toString() : '0',
      sub: formatCurrency(k?.total_overdue_amount ?? 0),
      textColor: 'text-red-600',
    },
    {
      label: 'Pagadas',
      value: k ? k.total_paid.toString() : '0',
      sub: 'facturas completadas',
      textColor: 'text-green-600',
    },
  ]
})

onMounted(async () => {
  await authStore.fetchUser()

  dashboardApi.getKpis().then(({ data }) => {
    kpis.value = data
    loadingKpis.value = false
  }).catch(() => { loadingKpis.value = false })

  invoicesApi.getAll({ per_page: 10, page: 1 }).then(({ data }) => {
    recentInvoices.value = data.data
    loadingInvoices.value = false
  }).catch(() => { loadingInvoices.value = false })

  dashboardApi.getOverdue().then(({ data }) => {
    overdueInvoices.value = data
    loadingOverdue.value = false
  }).catch(() => { loadingOverdue.value = false })
})
</script>
