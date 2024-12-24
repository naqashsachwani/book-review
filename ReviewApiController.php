<?php
namespace App\Http\Controllers\Api;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ReviewApiController extends Controller
{
    /**
     * Get a list of reviews with optional keyword search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $reviews = Review::with('book', 'user')->orderBy('created_at', 'DESC');

        if (!empty($request->keyword)) {
            $reviews = $reviews->where('review', 'like', '%' . $request->keyword . '%');
        }

        $reviews = $reviews->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get a single review by its ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $review = Review::with('book', 'user')->find($id);

        if ($review) {
            return response()->json([
                'status' => true,
                'data' => $review
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Review not found'
        ], 404);
    }

    /**
     * Update a review by its ID.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'review' => 'required|string',
            'status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review->review = $request->review;
        $review->status = $request->status;
        $review->save();

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Delete a review by its ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
