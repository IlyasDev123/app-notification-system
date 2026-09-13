<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'uuid'],
            'channel'   => ['required', 'string', 'in:email,sms,push'],
            'recipient' => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string', 'max:5000'],
            'options'   => ['nullable', 'array'],
        ];
    }
}
