<template>
  <div class="config-general">
    <div v-if="loading" class="loading-message">Chargement...</div>

    <div v-else-if="error" class="error-message">{{ error }}</div>

    <div v-else class="config-form">
      <div class="config-section">
        <h3 class="subsection-title">Numérotation des Devis</h3>
        <p class="subsection-description">
          Configurez le format de numérotation automatique des devis
        </p>

        <div class="form-group">
          <label class="form-label">Préfixe</label>
          <input
            v-model="settings.quote_number_prefix"
            type="text"
            class="form-input"
            placeholder="DEVIS"
          />
          <small class="form-hint">Texte qui précède le numéro (ex: DEVIS, QUOTE, DEV)</small>
        </div>

        <div class="form-group">
          <label class="form-label">Séparateur</label>
          <input
            v-model="settings.quote_number_separator"
            type="text"
            class="form-input"
            maxlength="2"
            placeholder="-"
          />
          <small class="form-hint">Caractère de séparation (ex: -, _, /)</small>
        </div>

        <div class="form-group">
          <label class="form-label">Format année</label>
          <select v-model="settings.quote_number_year_format" class="form-input">
            <option value="YYYY">YYYY (2025)</option>
            <option value="YY">YY (25)</option>
            <option value="">Pas d'année</option>
          </select>
          <small class="form-hint">Format d'affichage de l'année</small>
        </div>

        <div class="form-group">
          <label class="form-label">Nombre de zéros</label>
          <input
            v-model.number="settings.quote_number_padding"
            type="number"
            min="1"
            max="6"
            class="form-input"
          />
          <small class="form-hint">Nombre de chiffres pour le compteur (ex: 3 = 001, 4 = 0001)</small>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input
              v-model="settings.quote_number_reset_yearly"
              type="checkbox"
              class="checkbox-field"
            />
            <span>Réinitialiser le compteur chaque année</span>
          </label>
          <small class="form-hint">Si activé, le compteur repart à 1 au début de chaque année</small>
        </div>

        <div class="preview-box">
          <div class="preview-label">Aperçu:</div>
          <div class="preview-value">{{ previewQuoteNumber }}</div>
        </div>
      </div>

      <div class="config-section">
        <h3 class="subsection-title">Notifications Email</h3>
        <p class="subsection-description">
          Configurez l'envoi automatique d'emails aux clients
        </p>

        <div class="form-group">
          <label class="checkbox-label master-toggle">
            <input
              v-model="settings.email_notifications_enabled"
              type="checkbox"
              class="checkbox-field"
            />
            <span>Activer les notifications email</span>
          </label>
        </div>

        <div v-if="settings.email_notifications_enabled" class="email-settings">
          <div class="form-group">
            <label class="form-label">Nom de l'expéditeur</label>
            <input
              v-model="settings.email_sender_name"
              type="text"
              class="form-input"
              placeholder="Service Devis"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Email expéditeur</label>
            <input
              v-model="settings.email_sender_email"
              type="email"
              class="form-input"
              placeholder="devis@example.com"
            />
            <small class="form-hint">L'adresse email qui apparaîtra comme expéditeur</small>
          </div>

          <div class="form-group">
            <label class="form-label">Email de réponse (optionnel)</label>
            <input
              v-model="settings.email_reply_to"
              type="email"
              class="form-input"
              placeholder="contact@example.com"
            />
            <small class="form-hint">Email utilisé pour les réponses (si différent de l'expéditeur)</small>
          </div>

          <div class="notification-types">
            <h4 class="notification-types-title">Envoyer un email lors de :</h4>

            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="settings.email_on_quote_created"
                  type="checkbox"
                  class="checkbox-field"
                />
                <span>Création du devis</span>
              </label>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="settings.email_on_quote_validated"
                  type="checkbox"
                  class="checkbox-field"
                />
                <span>Validation du devis</span>
              </label>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="settings.email_on_quote_refused"
                  type="checkbox"
                  class="checkbox-field"
                />
                <span>Refus du devis</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-primary" @click="saveSettings" :disabled="saving">
          {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import quoteSettingsService from '../services/quoteSettingsService'

export default {
  name: 'ConfigGeneral',
  data() {
    return {
      settings: {
        quote_number_prefix: 'DEVIS',
        quote_number_separator: '-',
        quote_number_year_format: 'YYYY',
        quote_number_padding: 3,
        quote_number_counter: 0,
        quote_number_reset_yearly: true,
        email_notifications_enabled: false,
        email_on_quote_created: false,
        email_on_quote_validated: false,
        email_on_quote_refused: false,
        email_sender_name: 'Service Devis',
        email_sender_email: '',
        email_reply_to: ''
      },
      loading: true,
      saving: false,
      error: null,
      successMessage: null
    }
  },
  computed: {
    previewQuoteNumber() {
      const { quote_number_prefix, quote_number_separator, quote_number_year_format, quote_number_padding } = this.settings

      let parts = []

      if (quote_number_prefix) {
        parts.push(quote_number_prefix)
      }

      if (quote_number_year_format) {
        const year = new Date().getFullYear()
        const yearStr = quote_number_year_format === 'YY'
          ? String(year).slice(-2)
          : String(year)
        parts.push(yearStr)
      }

      const counter = String(1).padStart(quote_number_padding || 3, '0')
      parts.push(counter)

      return parts.join(quote_number_separator || '-')
    }
  },
  async mounted() {
    await this.loadSettings()
  },
  methods: {
  async loadSettings() {
  console.log("Loading settings...");
  try {
    this.loading = true;
    this.error = null;
    const data = await quoteSettingsService.get();
    console.log('Settings loaded:', data);

    if (data) {
      this.settings = {
        ...this.settings,
        id: data.id_quote_settings,
        validity_hours: data.validity_hours,
        quote_number_prefix: data.quote_number_prefix,
        quote_number_separator: data.quote_number_separator,
        quote_number_year_format: data.quote_number_year_format,
        quote_number_padding: data.quote_number_padding,
        quote_number_counter: data.quote_number_counter,
        quote_number_reset_yearly: data.quote_number_reset_yearly,
        email_notifications_enabled: data.email_notifications_enabled,
        email_on_quote_created: data.email_on_quote_created,
        email_on_quote_validated: data.email_on_quote_validated,
        email_on_quote_refused: data.email_on_quote_refused,
        email_sender_name: data.email_sender_name,
        email_sender_email: data.email_sender_email,
        email_reply_to: data.email_reply_to
      };
      console.log('Normalized settings:', this.settings);
    }
  } catch (err) {
    this.error = 'Erreur lors du chargement: ' + err.message;
    console.error(err);
  } finally {
    this.loading = false;
  }
},

    async saveSettings() {
      try {
        this.saving = true
        this.error = null
        this.successMessage = null

        await quoteSettingsService.update({
          id: this.settings.id,
          validity_hours: this.settings.validity_hours,
          quote_number_prefix: this.settings.quote_number_prefix,
          quote_number_separator: this.settings.quote_number_separator,
          quote_number_year_format: this.settings.quote_number_year_format,
          quote_number_padding: this.settings.quote_number_padding,
          quote_number_counter: this.settings.quote_number_counter,
          quote_number_reset_yearly: this.settings.quote_number_reset_yearly,
          email_notifications_enabled: this.settings.email_notifications_enabled,
          email_on_quote_created: this.settings.email_on_quote_created,
          email_on_quote_validated: this.settings.email_on_quote_validated,
          email_on_quote_refused: this.settings.email_on_quote_refused,
          email_sender_name: this.settings.email_sender_name,
          email_sender_email: this.settings.email_sender_email,
          email_reply_to: this.settings.email_reply_to
        })

        await this.loadSettings()

        this.successMessage = 'Configuration enregistrée avec succès'
        setTimeout(() => {
          this.successMessage = null
        }, 3000)
      } catch (err) {
        this.error = 'Erreur lors de l\'enregistrement: ' + err.message
        console.error(err)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
.config-general {
  max-width: 900px;
}

.loading-message {
  padding: 2rem;
  text-align: center;
  color: #6e6e73;
}

.error-message {
  padding: 1rem;
  background: #fff3f3;
  border: 1px solid #ffd4d4;
  border-radius: 8px;
  color: #d32f2f;
  margin-bottom: 1rem;
}

.success-message {
  padding: 1rem;
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  border-radius: 8px;
  color: #2e7d32;
  margin-top: 1rem;
}

.config-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.config-section {
  padding: 1.5rem;
  background: #fafafa;
  border-radius: 10px;
  border: 1px solid #e5e5e7;
}

.subsection-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 0.5rem;
}

.subsection-description {
  font-size: 0.875rem;
  color: #6e6e73;
  margin-bottom: 1.5rem;
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

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d2d2d7;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1d1d1f;
  background: white;
  transition: border-color 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #0071e3;
}

.form-hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: #6e6e73;
  font-style: italic;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.9375rem;
  color: #1d1d1f;
}

.checkbox-field {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #0071e3;
}

.master-toggle {
  font-weight: 500;
  font-size: 1rem;
}

.preview-box {
  margin-top: 1.5rem;
  padding: 1rem;
  background: white;
  border: 2px solid #0071e3;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.preview-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6e6e73;
}

.preview-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #0071e3;
  font-family: monospace;
}

.email-settings {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e5e7;
}

.notification-types {
  margin-top: 1.5rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
}

.notification-types-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 1rem;
}

.indent {
  margin-left: 2rem;
  padding-left: 1rem;
  border-left: 2px solid #e5e5e7;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
}

.btn-primary {
  padding: 0.75rem 2rem;
  background: #0071e3;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #0077ed;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .config-section {
    padding: 1rem;
  }
}
</style>
