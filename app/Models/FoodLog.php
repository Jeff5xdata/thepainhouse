<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'food_item_id',
        'meal_type',
        'quantity',
        'consumed_date',
        'consumed_time',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'consumed_date' => 'date',
        'consumed_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }

    /**
     * Get nutrition values for this log entry
     */
    public function getNutritionValues(): array
    {
        return $this->foodItem->getNutritionForQuantity($this->quantity);
    }

    /**
     * Get meal type options
     */
    public static function getMealTypeOptions(): array
    {
        return [
            'breakfast' => 'Breakfast',
            'lunch' => 'Lunch',
            'dinner' => 'Dinner',
            'snack' => 'Snack',
        ];
    }
}
