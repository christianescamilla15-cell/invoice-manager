<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const isOpen = ref(false)
const isPinned = ref(false)
const input = ref('')
const messages = ref<{ role: 'bot' | 'user'; text: string }[]>([])
const messagesEl = ref<HTMLElement | null>(null)
let hoverTimer: ReturnType<typeof setTimeout> | null = null

const scroll = () => nextTick(() => {
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight
})

const currentPage = computed(() => route.path)

interface Intent {
  keys: string[]
  response: string
  navigate?: string
}

const intents: Record<string, Intent> = {
  saludo: {
    keys: ['hola', 'buenas', 'hey', 'que tal', 'buenos dias', 'buenas tardes'],
    response: 'Hola! Soy el asistente de **Invoice Manager**. Puedo ayudarte a navegar el sistema, explicarte funcionalidades o guiarte paso a paso. ¿Que necesitas?'
  },
  dashboard: {
    keys: ['dashboard', 'inicio', 'panel', 'kpi', 'resumen', 'metricas', 'indicador'],
    response: '**Dashboard** muestra 4 KPIs clave:\n\n• **Total Facturas** — Cantidad total registradas\n• **Pendientes** — Facturas por cobrar con monto total\n• **Vencidas** — Facturas que pasaron su fecha limite (alerta roja)\n• **Pagadas** — Facturas liquidadas exitosamente\n\nTambien muestra las **ultimas 10 facturas** y una seccion de **alertas de vencimiento**.\n\n¿Quieres ir al Dashboard?',
    navigate: '/'
  },
  facturas: {
    keys: ['factura', 'facturas', 'invoice', 'lista', 'listado', 'ver facturas'],
    response: '**Facturas** es el modulo principal. Puedes:\n\n• **Listar** todas las facturas con filtros (estado, proveedor, rango de fechas)\n• **Crear** nueva factura con lineas de detalle dinamicas\n• **Editar** facturas existentes\n• **Cambiar estado** (Borrador → Pendiente → Pagada)\n• **Descargar PDF** profesional estilo CFDI mexicano\n\nLos **estados** son: Borrador (gris), Pendiente (amarillo), Pagada (verde), Vencida (rojo), Cancelada (gris oscuro).\n\n¿Quieres ir a Facturas?',
    navigate: '/invoices'
  },
  crear: {
    keys: ['crear', 'nueva', 'nuevo', 'agregar', 'registrar', 'generar factura', 'nueva factura'],
    response: '**Para crear una factura:**\n\n1. Ve a Facturas → "Nueva Factura"\n2. Selecciona el **proveedor** (o crea uno nuevo primero)\n3. Elige el **tipo de impuesto**:\n   - IVA 16% — Regimen general\n   - IVA + Retencion ISR — Para servicios profesionales\n   - Exento — Sin impuestos\n4. Establece **fecha de emision** y **vencimiento**\n5. Agrega **conceptos** (descripcion, cantidad, precio unitario)\n   - Los totales se calculan automaticamente\n6. Guarda como **Borrador** o directamente como **Pendiente**\n\n¿Quieres crear una factura ahora?',
    navigate: '/invoices/create'
  },
  proveedores: {
    keys: ['proveedor', 'proveedores', 'vendor', 'vendors', 'rfc', 'razon social', 'empresa'],
    response: '**Proveedores** gestiona tu catalogo de empresas:\n\n• **RFC** — Se guarda automaticamente en mayusculas (13 caracteres max)\n• **Razon Social** — Nombre legal de la empresa\n• **Contacto** — Nombre de la persona de contacto\n• **Datos** — Email, telefono, direccion, ciudad, estado, CP\n\nPuedes **crear, editar y eliminar** proveedores desde un modal rapido sin salir de la pagina. Cada proveedor muestra cuantas facturas tiene asociadas.\n\n¿Quieres ir a Proveedores?',
    navigate: '/vendors'
  },
  impuestos: {
    keys: ['impuesto', 'iva', 'retencion', 'isr', 'tax', 'exento', 'fiscal', 'calculo'],
    response: '**Tipos de impuesto disponibles:**\n\n• **IVA 16%** — Regimen general mexicano\n  Subtotal $10,000 → IVA $1,600 → Total $11,600\n\n• **IVA + Retencion ISR** — Servicios profesionales\n  Subtotal $10,000 → IVA $1,600 - ISR $1,066.70 → Total $10,533.30\n\n• **Exento** — Sin impuestos\n  Subtotal $10,000 → Total $10,000\n\nEl sistema usa el **Strategy Pattern** internamente: cada tipo de impuesto es una clase independiente que implementa la misma interfaz. Esto permite agregar nuevos regimenes sin modificar codigo existente (principio Open/Closed de SOLID).'
  },
  pdf: {
    keys: ['pdf', 'descargar', 'imprimir', 'exportar', 'documento'],
    response: '**Descarga de PDF:**\n\nDesde la vista de detalle de cualquier factura, haz click en **"Descargar PDF"**. El documento incluye:\n\n• Encabezado con datos de la empresa\n• RFC y datos fiscales del proveedor\n• Numero de factura (auto-generado: INV-YYYY-XXXX)\n• Tabla de conceptos con cantidades y precios\n• Desglose de subtotal, IVA, retenciones y total\n• Notas adicionales\n\nEl formato esta disenado al estilo CFDI mexicano.'
  },
  estados: {
    keys: ['estado', 'status', 'borrador', 'pendiente', 'pagada', 'vencida', 'cancelar', 'cancelada', 'cambiar estado'],
    response: '**Flujo de estados de una factura:**\n\n```\nBorrador → Pendiente → Pagada\n            ↓\n         Vencida (automatico si pasa la fecha)\n            ↓\n        Cancelada\n```\n\n• **Borrador** (gris) — En preparacion, editable\n• **Pendiente** (amarillo) — Enviada, esperando pago\n• **Pagada** (verde) — Liquidada exitosamente\n• **Vencida** (rojo) — Paso la fecha de vencimiento\n• **Cancelada** (gris oscuro) — Anulada\n\nDesde la vista de detalle puedes cambiar el estado con los botones de accion.'
  },
  arquitectura: {
    keys: ['arquitectura', 'solid', 'patron', 'pattern', 'repository', 'strategy', 'factory', 'service', 'codigo', 'tecnico'],
    response: '**Arquitectura del proyecto:**\n\n• **Repository Pattern** — Capa de abstraccion sobre Eloquent (InvoiceRepositoryInterface → EloquentInvoiceRepository)\n\n• **Service Layer** — Logica de negocio separada de controllers (InvoiceService, DashboardService, PdfExportService)\n\n• **Strategy Pattern** — Calculo de impuestos intercambiable (IvaTaxCalculator, IvaRetentionCalculator, ExemptTaxCalculator)\n\n• **Factory Pattern** — TaxCalculatorFactory.make() instancia la estrategia correcta\n\n**SOLID:**\n- **S** — Cada clase tiene una responsabilidad\n- **O** — Nuevos impuestos sin modificar codigo existente\n- **L** — Todas las estrategias son intercambiables\n- **I** — Interfaces pequenas y enfocadas\n- **D** — Controllers dependen de interfaces, no de implementaciones concretas'
  },
  filtros: {
    keys: ['filtro', 'filtrar', 'buscar', 'busqueda', 'fecha', 'rango'],
    response: '**Filtros disponibles en Facturas:**\n\n• **Estado** — Todas, Borrador, Pendiente, Pagada, Vencida, Cancelada\n• **Proveedor** — Dropdown con todos los proveedores\n• **Fecha desde/hasta** — Rango de fechas de emision\n\nLos filtros se aplican en tiempo real y la tabla se actualiza con paginacion (15 por pagina). Puedes combinar multiples filtros.\n\nEn **Proveedores** puedes buscar por nombre directamente en la tabla.'
  },
  navegacion: {
    keys: ['ir a', 'navegar', 'llevar', 'abrir', 'mostrar', 'donde', 'como llego'],
    response: '**Navegacion rapida:**\n\nDime a donde quieres ir:\n• "Ir al dashboard"\n• "Ir a facturas"\n• "Crear factura"\n• "Ir a proveedores"\n\nO preguntame sobre cualquier funcionalidad y te guio paso a paso.'
  },
  ayuda: {
    keys: ['ayuda', 'help', 'opciones', 'que puedo', 'como', 'funciona'],
    response: 'Puedo ayudarte con:\n\n• **Dashboard** — Explicacion de KPIs y metricas\n• **Facturas** — Crear, editar, filtrar, cambiar estado, PDF\n• **Proveedores** — Gestion del catalogo\n• **Impuestos** — IVA, retenciones, exenciones\n• **Estados** — Flujo de vida de una factura\n• **Arquitectura** — Patrones de diseno y SOLID\n• **Navegacion** — Te llevo a cualquier seccion\n\nTambien puedo darte sugerencias segun la pagina donde estes. ¿Que necesitas?'
  }
}

const contextualSuggestions = computed(() => {
  const path = currentPage.value
  if (path === '/') return [
    { label: 'Explicar KPIs', query: 'dashboard' },
    { label: 'Crear factura', query: 'crear factura' },
    { label: 'Ver vencidas', query: 'vencidas' },
  ]
  if (path === '/invoices') return [
    { label: 'Crear factura', query: 'crear factura' },
    { label: 'Filtrar', query: 'filtros' },
    { label: 'Estados', query: 'estados' },
  ]
  if (path.includes('/invoices/create') || path.includes('/edit')) return [
    { label: 'Tipos de impuesto', query: 'impuestos' },
    { label: 'Como llenar', query: 'crear factura' },
    { label: 'Proveedores', query: 'proveedores' },
  ]
  if (path.includes('/invoices/')) return [
    { label: 'Descargar PDF', query: 'pdf' },
    { label: 'Cambiar estado', query: 'estados' },
    { label: 'Editar', query: 'como edito' },
  ]
  if (path === '/vendors') return [
    { label: 'Que es RFC', query: 'rfc' },
    { label: 'Crear proveedor', query: 'proveedores' },
    { label: 'Ir a facturas', query: 'ir a facturas' },
  ]
  return [
    { label: '¿Que es esto?', query: 'ayuda' },
    { label: 'Facturas', query: 'facturas' },
    { label: 'Arquitectura', query: 'arquitectura' },
  ]
})

function matchIntent(text: string): { response: string; navigate?: string } {
  const normalized = text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  let bestMatch: Intent | null = null
  let bestScore = 0

  for (const [, intent] of Object.entries(intents)) {
    let score = 0
    for (const key of intent.keys) {
      const nKey = key.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      if (normalized.includes(nKey)) score++
    }
    if (score > bestScore) { bestScore = score; bestMatch = intent }
  }

  // Navigation shortcuts
  if (/ir a|llevar|abrir|mostrar/.test(normalized)) {
    if (/dashboard|inicio|panel/.test(normalized)) {
      router.push('/'); return { response: 'Listo, te lleve al **Dashboard**.' }
    }
    if (/factura/.test(normalized) && /crear|nueva/.test(normalized)) {
      router.push('/invoices/create'); return { response: 'Listo, te lleve a **crear una nueva factura**.' }
    }
    if (/factura/.test(normalized)) {
      router.push('/invoices'); return { response: 'Listo, te lleve a **Facturas**.' }
    }
    if (/proveedor/.test(normalized)) {
      router.push('/vendors'); return { response: 'Listo, te lleve a **Proveedores**.' }
    }
  }

  if (bestMatch && bestScore > 0) return bestMatch
  return {
    response: 'No estoy seguro de entender tu pregunta. Puedo ayudarte con:\n\n• **Facturas** — Crear, editar, filtrar, PDF\n• **Proveedores** — Gestion de catalogo\n• **Impuestos** — Tipos y calculos\n• **Dashboard** — KPIs y metricas\n• **Arquitectura** — Patrones SOLID\n\nO dime **"ir a facturas"** para navegar directamente.'
  }
}

function handleSend(text: string) {
  const trimmed = text.trim()
  if (!trimmed) return
  messages.value.push({ role: 'user', text: trimmed })
  input.value = ''
  isPinned.value = true
  scroll()
  setTimeout(() => {
    const result = matchIntent(trimmed)
    messages.value.push({ role: 'bot', text: result.response })
    scroll()
  }, 350)
}

function open() {
  if (isOpen.value) return
  isOpen.value = true
  if (messages.value.length === 0) {
    setTimeout(() => {
      messages.value.push({
        role: 'bot',
        text: 'Hola! Soy el asistente de **Invoice Manager**. Puedo explicarte cada seccion, guiarte para crear facturas, o resolver dudas sobre impuestos y arquitectura. ¿En que te ayudo?'
      })
      scroll()
    }, 200)
  }
}

function close() { isOpen.value = false; isPinned.value = false }

function onMouseEnter() { clearTimeout(hoverTimer!); hoverTimer = setTimeout(open, 300) }
function onMouseLeave() { clearTimeout(hoverTimer!); if (!isPinned.value) hoverTimer = setTimeout(close, 600) }

function formatMsg(text: string): string {
  return text
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/`([^`]+)`/g, '<code class="bg-gray-700 px-1 rounded text-xs">$1</code>')
    .replace(/```[\s\S]*?```/g, (m) => `<pre class="bg-gray-800 p-2 rounded text-xs my-1 overflow-x-auto">${m.replace(/```/g, '')}</pre>`)
    .replace(/\n/g, '<br/>')
}

watch(currentPage, () => {
  // Show contextual tip on page change if chat is open
  if (isOpen.value && isPinned.value) {
    const path = currentPage.value
    let tip = ''
    if (path === '/') tip = 'Estas en el **Dashboard**. Aqui ves los KPIs principales y facturas recientes.'
    else if (path === '/invoices') tip = 'Estas en **Facturas**. Usa los filtros arriba para buscar, o crea una nueva.'
    else if (path === '/invoices/create') tip = 'Estas creando una **nueva factura**. Selecciona proveedor, agrega conceptos y elige el tipo de impuesto.'
    else if (path === '/vendors') tip = 'Estas en **Proveedores**. Click en "Nuevo Proveedor" para agregar uno.'
    if (tip) {
      messages.value.push({ role: 'bot', text: tip })
      scroll()
    }
  }
})
</script>

<template>
  <div
    class="fixed bottom-6 right-6 z-50 font-sans"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <!-- FAB -->
    <button
      @click="isOpen ? close() : (isPinned = true, open())"
      class="w-13 h-13 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-600/30 transition-transform hover:scale-105 relative z-10 cursor-pointer"
    >
      <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>

    <!-- Panel -->
    <Transition name="chat">
      <div
        v-show="isOpen"
        class="absolute bottom-16 right-0 w-96 max-h-[520px] bg-gray-900 border border-emerald-600/20 rounded-2xl shadow-2xl shadow-black/40 flex flex-col overflow-hidden"
        @click="isPinned = true"
      >
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600/20 to-emerald-500/10 px-5 py-3.5 border-b border-emerald-600/15 shrink-0">
          <span class="block text-emerald-400 font-bold text-sm">Asistente Invoice Manager</span>
          <span class="block text-gray-500 text-xs mt-0.5">Ayuda, guia y navegacion</span>
        </div>

        <!-- Messages -->
        <div ref="messagesEl" class="flex-1 overflow-y-auto p-3.5 flex flex-col gap-2.5 max-h-80 min-h-20 scrollbar-thin">
          <div
            v-for="(msg, i) in messages"
            :key="i"
            :class="[
              'max-w-[88%] px-3.5 py-2.5 rounded-xl text-xs leading-relaxed',
              msg.role === 'bot'
                ? 'bg-gray-800 text-gray-300 self-start rounded-bl-sm'
                : 'bg-emerald-600/20 text-emerald-200 self-end rounded-br-sm'
            ]"
            v-html="formatMsg(msg.text)"
          />
        </div>

        <!-- Quick actions (contextual) -->
        <div class="flex flex-wrap gap-1.5 px-3.5 py-2 shrink-0">
          <button
            v-for="qa in contextualSuggestions"
            :key="qa.label"
            @click="handleSend(qa.query)"
            class="bg-transparent border border-emerald-600/20 text-emerald-400 px-2.5 py-1 rounded-full text-[10px] cursor-pointer hover:bg-emerald-600/15 transition-colors"
          >
            {{ qa.label }}
          </button>
        </div>

        <!-- Input -->
        <div class="flex gap-1.5 px-3 pb-3 pt-2 border-t border-gray-800 shrink-0">
          <input
            v-model="input"
            @keydown.enter="handleSend(input)"
            @focus="isPinned = true"
            placeholder="Pregunta o di 'ir a facturas'..."
            class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-gray-200 text-xs focus:outline-none focus:border-emerald-600 placeholder-gray-600"
          />
          <button
            @click="handleSend(input)"
            class="w-9 h-9 bg-emerald-600 hover:bg-emerald-500 rounded-lg flex items-center justify-center shrink-0 cursor-pointer transition-colors"
          >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.chat-enter-active, .chat-leave-active {
  transition: all 0.3s ease;
}
.chat-enter-from, .chat-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
.scrollbar-thin::-webkit-scrollbar { width: 3px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 2px; }
</style>
