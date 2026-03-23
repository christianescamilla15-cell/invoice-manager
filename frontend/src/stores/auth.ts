import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'
import * as authApi from '@/api/auth'
import { DEMO_USER } from '@/demo/mockData'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const isDemo = ref(localStorage.getItem('demo_mode') === '1')

  const isAuthenticated = computed(() => !!token.value || isDemo.value)

  function setToken(newToken: string | null) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const { data } = await authApi.login(email, password)
      setToken(data.token)
      user.value = data.user
    } finally {
      loading.value = false
    }
  }

  function loginDemo() {
    isDemo.value = true
    localStorage.setItem('demo_mode', '1')
    user.value = DEMO_USER
    setToken('demo-token')
  }

  async function logout() {
    try {
      if (!isDemo.value) await authApi.logout()
    } catch {
      // Ignore logout errors
    } finally {
      setToken(null)
      user.value = null
      isDemo.value = false
      localStorage.removeItem('demo_mode')
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await authApi.me()
      user.value = data
    } catch {
      setToken(null)
      user.value = null
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isDemo,
    login,
    loginDemo,
    logout,
    fetchUser,
  }
})
