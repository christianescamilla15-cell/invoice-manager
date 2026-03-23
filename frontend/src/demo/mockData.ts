import type { User, Vendor, Invoice, DashboardKpis } from '@/types'

export const DEMO_USER: User = {
  id: 1,
  name: 'Administrador',
  email: 'admin@invoicemanager.com',
}

export const DEMO_VENDORS: Vendor[] = [
  { id: 1, rfc: 'DNO210315AB1', business_name: 'Distribuidora El Norteño SA de CV', contact_name: 'Roberto Garza', email: 'roberto@norteno.mx', phone: '81 1234 5678', address: 'Av. Constitución 456', city: 'Monterrey', state: 'Nuevo León', zip_code: '64000', invoice_count: 8 },
  { id: 2, rfc: 'PSM180922CD3', business_name: 'Papelería y Servicios Monterrey', contact_name: 'Ana López', email: 'ana@papeleria-mty.mx', phone: '81 9876 5432', address: 'Calle Morelos 123', city: 'Monterrey', state: 'Nuevo León', zip_code: '64010', invoice_count: 6 },
  { id: 3, rfc: 'TMH150610EF5', business_name: 'Taller Mecánico Hernández', contact_name: 'Miguel Hernández', email: 'miguel@tallerhernandez.mx', phone: '55 2345 6789', address: 'Eje Central 789', city: 'CDMX', state: 'CDMX', zip_code: '06600', invoice_count: 4 },
  { id: 4, rfc: 'STC200101GH7', business_name: 'Soluciones Tech Central SA de CV', contact_name: 'Laura Martínez', email: 'laura@stcentral.mx', phone: '55 3456 7890', address: 'Insurgentes Sur 1200', city: 'CDMX', state: 'CDMX', zip_code: '03100', invoice_count: 7 },
  { id: 5, rfc: 'CAG190515IJ9', business_name: 'Consultoría Águila Global', contact_name: 'Fernando Ruiz', email: 'fernando@aguilaglobal.mx', phone: '33 4567 8901', address: 'Av. Vallarta 2345', city: 'Guadalajara', state: 'Jalisco', zip_code: '44100', invoice_count: 5 },
  { id: 6, rfc: 'LMP170830KL1', business_name: 'Limpieza y Mantenimiento Peninsular', contact_name: 'Carmen Dzul', email: 'carmen@lmpmerida.mx', phone: '99 5678 9012', address: 'Calle 60 #234', city: 'Mérida', state: 'Yucatán', zip_code: '97000', invoice_count: 3 },
  { id: 7, rfc: 'AIB160420MN3', business_name: 'Alimentos Industriales del Bajío', contact_name: 'José Ramírez', email: 'jose@aibajio.mx', phone: '47 6789 0123', address: 'Blvd. López Mateos 567', city: 'León', state: 'Guanajuato', zip_code: '37000', invoice_count: 4 },
  { id: 8, rfc: 'SEP210201OP5', business_name: 'Seguridad Privada Escudo', contact_name: 'Ricardo Vega', email: 'ricardo@escudoseg.mx', phone: '22 7890 1234', address: 'Calle 5 de Mayo 890', city: 'Puebla', state: 'Puebla', zip_code: '72000', invoice_count: 3 },
]

const statusMap: Record<string, { label: string; color: string }> = {
  draft: { label: 'Borrador', color: 'gray' },
  pending: { label: 'Pendiente', color: 'yellow' },
  paid: { label: 'Pagada', color: 'green' },
  overdue: { label: 'Vencida', color: 'red' },
  cancelled: { label: 'Cancelada', color: 'gray-dark' },
}

function makeInvoice(id: number, vendorId: number, status: string, taxType: string, daysAgo: number, dueDaysAgo: number, items: { description: string; quantity: number; unit_price: number }[]): Invoice {
  const now = new Date()
  const issued = new Date(now); issued.setDate(issued.getDate() - daysAgo)
  const due = new Date(now); due.setDate(due.getDate() - dueDaysAgo)

  const computedItems = items.map((it, i) => ({
    id: id * 100 + i,
    description: it.description,
    quantity: it.quantity,
    unit_price: it.unit_price,
    amount: Math.round(it.quantity * it.unit_price * 100) / 100,
  }))

  const subtotal = computedItems.reduce((s, i) => s + i.amount, 0)
  let tax_amount = 0, retention_amount = 0
  if (taxType === 'iva') { tax_amount = subtotal * 0.16 }
  else if (taxType === 'iva_retention') { tax_amount = subtotal * 0.16; retention_amount = subtotal * 0.10667 }
  const total = subtotal + tax_amount - retention_amount

  const vendor = DEMO_VENDORS.find(v => v.id === vendorId)!
  const year = issued.getFullYear()
  const num = String(id).padStart(4, '0')

  return {
    id,
    invoice_number: `INV-${year}-${num}`,
    vendor_id: vendorId,
    vendor,
    status,
    status_label: statusMap[status]?.label ?? status,
    status_color: statusMap[status]?.color ?? 'gray',
    tax_type: taxType,
    issued_at: issued.toISOString().split('T')[0],
    due_at: due.toISOString().split('T')[0],
    subtotal: Math.round(subtotal * 100) / 100,
    tax_amount: Math.round(tax_amount * 100) / 100,
    retention_amount: Math.round(retention_amount * 100) / 100,
    total: Math.round(total * 100) / 100,
    notes: null,
    paid_at: status === 'paid' ? issued.toISOString() : null,
    items: computedItems,
    created_at: issued.toISOString(),
  }
}

export const DEMO_INVOICES: Invoice[] = [
  makeInvoice(1, 1, 'paid', 'iva', 45, 15, [
    { description: 'Resma papel bond carta (paq. 10)', quantity: 5, unit_price: 890 },
    { description: 'Tóner HP LaserJet 26A', quantity: 3, unit_price: 1250 },
    { description: 'Archiveros metálicos 4 gavetas', quantity: 2, unit_price: 3500 },
  ]),
  makeInvoice(2, 4, 'paid', 'iva_retention', 40, 10, [
    { description: 'Licencia anual software contable', quantity: 1, unit_price: 12000 },
    { description: 'Soporte técnico mensual', quantity: 3, unit_price: 4500 },
    { description: 'Configuración servidores', quantity: 1, unit_price: 8000 },
  ]),
  makeInvoice(3, 3, 'paid', 'iva', 38, 8, [
    { description: 'Servicio mantenimiento preventivo vehículos', quantity: 4, unit_price: 3500 },
    { description: 'Cambio de aceite y filtros', quantity: 4, unit_price: 850 },
  ]),
  makeInvoice(4, 5, 'pending', 'iva_retention', 20, -10, [
    { description: 'Consultoría estratégica Q1', quantity: 1, unit_price: 45000 },
    { description: 'Análisis de mercado competitivo', quantity: 1, unit_price: 18000 },
  ]),
  makeInvoice(5, 2, 'pending', 'iva', 18, -12, [
    { description: 'Papelería general oficina', quantity: 1, unit_price: 3200 },
    { description: 'Artículos de limpieza industrial', quantity: 1, unit_price: 4800 },
    { description: 'Café gourmet (kg)', quantity: 10, unit_price: 380 },
    { description: 'Agua purificada garrafón', quantity: 20, unit_price: 45 },
  ]),
  makeInvoice(6, 6, 'pending', 'iva', 15, -15, [
    { description: 'Servicio limpieza profunda oficinas', quantity: 1, unit_price: 8500 },
    { description: 'Fumigación mensual', quantity: 1, unit_price: 3200 },
  ]),
  makeInvoice(7, 8, 'overdue', 'iva', 60, 5, [
    { description: 'Servicio vigilancia mensual', quantity: 2, unit_price: 15000 },
    { description: 'Monitoreo CCTV', quantity: 2, unit_price: 4500 },
  ]),
  makeInvoice(8, 7, 'overdue', 'iva', 55, 3, [
    { description: 'Insumos alimenticios comedor', quantity: 1, unit_price: 28000 },
    { description: 'Servicio catering evento corporativo', quantity: 1, unit_price: 35000 },
  ]),
  makeInvoice(9, 1, 'draft', 'iva', 5, -25, [
    { description: 'Sillas ergonómicas ejecutivas', quantity: 10, unit_price: 4500 },
    { description: 'Escritorios ajustables', quantity: 5, unit_price: 8900 },
  ]),
  makeInvoice(10, 4, 'draft', 'exempt', 3, -27, [
    { description: 'Capacitación equipo desarrollo (40hrs)', quantity: 1, unit_price: 25000 },
  ]),
  makeInvoice(11, 5, 'paid', 'iva_retention', 30, 0, [
    { description: 'Auditoría procesos internos', quantity: 1, unit_price: 38000 },
    { description: 'Reporte ejecutivo de hallazgos', quantity: 1, unit_price: 12000 },
  ]),
  makeInvoice(12, 2, 'cancelled', 'iva', 50, 20, [
    { description: 'Mobiliario sala de juntas (cancelado)', quantity: 1, unit_price: 65000 },
  ]),
  makeInvoice(13, 3, 'paid', 'iva', 25, -5, [
    { description: 'Reparación sistema eléctrico', quantity: 1, unit_price: 12000 },
    { description: 'Material eléctrico', quantity: 1, unit_price: 5600 },
  ]),
  makeInvoice(14, 6, 'pending', 'iva', 10, -20, [
    { description: 'Jardinería y áreas verdes', quantity: 1, unit_price: 4500 },
    { description: 'Suministro plantas ornamentales', quantity: 15, unit_price: 280 },
  ]),
  makeInvoice(15, 8, 'paid', 'iva', 35, 5, [
    { description: 'Instalación control de acceso', quantity: 1, unit_price: 22000 },
    { description: 'Tarjetas de proximidad (100 piezas)', quantity: 1, unit_price: 3500 },
  ]),
]

export const DEMO_KPIS: DashboardKpis = {
  total_invoices: DEMO_INVOICES.length,
  total_pending: DEMO_INVOICES.filter(i => i.status === 'pending').length,
  total_overdue: DEMO_INVOICES.filter(i => i.status === 'overdue').length,
  total_paid: DEMO_INVOICES.filter(i => i.status === 'paid').length,
  total_amount: DEMO_INVOICES.reduce((s, i) => s + i.total, 0),
  total_pending_amount: DEMO_INVOICES.filter(i => i.status === 'pending').reduce((s, i) => s + i.total, 0),
  total_overdue_amount: DEMO_INVOICES.filter(i => i.status === 'overdue').reduce((s, i) => s + i.total, 0),
}
