import api from './api'

export default {
  async searchProducts(query, limit = 50) {
    const response = await api.request('searchProducts', 'Product', { query, limit })
    return response.data || []
  },

  async getProduct(productId) {
    const response = await api.request('getProduct', 'Product', { id: productId })
    return response.data
  }
}
