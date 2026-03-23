<template>
  <RouterView />
  <ChatBot v-if="isAuthenticated" />
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ChatBot from '@/components/shared/ChatBot.vue'

const auth = useAuthStore()
const isAuthenticated = computed(() => auth.isAuthenticated)
</script>
