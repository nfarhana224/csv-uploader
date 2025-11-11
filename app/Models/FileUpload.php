<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileUpload extends Model
{
    protected $fillable = [
        'user_id','filename','filepath','status','total_rows','processed_rows','error_message'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}