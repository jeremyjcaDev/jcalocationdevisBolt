<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\UpdateQuoteSettingCommand;
use Jca\JcaLocationdevis\Repository\QuoteSettingRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;

use Doctrine\ORM\EntityManagerInterface;

class UpdateQuoteSettingHandler
{
    private QuoteSettingRepository $quoteSettingRepository;
    private CommandBusInterface $commandBus;

    public function __construct(QuoteSettingRepository $quoteSettingRepository, CommandBusInterface $commandBus)
    {
        $this->quoteSettingRepository = $quoteSettingRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(UpdateQuoteSettingCommand $command): void
    {
        $quoteSetting = $this->quoteSettingRepository->find($command->getIdQuoteSetting());

        if (!$quoteSetting) {
            throw new \InvalidArgumentException('Quote setting not found');
        }

        if ($command->getValidityHours() !== null) {
            $quoteSetting->setValidityHours($command->getValidityHours());
        }

        if ($command->getQuoteNumberPrefix() !== null) {
            $quoteSetting->setQuoteNumberPrefix($command->getQuoteNumberPrefix());
        }

        if ($command->getQuoteNumberYearFormat() !== null) {
            $quoteSetting->setQuoteNumberYearFormat($command->getQuoteNumberYearFormat());
        }

        if ($command->getQuoteNumberSeparator() !== null) {
            $quoteSetting->setQuoteNumberSeparator($command->getQuoteNumberSeparator());
        }

        if ($command->getQuoteNumberPadding() !== null) {
            $quoteSetting->setQuoteNumberPadding($command->getQuoteNumberPadding());
        }

        if ($command->getQuoteNumberCounter() !== null) {
            $quoteSetting->setQuoteNumberCounter($command->getQuoteNumberCounter());
        }

        if ($command->getQuoteNumberResetYearly() !== null) {
            $quoteSetting->setQuoteNumberResetYearly($command->getQuoteNumberResetYearly());
        }

        if ($command->getEmailNotificationsEnabled() !== null) {
            $quoteSetting->setEmailNotificationsEnabled($command->getEmailNotificationsEnabled());
        }

        if ($command->getEmailOnQuoteCreated() !== null) {
            $quoteSetting->setEmailOnQuoteCreated($command->getEmailOnQuoteCreated());
        }

        if ($command->getEmailOnQuoteValidated() !== null) {
            $quoteSetting->setEmailOnQuoteValidated($command->getEmailOnQuoteValidated());
        }

        if ($command->getEmailOnQuoteRefused() !== null) {
            $quoteSetting->setEmailOnQuoteRefused($command->getEmailOnQuoteRefused());
        }

        if ($command->getEmailOnQuoteExpiring() !== null) {
            $quoteSetting->setEmailOnQuoteExpiring($command->getEmailOnQuoteExpiring());
        }

        if ($command->getEmailExpiringDaysBefore() !== null) {
            $quoteSetting->setEmailExpiringDaysBefore($command->getEmailExpiringDaysBefore());
        }

        if ($command->getEmailSenderName() !== null) {
            $quoteSetting->setEmailSenderName($command->getEmailSenderName());
        }

        if ($command->getEmailSenderEmail() !== null) {
            $quoteSetting->setEmailSenderEmail($command->getEmailSenderEmail());
        }

        if ($command->getEmailReplyTo() !== null) {
            $quoteSetting->setEmailReplyTo($command->getEmailReplyTo());
        }

        $quoteSetting->setDateUpd(new \DateTimeImmutable());

        $this->quoteSettingRepository->save($quoteSetting);
    }
}