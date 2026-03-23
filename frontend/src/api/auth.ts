import api from './axios'
import type { User } from '@/types'

interface AuthResponse {
  token: string
  user: User
}

export function login(email: string, password: string) {
  return api.post<AuthResponse>('/auth/login', { email, password })
}

export function register(name: string, email: string, password: string) {
  return api.post<AuthResponse>('/auth/register', { name, email, password })
}

export function logout() {
  return api.post('/auth/logout')
}

export function me() {
  return api.get<User>('/auth/me')
}
