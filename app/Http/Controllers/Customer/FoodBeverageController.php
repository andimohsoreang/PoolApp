<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\FoodBeverage;
use App\Models\FoodBeverageRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodBeverageController extends Controller
{
    /**
     * Display a listing of food and beverage items
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search', '');

        $query = FoodBeverage::where('is_available', true);

        // Filter by category
        if ($category !== 'all') {
            $query->where('category', $category);
        }

        // Search by name or description
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get featured items for carousel
        $featuredItems = FoodBeverage::where('is_featured', true)
            ->where('is_available', true)
            ->limit(5)
            ->get();

        // Get all categories for filter
        $categories = FoodBeverage::select('category')
            ->distinct()
            ->pluck('category');

        // Get results with images
        $items = $query->with('images')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(12);

        return view('customer.food-beverages.index', compact('items', 'featuredItems', 'categories', 'category', 'search'));
    }

    /**
     * Display a specific food/beverage item
     */
    public function show(FoodBeverage $foodBeverage)
    {
        if (!$foodBeverage->is_available) {
            return redirect()->route('customer.food-beverages.index')
                ->with('error', 'This item is currently not available.');
        }

        $foodBeverage->load(['images', 'ratings' => function($query) {
            $query->where('is_approved', true)->with('user');
        }]);

        // Check if current user has already rated this item
        $userRating = null;
        if (Auth::check()) {
            $userRating = FoodBeverageRating::where('food_beverage_id', $foodBeverage->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        // Get similar items (same category)
        $similarItems = FoodBeverage::where('category', $foodBeverage->category)
            ->where('id', '!=', $foodBeverage->id)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return view('customer.food-beverages.show', compact('foodBeverage', 'userRating', 'similarItems'));
    }

    /**
     * Submit a rating for a food/beverage item
     */
    public function submitRating(Request $request, FoodBeverage $foodBeverage)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to rate items.');
        }

        // Check if user has already rated this item
        $existingRating = FoodBeverageRating::where('food_beverage_id', $foodBeverage->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingRating) {
            // Update existing rating
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            $message = 'Your rating has been updated.';
        } else {
            // Create new rating
            FoodBeverageRating::create([
                'food_beverage_id' => $foodBeverage->id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'review' => $request->review,
                'is_approved' => true, // Auto-approve for now, could be changed in the future
            ]);

            $message = 'Thank you for your rating!';
        }

        // Update average rating on food/beverage
        $foodBeverage->updateAverageRating();

        return redirect()->route('customer.food-beverages.show', $foodBeverage)
            ->with('success', $message);
    }

    /**
     * Delete customer's own rating
     */
    public function deleteRating(FoodBeverage $foodBeverage)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to manage your ratings.');
        }

        // Find user's rating for this item
        $rating = FoodBeverageRating::where('food_beverage_id', $foodBeverage->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$rating) {
            return redirect()->route('customer.food-beverages.show', $foodBeverage)
                ->with('error', 'You have not rated this item yet.');
        }

        // Delete the rating
        $rating->delete();

        // Update average rating on food/beverage
        $foodBeverage->updateAverageRating();

        return redirect()->route('customer.food-beverages.show', $foodBeverage)
            ->with('success', 'Your rating has been removed.');
    }
}
