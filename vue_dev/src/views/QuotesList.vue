<template>
  <div class="quotes-list">
    <div class="container">
      <div class="header-section">
        <div>
          <h1 class="page-title">Gestion des Devis</h1>
          <p class="page-description">Tous vos devis en un seul endroit</p>
        </div>
        <button class="btn-primary" @click="goToCreateQuote">
          <Icon name="plus" :size="18" />
          <span>Créer un devis</span>
        </button>
      </div>

      <div class="toolbar">
        <div class="search-section">
          <input
            v-model="searchEmail"
            type="text"
            class="search-input"
            placeholder="Rechercher par email..."
          />
          <input
            v-model="searchQuoteNumber"
            type="text"
            class="search-input"
            placeholder="Rechercher par numéro de devis..."
          />
          <select v-model="selectedType" class="search-input">
            <option value="all">Tous les types</option>
            <option value="rental_only">Location</option>
            <option value="standard">Classique</option>
          </select>
        </div>

        <div class="filters-section">
          <button
            v-for="filter in filters"
            :key="filter.value"
            :class="['filter-btn', { active: selectedFilter === filter.value }]"
            @click="selectedFilter = filter.value"
          >
            {{ filter.label }}
            <span v-if="getCountByStatus(filter.value) > 0" class="badge">
              {{ getCountByStatus(filter.value) }}
            </span>
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading-message">Chargement des devis...</div>

      <div v-else-if="paginatedQuotes.length === 0" class="empty-state">
        <p>Aucun devis trouvé</p>
        <p class="hint">{{ searchEmail ? 'Essayez une autre recherche' : 'Créez votre premier devis en cliquant sur le bouton ci-dessus' }}</p>
      </div>

      <div v-else class="table-container">
        <table class="quotes-table">
          <thead>
            <tr>
              <th>N° Devis</th>
              <th>Type</th>
              <th>Client</th>
              <th>Email</th>
              <th>Enregistré</th>
              <th>Créé le</th>
              <th>Valide jusqu'au</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="quote in paginatedQuotes" :key="quote.id_quote" @click="viewQuote(quote.id_quote)" class="table-row">
              <td class="quote-number-cell">
                <div>{{ quote.quote_number }}</div>
                <div class="amount-display">
                  <template v-if="quote.quote_type === 'rental_only' && quote.monthly_payment">
                    <div class="rental-display">
                      <span class="monthly-amount">{{ formatPrice(quote.monthly_payment) }}/mois</span>
                      <span class="duration-info">sur {{ quote.duration_months }} mois</span>
                    </div>
                  </template>
                  <template v-else>
                    {{ formatPrice(quote.total_amount || 0) }}
                  </template>
                </div>
              </td>
              <td>
                <span :class="['type-badge', quote.quote_type]">
                  {{ quote.quote_type === 'rental_only' ? 'Location' : 'Classique' }}
                </span>
              </td>
              <td>{{ quote.customer_name || '-' }}</td>
              <td>{{ quote.customer_email || '-' }}</td>
              <td>
                <span :class="['registration-badge', quote.is_registered ? 'registered' : 'guest']">
                  {{ quote.is_registered ? 'Oui' : 'Non' }}
                </span>
              </td>
              <td>{{ formatDate(quote.date_add) }}</td>
              <td>{{ quote.valid_until ? formatDate(quote.valid_until) : '-' }}</td>
              <td>
                <span :class="['status-badge', quote.status]">
                  {{ getStatusLabel(quote.status) }}
                </span>
              </td>
              <td class="actions-cell" @click.stop>
                <div class="actions-menu-container">
                  <button
                    class="actions-menu-trigger"
                    @click="toggleActionsMenu(quote.id_quote)"
                    title="Actions"
                  >
                    <Icon name="more-vertical" :size="18" />
                  </button>
                  <div
                    v-if="activeMenuQuoteId === quote.id_quote"
                    class="actions-dropdown"
                    @click.stop
                  >
                    <button
                      class="dropdown-item"
                      @click="handleDownloadPdf(quote.id_quote)"
                    >
                      <Icon name="download" :size="16" />
                      <span>Télécharger PDF</span>
                    </button>
                    <button
                      v-if="quote.status === 'pending'"
                      class="dropdown-item success"
                      @click="handleUpdateStatus(quote.id_quote, 'validated')"
                    >
                      <Icon name="check" :size="16" />
                      <span>Valider</span>
                    </button>
                    <button
                      v-if="quote.status === 'pending'"
                      class="dropdown-item danger"
                      @click="handleUpdateStatus(quote.id_quote, 'refused')"
                    >
                      <Icon name="x" :size="16" />
                      <span>Refuser</span>
                    </button>
                    <div class="dropdown-divider"></div>
                    <button
                      class="dropdown-item danger"
                      @click="handleDeleteQuote(quote.id_quote)"
                    >
                      <Icon name="trash" :size="16" />
                      <span>Supprimer</span>
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="pagination">
          <button
            class="pagination-btn"
            @click="currentPage--"
            :disabled="currentPage === 1"
          >
            <Icon name="arrow-left" :size="16" />
            <span>Précédent</span>
          </button>
          <span class="pagination-info">
            Page {{ currentPage }} sur {{ totalPages }} ({{ filteredQuotes.length }} devis)
          </span>
          <button
            class="pagination-btn"
            @click="currentPage++"
            :disabled="currentPage === totalPages"
          >
            <span>Suivant</span>
            <Icon name="arrow-right" :size="16" />
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
import quoteService from '../services/quoteService'
import Icon from '../components/Icons.vue'

export default {
  name: 'QuotesList',
  components: {
    Icon
  },
  data() {
    return {
      quotes: [],
      selectedFilter: 'all',
      searchEmail: '',
      searchQuoteNumber: '',
      selectedType: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      loading: true,
      error: null,
      activeMenuQuoteId: null,
      filters: [
        { value: 'all', label: 'Tous' },
        { value: 'pending', label: 'En attente' },
        { value: 'validated', label: 'Validés' },
        { value: 'expired', label: 'Expirés' },
        { value: 'refused', label: 'Refusés' }
      ]
    }
  },
  computed: {
    filteredQuotes() {
      let filtered = this.quotes

      if (this.selectedFilter !== 'all') {
        filtered = filtered.filter(q => q.status === this.selectedFilter)
      }

      if (this.searchEmail.trim()) {
        const search = this.searchEmail.toLowerCase()
        filtered = filtered.filter(q =>
          q.customer_email && q.customer_email.toLowerCase().includes(search)
        )
      }

      if (this.searchQuoteNumber.trim()) {
        const search = this.searchQuoteNumber.toLowerCase()
        filtered = filtered.filter(q =>
          q.quote_number && q.quote_number.toLowerCase().includes(search)
        )
      }

      if (this.selectedType !== 'all') {
        filtered = filtered.filter(q => q.quote_type === this.selectedType)
      }

      return filtered
    },

    totalPages() {
      return Math.ceil(this.filteredQuotes.length / this.itemsPerPage)
    },

    paginatedQuotes() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredQuotes.slice(start, end)
    }
  },

  watch: {
    filteredQuotes() {
      this.currentPage = 1
    }
  },
  async mounted() {
    await this.loadQuotes()
    document.addEventListener('click', this.closeActionsMenu)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeActionsMenu)
  },
  methods: {
    async loadQuotes() {
      try {
        this.loading = true
        this.error = null
        const quotes = await quoteService.getAll()

        this.quotes = quotes
      } catch (err) {
        this.error = 'Erreur lors du chargement des devis: ' + err.message
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    getCountByStatus(status) {
      if (status === 'all') return this.quotes.length
      return this.quotes.filter(q => q.status === status).length
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
        month: '2-digit',
        day: '2-digit'
      }).format(date)
    },

    formatPrice(price) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    },

    calculateMonthlyPayment(totalPrice, ratePercentage, durationMonths) {
      if (!totalPrice || !ratePercentage || !durationMonths) return 0

      const monthlyRate = ratePercentage / 100 / 12
      const numberOfPayments = durationMonths

      if (monthlyRate === 0) {
        return totalPrice / numberOfPayments
      }

      const monthlyPayment = (totalPrice * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
                             (Math.pow(1 + monthlyRate, numberOfPayments) - 1)

      return monthlyPayment
    },

    goToCreateQuote() {
      this.$emit('navigate', 'create-quote')
    },

    viewQuote(quoteId) {
      this.$emit('navigate', 'quote-detail', quoteId)
    },

    async updateStatus(quoteId, newStatus) {
      try {
        await quoteService.updateWithEmail(quoteId, { status: newStatus })
        await this.loadQuotes()
      } catch (err) {
        this.error = 'Erreur lors de la mise à jour: ' + err.message
        console.error(err)
      }
    },

    async downloadPdf(quoteId) {
      try {
        await quoteService.generatePdf(quoteId)
      } catch (err) {
        this.error = 'Erreur lors de la génération du PDF: ' + err.message
        console.error(err)
      }
    },

    async deleteQuote(quoteId) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')) {
        return
      }

      try {
        await quoteService.delete(quoteId)
        await this.loadQuotes()
      } catch (err) {
        this.error = 'Erreur lors de la suppression: ' + err.message
        console.error(err)
      }
    },

    toggleActionsMenu(quoteId) {
      this.activeMenuQuoteId = this.activeMenuQuoteId === quoteId ? null : quoteId
    },

    closeActionsMenu() {
      this.activeMenuQuoteId = null
    },

    async handleDownloadPdf(quoteId) {
      this.closeActionsMenu()
      await this.downloadPdf(quoteId)
    },

    async handleUpdateStatus(quoteId, status) {
      this.closeActionsMenu()
      await this.updateStatus(quoteId, status)
    },

    async handleDeleteQuote(quoteId) {
      this.closeActionsMenu()
      await this.deleteQuote(quoteId)
    }
  }
}
</script>

<style scoped>
.quotes-list {
  min-height: 100vh;
  background-color: #f5f5f7;
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 0.25rem;
  letter-spacing: -0.02em;
}

.page-description {
  font-size: 0.9375rem;
  color: #6e6e73;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #0071e3;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: #0077ed;
}

.toolbar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.search-section {
  width: 100%;
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 200px;
  max-width: 300px;
  padding: 0.75rem 1rem;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #0071e3;
  box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.filters-section {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.625rem 1.25rem;
  background: white;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-btn:hover {
  border-color: #0071e3;
}

.filter-btn.active {
  background: #0071e3;
  color: white;
  border-color: #0071e3;
}

.badge {
  background: rgba(0, 0, 0, 0.1);
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.filter-btn.active .badge {
  background: rgba(255, 255, 255, 0.2);
}

.loading-message {
  padding: 3rem;
  text-align: center;
  color: #6e6e73;
  font-size: 0.9375rem;
}

.empty-state {
  padding: 3rem 2rem;
  text-align: center;
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 12px;
  color: #6e6e73;
}

.empty-state p {
  margin: 0.5rem 0;
}

.hint {
  font-size: 0.875rem;
  color: #86868b;
}

.table-container {
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 12px;
  overflow: hidden;
}

.quotes-table {
  width: 100%;
  border-collapse: collapse;
}

.quotes-table thead {
  background: #f5f5f7;
}

.quotes-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1d1d1f;
  border-bottom: 2px solid #e5e5e7;
}

.quotes-table tbody tr {
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f5f5f7;
}

.quotes-table tbody tr:hover {
  background: #fafafa;
}

.quotes-table td {
  padding: 1rem;
  font-size: 0.9375rem;
  color: #1d1d1f;
}

.quote-number-cell {
  font-weight: 600;
  color: #0071e3;
}

.amount-display {
  font-size: 0.8125rem;
  color: #1d8a47;
  font-weight: 600;
  margin-top: 0.25rem;
}

.rental-display {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.monthly-amount {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1d8a47;
}

.duration-info {
  font-size: 0.75rem;
  color: #6e6e73;
  font-weight: 500;
}

.type-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
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

.registration-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
}

.registration-badge.registered {
  background: #e8f5e9;
  color: #2e7d32;
}

.registration-badge.guest {
  background: #fff3e0;
  color: #e65100;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 6px;
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

.actions-cell {
  white-space: nowrap;
  width: 60px;
}

.actions-menu-container {
  position: relative;
}

.actions-menu-trigger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: transparent;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  color: #1d1d1f;
  cursor: pointer;
  transition: all 0.2s ease;
}

.actions-menu-trigger:hover {
  background: #f5f5f7;
  border-color: #b8b8bd;
}

.actions-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 4px);
  min-width: 180px;
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  z-index: 1000;
  overflow: hidden;
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  text-align: left;
  font-size: 0.9375rem;
  color: #1d1d1f;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.dropdown-item:hover {
  background: #f5f5f7;
}

.dropdown-item.success {
  color: #2e7d32;
}

.dropdown-item.success:hover {
  background: #e8f5e9;
}

.dropdown-item.danger {
  color: #d32f2f;
}

.dropdown-item.danger:hover {
  background: #fff3f3;
}

.dropdown-divider {
  height: 1px;
  background: #e5e5e7;
  margin: 0.25rem 0;
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

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e5e7;
  background: #fafafa;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1d1d1f;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #0071e3;
  color: #0071e3;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6e6e73;
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    gap: 1rem;
  }

  .btn-primary {
    width: 100%;
  }

  .table-container {
    overflow-x: auto;
  }

  .quotes-table {
    min-width: 1000px;
  }

  .pagination {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>
