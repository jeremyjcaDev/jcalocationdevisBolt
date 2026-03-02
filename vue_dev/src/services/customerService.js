import api from './api'
import prestashopService from './prestashopService'

export default {
  async search(email) {
    const response = await api.request('search', 'QuoteCustomer', { email })
    return response.data || []
  },

  async getByEmail(email) {
    const response = await api.request('getByEmail', 'QuoteCustomer', { email })
    return response.data
  },

  async create(customer, idQuote = null) {
    let idCustomerPrestashop = null

    try {
      const prestashopCustomer = await prestashopService.searchCustomerByEmail(customer.email)
      if (prestashopCustomer && prestashopCustomer.id) {
        idCustomerPrestashop = prestashopCustomer.id
      }
    } catch (error) {
      console.warn('Could not fetch PrestaShop customer:', error.message)
    }

    const response = await api.request('save', 'QuoteCustomer', {
      email: customer.email,
      name: customer.name,
      phone: customer.phone,
      idQuote: idQuote,
      id_customer_prestashop: idCustomerPrestashop
    })
    return response.data
  },

  async createOrUpdate(customer, idQuote = null) {
    let idCustomerPrestashop = null

    try {
      const prestashopCustomer = await prestashopService.searchCustomerByEmail(customer.email)
      if (prestashopCustomer && prestashopCustomer.id) {
        idCustomerPrestashop = prestashopCustomer.id
      }
    } catch (error) {
      console.warn('Could not fetch PrestaShop customer:', error.message)
    }

    const response = await api.request('saveOrUpdate', 'QuoteCustomer', {
      email: customer.email,
      name: customer.name,
      phone: customer.phone,
      idQuote: idQuote,
      id_customer_prestashop: idCustomerPrestashop
    })
    return response.data
  }
}
