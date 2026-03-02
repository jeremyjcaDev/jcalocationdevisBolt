import api from './api'
import emailService from './emailService'

export default {
  async getAll(status = null) {
    const response = await api.request('list', 'Quote', { status })
    const quotes = response.data || []
    return quotes.map(quote => ({
      ...quote,
      id_quote: quote.id_quote || quote.id
    }))
  },

  async getById(id) {
    const response = await api.request('get', 'Quote', { id })
    const quote = response.data
    if (quote) {
      quote.id_quote = quote.id_quote || quote.id
      if (quote.items) {
        quote.items = quote.items.map(item => ({
          ...item,
          id_quote_item: item.id_quote_item || item.id
        }))
      }
    }
    return quote
  },

  async create(quoteData) {
    const response = await api.request('save', 'Quote', quoteData)
    if (response.success && response.data) {
      const quote = response.data
      quote.id_quote = quote.id_quote || quote.id
      return quote
    }
    throw new Error('Échec de la création du devis')
  },

  async update(id, updates) {
    const payload = { id }

    if (updates.customer_name !== undefined) payload.customerName = updates.customer_name
    if (updates.customer_email !== undefined) payload.customerEmail = updates.customer_email
    if (updates.customer_phone !== undefined) payload.customerPhone = updates.customer_phone
    if (updates.status !== undefined) payload.status = updates.status
    if (updates.quote_type !== undefined) payload.quote_type = updates.quote_type

    console.log('Update payload:', payload)

    const response = await api.request('update', 'Quote', payload)
    if (response.success && response.data) {
      const quote = response.data
      quote.id_quote = quote.id_quote || quote.id
      return quote
    }
    throw new Error('Échec de la mise à jour du devis')
  },

  async delete(id) {
    const response = await api.request('delete', 'Quote', { id })
    if (!response.success) {
      throw new Error(response.message || 'Échec de la suppression du devis')
    }
    return response
  },

  async getQuoteWithItems(quoteId) {
    const response = await api.request('getWithItems', 'Quote', { id: quoteId })
    const quote = response.data
    if (quote) {
      quote.id_quote = quote.id_quote || quote.id
      if (quote.items) {
        quote.items = quote.items.map(item => ({
          ...item,
          id_quote_item: item.id_quote_item || item.id
        }))
      }
    }
    return quote
  },

  async createQuoteWithItems(quoteData, items) {
    const response = await api.request('save', 'Quote', {
      ...quoteData,
      items
    })

    const fullQuote = response.data
    if (fullQuote) {
      fullQuote.id_quote = fullQuote.id_quote || fullQuote.id
      if (fullQuote.items) {
        fullQuote.items = fullQuote.items.map(item => ({
          ...item,
          id_quote_item: item.id_quote_item || item.id
        }))
      }
    }

    emailService.sendQuoteEmail('created', fullQuote, fullQuote.items || []).catch(err => {
      console.error('Failed to send quote creation email:', err)
    })

    return fullQuote
  },

  async updateWithEmail(id, updates) {
    const oldQuote = await this.getById(id)
    const updatedQuote = await this.update(id, updates)

    if (updates.status && updates.status !== oldQuote.status) {
      const quoteWithItems = await this.getQuoteWithItems(id)

      if (updates.status === 'validated') {
        emailService.sendQuoteEmail('validated', updatedQuote, quoteWithItems.items || []).catch(err => {
          console.error('Failed to send validation email:', err)
        })
      } else if (updates.status === 'refused') {
        emailService.sendQuoteEmail('refused', updatedQuote, quoteWithItems.items || []).catch(err => {
          console.error('Failed to send refusal email:', err)
        })
      }
    }

    if (updatedQuote) {
      updatedQuote.id_quote = updatedQuote.id_quote || updatedQuote.id
    }
    return updatedQuote
  },

  async generateQuoteNumber() {
    const response = await api.request('generateNumber', 'Quote', {})
    return response.data
  },

  async updateWithItems(id, quoteData, items) {
    await this.update(id, quoteData)

    await api.request('bulkDelete', 'QuoteItem', { quoteId: id })

    if (items && items.length > 0) {
      await api.request('bulkInsert', 'QuoteItem', { quoteId: id, items })
    }

    return await this.getById(id)
  },

  async generatePdf(id) {
    const config = window.JCA_LOCATIONDEVIS_CONFIG
    if (!config || !config.apiBaseUrl) {
      throw new Error('Configuration API manquante')
    }

    const response = await fetch(config.apiBaseUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': config.token
      },
      body: JSON.stringify({
        entity: 'Quote',
        action: 'generatePdf',
        data: { id }
      })
    })

    if (!response.ok) {
      throw new Error('Erreur lors de la génération du PDF')
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `Devis_${id}.pdf`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  }
}
