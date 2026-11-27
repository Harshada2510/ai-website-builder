<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'business_name',
        'business_slug',
        'business_category',
        'city',
        'address',
        'description',
        'phone',
        'email',
        'website_url',
        'instagram_url',
        'facebook_url',
        'linkedin_url',
        'google_business_url',
        'status',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(BusinessFile::class);
    }

    public function sourceContents(): HasMany
    {
        return $this->hasMany(BusinessSourceContent::class);
    }

    public function generatedWebsites(): HasMany
    {
        return $this->hasMany(GeneratedWebsite::class);
    }
}
