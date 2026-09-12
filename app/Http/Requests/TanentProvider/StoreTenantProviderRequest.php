<?php

namespace App\Http\Requests\TanentProvider;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel'       => ['required', 'string', 'in:email,sms,push'],
            'provider_name' => ['required', 'string', 'in:sendgrid,mailgun,twilio,firebase'],
            'credentials'   => ['required', 'array'],
            'priority'      => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }
}
