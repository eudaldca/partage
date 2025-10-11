<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperExpense
 */
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'date',
        'description',
        'owner_id',
        'category_id',
        'split_with',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => MoneyCast::class,
        'split_with' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
