<?php
// app/Http/Requests/LeadStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You'll implement proper authorization as needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'personal_phone_country_id' => 'nullable|exists:countries,id',
            'personal_phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'business_phone_country_id' => 'nullable|exists:countries,id',
            'business_phone' => 'nullable|string|max:20',
            'home_phone_country_id' => 'nullable|exists:countries,id',
            'home_phone' => 'nullable|string|max:20',
            'nationality_id' => 'nullable|exists:countries,id',
            'residence_country_id' => 'nullable|exists:countries,id',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'status_id' => 'required|exists:lead_statuses,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'sources' => 'nullable|array',
            'sources.*' => 'exists:lead_sources,id',
        ];
    }
}