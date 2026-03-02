<?php

namespace Jca\JcaLocationdevis\QueryHandler;

use Jca\JcaLocationdevis\Query\GetQuoteSettingQuery;
use Jca\JcaLocationdevis\Repository\QuoteSettingRepository;

class GetQuoteSettingHandler
{
    private QuoteSettingRepository $quoteSettingRepository;

    public function __construct(QuoteSettingRepository $quoteSettingRepository)
    {
        $this->quoteSettingRepository = $quoteSettingRepository;
    }

    public function handle(GetQuoteSettingQuery $query): ?array
    {
        $settings = $this->quoteSettingRepository->findLatestSettings();

        if (!$settings) {
            return null;
        }

        return [
            'id_quote_settings' => $settings->getId(),
            'validity_hours' => $settings->getValidityHours(),
            'quote_number_prefix' => $settings->getQuoteNumberPrefix(),
            'quote_number_year_format' => $settings->getQuoteNumberYearFormat(),
            'quote_number_separator' => $settings->getQuoteNumberSeparator(),
            'quote_number_padding' => $settings->getQuoteNumberPadding(),
            'quote_number_counter' => $settings->getQuoteNumberCounter(),
            'quote_number_reset_yearly' => $settings->getQuoteNumberResetYearly(),
            'quote_number_last_year' => $settings->getQuoteNumberLastYear(),
            'email_notifications_enabled' => $settings->getEmailNotificationsEnabled(),
            'email_on_quote_created' => $settings->getEmailOnQuoteCreated(),
            'email_on_quote_validated' => $settings->getEmailOnQuoteValidated(),
            'email_on_quote_refused' => $settings->getEmailOnQuoteRefused(),
            'email_on_quote_expiring' => $settings->getEmailOnQuoteExpiring(),
            'email_expiring_days_before' => $settings->getEmailExpiringDaysBefore(),
            'email_sender_name' => $settings->getEmailSenderName(),
            'email_sender_email' => $settings->getEmailSenderEmail(),
            'email_reply_to' => $settings->getEmailReplyTo(),
        ];
    }
}
