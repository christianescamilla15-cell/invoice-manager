import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import type { Invoice } from '@/types'
import * as invoicesApi from '@/api/invoices'
import { useAuthStore } from './auth'
import { DEMO_INVOICES } from '@/demo/mockData'

const statusMap: Record<string, { label: string; color: string }> = {
  draft: { label: 'Borrador', color: 'gray' },
  pending: { label: 'Pendiente', color: 'yellow' },
  paid: { label: 'Pagada', color: 'green' },
  overdue: { label: 'Vencida', color: 'red' },
  cancelled: { label: 'Cancelada', color: 'gray-dark' },
}

export const useInvoicesStore = defineStore('invoices', () => {
  const invoices = ref<Invoice[]>([])
  const currentInvoice = ref<Invoice | null>(null)
  const loading = ref(false)

  const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  const filters = reactive({
    status: '' as string,
    vendor_id: null as number | null,
    from: '' as string,
    to: '' as string,
  })

  async function fetchAll(page?: number) {
    const auth = useAuthStore()
    loading.value = true
    try {
      if (auth.isDemo) {
        let filtered = [...DEMO_INVOICES]
        if (filters.status) filtered = filtered.filter(i => i.status === filters.status)
        if (filters.vendor_id) filtered = filtered.filter(i => i.vendor_id === filters.vendor_id)
        if (filters.from) filtered = filtered.filter(i => i.issued_at >= filters.from)
        if (filters.to) filtered = filtered.filter(i => i.issued_at <= filters.to)
        invoices.value = filtered
        pagination.current_page = 1
        pagination.last_page = 1
        pagination.total = filtered.length
        return
      }
      const params: invoicesApi.InvoiceFilters = {
        page: page ?? pagination.current_page,
        per_page: pagination.per_page,
      }
      if (filters.status) params.status = filters.status
      if (filters.vendor_id) params.vendor_id = filters.vendor_id
      if (filters.from) params.from = filters.from
      if (filters.to) params.to = filters.to

      const { data } = await invoicesApi.getAll(params)
      invoices.value = data.data
      Object.assign(pagination, data.meta)
    } finally {
      loading.value = false
    }
  }

  async function fetchById(id: number) {
    const auth = useAuthStore()
    loading.value = true
    try {
      if (auth.isDemo) {
        const inv = DEMO_INVOICES.find(i => i.id === id) ?? null
        currentInvoice.value = inv
        return inv
      }
      const { data } = await invoicesApi.getById(id)
      currentInvoice.value = data
      return data
    } finally {
      loading.value = false
    }
  }

  async function create(data: any) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const id = DEMO_INVOICES.length + 1
      const items = (data.items || []).map((it: any, i: number) => ({
        id: id * 100 + i, description: it.description, quantity: it.quantity,
        unit_price: it.unit_price, amount: it.quantity * it.unit_price,
      }))
      const subtotal = items.reduce((s: number, i: any) => s + i.amount, 0)
      let tax = 0, ret = 0
      if (data.tax_type === 'iva') tax = subtotal * 0.16
      else if (data.tax_type === 'iva_retention') { tax = subtotal * 0.16; ret = subtotal * 0.10667 }
      const inv: Invoice = {
        id, invoice_number: `INV-2026-${String(id).padStart(4, '0')}`, vendor_id: data.vendor_id,
        vendor: undefined, status: data.status || 'draft',
        status_label: statusMap[data.status || 'draft']?.label ?? 'Borrador',
        status_color: statusMap[data.status || 'draft']?.color ?? 'gray',
        tax_type: data.tax_type, issued_at: data.issued_at, due_at: data.due_at,
        subtotal, tax_amount: tax, retention_amount: ret, total: subtotal + tax - ret,
        notes: data.notes, paid_at: null, items, created_at: new Date().toISOString(),
      }
      DEMO_INVOICES.push(inv)
      return inv
    }
    const { data: invoice } = await invoicesApi.create(data)
    return invoice
  }

  async function update(id: number, data: any) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const idx = DEMO_INVOICES.findIndex(i => i.id === id)
      if (idx !== -1) Object.assign(DEMO_INVOICES[idx], data)
      return DEMO_INVOICES[idx]
    }
    const { data: invoice } = await invoicesApi.update(id, data)
    return invoice
  }

  async function remove(id: number) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const idx = DEMO_INVOICES.findIndex(i => i.id === id)
      if (idx !== -1) DEMO_INVOICES.splice(idx, 1)
      invoices.value = invoices.value.filter(inv => inv.id !== id)
      return
    }
    await invoicesApi.deleteInvoice(id)
    invoices.value = invoices.value.filter(inv => inv.id !== id)
  }

  async function updateStatus(id: number, status: string) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const inv = DEMO_INVOICES.find(i => i.id === id)
      if (inv) {
        inv.status = status
        inv.status_label = statusMap[status]?.label ?? status
        inv.status_color = statusMap[status]?.color ?? 'gray'
        if (status === 'paid') inv.paid_at = new Date().toISOString()
      }
      currentInvoice.value = inv ?? null
      const idx = invoices.value.findIndex(i => i.id === id)
      if (idx !== -1 && inv) invoices.value[idx] = { ...inv }
      return inv
    }
    const { data: invoice } = await invoicesApi.updateStatus(id, status)
    currentInvoice.value = invoice
    const idx = invoices.value.findIndex(inv => inv.id === id)
    if (idx !== -1) invoices.value[idx] = invoice
    return invoice
  }

  return {
    invoices, currentInvoice, loading, pagination, filters,
    fetchAll, fetchById, create, update, remove, updateStatus,
  }
})
