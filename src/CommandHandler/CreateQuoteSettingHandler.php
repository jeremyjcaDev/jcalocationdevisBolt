<?php

namespace Jca\JcaLocationdevis\CommandHandler;

use Jca\JcaLocationdevis\Command\CreateQuoteSettingCommand;
use Jca\JcaLocationdevis\Entity\QuoteSetting;
use Doctrine\ORM\EntityManagerInterface;

class CreateQuoteSettingHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(CreateQuoteSettingCommand $command): void
    {
        $quoteSetting = new QuoteSetting();
        $quoteSetting->setValidityHours($command->getValidityHours());
        $quoteSetting->setQuoteNumberPrefix($command->getQuoteNumberPrefix());
        $quoteSetting->setQuoteNumberYearFormat($command->getQuoteNumberYearFormat());
        $quoteSetting->setQuoteNumberSeparator($command->getQuoteNumberSeparator());
        $quoteSetting->setQuoteNumberPadding($command->getQuoteNumberPadding());
        $quoteSetting->setQuoteNumberCounter(1);
        $quoteSetting->setQuoteNumberResetYearly(false);
        $quoteSetting->setQuoteNumberLastYear((int)date('Y'));
        $quoteSetting->setEmailNotificationsEnabled(false);
        $quoteSetting->setEmailOnQuoteCreated(false);
        $quoteSetting->setEmailOnQuoteValidated(false);
        $quoteSetting->setEmailOnQuoteRefused(false);
        $quoteSetting->setEmailOnQuoteExpiring(false);
        $quoteSetting->setEmailExpiringDaysBefore(7);
        $quoteSetting->setEmailSenderName('');
        $quoteSetting->setEmailSenderEmail('');
        $quoteSetting->setEmailReplyTo('');
        $quoteSetting->setDateAdd(new \DateTimeImmutable());
        $quoteSetting->setDateUpd(new \DateTimeImmutable());

        $this->entityManager->persist($quoteSetting);
        $this->entityManager->flush();
    }
}
