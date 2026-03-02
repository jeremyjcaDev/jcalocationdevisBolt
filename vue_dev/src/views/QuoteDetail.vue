<template>
  <div class="quote-detail">
    <div class="container">
      <div class="header-section">
        <button class="btn-back" @click="goBack">
          <CustomIcon name="arrow-left" :size="18" />
          <span>Retour</span>
        </button>
        <div class="header-actions">
          <button
            class="btn-primary"
            @click="downloadPdf"
          >
            <CustomIcon name="download" :size="18" />
            <span>Télécharger PDF</span>
          </button>
          <button
            v-if="canEdit"
            class="btn-secondary"
            @click="editQuote"
          >
            <CustomIcon name="edit" :size="18" />
            <span>Modifier</span>
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading-message">Chargement du devis...</div>

      <div v-else-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-else-if="quote" class="quote-container">
        <div class="quote-header">
          <div class="quote-number-display">
            <h1>{{ quote.quote_number }}</h1>
            <span :class="['status-badge', quote.status]">
              {{ getStatusLabel(quote.status) }}
            </span>
          </div>
          <div class="quote-type">
            <span :class="['type-badge', quote.quote_type]">
              <CustomIcon :name="quote.quote_type === 'rental_only' ? 'package' : 'briefcase'" :size="16" />
              <span>{{ quote.quote_type === 'rental_only' ? 'Location' : 'Vente' }}</span>
            </span>
          </div>
        </div>

        <div class="info-grid">
          <div class="info-card">
            <h2 class="card-title">Informations Client</h2>
            <div class="info-row">
              <span class="label">Nom:</span>
              <span class="value">{{ quote.customer_name || '-' }}</span>
            </div>
            <div class="info-row">
              <span class="label">Email:</span>
              <span class="value">{{ quote.customer_email || '-' }}</span>
            </div>
            <div class="info-row">
              <span class="label">Téléphone:</span>
              <span class="value">{{ quote.customer_phone || '-' }}</span>
            </div>
          </div>

          <div class="info-card">
            <h2 class="card-title">Dates</h2>
            <div class="info-row">
              <span class="label">Créé le:</span>
              <span class="value">{{ formatDate(quote.date_add) }}</span>
            </div>
            <div class="info-row">
              <span class="label">Valide jusqu'au:</span>
              <span class="value">{{ formatDate(quote.valid_until) }}</span>
            </div>
            <div class="info-row">
              <span class="label">État:</span>
              <span :class="['value', 'status-indicator', { 'expired': isExpired }]">
                <CustomIcon :name="isExpired ? 'x' : 'check'" :size="16" />
                <span>{{ isExpired ? 'Expiré' : 'Valide' }}</span>
              </span>
            </div>
          </div>
        </div>

        <div class="products-section">
          <h2 class="section-title">Produits</h2>

          <div v-if="items.length === 0" class="empty-state">
            Aucun produit dans ce devis
          </div>

          <div v-else class="products-list">
            <div v-for="item in items" :key="item.id_quote_item" class="product-card">
              <div class="product-header">
                <div>
                  <div class="product-name">{{ item.product_name }}</div>
                  <div class="product-reference">Réf: {{ item.product_reference }}</div>
                  <div v-if="item.discount_percentage" class="product-promo">
                    <span class="promo-badge">-{{ item.discount_percentage }}%</span>
                  </div>
                </div>
                <div class="product-price-section">
                  <div class="quantity-display">Qté: {{ item.quantity || 1 }}</div>
                  <div v-if="item.original_price" class="original-price">{{ formatPrice(item.original_price * (item.quantity || 1)) }}</div>
                  <div class="product-price">{{ formatPrice(item.price * (item.quantity || 1)) }}</div>
                </div>
              </div>

              <div v-if="item.is_rental" class="rental-info">
                <div class="rental-badge">
                  <CustomIcon name="package" :size="16" />
                  <span>En location</span>
                </div>
                <div class="rental-details">
                  <div class="detail-item">
                    <span class="detail-label">Durée:</span>
                    <span class="detail-value">{{ item.duration_months }} mois</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Taux:</span>
                    <span class="detail-value">{{ item.rate_percentage }}%</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Mensualité totale:</span>
                    <span class="detail-value highlight">{{ formatPrice(calculateMonthlyPayment(item) * (item.quantity || 1)) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="total-section">
            <div v-if="quote.quote_type === 'rental_only' && hasRentalItems" class="total-row rental-total">
              <div class="rental-total-info">
                <span class="total-label">Mensualité totale:</span>
                <span class="total-value">{{ formatPrice(totalMonthlyPayment) }}/mois</span>
              </div>
              <div class="duration-display">
                <span class="duration-label">Durée:</span>
                <span class="duration-value">{{ rentalDuration }} mois</span>
              </div>
            </div>
            <div v-else class="total-row">
              <span class="total-label">Total HT:</span>
              <span class="total-value">{{ formatPrice(totalAmount) }}</span>
            </div>
          </div>
        </div>

        <div class="actions-section">
          <button
            v-if="quote.status === 'pending'"
            class="btn-success"
            @click="updateStatus('validated')"
          >
            <CustomIcon name="check" :size="20" />
            <span>Valider le devis</span>
          </button>
          <button
            v-if="quote.status === 'pending'"
            class="btn-danger"
            @click="updateStatus('refused')"
          >
            <CustomIcon name="x" :size="20" />
            <span>Refuser le devis</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import quoteService from '../services/quoteService'
import CustomIcon from '../components/Icons.vue'

export default {
  name: 'QuoteDetail',
  components: {
    CustomIcon
  },
  props: {
    quoteId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      quote: null,
      items: [],
      loading: true,
      error: null
    }
  },
  computed: {
    totalAmount() {
      return this.items.reduce((sum, item) => sum + (parseFloat(item.price) * (item.quantity || 1)), 0)
    },
    hasRentalItems() {
      return this.items.some(item => item.is_rental && item.duration_months && item.rate_percentage)
    },
    totalMonthlyPayment() {
      return this.items
        .filter(item => item.is_rental && item.duration_months && item.rate_percentage)
        .reduce((sum, item) => sum + (this.calculateMonthlyPayment(item) * (item.quantity || 1)), 0)
    },
    rentalDuration() {
      const rentalItem = this.items.find(item => item.is_rental && item.duration_months)
      return rentalItem ? rentalItem.duration_months : 0
    },
    isExpired() {
      if (!this.quote?.valid_until) return false
      return new Date(this.quote.valid_until) < new Date()
    },
    canEdit() {
      if (!this.quote) return false
      return !this.isExpired && this.quote.status !== 'validated' && this.quote.status !== 'refused'
    }
  },
  async mounted() {
    await this.loadQuote()
  },
  methods: {
    async loadQuote() {
      try {
        this.loading = true
        this.error = null

        const result = await quoteService.getById(this.quoteId)

        if (!result) {
          this.error = 'Devis introuvable'
          return
        }

        this.quote = result
        this.items = result.items || []

      } catch (err) {
        this.error = 'Erreur lors du chargement: ' + err.message
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    getStatusLabel(status) {
      const labels = {
        draft: 'Brouillon',
        pending: 'En attente',
        validated: 'Validé',
        expired: 'Expiré',
        refused: 'Refusé'
      }
      return labels[status] || status
    },

    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date)
    },

    formatPrice(price) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },

    calculateMonthlyPayment(item) {
      if (!item.duration_months || !item.rate_percentage) return 0
      const price = parseFloat(item.price)
      const rate = parseFloat(item.rate_percentage) / 100
      const months = parseInt(item.duration_months)

      const monthlyRate = rate / 12
      const monthlyPayment = (price * monthlyRate * Math.pow(1 + monthlyRate, months)) /
                            (Math.pow(1 + monthlyRate, months) - 1)

      return monthlyPayment
    },

    async updateStatus(newStatus) {
      try {
        await quoteService.updateWithEmail(this.quoteId, { status: newStatus })
        await this.loadQuote()
      } catch (err) {
        this.error = 'Erreur lors de la mise à jour: ' + err.message
        console.error(err)
      }
    },

    goBack() {
      this.$emit('navigate', 'quotes-list')
    },

    async downloadPdf() {
      try {
        await quoteService.generatePdf(this.quoteId)
      } catch (err) {
        this.error = 'Erreur lors de la génération du PDF: ' + err.message
        console.error(err)
      }
    },

    editQuote() {
      this.$emit('navigate', 'edit-quote', this.quoteId)
    }
  }
}
</script>

<style scoped>
.quote-detail {
  min-height: 100vh;
  background-color: #f5f5f7;
  padding: 2rem 0;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-back:hover {
  border-color: #0071e3;
  color: #0071e3;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #0071e3;
  border: 1px solid #0071e3;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-primary:hover {
  background: #0077ed;
  border-color: #0077ed;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  border: 1px solid #0071e3;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #0071e3;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-secondary:hover {
  background: #0071e3;
  color: white;
}

.loading-message {
  padding: 3rem;
  text-align: center;
  color: #6e6e73;
  font-size: 0.9375rem;
}

.error-message {
  padding: 1rem 1.5rem;
  background: #fff3f3;
  border: 1px solid #ffd4d4;
  border-radius: 12px;
  color: #d32f2f;
  font-size: 0.9375rem;
}

.quote-container {
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 12px;
  padding: 2rem;
}

.quote-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f5f5f7;
  margin-bottom: 2rem;
}

.quote-number-display {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.quote-number-display h1 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0;
}

.type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
}

.type-badge.rental_only {
  background: #e6f4ea;
  color: #1d8a47;
}

.type-badge.standard {
  background: #e3f2fd;
  color: #1565c0;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background: #fff3e0;
  color: #e65100;
}

.status-badge.validated {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.expired {
  background: #f5f5f7;
  color: #6e6e73;
}

.status-badge.refused {
  background: #fff3f3;
  color: #d32f2f;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.info-card {
  padding: 1.5rem;
  background: #fafafa;
  border-radius: 10px;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0 0 1rem 0;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e5e7;
}

.info-row:last-child {
  border-bottom: none;
}

.info-row .label {
  color: #6e6e73;
  font-size: 0.9375rem;
}

.info-row .value {
  color: #1d1d1f;
  font-weight: 500;
  font-size: 0.9375rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  color: #2e7d32;
}

.status-indicator.expired {
  color: #d32f2f;
}

.products-section {
  margin-top: 2rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0 0 1.5rem 0;
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: #6e6e73;
  background: #fafafa;
  border-radius: 10px;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.product-card {
  padding: 1.5rem;
  background: #fafafa;
  border: 1px solid #e5e5e7;
  border-radius: 10px;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
}

.product-reference {
  font-size: 0.875rem;
  color: #6e6e73;
}

.product-promo {
  margin-top: 0.5rem;
}

.promo-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  background: #ff3b30;
  color: white;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.product-price-section {
  text-align: right;
}

.quantity-display {
  font-size: 0.875rem;
  color: #6e6e73;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.original-price {
  font-size: 0.875rem;
  color: #6e6e73;
  text-decoration: line-through;
  margin-bottom: 0.25rem;
}

.product-price {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d8a47;
}

.rental-info {
  padding-top: 1rem;
  border-top: 1px solid #e5e5e7;
}

.rental-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.875rem;
  background: #e6f4ea;
  color: #1d8a47;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1rem;
}

.rental-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.875rem;
  color: #6e6e73;
}

.detail-value {
  font-size: 0.9375rem;
  color: #1d1d1f;
  font-weight: 500;
}

.detail-value.highlight {
  color: #1d8a47;
  font-size: 1rem;
  font-weight: 600;
}

.total-section {
  padding: 1.5rem;
  background: #f5f5f7;
  border-radius: 10px;
  margin-top: 1rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
}

.total-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d8a47;
}

.rental-total {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.rental-total-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.duration-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e5e7;
}

.duration-label {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #6e6e73;
}

.duration-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #0071e3;
}

.actions-section {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f5f5f7;
}

.btn-success {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: #2e7d32;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-success:hover {
  background: #1b5e20;
}

.btn-danger {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: #d32f2f;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-danger:hover {
  background: #b71c1c;
}

@media (max-width: 768px) {
  .quote-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .rental-details {
    grid-template-columns: 1fr;
  }

  .actions-section {
    flex-direction: column;
  }
}
</style>
