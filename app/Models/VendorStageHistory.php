<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorStageHistory extends Model
{
    use HasFactory;

    protected $table = 'vendor_stage_history';

    protected $fillable = [
        'vendor_id',
        'stage',
        'action',
        'comment',
        'actor_id',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}