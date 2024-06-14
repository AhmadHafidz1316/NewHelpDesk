<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'priority_id' => 'sometimes|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
