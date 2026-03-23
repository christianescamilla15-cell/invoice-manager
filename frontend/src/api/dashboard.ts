import api from './axios'
import type { DashboardKpis, Invoice } from '@/types'

interface MonthlyTotal {
  month: string
  total: number
  count: number
}

export function getKpis() {
  return api.get<DashboardKpis>('/dashboard/kpis')
}

export function getOverdue() {
  return api.get<Invoice[]>('/dashboard/overdue')
}

export function getMonthlyTotals() {
  return api.get<MonthlyTotal[]>('/dashboard/monthly-totals')
}
