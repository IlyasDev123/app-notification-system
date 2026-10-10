<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'channel'   => ['required', 'string', 'in:email,sms,push'],
            'recipient' => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string', 'max:5000'],
            'options'   => ['nullable', 'array'],
        ];
    }
}
