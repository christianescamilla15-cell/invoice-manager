import api from './axios'
import type { Vendor } from '@/types'

export function getAll() {
  return api.get<Vendor[]>('/vendors')
}

export function getById(id: number) {
  return api.get<Vendor>(`/vendors/${id}`)
}

export function create(data: Partial<Vendor>) {
  return api.post<Vendor>('/vendors', data)
}

export function update(id: number, data: Partial<Vendor>) {
  return api.put<Vendor>(`/vendors/${id}`, data)
}

export function deleteVendor(id: number) {
  return api.delete(`/vendors/${id}`)
}
