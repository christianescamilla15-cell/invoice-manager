export interface User {
  id: number
  name: string
  email: string
}

export interface Vendor {
  id: number
  rfc: string
  business_name: string
  contact_name: string | null
  email: string | null
  phone: string | null
  address: string | null
  city: string | null
  state: string | null
  zip_code: string | null
  invoice_count?: number
}

export interface InvoiceItem {
  id?: number
  description: string
  quantity: number
  unit_price: number
  amount: number
}

export interface Invoice {
  id: number
  invoice_number: string
  vendor_id: number
  vendor?: Vendor
  status: string
  status_label: string
  status_color: string
  tax_type: string
  issued_at: string
  due_at: string
  subtotal: number
  tax_amount: number
  retention_amount: number
  total: number
  notes: string | null
  paid_at: string | null
  items: InvoiceItem[]
  created_at: string
}

export interface DashboardKpis {
  total_invoices: number
  total_pending: number
  total_overdue: number
  total_paid: number
  total_amount: number
  total_pending_amount: number
  total_overdue_amount: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}
