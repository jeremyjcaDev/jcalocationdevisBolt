<?php

namespace Jca\JcaLocationdevis\Service;

use Configuration;
use Context;
use Mail;

class QuoteEmailService
{
    private $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public function sendQuoteCreatedEmail($quote, $items)
    {
        $settings = $this->getQuoteSettings();

        if (!$settings || !$settings['email_notifications_enabled'] || !$settings['email_on_quote_created']) {
            return ['success' => false, 'reason' => 'notifications_disabled'];
        }

        if (empty($quote['customer_email'])) {
            return ['success' => false, 'reason' => 'no_email'];
        }

        $emailContent = $this->generateQuoteCreatedEmail($quote, $items, $settings);

        return $this->sendEmail(
            $quote['customer_email'],
            $emailContent['subject'],
            $emailContent['html'],
            $settings
        );
    }

    public function sendQuoteStatusEmail($quote, $items, $status)
    {
        $settings = $this->getQuoteSettings();

        if (!$settings || !$settings['email_notifications_enabled']) {
            return ['success' => false, 'reason' => 'notifications_disabled'];
        }

        $statusMap = [
            'validated' => 'email_on_quote_validated',
            'refused' => 'email_on_quote_refused'
        ];

        if (!isset($statusMap[$status]) || !$settings[$statusMap[$status]]) {
            return ['success' => false, 'reason' => 'email_type_disabled'];
        }

        if (empty($quote['customer_email'])) {
            return ['success' => false, 'reason' => 'no_email'];
        }

        $emailContent = $this->generateQuoteStatusEmail($quote, $items, $status, $settings);

        return $this->sendEmail(
            $quote['customer_email'],
            $emailContent['subject'],
            $emailContent['html'],
            $settings
        );
    }

    private function sendEmail($to, $subject, $html, $settings)
    {
        try {
            $fromName = $settings['email_sender_name'] ?? Configuration::get('PS_SHOP_NAME');
            $fromEmail = $settings['email_sender_email'] ?? Configuration::get('PS_SHOP_EMAIL');
            $replyTo = $settings['email_reply_to'] ?? $fromEmail;

            $templateVars = [
                '{content}' => $html,
                '{shop_name}' => Configuration::get('PS_SHOP_NAME')
            ];

            $modulePath = _PS_MODULE_DIR_ . 'jca_locationdevis/mails/';

            error_log('=== EMAIL DEBUG ===');
            error_log('Module path: ' . $modulePath);
            error_log('Language ID: ' . (int)$this->context->language->id);
            error_log('Language ISO: ' . $this->context->language->iso_code);
            error_log('Template: custom');
            error_log('Files check: ' . (file_exists($modulePath . $this->context->language->iso_code . '/custom.html') ? 'EXISTS' : 'NOT FOUND'));

            $result = Mail::Send(
                (int)$this->context->language->id,
                'custom',
                $subject,
                $templateVars,
                $to,
                null,
                $fromEmail,
                $fromName,
                null,
                null,
                $modulePath,
                false,
                null
            );

            error_log('Mail result: ' . ($result ? 'SUCCESS' : 'FAILED'));

            if ($result) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Mail::Send returned false'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function getQuoteSettings()
    {
        $db = \Db::getInstance();
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'jca_quote_settings` WHERE 1';
        $result = $db->getRow($sql);

        if (!$result) {
            return null;
        }

        return [
            'email_notifications_enabled' => (bool)$result['email_notifications_enabled'],
            'email_on_quote_created' => (bool)$result['email_on_quote_created'],
            'email_on_quote_validated' => (bool)$result['email_on_quote_validated'],
            'email_on_quote_refused' => (bool)$result['email_on_quote_refused'],
            'email_sender_name' => $result['email_sender_name'],
            'email_sender_email' => $result['email_sender_email'],
            'email_reply_to' => $result['email_reply_to']
        ];
    }

    private function generateQuoteCreatedEmail($quote, $items, $settings)
    {
        $subject = 'Nouveau devis #' . $quote['quote_number'];

        $itemsHtml = '';
        $total = 0;
        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $total += $subtotal;
            $itemsHtml .= '<tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">' . htmlspecialchars($item['product_name']) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . $item['quantity'] . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($item['unit_price'], 2, ',', ' ') . ' €</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">' . number_format($subtotal, 2, ',', ' ') . ' €</td>
            </tr>';
        }

        $html = '
        <html>
        <head>
            <meta charset="utf-8">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c3e50;">Nouveau devis créé</h2>
                <p>Bonjour ' . htmlspecialchars($quote['customer_firstname']) . ' ' . htmlspecialchars($quote['customer_lastname']) . ',</p>
                <p>Votre devis <strong>#' . htmlspecialchars($quote['quote_number']) . '</strong> a bien été créé.</p>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin-top: 0;">Détails du devis</h3>
                    <p><strong>Date:</strong> ' . date('d/m/Y', strtotime($quote['quote_date'])) . '</p>
                    <p><strong>Validité:</strong> jusqu\'au ' . date('d/m/Y', strtotime($quote['expiry_date'])) . '</p>
                    <p><strong>Statut:</strong> ' . ucfirst($quote['status']) . '</p>
                </div>

                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <thead>
                        <tr style="background: #2c3e50; color: white;">
                            <th style="padding: 10px; text-align: left;">Produit</th>
                            <th style="padding: 10px; text-align: center;">Quantité</th>
                            <th style="padding: 10px; text-align: right;">Prix unitaire</th>
                            <th style="padding: 10px; text-align: right;">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td colspan="3" style="padding: 10px; text-align: right;">Total HT:</td>
                            <td style="padding: 10px; text-align: right;">' . number_format($total, 2, ',', ' ') . ' €</td>
                        </tr>
                    </tfoot>
                </table>

                <p>Nous reviendrons vers vous prochainement pour finaliser votre devis.</p>
                <p>Cordialement,<br>' . htmlspecialchars($settings['email_sender_name']) . '</p>
            </div>
        </body>
        </html>';

        return ['subject' => $subject, 'html' => $html];
    }

    private function generateQuoteStatusEmail($quote, $items, $status, $settings)
    {
        $statusLabels = [
            'validated' => 'validé',
            'refused' => 'refusé'
        ];

        $subject = 'Devis #' . $quote['quote_number'] . ' ' . $statusLabels[$status];

        $statusMessage = $status === 'validated'
            ? '<p style="color: #27ae60; font-weight: bold;">Votre devis a été validé.</p>'
            : '<p style="color: #e74c3c; font-weight: bold;">Votre devis a été refusé.</p>';

        $html = '
        <html>
        <head>
            <meta charset="utf-8">
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c3e50;">Mise à jour de votre devis</h2>
                <p>Bonjour ' . htmlspecialchars($quote['customer_firstname']) . ' ' . htmlspecialchars($quote['customer_lastname']) . ',</p>

                ' . $statusMessage . '

                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin-top: 0;">Référence du devis</h3>
                    <p><strong>Numéro:</strong> #' . htmlspecialchars($quote['quote_number']) . '</p>
                    <p><strong>Date:</strong> ' . date('d/m/Y', strtotime($quote['quote_date'])) . '</p>
                </div>

                <p>Pour toute question, n\'hésitez pas à nous contacter.</p>
                <p>Cordialement,<br>' . htmlspecialchars($settings['email_sender_name']) . '</p>
            </div>
        </body>
        </html>';

        return ['subject' => $subject, 'html' => $html];
    }
}
