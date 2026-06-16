<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupBookingStatusRequest extends FormRequest
{
    protected $errorBag = 'bookingLookup';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lookup_booking_number' => ['required', 'string', 'max:32'],
            'lookup_whatsapp' => ['required', 'string', 'max:32', 'regex:/^[0-9+()\s-]{8,32}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'lookup_booking_number' => 'nomor booking',
            'lookup_whatsapp' => 'nomor WhatsApp',
        ];
    }
}
