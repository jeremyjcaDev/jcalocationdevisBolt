const emailService = {
  async sendQuoteEmail(emailType, quote) {
    try {
      // Replace supabase logic with empty data
      const settings = null

      if (!settings.email_notifications_enabled) {
        console.log('Email notifications are disabled')
        return { success: false, reason: 'notifications_disabled' }
      }

      const shouldSend = {
        created: settings.email_on_quote_created,
        validated: settings.email_on_quote_validated,
        refused: settings.email_on_quote_refused,
        expiring: settings.email_on_quote_expiring
      }

      if (!shouldSend[emailType]) {
        console.log(`Email for ${emailType} is disabled`)
        return { success: false, reason: 'email_type_disabled' }
      }

      if (!quote.customer_email) {
        console.log('No customer email provided')
        return { success: false, reason: 'no_email' }
      }

      // Removed API URL construction using Supabase

      // Mocking a successful email send response
      const response = {
        ok: true,
        json: async () => ({ success: true })
      }

      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.error || 'Failed to send email')
      }

      const result = await response.json()
      return { success: true, result }
    } catch (error) {
      console.error('Error sending email:', error)
      return { success: false, error: error.message }
    }
  },

  generateEmailContent(emailType, quote) {
    const totalAmount = quote.items.reduce((sum, item) => sum + parseFloat(item.price || 0), 0)

    const formatPrice = (price) => {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date)
    }

    const templates = {
      created: {
        subject: `Nouveau devis ${quote.quote_number}`,
        html: `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="utf-8">
            <style>
              body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
              .container { max-width: 600px; margin: 0 auto; padding: 20px; }
              .header { background: #0071e3; color: white; padding: 20px; text-align: center; }
              .content { padding: 20px; background: #f5f5f7; }
              .quote-number { font-size: 24px; font-weight: bold; color: #0071e3; margin: 20px 0; }
              .info-row { padding: 10px; border-bottom: 1px solid #ddd; }
              .info-label { font-weight: bold; color: #666; }
              .total { font-size: 20px; font-weight: bold; color: #1d8a47; text-align: right; margin-top: 20px; }
              .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="header">
                <h1>Votre devis est prêt</h1>
              </div>
              <div class="content">
                <p>Bonjour ${quote.customer_name || 'Cher client'},</p>
                <p>Nous vous remercions de votre demande. Voici votre devis :</p>

                <div class="quote-number">Devis N° ${quote.quote_number}</div>

                <div class="info-row">
                  <span class="info-label">Type :</span>
                  ${quote.quote_type === 'rental_only' ? 'Location' : 'Vente'}
                </div>
                <div class="info-row">
                  <span class="info-label">Date de création :</span>
                  ${formatDate(quote.created_at)}
                </div>
                ${quote.valid_until ? `
                <div class="info-row">
                  <span class="info-label">Valide jusqu'au :</span>
                  ${formatDate(quote.valid_until)}
                </div>
                ` : ''}

                <div class="total">
                  Total : ${formatPrice(totalAmount)}
                </div>

                <p style="margin-top: 20px;">Pour toute question, n'hésitez pas à nous contacter.</p>
              </div>
              <div class="footer">
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
              </div>
            </div>
          </body>
          </html>
        `
      },
      validated: {
        subject: `Devis ${quote.quote_number} validé`,
        html: `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="utf-8">
            <style>
              body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
              .container { max-width: 600px; margin: 0 auto; padding: 20px; }
              .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
              .content { padding: 20px; background: #f5f5f7; }
              .quote-number { font-size: 24px; font-weight: bold; color: #2e7d32; margin: 20px 0; }
              .success-badge { background: #e8f5e9; color: #2e7d32; padding: 10px 20px; border-radius: 5px; display: inline-block; margin: 20px 0; }
              .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="header">
                <h1>✓ Devis validé</h1>
              </div>
              <div class="content">
                <p>Bonjour ${quote.customer_name || 'Cher client'},</p>
                <p>Nous avons le plaisir de vous informer que votre devis a été validé :</p>

                <div class="quote-number">Devis N° ${quote.quote_number}</div>

                <div class="success-badge">
                  Votre devis a été accepté
                </div>

                <p>Nous allons prendre contact avec vous prochainement pour finaliser votre commande.</p>
                <p>Merci de votre confiance !</p>
              </div>
              <div class="footer">
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
              </div>
            </div>
          </body>
          </html>
        `
      },
      refused: {
        subject: `Devis ${quote.quote_number} - Mise à jour`,
        html: `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="utf-8">
            <style>
              body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
              .container { max-width: 600px; margin: 0 auto; padding: 20px; }
              .header { background: #6e6e73; color: white; padding: 20px; text-align: center; }
              .content { padding: 20px; background: #f5f5f7; }
              .quote-number { font-size: 24px; font-weight: bold; color: #6e6e73; margin: 20px 0; }
              .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="header">
                <h1>Mise à jour de votre devis</h1>
              </div>
              <div class="content">
                <p>Bonjour ${quote.customer_name || 'Cher client'},</p>
                <p>Nous vous informons que votre devis :</p>

                <div class="quote-number">Devis N° ${quote.quote_number}</div>

                <p>N'a pas pu être accepté dans sa forme actuelle.</p>
                <p>N'hésitez pas à nous contacter pour discuter d'une nouvelle proposition adaptée à vos besoins.</p>
                <p>Nous restons à votre disposition.</p>
              </div>
              <div class="footer">
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
              </div>
            </div>
          </body>
          </html>
        `
      },
      expiring: {
        subject: `Rappel : Votre devis ${quote.quote_number} expire bientôt`,
        html: `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="utf-8">
            <style>
              body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
              .container { max-width: 600px; margin: 0 auto; padding: 20px; }
              .header { background: #ff9800; color: white; padding: 20px; text-align: center; }
              .content { padding: 20px; background: #f5f5f7; }
              .quote-number { font-size: 24px; font-weight: bold; color: #ff9800; margin: 20px 0; }
              .warning-badge { background: #fff3e0; color: #e65100; padding: 10px 20px; border-radius: 5px; display: inline-block; margin: 20px 0; }
              .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="header">
                <h1>⏰ Rappel de validité</h1>
              </div>
              <div class="content">
                <p>Bonjour ${quote.customer_name || 'Cher client'},</p>
                <p>Nous souhaitons vous rappeler que votre devis arrive bientôt à expiration :</p>

                <div class="quote-number">Devis N° ${quote.quote_number}</div>

                <div class="warning-badge">
                  Expire le ${formatDate(quote.valid_until)}
                </div>

                <p>Si vous êtes intéressé par cette offre, nous vous invitons à nous contacter rapidement.</p>
                <p>Nous restons à votre disposition pour toute question.</p>
              </div>
              <div class="footer">
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
              </div>
            </div>
          </body>
          </html>
        `
      }
    }

    return templates[emailType] || templates.created
  }
}

export default emailService
