<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name, // Uses the accessor
            'email' => $this->email,
            'personal_phone' => $this->personal_phone,
            'full_personal_phone' => $this->full_personal_phone, // Uses the accessor
            'description' => $this->description,
            'address' => $this->address,
            'business_phone' => $this->business_phone,
            'full_business_phone' => $this->full_business_phone, // Uses the accessor
            'home_phone' => $this->home_phone,
            'full_home_phone' => $this->full_home_phone, // Uses the accessor
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            'gender' => $this->gender,
            
            // Relations when loaded
            'status' => $this->when($this->relationLoaded('status'), function () {
                return [
                    'id' => $this->status->id,
                    'name' => $this->status->name,
                    'color' => $this->status->color,
                ];
            }),
            
            'nationality' => $this->when($this->relationLoaded('nationality'), function () {
                return $this->nationality ? [
                    'id' => $this->nationality->id,
                    'name' => $this->nationality->name,
                    'code' => $this->nationality->code,
                ] : null;
            }),
            
            'residence_country' => $this->when($this->relationLoaded('residenceCountry'), function () {
                return $this->residenceCountry ? [
                    'id' => $this->residenceCountry->id,
                    'name' => $this->residenceCountry->name,
                    'code' => $this->residenceCountry->code,
                ] : null;
            }),
            
            'tags' => $this->when($this->relationLoaded('tags'), function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'color' => $tag->color,
                    ];
                });
            }),
            
            'sources' => $this->when($this->relationLoaded('sources'), function () {
                return $this->sources->map(function ($source) {
                    return [
                        'id' => $source->id,
                        'name' => $source->name,
                        'associated_at' => $source->pivot->associated_at,
                    ];
                });
            }),
            
            'created_by' => $this->when($this->relationLoaded('creator'), function () {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ] : null;
            }),
            
            'updated_by' => $this->when($this->relationLoaded('updater'), function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                ] : null;
            }),
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}