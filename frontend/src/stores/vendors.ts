import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Vendor } from '@/types'
import * as vendorsApi from '@/api/vendors'
import { useAuthStore } from './auth'
import { DEMO_VENDORS } from '@/demo/mockData'

export const useVendorsStore = defineStore('vendors', () => {
  const vendors = ref<Vendor[]>([])
  const loading = ref(false)

  async function fetchAll() {
    const auth = useAuthStore()
    loading.value = true
    try {
      if (auth.isDemo) {
        vendors.value = [...DEMO_VENDORS]
        return
      }
      const { data } = await vendorsApi.getAll()
      vendors.value = data
    } finally {
      loading.value = false
    }
  }

  async function create(data: Partial<Vendor>) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const id = DEMO_VENDORS.length + 1
      const vendor: Vendor = {
        id, rfc: (data.rfc ?? '').toUpperCase(), business_name: data.business_name ?? '',
        contact_name: data.contact_name ?? null, email: data.email ?? null,
        phone: data.phone ?? null, address: data.address ?? null,
        city: data.city ?? null, state: data.state ?? null, zip_code: data.zip_code ?? null,
        invoice_count: 0,
      }
      DEMO_VENDORS.push(vendor)
      vendors.value.push(vendor)
      return vendor
    }
    const { data: vendor } = await vendorsApi.create(data)
    vendors.value.push(vendor)
    return vendor
  }

  async function update(id: number, data: Partial<Vendor>) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const idx = DEMO_VENDORS.findIndex(v => v.id === id)
      if (idx !== -1) Object.assign(DEMO_VENDORS[idx], data, { rfc: (data.rfc ?? DEMO_VENDORS[idx].rfc).toUpperCase() })
      const vidx = vendors.value.findIndex(v => v.id === id)
      if (vidx !== -1) vendors.value[vidx] = { ...DEMO_VENDORS[idx] }
      return DEMO_VENDORS[idx]
    }
    const { data: vendor } = await vendorsApi.update(id, data)
    const idx = vendors.value.findIndex(v => v.id === id)
    if (idx !== -1) vendors.value[idx] = vendor
    return vendor
  }

  async function remove(id: number) {
    const auth = useAuthStore()
    if (auth.isDemo) {
      const idx = DEMO_VENDORS.findIndex(v => v.id === id)
      if (idx !== -1) DEMO_VENDORS.splice(idx, 1)
      vendors.value = vendors.value.filter(v => v.id !== id)
      return
    }
    await vendorsApi.deleteVendor(id)
    vendors.value = vendors.value.filter(v => v.id !== id)
  }

  return { vendors, loading, fetchAll, create, update, remove }
})
