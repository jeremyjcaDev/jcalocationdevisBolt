<template>
  <div class="quote-builder">
    <h2 class="section-title">Créer un Devis</h2>

    <div class="search-section">
      <div class="search-wrapper">
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Rechercher un produit (nom ou référence)..."
          @input="onSearchInput"
        />
        <span class="search-icon">🔍</span>
      </div>

      <div v-if="searching" class="loading-message">Recherche en cours...</div>

      <div v-if="searchResults.length > 0" class="search-results">
        <div
          v-for="product in searchResults"
          :key="product.id"
          class="result-item"
          @click="selectProduct(product)"
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
    </div>

    <div v-if="selectedProducts.length > 0" class="selected-products">
      <h3 class="subsection-title">Produits sélectionnés</h3>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Produit</th>
              <th>Référence</th>
              <th>Prix</th>
              <th>Configuration</th>
              <th>Durée</th>
              <th>Taux</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in selectedProducts" :key="index">
              <td>{{ item.product_name }}</td>
              <td>{{ item.product_reference }}</td>
              <td>{{ formatPrice(item.price) }}</td>
              <td>
                <select
                  v-model="item.rental_config_id"
                  class="select-field"
                  @change="onConfigChange(item)"
                >
                  <option value="">Choisir une configuration</option>
                  <option
                    v-for="config in rentalConfigs"
                    :key="config.id"
                    :value="config.id"
                  >
                    {{ config.price_min }}€ - {{ config.price_max }}€
                  </option>
                </select>
              </td>
              <td>
                <select
                  v-model.number="item.duration_months"
                  class="select-field"
                  :disabled="!item.rental_config_id"
                  @change="onDurationChange(item)"
                >
                  <option value="">Choisir</option>
                  <option value="36">36 mois</option>
                  <option value="60">60 mois</option>
                </select>
              </td>
              <td>
                <span v-if="item.rate_percentage">{{ item.rate_percentage }}%</span>
                <span v-else>-</span>
              </td>
              <td class="actions-cell">
                <button
                  class="btn-delete"
                  @click="removeProduct(index)"
                  title="Supprimer"
                >
                  <span class="delete-icon">×</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="actions-bar">
        <button class="btn-secondary" @click="clearProducts">
          Tout effacer
        </button>
        <button class="btn-primary" @click="saveQuote">
          Enregistrer le devis
        </button>
      </div>
    </div>

    <div v-if="saveMessage" class="save-message">
      {{ saveMessage }}
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import prestashopService from '../services/prestashopService'
import rentalConfigService from '../services/rentalConfigService'

export default {
  name: 'QuoteBuilder',
  data() {
    return {
      searchQuery: '',
      searchResults: [],
      searching: false,
      selectedProducts: [],
      rentalConfigs: [],
      searchTimeout: null,
      saveMessage: null,
      error: null
    }
  },
  async mounted() {
    await this.loadRentalConfigs()
  },
  methods: {
    async loadRentalConfigs() {
      try {
        this.rentalConfigs = await rentalConfigService.getAll()
      } catch (err) {
        this.error = 'Erreur lors du chargement des configurations: ' + err.message
        console.error(err)
      }
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

    selectProduct(product) {
      const alreadyAdded = this.selectedProducts.find(
        p => p.product_id === product.id
      )

      if (alreadyAdded) {
        this.showMessage('Ce produit est déjà dans la liste', true)
        return
      }

      this.selectedProducts.push({
        product_id: product.id,
        product_reference: product.reference || '',
        product_name: product.name,
        price: parseFloat(product.price) || 0,
        rental_config_id: '',
        duration_months: '',
        rate_percentage: null
      })

      this.searchQuery = ''
      this.searchResults = []
      this.showMessage('Produit ajouté')
    },

    onConfigChange(item) {
      item.duration_months = ''
      item.rate_percentage = null
    },

    onDurationChange(item) {
      if (!item.rental_config_id || !item.duration_months) {
        item.rate_percentage = null
        return
      }

      const config = this.rentalConfigs.find(c => c.id === item.rental_config_id)
      if (config) {
        if (item.duration_months === 36) {
          item.rate_percentage = config.duration_36_months
        } else if (item.duration_months === 60) {
          item.rate_percentage = config.duration_60_months
        }
      }
    },

    removeProduct(index) {
      this.selectedProducts.splice(index, 1)
    },

    clearProducts() {
      if (confirm('Êtes-vous sûr de vouloir tout effacer ?')) {
        this.selectedProducts = []
      }
    },

    saveQuote() {
      if (this.selectedProducts.length === 0) {
        this.showMessage('Veuillez ajouter au moins un produit', true)
        return
      }

      const incomplete = this.selectedProducts.find(
        p => !p.rental_config_id || !p.duration_months
      )

      if (incomplete) {
        this.showMessage('Veuillez configurer tous les produits', true)
        return
      }

      console.log('Saving quote with products:', this.selectedProducts)
      this.showMessage('Fonctionnalité de sauvegarde en cours de développement')
    },

    formatPrice(price) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },

    showMessage(message, isError = false) {
      if (isError) {
        this.error = message
        setTimeout(() => {
          this.error = null
        }, 3000)
      } else {
        this.saveMessage = message
        setTimeout(() => {
          this.saveMessage = null
        }, 3000)
      }
    }
  }
}
</script>

<style scoped>
.quote-builder {
  width: 100%;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1.5rem;
}

.subsection-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1rem;
}

.search-section {
  margin-bottom: 2rem;
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
  max-height: 400px;
  overflow-y: auto;
  background: white;
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
  margin-top: 2rem;
}

.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table thead {
  background: #fafafa;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1d1d1f;
  border-bottom: 1px solid #e5e5e7;
  white-space: nowrap;
}

.data-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f5f5f7;
  font-size: 0.9375rem;
}

.data-table tbody tr:last-child td {
  border-bottom: none;
}

.data-table tbody tr:hover {
  background: #fafafa;
}

.select-field {
  width: 100%;
  min-width: 150px;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  background: white;
  transition: border-color 0.2s ease;
}

.select-field:focus {
  outline: none;
  border-color: #0071e3;
}

.select-field:disabled {
  background: #f5f5f7;
  color: #86868b;
  cursor: not-allowed;
}

.actions-column {
  width: 80px;
  text-align: center;
}

.actions-cell {
  text-align: center;
}

.btn-delete {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  background: transparent;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  color: #d32f2f;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete:hover {
  background: #fff3f3;
  border-color: #d32f2f;
}

.delete-icon {
  font-size: 1.5rem;
  line-height: 1;
}

.actions-bar {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1rem 0;
}

.btn-primary,
.btn-secondary {
  padding: 0.625rem 1.5rem;
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

.save-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #e8f5e9;
  border: 1px solid #c8e6c9;
  border-radius: 8px;
  color: #2e7d32;
  font-size: 0.9375rem;
  animation: slideIn 0.3s ease;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #fff3f3;
  border: 1px solid #ffd4d4;
  border-radius: 8px;
  color: #d32f2f;
  font-size: 0.9375rem;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .actions-bar {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .result-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .product-price {
    margin-left: 0;
  }
}
</style>
