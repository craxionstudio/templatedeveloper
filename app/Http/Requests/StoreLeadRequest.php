<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\GuardsAgainstSpam;
use App\Models\Cluster;
use App\Models\HouseType;
use App\Models\Lead;
use App\Support\Phone;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    use GuardsAgainstSpam;

    public const POSITIONS = ['sidebar', 'inline', 'modal', 'kontak', 'sticky'];

    /**
     * Maksimal lead tersimpan per nomor WA dalam 24 jam terakhir.
     */
    public const MAX_PER_NUMBER_PER_DAY = 3;

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
            ...$this->spamRules(),
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'whatsapp' => ['required', 'string', 'max:25', function (string $attribute, mixed $value, Closure $fail): void {
                if (Phone::normalize(is_string($value) ? $value : null) === null) {
                    $fail('Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.');
                }
            }],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'cluster_id' => ['nullable', 'integer', Rule::exists(Cluster::class, 'id')->where('is_published', true)->whereNull('deleted_at')],
            'house_type_id' => ['nullable', 'integer', Rule::exists(HouseType::class, 'id')->where('cluster_id', $this->integer('cluster_id'))->where('is_published', true)],
            'payment_plan' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'source_page' => ['nullable', 'string', 'max:500'],
            'source_position' => ['nullable', 'string', Rule::in(self::POSITIONS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama terlalu pendek.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'cluster_id.exists' => 'Pilihan cluster tidak tersedia.',
            'house_type_id.exists' => 'Pilihan tipe tidak tersedia.',
            'message.max' => 'Pesan maksimal 2.000 karakter.',
            'consent.accepted' => 'Centang persetujuan Kebijakan Privasi untuk melanjutkan.',
        ];
    }

    /**
     * Dicek setelah validasi lolos (lihat GuardsAgainstSpam::after), jadi salah isi form
     * tidak ikut terhitung. Hanya lead yang benar-benar tersimpan yang dihitung.
     */
    public function exceedsNumberLimit(): bool
    {
        return Lead::query()
            ->where('whatsapp', $this->normalizedWhatsapp())
            ->where('created_at', '>=', now()->subDay())
            ->count() >= self::MAX_PER_NUMBER_PER_DAY;
    }

    public function normalizedWhatsapp(): string
    {
        return (string) Phone::normalize($this->string('whatsapp')->toString());
    }
}
