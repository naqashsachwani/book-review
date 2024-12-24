<?php
namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeApiController extends Controller
{
    /**
     * Get a list of books with optional keyword search and reviews count and rating.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $books = Book::withCount('reviews')->withSum('reviews', 'rating')->orderBy('created_at', 'DESC');

        if (!empty($request->keyword)) {
            $books->where('title', 'like', '%' . $request->keyword . '%');
        }

        $books = $books->where('status', 1)->paginate(8);

        return response()->json([
            'status' => true,
            'data' => $books
        ]);
    }

    /**
     * Get the details of a book along with its reviews and related books.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($id)
    {
        $book = Book::with(['reviews.user', 'reviews' => function ($query) {
            $query->where('status', 1);
        }])
        ->withCount('reviews')
        ->withSum('reviews', 'rating')
        ->findOrFail($id);

        if ($book->status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $relatedBooks = Book::where('status', 1)
            ->withCount('reviews')
            ->withSum('reviews', 'rating')
            ->take(3)
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'book' => $book,
                'related_books' => $relatedBooks
            ]
        ]);
    }

    /**
     * Save a review for a book.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveReview(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'errors' => ['auth' => 'You need to be logged in to submit a review.']
            ]);
        }

        $validator = Validator::make($request->all(), [
            'review' => 'required|min:10',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $countReview = Review::where('user_id', Auth::user()->id)->where('book_id', $request->book_id)->count();

        if ($countReview > 0) {
            return response()->json([
                'status' => false,
                'message' => 'You already submitted a review for this book.'
            ]);
        }

        $review = new Review();
        $review->review = $request->review;
        $review->rating = $request->rating;
        $review->user_id = Auth::user()->id; // Ensure user is logged in
        $review->book_id = $request->book_id;
        $review->save();

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully.'
        ]);
    }
}
