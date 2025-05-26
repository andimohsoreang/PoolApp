<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodBeverage;
use App\Models\FoodBeverageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FoodBeverageController extends Controller
{
    /**
     * Debug helper function
     */
    private function debug($message, $data = null, $level = 'info')
    {
        $debugInfo = [
            'timestamp' => now()->toDateTimeString(),
            'message' => $message,
            'data' => $data,
            'memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
        ];

        switch ($level) {
            case 'error':
                Log::error('FoodBeverage Debug:', $debugInfo);
                break;
            case 'warning':
                Log::warning('FoodBeverage Debug:', $debugInfo);
                break;
            default:
                Log::info('FoodBeverage Debug:', $debugInfo);
        }

        if (config('app.debug')) {
            return $debugInfo;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $this->debug('Starting index method');

            $foodBeverages = FoodBeverage::orderBy('category')->orderBy('name')->paginate(10);

            $this->debug('Retrieved food beverages', [
                'count' => $foodBeverages->count(),
                'total' => $foodBeverages->total(),
                'current_page' => $foodBeverages->currentPage()
            ]);

            return view('admin.food-beverages.index', compact('foodBeverages'));
        } catch (\Exception $e) {
            $this->debug('Error in index method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while retrieving the food beverages.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->debug('Accessing create form');
        return view('admin.food-beverages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->debug('Starting store method', [
                'request_data' => $request->except(['thumbnail', 'images'])
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'category' => 'required|in:food,beverage,snack,other',
                'thumbnail' => 'nullable|image|max:2048',
                'is_available' => 'nullable|in:1',
                'is_featured' => 'nullable|in:1',
                'images.*' => 'nullable|image|max:2048',
            ]);

            $this->debug('Validation passed');

            // Handle thumbnail upload
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $this->debug('Processing thumbnail upload');
                $thumbnailPath = $request->file('thumbnail')->store('food-beverages', 'public');
                $this->debug('Thumbnail uploaded', ['path' => $thumbnailPath]);
            }

            // Create the food/beverage item
            $foodBeverage = FoodBeverage::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
                'thumbnail' => $thumbnailPath,
                'is_available' => $request->has('is_available'),
                'is_featured' => $request->has('is_featured'),
            ]);

            $this->debug('Food beverage created', ['id' => $foodBeverage->id]);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $this->debug('Processing multiple image uploads', [
                    'count' => count($request->file('images'))
                ]);

                $isPrimarySet = false;
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $image->store('food-beverages/gallery', 'public');
                    $isPrimary = (!$isPrimarySet && !$thumbnailPath);

                    if ($isPrimary) {
                        $isPrimarySet = true;
                    }

                    FoodBeverageImage::create([
                        'food_beverage_id' => $foodBeverage->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->name . ' ' . ($index + 1),
                        'order' => $index,
                        'is_primary' => $isPrimary,
                    ]);

                    $this->debug('Image uploaded', [
                        'index' => $index,
                        'path' => $imagePath,
                        'is_primary' => $isPrimary
                    ]);
                }
            }

            DB::commit();
            $this->debug('Store operation completed successfully');

            return redirect()->route('admin.food-beverages.index')
                ->with('success', 'Food/Beverage item created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->debug('Error in store method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            // Clean up any uploaded files if there was an error
            if (isset($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the food/beverage item.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FoodBeverage $foodBeverage)
    {
        try {
            $this->debug('Accessing show method', ['id' => $foodBeverage->id]);

            $foodBeverage->load(['images', 'ratings.user']);

            $this->debug('Loaded relationships', [
                'images_count' => $foodBeverage->images->count(),
                'ratings_count' => $foodBeverage->ratings->count()
            ]);

            return view('admin.food-beverages.show', compact('foodBeverage'));
        } catch (\Exception $e) {
            $this->debug('Error in show method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while retrieving the food/beverage details.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FoodBeverage $foodBeverage)
    {
        try {
            $this->debug('Accessing edit form', ['id' => $foodBeverage->id]);

            $foodBeverage->load('images');

            $this->debug('Loaded images', ['count' => $foodBeverage->images->count()]);

            return view('admin.food-beverages.edit', compact('foodBeverage'));
        } catch (\Exception $e) {
            $this->debug('Error in edit method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FoodBeverage $foodBeverage)
    {
        DB::beginTransaction();
        try {
            $this->debug('Starting update method', [
                'id' => $foodBeverage->id,
                'request_data' => $request->except(['thumbnail', 'images'])
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'category' => 'required|in:food,beverage,snack,other',
                'thumbnail' => 'nullable|image|max:2048',
                'is_available' => 'nullable|in:1',
                'is_featured' => 'nullable|in:1',
                'images.*' => 'nullable|image|max:2048',
            ]);

            $this->debug('Validation passed');

            // Handle thumbnail update
            $thumbnailPath = $foodBeverage->thumbnail;
            if ($request->hasFile('thumbnail')) {
                $this->debug('Processing new thumbnail upload');

                if ($foodBeverage->thumbnail) {
                    Storage::disk('public')->delete($foodBeverage->thumbnail);
                    $this->debug('Deleted old thumbnail', ['path' => $foodBeverage->thumbnail]);
                }

                $thumbnailPath = $request->file('thumbnail')->store('food-beverages', 'public');
                $this->debug('New thumbnail uploaded', ['path' => $thumbnailPath]);
            }

            // Update the food/beverage item
            $foodBeverage->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
                'thumbnail' => $thumbnailPath,
                'is_available' => $request->has('is_available'),
                'is_featured' => $request->has('is_featured'),
            ]);

            $this->debug('Food beverage updated');

            // Handle additional image uploads
            if ($request->hasFile('images')) {
                $this->debug('Processing additional image uploads', [
                    'count' => count($request->file('images'))
                ]);

                $lastOrder = $foodBeverage->images()->max('order') ?? 0;

                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $image->store('food-beverages/gallery', 'public');

                    FoodBeverageImage::create([
                        'food_beverage_id' => $foodBeverage->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->name . ' ' . ($index + $lastOrder + 1),
                        'order' => $index + $lastOrder + 1,
                        'is_primary' => false,
                    ]);

                    $this->debug('Additional image uploaded', [
                        'index' => $index,
                        'path' => $imagePath
                    ]);
                }
            }

            DB::commit();
            $this->debug('Update operation completed successfully');

            return redirect()->route('admin.food-beverages.index')
                ->with('success', 'Food/Beverage item updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->debug('Error in update method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the food/beverage item.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FoodBeverage $foodBeverage)
    {
        DB::beginTransaction();
        try {
            $this->debug('Starting destroy method', ['id' => $foodBeverage->id]);

            // Delete all related images from storage
            if ($foodBeverage->thumbnail) {
                Storage::disk('public')->delete($foodBeverage->thumbnail);
                $this->debug('Deleted thumbnail', ['path' => $foodBeverage->thumbnail]);
            }

            foreach ($foodBeverage->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $this->debug('Deleted image', ['path' => $image->image_path]);
            }

            // Delete the record
            $foodBeverage->delete();
            $this->debug('Food beverage record deleted');

            DB::commit();
            $this->debug('Destroy operation completed successfully');

            return redirect()->route('admin.food-beverages.index')
                ->with('success', 'Food/Beverage item deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->debug('Error in destroy method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while deleting the food/beverage item.');
        }
    }

    /**
     * Delete a specific image
     */
    public function deleteImage(FoodBeverageImage $image)
    {
        try {
            $this->debug('Starting deleteImage method', [
                'image_id' => $image->id,
                'food_beverage_id' => $image->food_beverage_id
            ]);

            $foodBeverageId = $image->food_beverage_id;

            // Delete the image file
            Storage::disk('public')->delete($image->image_path);
            $this->debug('Deleted image file', ['path' => $image->image_path]);

            // Delete the record
            $image->delete();
            $this->debug('Image record deleted');

            return redirect()->route('admin.food-beverages.edit', $foodBeverageId)
                ->with('success', 'Image deleted successfully');

        } catch (\Exception $e) {
            $this->debug('Error in deleteImage method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while deleting the image.');
        }
    }

    /**
     * Set an image as primary
     */
    public function setPrimaryImage(FoodBeverageImage $image)
    {
        try {
            $this->debug('Starting setPrimaryImage method', [
                'image_id' => $image->id,
                'food_beverage_id' => $image->food_beverage_id
            ]);

            $foodBeverageId = $image->food_beverage_id;

            // Update all other images for this food/beverage to not be primary
            FoodBeverageImage::where('food_beverage_id', $foodBeverageId)
                ->update(['is_primary' => false]);
            $this->debug('Reset all images to non-primary');

            // Set this image as primary
            $image->update(['is_primary' => true]);
            $this->debug('Set image as primary');

            return redirect()->route('admin.food-beverages.edit', $foodBeverageId)
                ->with('success', 'Primary image updated');

        } catch (\Exception $e) {
            $this->debug('Error in setPrimaryImage method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while updating the primary image.');
        }
    }

    /**
     * Manage ratings for a food/beverage item
     */
    public function manageRatings(FoodBeverage $foodBeverage)
    {
        try {
            $this->debug('Accessing manageRatings method', ['id' => $foodBeverage->id]);

            $foodBeverage->load('ratings.user');

            $this->debug('Loaded ratings', ['count' => $foodBeverage->ratings->count()]);

            return view('admin.food-beverages.ratings', compact('foodBeverage'));
        } catch (\Exception $e) {
            $this->debug('Error in manageRatings method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while loading the ratings.');
        }
    }

    /**
     * Toggle a rating's approval status
     */
    public function toggleRatingApproval(Request $request, $ratingId)
    {
        try {
            $this->debug('Starting toggleRatingApproval method', ['rating_id' => $ratingId]);

            $rating = \App\Models\FoodBeverageRating::findOrFail($ratingId);
            $rating->update(['is_approved' => !$rating->is_approved]);

            $this->debug('Rating approval status toggled', [
                'rating_id' => $ratingId,
                'new_status' => $rating->is_approved
            ]);

            return redirect()->back()->with('success', 'Rating status updated');
        } catch (\Exception $e) {
            $this->debug('Error in toggleRatingApproval method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while updating the rating status.');
        }
    }

    /**
     * Delete a rating
     */
    public function deleteRating($ratingId)
    {
        try {
            $this->debug('Attempting to delete rating', ['rating_id' => $ratingId]);

            $rating = DB::table('food_beverage_ratings')->where('id', $ratingId)->first();

            if (!$rating) {
                return redirect()->back()->with('error', 'Rating not found.');
            }

            DB::table('food_beverage_ratings')->where('id', $ratingId)->delete();

            $this->debug('Rating deleted successfully');

            return redirect()->back()->with('success', 'Rating deleted successfully.');
        } catch (\Exception $e) {
            $this->debug('Error deleting rating', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while deleting the rating.');
        }
    }

    /**
     * Display food & beverage orders
     */
    public function orders()
    {
        try {
            $this->debug('Accessing orders page');

            // Get F&B orders from the database
            // This will need to be adjusted based on your actual database structure
            $orders = DB::table('food_beverage_orders')
                ->join('users', 'users.id', '=', 'food_beverage_orders.customer_id')
                ->select('food_beverage_orders.*', 'users.name as customer_name')
                ->orderBy('food_beverage_orders.created_at', 'desc')
                ->paginate(15);

            $this->debug('Retrieved F&B orders', [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage()
            ]);

            return view('admin.food-beverages.orders', compact('orders'));
        } catch (\Exception $e) {
            $this->debug('Error in orders method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->route('admin.dashboard')
                ->with('error', 'An error occurred while retrieving the food & beverage orders.');
        }
    }

    /**
     * Display specific order details
     */
    public function orderDetails($id)
    {
        try {
            $this->debug('Accessing order details', ['order_id' => $id]);

            // Get order details from the database
            $order = DB::table('food_beverage_orders')
                ->join('users', 'users.id', '=', 'food_beverage_orders.customer_id')
                ->select('food_beverage_orders.*', 'users.name as customer_name', 'users.phone')
                ->where('food_beverage_orders.id', $id)
                ->first();

            if (!$order) {
                return '<div class="alert alert-danger">Order not found.</div>';
            }

            // Decode order items
            $orderItems = json_decode($order->order_details, true);

            return view('admin.food-beverages.order-details', compact('order', 'orderItems'))->render();
        } catch (\Exception $e) {
            $this->debug('Error in orderDetails method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return '<div class="alert alert-danger">Error loading order details: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Process a pending order
     */
    public function processOrder($id)
    {
        try {
            $this->debug('Processing order', ['order_id' => $id]);

            // Update order status in the database
            $updated = DB::table('food_beverage_orders')
                ->where('id', $id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'processing',
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return redirect()->back()->with('error', 'Order could not be processed. It may have already been processed or does not exist.');
            }

            $this->debug('Order processed successfully');

            return redirect()->back()->with('success', 'Order has been marked as processing.');
        } catch (\Exception $e) {
            $this->debug('Error processing order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while processing the order.');
        }
    }

    /**
     * Complete a processing order
     */
    public function completeOrder($id)
    {
        try {
            $this->debug('Completing order', ['order_id' => $id]);

            // Update order status in the database
            $updated = DB::table('food_beverage_orders')
                ->where('id', $id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return redirect()->back()->with('error', 'Order could not be completed. It may not be in processing status or does not exist.');
            }

            $this->debug('Order completed successfully');

            return redirect()->back()->with('success', 'Order has been marked as completed.');
        } catch (\Exception $e) {
            $this->debug('Error completing order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while completing the order.');
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder($id)
    {
        try {
            $this->debug('Cancelling order', ['order_id' => $id]);

            // Update order status in the database
            $updated = DB::table('food_beverage_orders')
                ->where('id', $id)
                ->whereIn('status', ['pending', 'processing'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return redirect()->back()->with('error', 'Order could not be cancelled. It may already be completed or does not exist.');
            }

            $this->debug('Order cancelled successfully');

            return redirect()->back()->with('success', 'Order has been cancelled.');
        } catch (\Exception $e) {
            $this->debug('Error cancelling order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return redirect()->back()->with('error', 'An error occurred while cancelling the order.');
        }
    }
}
