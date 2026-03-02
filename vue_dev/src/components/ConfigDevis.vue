<template>
  <div class="config-devis">
    <h2 class="section-title">Configuration des Devis</h2>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else class="settings-container">
      <div class="setting-group">
        <label class="setting-label">
          <span class="label-text">Durée de validité d'un devis</span>
          <span class="label-hint">Nombre d'heures avant expiration</span>
        </label>
        <div class="input-wrapper">
          <input
            v-model.number="settings.validity_hours"
            type="number"
            min="1"
            step="1"
            class="input-field"
            @blur="saveSettings"
          />
          <span class="input-suffix">heures</span>
        </div>
      </div>

      <div v-if="saveMessage" class="save-message">
        {{ saveMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import quoteSettingsService from '../services/quoteSettingsService'

export default {
  name: 'ConfigDevis',
  data() {
    return {
      settings: {
        validity_hours: 48
      },
      loading: true,
      error: null,
      saveMessage: null
    }
  },
  async mounted() {
    await this.loadSettings()
  },
  methods: {
    async loadSettings() {
      try {
        this.loading = true
        this.error = null
        this.settings = await quoteSettingsService.get()
      } catch (err) {
        this.error = 'Erreur lors du chargement des paramètres: ' + err.message
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async saveSettings() {
      if (!this.settings.id_quote_settings || !this.settings.validity_hours || this.settings.validity_hours < 1) {
        return
      }

      try {
        await quoteSettingsService.update({
          id: this.settings.id_quote_settings,
          validity_hours: this.settings.validity_hours
        })
        this.showSaveMessage('Paramètres enregistrés avec succès')
      } catch (err) {
        this.error = 'Erreur lors de la sauvegarde: ' + err.message
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
.config-devis {
  width: 100%;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1.5rem;
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

.settings-container {
  max-width: 600px;
}

.setting-group {
  background: white;
  border: 1px solid #e5e5e7;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.setting-label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

.label-text {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #1d1d1f;
}

.label-hint {
  font-size: 0.875rem;
  color: #6e6e73;
}

.input-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.input-field {
  flex: 0 0 120px;
  padding: 0.625rem 0.875rem;
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

.input-suffix {
  font-size: 0.9375rem;
  color: #6e6e73;
}

.save-message {
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
  .settings-container {
    max-width: 100%;
  }

  .input-wrapper {
    flex-direction: column;
    align-items: flex-start;
  }

  .input-field {
    width: 100%;
  }
}
</style>
