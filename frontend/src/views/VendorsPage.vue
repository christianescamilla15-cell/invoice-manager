<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Proveedores</h1>
          <p class="mt-1 text-sm text-gray-500">Administra tus proveedores</p>
        </div>
        <button
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
          @click="openCreate"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nuevo Proveedor
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
              <tr>
                <th class="px-6 py-3">RFC</th>
                <th class="px-6 py-3">Razon Social</th>
                <th class="px-6 py-3">Contacto</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3">Telefono</th>
                <th class="px-6 py-3">Ciudad</th>
                <th class="px-6 py-3">Facturas</th>
                <th class="px-6 py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-if="store.loading">
                <tr v-for="n in 5" :key="n">
                  <td class="px-6 py-4" v-for="c in 8" :key="c">
                    <div class="h-4 w-20 animate-pulse rounded bg-gray-200" />
                  </td>
                </tr>
              </template>
              <template v-else-if="store.vendors.length === 0">
                <tr>
                  <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                    No hay proveedores registrados
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr v-for="vendor in store.vendors" :key="vendor.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-mono text-xs text-gray-900">{{ vendor.rfc }}</td>
                  <td class="px-6 py-4 font-medium text-gray-900">{{ vendor.business_name }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ vendor.contact_name ?? '-' }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ vendor.email ?? '-' }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ vendor.phone ?? '-' }}</td>
                  <td class="px-6 py-4 text-gray-600">
                    {{ [vendor.city, vendor.state].filter(Boolean).join(', ') || '-' }}
                  </td>
                  <td class="px-6 py-4 text-gray-600">{{ vendor.invoice_count ?? 0 }}</td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-indigo-600"
                        title="Editar"
                        @click="openEdit(vendor)"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                        title="Eliminar"
                        @click="confirmDelete(vendor)"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <Teleport to="body">
        <Transition name="modal">
          <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="closeModal"
          >
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl">
              <h3 class="mb-4 text-lg font-semibold text-gray-900">
                {{ editingVendor ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
              </h3>

              <div v-if="modalError" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                {{ modalError }}
              </div>

              <form @submit.prevent="handleSaveVendor" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">RFC *</label>
                    <input
                      v-model="vendorForm.rfc"
                      type="text"
                      required
                      maxlength="13"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="XAXX010101000"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Razon Social *</label>
                    <input
                      v-model="vendorForm.business_name"
                      type="text"
                      required
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Nombre de la empresa"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Contacto</label>
                    <input
                      v-model="vendorForm.contact_name"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Nombre del contacto"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input
                      v-model="vendorForm.email"
                      type="email"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="contacto@empresa.com"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Telefono</label>
                    <input
                      v-model="vendorForm.phone"
                      type="tel"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="55 1234 5678"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Direccion</label>
                    <input
                      v-model="vendorForm.address"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Calle y numero"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ciudad</label>
                    <input
                      v-model="vendorForm.city"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Ciudad"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                    <input
                      v-model="vendorForm.state"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="Estado"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Codigo Postal</label>
                    <input
                      v-model="vendorForm.zip_code"
                      type="text"
                      maxlength="5"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                      placeholder="00000"
                    />
                  </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                  <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="closeModal"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="savingVendor"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                  >
                    {{ savingVendor ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Delete Confirm -->
      <ConfirmModal
        :visible="showDeleteConfirm"
        title="Eliminar Proveedor"
        :message="`¿Seguro que deseas eliminar a ${vendorToDelete?.business_name}? Esta accion no se puede deshacer.`"
        confirm-text="Eliminar"
        variant="danger"
        @confirm="handleDelete"
        @cancel="showDeleteConfirm = false"
      />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import ConfirmModal from '@/components/shared/ConfirmModal.vue'
import { useVendorsStore } from '@/stores/vendors'
import type { Vendor } from '@/types'

const store = useVendorsStore()

const showModal = ref(false)
const editingVendor = ref<Vendor | null>(null)
const savingVendor = ref(false)
const modalError = ref('')

const showDeleteConfirm = ref(false)
const vendorToDelete = ref<Vendor | null>(null)

const vendorForm = reactive({
  rfc: '',
  business_name: '',
  contact_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
})

function resetForm() {
  vendorForm.rfc = ''
  vendorForm.business_name = ''
  vendorForm.contact_name = ''
  vendorForm.email = ''
  vendorForm.phone = ''
  vendorForm.address = ''
  vendorForm.city = ''
  vendorForm.state = ''
  vendorForm.zip_code = ''
  modalError.value = ''
}

function openCreate() {
  editingVendor.value = null
  resetForm()
  showModal.value = true
}

function openEdit(vendor: Vendor) {
  editingVendor.value = vendor
  vendorForm.rfc = vendor.rfc
  vendorForm.business_name = vendor.business_name
  vendorForm.contact_name = vendor.contact_name ?? ''
  vendorForm.email = vendor.email ?? ''
  vendorForm.phone = vendor.phone ?? ''
  vendorForm.address = vendor.address ?? ''
  vendorForm.city = vendor.city ?? ''
  vendorForm.state = vendor.state ?? ''
  vendorForm.zip_code = vendor.zip_code ?? ''
  modalError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingVendor.value = null
}

async function handleSaveVendor() {
  savingVendor.value = true
  modalError.value = ''

  const payload = {
    rfc: vendorForm.rfc.toUpperCase(),
    business_name: vendorForm.business_name,
    contact_name: vendorForm.contact_name || null,
    email: vendorForm.email || null,
    phone: vendorForm.phone || null,
    address: vendorForm.address || null,
    city: vendorForm.city || null,
    state: vendorForm.state || null,
    zip_code: vendorForm.zip_code || null,
  }

  try {
    if (editingVendor.value) {
      await store.update(editingVendor.value.id, payload)
    } else {
      await store.create(payload)
    }
    closeModal()
  } catch (err: any) {
    modalError.value = err.response?.data?.message ?? 'Error al guardar el proveedor'
  } finally {
    savingVendor.value = false
  }
}

function confirmDelete(vendor: Vendor) {
  vendorToDelete.value = vendor
  showDeleteConfirm.value = true
}

async function handleDelete() {
  if (!vendorToDelete.value) return
  try {
    await store.remove(vendorToDelete.value.id)
  } catch (err: any) {
    alert(err.response?.data?.message ?? 'Error al eliminar el proveedor')
  } finally {
    showDeleteConfirm.value = false
    vendorToDelete.value = null
  }
}

onMounted(() => {
  store.fetchAll()
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
