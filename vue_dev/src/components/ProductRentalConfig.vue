<template>
  <div class="product-rental-config">
    <div class="header-section">
      <div>
        <h2 class="section-title">Configuration Location des Produits</h2>
        <p class="section-description">Définir quels produits sont disponibles en location sur le site web</p>
      </div>
    </div>

    <div class="search-section">
      <div class="search-wrapper">
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Rechercher un produit à configurer (nom ou référence)..."
          @input="onSearchInput"
        />
        <span class="search-icon">🔍</span>
      </div>

      <div v-if="searching" class="loading-message">Recherche en cours...</div>

      <div v-if="searchResults.length > 0" class="search-results">
        <div
          v-for="product in searchResults"
          :key="`${product.id}-${product.id_product_attribute || 0}`"
          class="result-item"
          @click="addProduct(product)"
        >
          <img v-if="product.image" :src="product.image" :alt="product.name" class="product-image" />
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

    <div v-if="loading" class="loading-message">Chargement des produits configurés...</div>

    <div v-else-if="configuredProducts.length > 0" class="configured-products">
      <h3 class="subsection-title">Produits configurés pour la location ({{ configuredProducts.length }})</h3>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Produit</th>
              <th>Référence</th>
              <th>Prix</th>
              <th class="center-text">Location activée</th>
              <th class="center-text">36 mois</th>
              <th class="center-text">60 mois</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in configuredProducts" :key="item.id">
              <td>{{ item.product_name }}</td>
              <td>{{ item.product_reference }}</td>
              <td>{{ formatPrice(item.product_price) }}</td>
              <td class="center-cell">
                <input
                  type="checkbox"
                  v-model="item.rental_enabled"
                  class="checkbox-field"
                  @change="saveProduct(item)"
                />
              </td>
              <td class="center-cell">
                <input
                  type="checkbox"
                  v-model="item.duration_36_enabled"
                  class="checkbox-field"
                  :disabled="!item.rental_enabled"
                  @change="saveProduct(item)"
                />
              </td>
              <td class="center-cell">
                <input
                  type="checkbox"
                  v-model="item.duration_60_enabled"
                  class="checkbox-field"
                  :disabled="!item.rental_enabled"
                  @change="saveProduct(item)"
                />
              </td>
              <td class="actions-cell">
                <button
                  class="btn-delete"
                  @click="deleteProduct(item)"
                  title="Supprimer"
                >
                  <span class="delete-icon">×</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else-if="!loading" class="empty-state">
      <p>Aucun produit configuré pour la location.</p>
      <p class="hint">Recherchez et ajoutez des produits ci-dessus pour commencer.</p>
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
import productRentalService from '../services/productRentalService'

export default {
  name: 'ProductRentalConfig',
  data() {
    return {
      searchQuery: '',
      searchResults: [],
      searching: false,
      configuredProducts: [],
      searchTimeout: null,
      loading: true,
      saveMessage: null,
      error: null
    }
  },
  async mounted() {
    await this.loadConfiguredProducts()
  },
  methods: {

    async loadConfiguredProducts() {
      try {
        this.loading = true
        this.configuredProducts = await productRentalService.getAll()
      } catch (err) {
        this.error = 'Erreur lors du chargement des produits: ' + err.message
        console.error(err)
      } finally {
        this.loading = false
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

    async addProduct(product) {
      const uniqueKey = product.id_product_attribute
        ? `${product.id}-${product.id_product_attribute}`
        : product.id

      const alreadyConfigured = this.configuredProducts.find(p => {
        const configKey = p.id_product_attribute
          ? `${p.product_id}-${p.id_product_attribute}`
          : p.product_id
        return configKey === uniqueKey
      })

      if (alreadyConfigured) {
        this.showMessage('Ce produit est déjà configuré', true)
        return
      }

      try {
        const productData = {
          idProduct: product.id,
          productReference: product.reference || '',
          productName: product.name,
          productPrice: parseFloat(product.price) || 0
        }

        if (product.id_product_attribute) {
          productData.idProductAttribute = product.id_product_attribute
        }

        await productRentalService.create(productData)
        await this.loadConfiguredProducts()
        this.searchQuery = ''
        this.searchResults = []
        this.showMessage('Produit ajouté avec succès')
      } catch (err) {
        this.error = 'Erreur lors de l\'ajout: ' + err.message
        console.error(err)
      }
    },

    async saveProduct(product) {
      try {
        await productRentalService.update(product.id, {
          rental_enabled: product.rental_enabled,
          duration_36_enabled: product.duration_36_enabled,
          duration_60_enabled: product.duration_60_enabled
        })
        this.showMessage('Configuration enregistrée')
      } catch (err) {
        this.error = 'Erreur lors de la sauvegarde: ' + err.message
        console.error(err)
      }
    },

    async deleteProduct(product) {
      if (!confirm(`Supprimer la configuration pour "${product.product_name}" ?`)) {
        return
      }

      try {
        await productRentalService.delete(product.id)
        const index = this.configuredProducts.findIndex(p => p.id === product.id)
        if (index > -1) {
          this.configuredProducts.splice(index, 1)
        }
        this.showMessage('Produit supprimé')
      } catch (err) {
        this.error = 'Erreur lors de la suppression: ' + err.message
        console.error(err)
      }
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
.product-rental-config {
  width: 100%;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
}

.section-description {
  font-size: 0.9375rem;
  color: #6e6e73;
}

.subsection-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1rem;
}

.search-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
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
  gap: 1rem;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background: #fafafa;
}

.product-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e5e7;
  flex-shrink: 0;
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
  flex-shrink: 0;
}

.no-results {
  padding: 2rem;
  text-align: center;
  color: #6e6e73;
  font-size: 0.9375rem;
}

.configured-products {
  margin-top: 2rem;
}

.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  background: white;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
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

.center-text {
  text-align: center;
}

.center-cell {
  text-align: center;
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

.checkbox-field {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #0071e3;
}

.checkbox-field:disabled {
  cursor: not-allowed;
  opacity: 0.5;
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

.empty-state {
  padding: 3rem 2rem;
  text-align: center;
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  color: #6e6e73;
}

.empty-state p {
  margin: 0.5rem 0;
}

.hint {
  font-size: 0.875rem;
  color: #86868b;
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
  .result-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .product-price {
    margin-left: 0;
  }

  .table-wrapper {
    overflow-x: scroll;
  }
}
</style>
