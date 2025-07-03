<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use Illuminate\Http\Request;

class FoodItemController extends Controller
{
    /**
     * Display the nutrition facts for a specific food item
     */
    public function show(FoodItem $foodItem)
    {
        return view('food-items.show', compact('foodItem'));
    }

    /**
     * Display a list of all food items in the database
     */
    public function index(Request $request)
    {
        $query = FoodItem::query();
        
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        
        $foodItems = $query->orderBy('name')->paginate(20);
        return view('food-items.index', compact('foodItems'));
    }
}
