<?php

namespace App\Services;

use App\Contracts\NotificationProviderInterface;
use App\Services\Providers\MailgunProvider;
use App\Services\Providers\SmtpProvider;
use App\Services\Providers\TwilioProvider;
use InvalidArgumentException;

class NotificationProviderFactory
{
    public function make(string $providerName): NotificationProviderInterface
    {
        /** @var class-string<NotificationProviderInterface> $driverClass */
        $driverClass = match (strtolower($providerName)) {
            'mailgun' => MailgunProvider::class,
            // 'smtp'    => SmtpProvider::class,
            'twilio'  => TwilioProvider::class,
            default   => throw new InvalidArgumentException("Unsupported provider [{$providerName}]."),
        };

        /** @var NotificationProviderInterface $provider */
        $provider = app($driverClass);

        return $provider;
    }
}
