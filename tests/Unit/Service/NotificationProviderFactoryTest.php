<?php

use App\Contracts\NotificationProviderInterface;
use App\Services\NotificationProviderFactory;
use App\Services\Providers\MailgunProvider;
use App\Services\Providers\TwilioProvider;

it('resolves valid NotificationProviderInterface instances from factory', function (string $name, string $expectedClass) {
    $factory = new NotificationProviderFactory();

    $provider = $factory->make($name);

    expect($provider)
        ->toBeInstanceOf(NotificationProviderInterface::class)
        ->toBeInstanceOf($expectedClass);
})->with([
    ['mailgun', MailgunProvider::class],
    ['twilio', TwilioProvider::class],
]);

it('throws exception for unsupported provider', function () {
    $factory = new NotificationProviderFactory();

    $factory->make('unsupported-vendor');
})->throws(InvalidArgumentException::class);
