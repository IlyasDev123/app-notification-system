<?php

use App\Contracts\NotificationProviderInterface;
use App\Services\NotificationProviderFactory;
use App\Services\Provider\MailgunProvider;
use App\Services\Provider\SmtpProvider;
use App\Services\Provider\TwilioProvider;

it('resolves valid NotificationProviderInterface instances from factory', function (string $name, string $expectedClass) {
    $factory = new NotificationProviderFactory();

    $provider = $factory->make($name);

    expect($provider)
        ->toBeInstanceOf(NotificationProviderInterface::class)
        ->toBeInstanceOf($expectedClass);
})->with([
    ['mailgun', MailgunProvider::class],
    ['smtp', SmtpProvider::class],
    ['twilio', TwilioProvider::class],
]);

it('throws exception for unsupported provider', function () {
    $factory = new NotificationProviderFactory();

    $factory->make('unsupported-vendor');
})->throws(InvalidArgumentException::class);
