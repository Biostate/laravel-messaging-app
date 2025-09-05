<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message',
        'status',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
    ];

    public static function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'message' => 'required|string|max:160',
            'status' => 'required|in:draft,sending,completed,failed',
        ];
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
