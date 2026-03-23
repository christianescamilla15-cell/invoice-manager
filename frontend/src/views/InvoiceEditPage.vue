<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <button
          class="rounded-lg border border-gray-300 p-2 text-gray-600 hover:bg-gray-100"
          @click="router.push(`/invoices/${route.params.id}`)"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Factura</h1>
          <p class="mt-1 text-sm text-gray-500">{{ form.invoice_number }}</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div class="h-64 animate-pulse rounded-xl bg-gray-200" />
        <div class="h-48 animate-pulse rounded-xl bg-gray-200" />
      </div>

      <form v-else @submit.prevent class="space-y-6">
        <!-- Main info -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-gray-900">Informacion General</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Proveedor *</label>
              <select
                v-model="form.vendor_id"
                required
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
              >
                <option :value="null" disabled>Seleccionar proveedor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.business_name }}
                </option>
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Tipo de Impuesto *</label>
              <select
                v-model="form.tax_type"
                required
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
              >
                <option value="iva_16">IVA 16%</option>
                <option value="iva_retention">IVA + Retencion</option>
                <option value="exempt">Exento</option>
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Fecha de Emision *</label>
              <input
                v-model="form.issued_at"
                type="date"
                required
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Fecha de Vencimiento *</label>
              <input
                v-model="form.due_at"
                type="date"
                required
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Notas</label>
              <textarea
                v-model="form.notes"
                rows="2"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                placeholder="Notas opcionales..."
              />
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Conceptos</h2>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="addItem"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Agregar
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="grid grid-cols-12 gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3"
            >
              <div class="col-span-12 sm:col-span-5">
                <label class="mb-1 block text-xs font-medium text-gray-500">Descripcion</label>
                <input
                  v-model="item.description"
                  type="text"
                  required
                  class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                />
              </div>
              <div class="col-span-4 sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-500">Cantidad</label>
                <input
                  v-model.number="item.quantity"
                  type="number"
                  min="1"
                  step="1"
                  required
                  class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                />
              </div>
              <div class="col-span-4 sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-500">Precio Unit.</label>
                <input
                  v-model.number="item.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                />
              </div>
              <div class="col-span-3 sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-500">Importe</label>
                <p class="py-2 text-sm font-medium text-gray-900">
                  {{ formatCurrency(item.quantity * item.unit_price) }}
                </p>
              </div>
              <div class="col-span-1 flex items-end justify-center pb-2">
                <button
                  v-if="form.items.length > 1"
                  type="button"
                  class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600"
                  @click="removeItem(index)"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-gray-900">Totales</h2>
          <div class="ml-auto max-w-xs space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Subtotal</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">IVA (16%)</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(taxAmount) }}</span>
            </div>
            <div v-if="form.tax_type === 'iva_retention'" class="flex justify-between text-sm">
              <span class="text-gray-500">Retencion</span>
              <span class="font-medium text-red-600">-{{ formatCurrency(retentionAmount) }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-200 pt-2 text-base">
              <span class="font-semibold text-gray-900">Total</span>
              <span class="font-bold text-gray-900">{{ formatCurrency(total) }}</span>
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <button
            type="button"
            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
            @click="router.push(`/invoices/${route.params.id}`)"
          >
            Cancelar
          </button>
          <button
            type="button"
            :disabled="saving"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
            @click="handleUpdate"
          >
            {{ saving ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useInvoicesStore } from '@/stores/invoices'
import { useVendorsStore } from '@/stores/vendors'
import type { Vendor, InvoiceItem } from '@/types'

const router = useRouter()
const route = useRoute()
const invoicesStore = useInvoicesStore()
const vendorsStore = useVendorsStore()

const vendors = ref<Vendor[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')

const form = reactive({
  invoice_number: '',
  vendor_id: null as number | null,
  tax_type: 'iva_16',
  issued_at: '',
  due_at: '',
  notes: '',
  items: [] as InvoiceItem[],
})

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0, amount: 0 })
}

function removeItem(index: number) {
  form.items.splice(index, 1)
}

const subtotal = computed(() =>
  form.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0),
)

const taxAmount = computed(() => {
  if (form.tax_type === 'exempt') return 0
  return subtotal.value * 0.16
})

const retentionAmount = computed(() => {
  if (form.tax_type !== 'iva_retention') return 0
  return subtotal.value * 0.0667
})

const total = computed(() => subtotal.value + taxAmount.value - retentionAmount.value)

const currencyFormatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
function formatCurrency(value: number): string {
  return currencyFormatter.format(value)
}

async function handleUpdate() {
  if (!form.vendor_id) {
    error.value = 'Selecciona un proveedor'
    return
  }
  if (form.items.some((item) => !item.description || item.quantity <= 0 || item.unit_price <= 0)) {
    error.value = 'Completa todos los conceptos correctamente'
    return
  }

  saving.value = true
  error.value = ''

  try {
    const id = Number(route.params.id)
    const payload = {
      vendor_id: form.vendor_id,
      tax_type: form.tax_type,
      issued_at: form.issued_at,
      due_at: form.due_at,
      notes: form.notes || null,
      items: form.items.map((item) => ({
        id: item.id,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        amount: item.quantity * item.unit_price,
      })),
    }
    await invoicesStore.update(id, payload)
    router.push(`/invoices/${id}`)
  } catch (err: any) {
    error.value = err.response?.data?.message ?? 'Error al actualizar la factura'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const id = Number(route.params.id)
  try {
    const [invoice] = await Promise.all([
      invoicesStore.fetchById(id),
      vendorsStore.fetchAll(),
    ])
    vendors.value = vendorsStore.vendors

    if (invoice) {
      form.invoice_number = invoice.invoice_number
      form.vendor_id = invoice.vendor_id
      form.tax_type = invoice.tax_type
      form.issued_at = invoice.issued_at.split('T')[0]
      form.due_at = invoice.due_at.split('T')[0]
      form.notes = invoice.notes ?? ''
      form.items = invoice.items.length > 0
        ? invoice.items.map((item) => ({ ...item }))
        : [{ description: '', quantity: 1, unit_price: 0, amount: 0 }]
    }
  } catch {
    error.value = 'Error al cargar la factura'
  } finally {
    loading.value = false
  }
})
</script>
