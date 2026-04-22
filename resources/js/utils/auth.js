export function getAuthUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

export function hasRole(roles) {
  const user = getAuthUser()
  if (!user) return false
  const roleList = Array.isArray(roles) ? roles : [roles]
  return roleList.includes(user.role)
}

export function isAdmin() {
  return hasRole(['super_admin', 'admin'])
}

export function isOwner() {
  return hasRole(['super_admin', 'admin', 'owner'])
}

export function isSuperAdmin() {
  return hasRole(['super_admin'])
}
