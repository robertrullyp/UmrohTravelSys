<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
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
            'umrah_package_id' => ['required', 'integer', 'exists:umrah_packages,id'],
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:32', 'regex:/^[0-9+()\\s-]{8,32}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'pilgrims_count' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['umrah_package_id', 'schedule_id', 'pilgrims_count'])) {
                    return;
                }

                $schedule = Schedule::query()
                    ->with('umrahPackage')
                    ->find($this->integer('schedule_id'));

                if (
                    $schedule === null
                    || ! $schedule->is_active
                    || ! $schedule->umrahPackage?->is_active
                    || $schedule->departure_date->lt(today())
                    || $schedule->umrah_package_id !== $this->integer('umrah_package_id')
                ) {
                    $validator->errors()->add('schedule_id', 'Jadwal tidak tersedia untuk paket yang dipilih.');

                    return;
                }

                if ($schedule->quota < $this->integer('pilgrims_count')) {
                    $validator->errors()->add(
                        'pilgrims_count',
                        "Jumlah jamaah melebihi sisa kuota ({$schedule->quota}).",
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'umrah_package_id' => 'paket umrah',
            'schedule_id' => 'jadwal',
            'customer_name' => 'nama pemesan',
            'whatsapp' => 'nomor WhatsApp',
            'pilgrims_count' => 'jumlah jamaah',
            'notes' => 'catatan',
        ];
    }
}
