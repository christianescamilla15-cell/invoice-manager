import api from './axios'
import type { Invoice, PaginatedResponse } from '@/types'

export interface InvoiceFilters {
  page?: number
  per_page?: number
  status?: string
  vendor_id?: number | null
  from?: string
  to?: string
}

export function getAll(filters?: InvoiceFilters) {
  return api.get<PaginatedResponse<Invoice>>('/invoices', { params: filters })
}

export function getById(id: number) {
  return api.get<Invoice>(`/invoices/${id}`)
}

export function create(data: Partial<Invoice>) {
  return api.post<Invoice>('/invoices', data)
}

export function update(id: number, data: Partial<Invoice>) {
  return api.put<Invoice>(`/invoices/${id}`, data)
}

export function deleteInvoice(id: number) {
  return api.delete(`/invoices/${id}`)
}

export function updateStatus(id: number, status: string) {
  return api.patch<Invoice>(`/invoices/${id}/status`, { status })
}

export function downloadPdf(id: number) {
  return api.get(`/invoices/${id}/pdf`, { responseType: 'blob' }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `factura-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  })
}
