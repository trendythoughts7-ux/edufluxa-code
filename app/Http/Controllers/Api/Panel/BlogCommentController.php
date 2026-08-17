<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Api\Comment;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{

    public function index(Request $request)
    {
        $user = apiAuth();
        $posts = Blog::where('author_id', $user->id)->get();
        $blogIds = $posts->pluck('id')->toArray();
        $comments = Comment::whereIn('blog_id', $blogIds)->handleFilters()->orderBy('created_at', 'desc')
            ->paginate(min((int) request('per_page', 20), 100));

        $blogId = $request->get('blog_id', null);

        if (!empty($blogId) and is_numeric($blogId)) {
            $data['selectedPost'] = Blog::where('id', $blogId)
                ->where('author_id', $user->id)
                ->first();
        }
        $resource = $comments->through(fn($comment) => new CommentResource($comment));
     //   $resource->panel = true;
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [
                'comments' => $resource,

            ]);

    }
}
