<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'id',
        'name',
        'category_id',
        'description',
        'manufacturer_id',
        'model_code',
        'price',
        'varanty',
        'availability'
    ];
}
