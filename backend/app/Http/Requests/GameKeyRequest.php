<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameKeyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'game_id' => 'required|exists:games,id',
            'state' => 'required|string|in:disponible,vendida,reservada,inactiva',
            'region' => 'required|string',
            'key' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'delivery_time' => 'required|string',
            'seller_id' => 'required|exists:users,id',
            'platform' => 'required|string',
            'sale_id' => 'nullable|exists:sales,id',
        ];
    }
}
