<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'body'    => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $post = Post::create($validated);

        return response()->json([
            'message' => 'Post created successfully',
            'post'    => $post,
        ], 201);
    }

    public function index(Request $request)
    {
        // Ambil query search, page, dan limit dari parameter query
        $search = $request->query('search');
        $page   = $request->query('page', 1);   // Default page = 1
        $limit  = $request->query('limit', 10); // Default limit = 10

        $query = Post::query();

        // Jika ada parameter search, tambahkan kondisi untuk mencari berdasarkan title
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Lakukan paginasi berdasarkan page dan limit
        $posts = $query->paginate($limit, ['*'], 'page', $page);

        // Kembalikan hasil dalam format JSON
        return response()->json($posts);
    }
    public function showUserPosts($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->posts);
    }
}
