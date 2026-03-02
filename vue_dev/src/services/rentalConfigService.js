import api from './api'

// Transform snake_case (frontend/Supabase) to camelCase (backend PHP)
function toBackendFormat(config) {
  return {
    id: config.id,
    priceMin: config.price_min,
    priceMax: config.price_max,
    duration36Months: config.duration_36_months,
    duration60Months: config.duration_60_months,
    sortOrder: config.sort_order
  }
}

// Transform data from Supabase (snake_case or camelCase) to frontend format (snake_case)
function toFrontendFormat(config) {
  return {
    id: config.id || config.id_rental_configuration,
    price_min: config.price_min ?? config.priceMin,
    price_max: config.price_max ?? config.priceMax,
    duration_36_months: config.duration_36_months ?? config.duration36Months,
    duration_60_months: config.duration_60_months ?? config.duration60Months,
    sort_order: config.sort_order ?? config.sortOrder
  }
}

export default {
  async getAll() {
    const response = await api.request('list', 'RentalConfiguration', {})
    const data = response.data || []
    return data.map(toFrontendFormat)
  },

  async create(configuration) {
    const backendConfig = toBackendFormat(configuration)
    const response = await api.request('save', 'RentalConfiguration', backendConfig)
    if (!response.success) {
      throw new Error(response.message || 'Échec de la création de la configuration')
    }
    return toFrontendFormat(configuration)
  },

  async update(id, configuration) {
    const backendConfig = toBackendFormat({ id, ...configuration })
    const response = await api.request('update', 'RentalConfiguration', backendConfig)
    if (!response.success) {
      throw new Error(response.message || 'Échec de la mise à jour de la configuration')
    }
    return toFrontendFormat({ id, ...configuration })
  },

  async delete(id) {
    await api.request('delete', 'RentalConfiguration', { id })
  },

  async updateSortOrders(configurations) {
    const promises = configurations.map((config, index) =>
      this.update(config.id, { ...config, sort_order: index })
    )
    return await Promise.all(promises)
  }
}
