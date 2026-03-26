import { computed } from 'vue'

export function useAuthState() {
  const getUser = () => {
    try {
      return JSON.parse(localStorage.getItem('user') || '{}')
    } catch (e) {
      return {}
    }
  }

  const user = computed(() => getUser())

  const emailVerified = computed(() => {
    return !!user.value?.email_verified_at
  })

  return {
    user,
    emailVerified,
  }
}