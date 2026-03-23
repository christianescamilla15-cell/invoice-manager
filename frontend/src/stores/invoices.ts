import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import type { Invoice } from '@/types'
import * as invoicesApi from '@/api/invoices'

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
    loading.value = true
    try {
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
    loading.value = true
    try {
      const { data } = await invoicesApi.getById(id)
      currentInvoice.value = data
      return data
    } finally {
      loading.value = false
    }
  }

  async function create(data: Partial<Invoice>) {
    const { data: invoice } = await invoicesApi.create(data)
    return invoice
  }

  async function update(id: number, data: Partial<Invoice>) {
    const { data: invoice } = await invoicesApi.update(id, data)
    return invoice
  }

  async function remove(id: number) {
    await invoicesApi.deleteInvoice(id)
    invoices.value = invoices.value.filter((inv) => inv.id !== id)
  }

  async function updateStatus(id: number, status: string) {
    const { data: invoice } = await invoicesApi.updateStatus(id, status)
    currentInvoice.value = invoice

    const idx = invoices.value.findIndex((inv) => inv.id === id)
    if (idx !== -1) {
      invoices.value[idx] = invoice
    }
    return invoice
  }

  return {
    invoices,
    currentInvoice,
    loading,
    pagination,
    filters,
    fetchAll,
    fetchById,
    create,
    update,
    remove,
    updateStatus,
  }
})
