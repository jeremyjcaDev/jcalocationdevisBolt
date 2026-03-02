<template>
  <div class="create-quote">
    <div class="container">
      <div class="header-section">
        <button class="btn-back" @click="goBack">
          <Icon name="arrow-left" :size="18" />
          <span>Retour</span>
        </button>
        <h1 class="page-title">Créer un Devis</h1>
      </div>

      <div class="form-container">
        <div class="form-section">
          <h2 class="section-title">Informations Client</h2>

          <div class="form-group">
            <label class="form-label">Type de devis</label>
            <select
              v-model="formData.quote_type"
              class="form-input"
            >
              <option value="standard">Devis standard (vente)</option>
              <option value="rental_only">Location uniquement</option>
            </select>
            <small class="form-hint">
              {{ formData.quote_type === 'rental_only' ? 'Tous les produits seront en location' : 'Devis de vente classique sans location' }}
            </small>
          </div>

          <div v-if="formData.quote_type === 'rental_only'" class="form-group rental-duration-section">
            <label class="form-label">Durée de location pour ce devis</label>
            <select
              v-model.number="formData.global_duration_months"
              class="form-input"
              @change="applyGlobalDuration"
            >
              <option value="">Choisir la durée</option>
              <option value="36">36 mois</option>
              <option value="60">60 mois</option>
            </select>
            <small class="form-hint">
              Cette durée s'appliquera à tous les produits du devis
            </small>
          </div>

          <div class="form-group">
            <label class="form-label">Email du client</label>
            <div class="autocomplete-wrapper">
              <input
                v-model="formData.customer_email"
                type="email"
                class="form-input"
                placeholder="email@example.com"
                @input="onEmailInput"
                @focus="emailInputFocused = true"
                @blur="onEmailBlur"
              />
              <div v-if="customerSuggestions.length > 0 && emailInputFocused" class="autocomplete-dropdown">
                <div
                  v-for="customer in customerSuggestions"
                  :key="customer.id"
                  class="autocomplete-item"
                  @mousedown="selectCustomer(customer)"
                >
                  <div class="customer-info-row">
                    <div>
                      <div class="customer-email">{{ customer.email }}</div>
                      <div class="customer-name">{{ customer.name || 'Pas de nom' }}</div>
                    </div>
                    <div v-if="customer.id_customer_prestashop" class="customer-badge">
                      <Icon name="check" :size="14" />
                      <span>Client ID: {{ customer.id_customer_prestashop }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Nom du client</label>
            <input
              v-model="formData.customer_name"
              type="text"
              class="form-input"
              placeholder="Nom complet du client"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Téléphone du client</label>
            <input
              v-model="formData.customer_phone"
              type="tel"
              class="form-input"
              placeholder="+33 6 12 34 56 78"
            />
          </div>

          <div v-if="quoteSettings" class="validity-info">
            <Icon name="info" :size="20" />
            <span>Ce devis sera valable jusqu'au <strong>{{ calculateValidUntil() }}</strong></span>
          </div>
        </div>

        <div class="form-section">
          <h2 class="section-title">Produits</h2>

          <div class="search-wrapper">
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              placeholder="Rechercher un produit..."
              @input="onSearchInput"
            />
            <span class="search-icon">
              <Icon name="search" :size="20" />
            </span>
          </div>

          <div v-if="searching" class="loading-message">Recherche en cours...</div>

          <div v-if="searchResults.length > 0" class="search-results">
            <div
              v-for="product in searchResults"
              :key="product.id"
              class="result-item"
              @click="addProduct(product)"
            >
              <div class="product-info">
                <div class="product-name">{{ product.name }}</div>
                <div class="product-reference">Réf: {{ product.reference }}</div>
              </div>
              <div class="product-price">{{ formatPrice(product.price) }}</div>
            </div>
          </div>

          <div v-if="!searching && searchQuery && searchResults.length === 0" class="no-results">
            Aucun produit trouvé
          </div>

          <div v-if="selectedProducts.length > 0" class="selected-products">
            <h3 class="subsection-title">Produits sélectionnés ({{ selectedProducts.length }})</h3>

            <div class="products-list">
              <div
                v-for="(item, index) in selectedProducts"
                :key="index"
                class="product-card"
              >
                <div class="product-header">
                  <div>
                    <div class="product-name">{{ item.product_name }}</div>
                    <div class="product-reference">Réf: {{ item.product_reference }}</div>
                  </div>
                  <button
                    class="btn-remove"
                    @click="removeProduct(index)"
                    title="Retirer"
                  >
                    <Icon name="x" :size="20" />
                  </button>
                </div>

                <div class="product-details">
                  <div class="detail-row">
                    <span class="label">Prix unitaire:</span>
                    <span class="value">{{ formatPrice(item.price) }}</span>
                  </div>

                  <div class="detail-row">
                    <span class="label">Quantité:</span>
                    <input
                      type="number"
                      v-model.number="item.quantity"
                      min="1"
                      class="quantity-input"
                    />
                  </div>

                  <div class="detail-row">
                    <span class="label">Total:</span>
                    <span class="value total-price">{{ formatPrice(item.price * item.quantity) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="formData.quote_type === 'rental_only' && selectedProducts.length > 0" class="summary-section">
          <div class="summary-card">
            <h3 class="summary-title">Récapitulatif</h3>

            <div v-if="formData.global_duration_months" class="summary-content">
              <div class="summary-row">
                <span class="summary-label">Durée de location:</span>
                <span class="summary-value">{{ formData.global_duration_months }} mois</span>
              </div>

              <div class="summary-row">
                <span class="summary-label">Total HT:</span>
                <span class="summary-value">{{ formatPrice(calculateTotalPrice()) }}</span>
              </div>

              <div class="summary-row total-row">
                <span class="summary-label">Mensualité totale:</span>
                <span class="summary-value monthly-payment-total">{{ formatPrice(calculateTotalMonthlyPayment()) }}</span>
              </div>
            </div>

            <div v-else class="rental-warning">
              ⚠️ Veuillez choisir une durée de location ci-dessus
            </div>
          </div>
        </div>

        <div class="actions-bar">
          <button class="btn-secondary" @click="goBack">
            Annuler
          </button>
          <button class="btn-primary" @click="saveQuote">
            Créer le devis
          </button>
        </div>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import prestashopService from '../services/prestashopService'
import quoteService from '../services/quoteService'
import rentalConfigService from '../services/rentalConfigService'
import quoteSettingsService from '../services/quoteSettingsService'
import customerService from '../services/customerService'
import Icon from '../components/Icons.vue'

export default {
  name: 'CreateQuote',
  components: {
    Icon
  },
  data() {
    return {
      formData: {
        quote_type: 'standard',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        global_duration_months: null
      },
      searchQuery: '',
      searchResults: [],
      searching: false,
      selectedProducts: [],
      rentalConfigs: [],
      quoteSettings: null,
      customerSuggestions: [],
      emailInputFocused: false,
      searchTimeout: null,
      emailSearchTimeout: null,
      error: null
    }
  },
  async mounted() {
    await this.loadRentalConfigs()
    await this.loadQuoteSettings()
  },
  methods: {
    async loadRentalConfigs() {
      try {
        this.rentalConfigs = await rentalConfigService.getAll()
        console.log('Configurations de location chargées:', this.rentalConfigs)
      } catch (err) {
        console.error('Erreur lors du chargement des configurations:', err)
      }
    },

    async loadQuoteSettings() {
      try {
        this.quoteSettings = await quoteSettingsService.get()
      } catch (err) {
        console.error('Erreur lors du chargement des paramètres de devis:', err)
      }
    },

    calculateValidUntil() {
      if (!this.quoteSettings || !this.quoteSettings.validity_hours) {
        return '-'
      }
      const date = new Date()
      date.setHours(date.getHours() + this.quoteSettings.validity_hours)
      return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    onSearchInput() {
      clearTimeout(this.searchTimeout)

      if (this.searchQuery.length < 2) {
        this.searchResults = []
        return
      }

      this.searchTimeout = setTimeout(() => {
        this.searchProducts()
      }, 500)
    },

    async searchProducts() {
      if (!this.searchQuery || this.searchQuery.length < 2) {
        return
      }

      try {
        this.searching = true
        this.error = null
        this.searchResults = await prestashopService.searchProducts(this.searchQuery)
      } catch (err) {
        this.error = 'Erreur lors de la recherche: ' + err.message
        console.error(err)
        this.searchResults = []
      } finally {
        this.searching = false
      }
    },

    addProduct(product) {
      const alreadyAdded = this.selectedProducts.find(
        p => p.product_id === product.id
      )

      if (alreadyAdded) {
        this.error = 'Ce produit est déjà dans la liste'
        setTimeout(() => { this.error = null }, 3000)
        return
      }

      const newProduct = {
        product_id: product.id,
        product_reference: product.reference || '',
        product_name: product.name,
        price: parseFloat(product.price) || 0,
        quantity: 1,
        original_price: product.originalPrice ? parseFloat(product.originalPrice) : null,
        discount_percentage: product.discountPercentage ? parseFloat(product.discountPercentage) : null,
        is_rental: this.formData.quote_type === 'rental_only',
        duration_months: null,
        rate_percentage: null,
        rental_config_id: null
      }

      // Si c'est une location et qu'une durée est déjà sélectionnée
      if (newProduct.is_rental && this.formData.global_duration_months) {
        newProduct.duration_months = this.formData.global_duration_months
        this.selectedProducts.push(newProduct)
        this.$nextTick(() => {
          this.calculateRate(newProduct)
        })
      } else {
        this.selectedProducts.push(newProduct)
      }

      this.searchQuery = ''
      this.searchResults = []
    },

    removeProduct(index) {
      this.selectedProducts.splice(index, 1)
    },


    async onEmailInput() {
      clearTimeout(this.emailSearchTimeout)

      if (this.formData.customer_email.length < 3) {
        this.customerSuggestions = []
        return
      }

      this.emailSearchTimeout = setTimeout(async () => {
        try {
          this.customerSuggestions = await customerService.search(this.formData.customer_email)
        } catch (err) {
          console.error('Erreur recherche clients:', err)
        }
      }, 500)
    },

    selectCustomer(customer) {
      this.formData.customer_email = customer.email
      this.formData.customer_name = customer.name || ''
      this.formData.customer_phone = customer.phone || ''
      this.customerSuggestions = []
      this.emailInputFocused = false
    },

    onEmailBlur() {
      setTimeout(() => {
        this.emailInputFocused = false
      }, 200)
    },

    applyGlobalDuration() {
      if (!this.formData.global_duration_months) {
        return
      }

      this.selectedProducts.forEach(item => {
        if (item.is_rental) {
          item.duration_months = this.formData.global_duration_months
          this.calculateRate(item)
        }
      })
    },

    calculateRate(item) {
      console.log('calculateRate appelé:', {
        is_rental: item.is_rental,
        duration_months: item.duration_months,
        price: item.price,
        configs: this.rentalConfigs
      })

      if (!item.is_rental || !item.duration_months || !item.price) {
        this.$set(item, 'rate_percentage', null)
        return
      }

      const config = this.rentalConfigs.find(
        c => item.price >= c.price_min && item.price <= c.price_max
      )

      console.log('Configuration trouvée:', config)

      if (config) {
        item.rental_config_id = config.id
        const rate = item.duration_months === 36
          ? config.duration_36_months
          : config.duration_60_months

        console.log('Taux trouvé:', rate, 'Type:', typeof rate)
        const numericRate = parseFloat(rate)
        console.log('Taux converti:', numericRate)

        this.$set(item, 'rate_percentage', numericRate)
        this.$forceUpdate()
      } else {
        item.rental_config_id = null
        this.$set(item, 'rate_percentage', null)
        this.error = `Aucune configuration de location trouvée pour le prix ${this.formatPrice(item.price)}`
        setTimeout(() => { this.error = null }, 3000)
      }
    },

    async saveQuote() {
      if (!this.formData.customer_name) {
        this.error = 'Veuillez saisir le nom du client'
        return
      }

      if (this.selectedProducts.length === 0) {
        this.error = 'Veuillez ajouter au moins un produit'
        return
      }

      if (this.formData.quote_type === 'rental_only' && !this.formData.global_duration_months) {
        this.error = 'Veuillez choisir une durée de location (36 ou 60 mois)'
        return
      }

      for (const product of this.selectedProducts) {
        if (product.is_rental && (!product.duration_months || !product.rate_percentage)) {
          this.error = 'Erreur: certains produits en location n\'ont pas de taux calculé'
          return
        }
      }

      try {
        let validUntil = null
        if (this.quoteSettings && this.quoteSettings.validity_hours) {
          const date = new Date()
          date.setHours(date.getHours() + this.quoteSettings.validity_hours)
          validUntil = date.toISOString().split('T')[0]
        }

        let idCustomerPrestashop = null
        if (this.formData.customer_email) {
          try {
            const prestashopCustomer = await prestashopService.searchCustomerByEmail(this.formData.customer_email)
            if (prestashopCustomer && prestashopCustomer.id) {
              idCustomerPrestashop = prestashopCustomer.id
            }
          } catch (error) {
            console.warn('Could not fetch PrestaShop customer:', error.message)
          }
        }

        const quoteData = {
          quote_type: this.formData.quote_type,
          customer_name: this.formData.customer_name,
          customer_email: this.formData.customer_email || null,
          customer_phone: this.formData.customer_phone || null,
          id_customer_prestashop: idCustomerPrestashop,
          status: 'pending',
          valid_until: validUntil
        }

        const createdQuote = await quoteService.createQuoteWithItems(quoteData, this.selectedProducts)

        if (this.formData.customer_email && createdQuote && createdQuote.id_quote) {
          try {
            await customerService.create({
              email: this.formData.customer_email,
              name: this.formData.customer_name,
              phone: this.formData.customer_phone
            }, createdQuote.id_quote)
            console.log('Customer saved in quote_customers')
          } catch (error) {
            console.error('Failed to save customer in quote_customers:', error.message)
          }
        }

        this.goBack()
      } catch (err) {
        this.error = 'Erreur lors de la création du devis: ' + err.message
        console.error(err)
      }
    },

    calculateMonthlyPayment(item) {
      if (!item.price || !item.duration_months || !item.rate_percentage) {
        return 0
      }

      const principal = item.price * (item.quantity || 1)
      const monthlyRate = (item.rate_percentage / 100) / 12
      const numberOfPayments = item.duration_months

      const monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
                            (Math.pow(1 + monthlyRate, numberOfPayments) - 1)

      return monthlyPayment
    },

    calculateTotalPrice() {
      return this.selectedProducts.reduce((total, item) => {
        return total + (item.price * (item.quantity || 1))
      }, 0)
    },

    calculateTotalMonthlyPayment() {
      return this.selectedProducts.reduce((total, item) => {
        if (item.is_rental && item.duration_months && item.rate_percentage) {
          return total + this.calculateMonthlyPayment(item)
        }
        return total
      }, 0)
    },

    formatPrice(price) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },

    goBack() {
      this.$emit('navigate', 'quotes-list')
    }
  }
}
</script>

<style scoped>
.create-quote {
  min-height: 100vh;
  background-color: #f5f5f7;
  padding: 2rem 0;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.header-section {
  margin-bottom: 2rem;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 1rem;
}

.btn-back:hover {
  background: #f5f5f7;
}

.page-title {
  font-size: 2rem;
  font-weight: 600;
  color: #1d1d1f;
  letter-spacing: -0.02em;
}

.form-container {
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 12px;
  overflow: hidden;
}

.form-section {
  padding: 2rem;
  border-bottom: 1px solid #e5e5e7;
}

.form-section:last-child {
  border-bottom: none;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1.5rem;
}

.subsection-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 1.5rem 0 1rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.5rem;
}

.form-input,
.select-field {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  transition: border-color 0.2s ease;
}

.form-input:focus,
.select-field:focus {
  outline: none;
  border-color: #0071e3;
}

.search-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.search-input {
  width: 100%;
  padding: 0.875rem 3rem 0.875rem 1rem;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  transition: border-color 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #0071e3;
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.25rem;
  pointer-events: none;
}

.loading-message {
  padding: 1rem;
  text-align: center;
  color: #6e6e73;
  font-size: 0.9375rem;
}

.search-results {
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.result-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #f5f5f7;
  cursor: pointer;
  transition: background 0.2s ease;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background: #fafafa;
}

.product-info {
  flex: 1;
}

.product-name {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
}

.product-reference {
  font-size: 0.875rem;
  color: #6e6e73;
}

.product-price {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #0071e3;
  margin-left: 1rem;
}

.no-results {
  padding: 2rem;
  text-align: center;
  color: #6e6e73;
  font-size: 0.9375rem;
}

.selected-products {
  margin-top: 1.5rem;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-card {
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  padding: 1rem;
  background: #fafafa;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.btn-remove {
  width: 32px;
  height: 32px;
  padding: 0;
  background: transparent;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  color: #d32f2f;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background: #fff3f3;
  border-color: #d32f2f;
}

.product-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
}

.detail-row .label {
  color: #6e6e73;
}

.detail-row .value {
  font-weight: 600;
  color: #1d1d1f;
}

.quantity-input {
  width: 80px;
  padding: 0.375rem 0.5rem;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1d1d1f;
  text-align: center;
  transition: border-color 0.2s ease;
}

.quantity-input:focus {
  outline: none;
  border-color: #0071e3;
}

.total-price {
  color: #0071e3 !important;
  font-size: 1rem;
}

.autocomplete-wrapper {
  position: relative;
}

.autocomplete-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  margin-top: 0.25rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
}

.autocomplete-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f5f5f7;
}

.autocomplete-item:last-child {
  border-bottom: none;
}

.autocomplete-item:hover {
  background: #f5f5f7;
}

.customer-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.customer-email {
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
}

.customer-name {
  font-size: 0.875rem;
  color: #6e6e73;
}

.customer-badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  background: #e6f4ea;
  border: 1px solid #34a853;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #1d8a47;
  white-space: nowrap;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.9375rem;
  font-weight: 500;
  color: #1d1d1f;
}

.checkbox-field {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #0071e3;
}

.rental-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  background: #e6f4ea;
  border: 1px solid #34a853;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1d8a47;
}

.form-hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6e6e73;
  font-style: italic;
}

.validity-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #e3f2fd;
  border: 1px solid #2196f3;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1565c0;
  margin-top: 1rem;
}

.rental-duration-section {
  background: #e3f2fd;
  border: 1px solid #2196f3;
  border-radius: 8px;
  padding: 1rem;
}

.rental-duration-section .form-label {
  color: #1565c0;
  font-weight: 600;
}

.rental-info {
  margin-top: 0.75rem;
  padding: 1rem;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 8px;
}

.rental-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.rental-detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.rental-detail-item .label {
  color: #6e6e73;
  font-weight: 500;
}

.rental-detail-item .value {
  color: #1d1d1f;
  font-weight: 600;
}

.rental-detail-item .monthly-payment {
  color: #1d8a47;
  font-size: 1rem;
}

.rental-warning {
  margin-top: 0.75rem;
  padding: 0.75rem;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #856404;
  text-align: center;
}

.summary-section {
  margin-top: 1.5rem;
  padding: 0 2rem 1.5rem;
}

.summary-card {
  background: #f8f9fa;
  border: 2px solid #0071e3;
  border-radius: 12px;
  padding: 1.5rem;
}

.summary-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0 0 1.25rem 0;
  text-align: center;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e5e7;
}

.summary-row.total-row {
  border-bottom: none;
  padding-top: 1rem;
  margin-top: 0.5rem;
  border-top: 2px solid #0071e3;
}

.summary-label {
  font-size: 1rem;
  color: #6e6e73;
  font-weight: 500;
}

.total-row .summary-label {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
}

.summary-value {
  font-size: 1rem;
  color: #1d1d1f;
  font-weight: 600;
}

.total-row .summary-value {
  font-size: 1.375rem;
  color: #1d8a47;
}

.monthly-payment-total {
  color: #1d8a47;
}

.actions-bar {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem 2rem;
  background: #fafafa;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #0071e3;
  color: white;
}

.btn-primary:hover {
  background: #0077ed;
}

.btn-secondary {
  background: transparent;
  color: #0071e3;
  border: 1px solid #0071e3;
}

.btn-secondary:hover {
  background: #f5f5f7;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #fff3f3;
  border: 1px solid #ffd4d4;
  border-radius: 8px;
  color: #d32f2f;
  font-size: 0.9375rem;
}

@media (max-width: 768px) {
  .actions-bar {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .rental-options {
    grid-template-columns: 1fr;
  }
}
</style>
