import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('@/views/DashboardPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices',
      name: 'invoices',
      component: () => import('@/views/InvoicesPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/create',
      name: 'invoices-create',
      component: () => import('@/views/InvoiceCreatePage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/:id',
      name: 'invoices-show',
      component: () => import('@/views/InvoiceShowPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/invoices/:id/edit',
      name: 'invoices-edit',
      component: () => import('@/views/InvoiceEditPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/vendors',
      name: 'vendors',
      component: () => import('@/views/VendorsPage.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
