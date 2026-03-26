
import { computed } from 'vue'

export function useAuthState(){
const user = computed(()=>{
try{
return JSON.parse(localStorage.getItem('user')||'{}')
}catch{
return {}
}
})

const emailVerified = computed(()=>!!user.value?.email_verified_at)

return { user,emailVerified }
}
