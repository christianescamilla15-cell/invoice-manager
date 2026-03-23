<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Loading -->
      <template v-if="loading">
        <div class="h-10 w-48 animate-pulse rounded bg-gray-200" />
        <div class="h-96 animate-pulse rounded-xl bg-gray-200" />
      </template>

      <template v-else-if="invoice">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-4">
            <button
              class="rounded-lg border border-gray-300 p-2 text-gray-600 hover:bg-gray-100"
              @click="router.push('/invoices')"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
              <div class="mt-1 flex items-center gap-2">
                <StatusBadge :status="invoice.status" :label="invoice.status_label" />
                <span class="text-sm text-gray-500">{{ invoice.vendor?.business_name }}</span>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <!-- Status actions -->
            <button
              v-if="invoice.status === 'draft'"
              class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-600"
              @click="handleStatusChange('pending')"
            >
              Marcar Pendiente
            </button>
            <button
              v-if="invoice.status === 'pending' || invoice.status === 'overdue'"
              class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700"
              @click="handleStatusChange('paid')"
            >
              Marcar Pagada
            </button>
            <button
              v-if="invoice.status !== 'cancelled' && invoice.status !== 'paid'"
              class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="handleStatusChange('cancelled')"
            >
              Cancelar
            </button>
            <button
              class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="handleDownloadPdf"
              :disabled="downloadingPdf"
            >
              {{ downloadingPdf ? 'Descargando...' : 'Descargar PDF' }}
            </button>
            <RouterLink
              v-if="invoice.status === 'draft'"
              :to="`/invoices/${invoice.id}/edit`"
              class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
              Editar
            </RouterLink>
          </div>
        </div>

        <!-- Invoice details -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
          <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <p class="text-xs font-medium uppercase text-gray-500">Proveedor</p>
              <p class="mt-1 text-sm font-medium text-gray-900">{{ invoice.vendor?.business_name ?? '-' }}</p>
              <p v-if="invoice.vendor?.rfc" class="text-xs text-gray-500">RFC: {{ invoice.vendor.rfc }}</p>
            </div>
            <div>
              <p class="text-xs font-medium uppercase text-gray-500">Tipo de Impuesto</p>
              <p class="mt-1 text-sm font-medium text-gray-900">{{ taxTypeLabel }}</p>
            </div>
            <div>
              <p class="text-xs font-medium uppercase text-gray-500">Fecha de Emision</p>
              <p class="mt-1 text-sm font-medium text-gray-900">{{ formatDate(invoice.issued_at) }}</p>
            </div>
            <div>
              <p class="text-xs font-medium uppercase text-gray-500">Fecha de Vencimiento</p>
              <p class="mt-1 text-sm font-medium text-gray-900">{{ formatDate(invoice.due_at) }}</p>
            </div>
          </div>

          <div v-if="invoice.notes" class="border-t border-gray-200 px-6 py-4">
            <p class="text-xs font-medium uppercase text-gray-500">Notas</p>
            <p class="mt-1 text-sm text-gray-700">{{ invoice.notes }}</p>
          </div>
        </div>

        <!-- Line items -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
          <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Conceptos</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                  <th class="px-6 py-3">Descripcion</th>
                  <th class="px-6 py-3 text-right">Cantidad</th>
                  <th class="px-6 py-3 text-right">Precio Unit.</th>
                  <th class="px-6 py-3 text-right">Importe</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in invoice.items" :key="item.id">
                  <td class="px-6 py-4 text-gray-900">{{ item.description }}</td>
                  <td class="px-6 py-4 text-right text-gray-600">{{ item.quantity }}</td>
                  <td class="px-6 py-4 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-6 py-4 text-right font-medium text-gray-900">{{ formatCurrency(item.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div class="border-t border-gray-200 px-6 py-4">
            <div class="ml-auto max-w-xs space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(invoice.subtotal) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">IVA</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(invoice.tax_amount) }}</span>
              </div>
              <div v-if="invoice.retention_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-500">Retencion</span>
                <span class="font-medium text-red-600">-{{ formatCurrency(invoice.retention_amount) }}</span>
              </div>
              <div class="flex justify-between border-t border-gray-200 pt-2 text-lg">
                <span class="font-semibold text-gray-900">Total</span>
                <span class="font-bold text-gray-900">{{ formatCurrency(invoice.total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Paid date -->
        <div v-if="invoice.paid_at" class="rounded-xl border border-green-200 bg-green-50 p-4">
          <p class="text-sm font-medium text-green-800">
            Factura pagada el {{ formatDate(invoice.paid_at) }}
          </p>
        </div>
      </template>

      <template v-else>
        <div class="py-12 text-center text-gray-500">Factura no encontrada</div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { useInvoicesStore } from '@/stores/invoices'
import { downloadPdf } from '@/api/invoices'
import type { Invoice } from '@/types'

const router = useRouter()
const route = useRoute()
const store = useInvoicesStore()

const invoice = ref<Invoice | null>(null)
const loading = ref(true)
const downloadingPdf = ref(false)

const currencyFormatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
function formatCurrency(value: number): string {
  return currencyFormatter.format(value)
}
function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
}

const taxTypeLabels: Record<string, string> = {
  iva_16: 'IVA 16%',
  iva_retention: 'IVA + Retencion',
  exempt: 'Exento',
}
const taxTypeLabel = computed(() => taxTypeLabels[invoice.value?.tax_type ?? ''] ?? invoice.value?.tax_type)

async function handleStatusChange(status: string) {
  if (!invoice.value) return
  try {
    invoice.value = await store.updateStatus(invoice.value.id, status)
  } catch (err: any) {
    alert(err.response?.data?.message ?? 'Error al cambiar el estado')
  }
}

async function handleDownloadPdf() {
  if (!invoice.value) return
  downloadingPdf.value = true
  try {
    await downloadPdf(invoice.value.id)
  } catch {
    alert('Error al descargar el PDF')
  } finally {
    downloadingPdf.value = false
  }
}

onMounted(async () => {
  const id = Number(route.params.id)
  try {
    invoice.value = (await store.fetchById(id)) ?? null
  } catch {
    invoice.value = null
  } finally {
    loading.value = false
  }
})
</script>
