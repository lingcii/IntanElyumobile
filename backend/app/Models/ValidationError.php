<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationError extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'fare_upload_id', 'row_number', 'field', 'error'
    ];
    
    public function upload()
    {
        return $this->belongsTo(FareUpload::class, 'fare_upload_id');
    }
}
