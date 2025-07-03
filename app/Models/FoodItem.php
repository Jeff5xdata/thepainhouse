<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'barcode',
        'serving_size',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'fiber',
        'sugar',
        'sodium',
        'cholesterol',
        'image_url',
        'fatsecret_id',
    ];

    protected $casts = [
        'calories' => 'decimal:2',
        'protein' => 'decimal:2',
        'carbohydrates' => 'decimal:2',
        'fat' => 'decimal:2',
        'fiber' => 'decimal:2',
        'sugar' => 'decimal:2',
        'sodium' => 'decimal:2',
        'cholesterol' => 'decimal:2',
    ];

    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }

    /**
     * Get nutrition values for a given quantity
     */
    public function getNutritionForQuantity(float $quantity = 1.0): array
    {
        return [
            'calories' => $this->calories * $quantity,
            'protein' => $this->protein * $quantity,
            'carbohydrates' => $this->carbohydrates * $quantity,
            'fat' => $this->fat * $quantity,
            'fiber' => $this->fiber * $quantity,
            'sugar' => $this->sugar * $quantity,
            'sodium' => $this->sodium * $quantity,
            'cholesterol' => $this->cholesterol * $quantity,
        ];
    }

    /**
     * Get display name with brand
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->brand ? "{$this->brand} - {$this->name}" : $this->name;
    }
}
