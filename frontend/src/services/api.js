import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
})

api.interceptors.request.use((config) => {
  // Use admin token for admin routes
  if (config.url?.startsWith('/admin')) {
    const adminToken = localStorage.getItem('admin_token')
    if (adminToken) {
      config.headers.Authorization = `Bearer ${adminToken}`
    }
  } else {
    const token = localStorage.getItem('budgetwise_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 503 && !error.config.url?.startsWith('/admin')) {
      window.location.href = '/maintenance'
      return Promise.reject(error)
    }

    if (error?.response?.status === 401) {
      if (error.config.url?.startsWith('/admin')) {
        localStorage.removeItem('admin_token')
        const isAdminLogin = window.location.pathname === '/admin/login'
        if (!isAdminLogin) {
          window.location.href = '/admin/login'
        }
      } else {
        localStorage.removeItem('budgetwise_token')
        localStorage.removeItem('budgetwise_user')

        const isAuthPage = window.location.pathname === '/login' || window.location.pathname === '/register'
        if (!isAuthPage) {
          window.location.href = '/login'
        }
      }
    }

    return Promise.reject(error)
  }
)

export default api
