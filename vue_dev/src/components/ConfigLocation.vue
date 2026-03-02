<template>
  <div class="config-location">
    <div class="section-header">
      <h2 class="section-title">Configuration des Locations</h2>
      <button class="btn-add" @click="addRow">
        <span class="btn-icon">+</span>
        Ajouter une tranche
      </button>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Prix min (€)</th>
            <th>Prix max (€)</th>
            <th>Durée 36 mois (%)</th>
            <th>Durée 60 mois (%)</th>
            <th class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in configurations" :key="row.id || index">
            <td>
              <input
                v-model.number="row.price_min"
                type="number"
                step="0.01"
                min="0"
                class="input-field input-number"
                placeholder="0.00"
                @blur="saveRow(row)"
              />
            </td>
            <td>
              <input
                v-model.number="row.price_max"
                type="number"
                step="0.01"
                min="0"
                class="input-field input-number"
                placeholder="0.00"
                @blur="saveRow(row)"
              />
            </td>
            <td>
              <input
                v-model.number="row.duration_36_months"
                type="number"
                step="0.01"
                min="0"
                max="100"
                class="input-field input-number"
                placeholder="0.00"
                @blur="saveRow(row)"
              />
            </td>
            <td>
              <input
                v-model.number="row.duration_60_months"
                type="number"
                step="0.01"
                min="0"
                max="100"
                class="input-field input-number"
                placeholder="0.00"
                @blur="saveRow(row)"
              />
            </td>
            <td class="actions-cell">
              <button
                class="btn-delete"
                @click="deleteRow(row, index)"
                title="Supprimer"
              >
                <span class="delete-icon">×</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="configurations.length === 0" class="empty-state">
        <p>Aucune configuration de location.</p>
        <p class="empty-hint">Cliquez sur "Ajouter une tranche" pour commencer.</p>
      </div>
    </div>

    <div v-if="saveMessage" class="save-message">
      {{ saveMessage }}
    </div>
  </div>
</template>

<script>
import rentalConfigService from '../services/rentalConfigService'

export default {
  name: 'ConfigLocation',
  data() {
    return {
      configurations: [],
      loading: true,
      error: null,
      saveMessage: null
    }
  },
  async mounted() {
    await this.loadConfigurations()
  },
  methods: {
    async loadConfigurations() {
      try {
        this.loading = true
        this.error = null
        this.configurations = await rentalConfigService.getAll()
      } catch (err) {
        this.error = 'Erreur lors du chargement des configurations: ' + err.message
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    addRow() {
      this.configurations.push({
        price_min: 0,
        price_max: 0,
        duration_36_months: 0,
        duration_60_months: 0,
        sort_order: this.configurations.length,
        isNew: true
      })
    },

    async saveRow(row) {
      if (row.price_min === undefined || row.price_max === undefined) {
        return
      }

      try {
        if (row.isNew) {
          const newRow = await rentalConfigService.create({
            price_min: row.price_min || 0,
            price_max: row.price_max || 0,
            duration_36_months: row.duration_36_months || 0,
            duration_60_months: row.duration_60_months || 0,
            sort_order: row.sort_order
          })
          Object.assign(row, newRow)
          delete row.isNew
          this.showSaveMessage('Configuration créée avec succès')
        } else if (row.id) {
          await rentalConfigService.update(row.id, {
            price_min: row.price_min || 0,
            price_max: row.price_max || 0,
            duration_36_months: row.duration_36_months || 0,
            duration_60_months: row.duration_60_months || 0
          })
          this.showSaveMessage('Configuration mise à jour')
        }
      } catch (err) {
        this.error = 'Erreur lors de la sauvegarde: ' + err.message
        console.error(err)
      }
    },

    async deleteRow(row, index) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?')) {
        return
      }

      try {
        if (row.id) {
          await rentalConfigService.delete(row.id)
          this.showSaveMessage('Configuration supprimée')
        }
        this.configurations.splice(index, 1)
      } catch (err) {
        this.error = 'Erreur lors de la suppression: ' + err.message
        console.error(err)
      }
    },

    showSaveMessage(message) {
      this.saveMessage = message
      setTimeout(() => {
        this.saveMessage = null
      }, 3000)
    }
  }
}
</script>

<style scoped>
.config-location {
  width: 100%;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d1d1f;
  margin: 0;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: #0071e3;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-add:hover {
  background: #0077ed;
}

.btn-icon {
  font-size: 1.25rem;
  line-height: 1;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #6e6e73;
}

.error-message {
  padding: 1rem;
  background: #fff3f3;
  border: 1px solid #ffd4d4;
  border-radius: 8px;
  color: #d32f2f;
  font-size: 0.9375rem;
}

.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
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
}

.data-table tbody tr:last-child td {
  border-bottom: none;
}

.data-table tbody tr:hover {
  background: #fafafa;
}

.input-field {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d2d2d7;
  border-radius: 6px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  transition: border-color 0.2s ease;
}

.input-field:focus {
  outline: none;
  border-color: #0071e3;
}

.input-number {
  max-width: 120px;
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
  padding: 3rem 1rem;
  text-align: center;
}

.empty-state p {
  color: #6e6e73;
  margin: 0.5rem 0;
}

.empty-hint {
  font-size: 0.875rem;
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
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .btn-add {
    width: 100%;
    justify-content: center;
  }

  .input-number {
    max-width: 100%;
  }
}
</style>
