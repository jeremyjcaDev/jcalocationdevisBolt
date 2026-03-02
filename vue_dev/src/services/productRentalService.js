import api from './api'

export default {
  async getAll() {
    const response = await api.request('list', 'ProductRentalAvailability', {})
    console.log('ProductRental getAll response:', response)
    return response.data || []
  },

  async getByProductId(productId) {
    const response = await api.request('getByProduct', 'ProductRentalAvailability', { productId })
    return response.data
  },

  async create(productData) {
    const response = await api.request('save', 'ProductRentalAvailability', productData)
    if (!response.success) {
      throw new Error(response.message || 'Échec de la création de la disponibilité produit')
    }
    return productData
  },

  async update(id, updates) {
    const response = await api.request('update', 'ProductRentalAvailability', { id, ...updates })
    if (!response.success) {
      throw new Error(response.message || 'Échec de la mise à jour de la disponibilité produit')
    }
    return { id, ...updates }
  },

  async delete(id) {
    await api.request('delete', 'ProductRentalAvailability', { id })
  }
} 
