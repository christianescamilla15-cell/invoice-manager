import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Vendor } from '@/types'
import * as vendorsApi from '@/api/vendors'

export const useVendorsStore = defineStore('vendors', () => {
  const vendors = ref<Vendor[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      const { data } = await vendorsApi.getAll()
      vendors.value = data
    } finally {
      loading.value = false
    }
  }

  async function create(data: Partial<Vendor>) {
    const { data: vendor } = await vendorsApi.create(data)
    vendors.value.push(vendor)
    return vendor
  }

  async function update(id: number, data: Partial<Vendor>) {
    const { data: vendor } = await vendorsApi.update(id, data)
    const idx = vendors.value.findIndex((v) => v.id === id)
    if (idx !== -1) {
      vendors.value[idx] = vendor
    }
    return vendor
  }

  async function remove(id: number) {
    await vendorsApi.deleteVendor(id)
    vendors.value = vendors.value.filter((v) => v.id !== id)
  }

  return {
    vendors,
    loading,
    fetchAll,
    create,
    update,
    remove,
  }
})
