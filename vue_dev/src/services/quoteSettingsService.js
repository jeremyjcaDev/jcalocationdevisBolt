import api from './api'

export default {
  async get() {
    const response = await api.request('get', 'QuoteSetting', {})
    return response.data
  },

  async update(settings) {
    const response = await api.request('update', 'QuoteSetting', settings)
    return response.data
  }
}
