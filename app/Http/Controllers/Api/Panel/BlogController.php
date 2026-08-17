<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Resources\CommentResource;
use App\Models\Blog;
use App\Models\Api\Comment;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    public function index()
    {
        $user = apiAuth();
        $query = Blog::where('author_id', $user->id);

        $posts = deepClone($query)
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) request('per_page', 20), 100));

        $blogIds = deepClone($query)->pluck('id')->toArray();

        $postsCount = count($blogIds);
        $commentsCount = Comment::whereIn('blog_id', $blogIds)->count();
        $pendingPublishCount = deepClone($query)->where('status', 'pending')->count();

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [
                'posts_count' => $postsCount,
                'comment_count' => $commentsCount,
                'pending_publish_count' => $pendingPublishCount,
                'blogs' => $posts->through(fn($post) => new BlogResource($post))
            ]);

    }

    public function show(Blog $blog)
    {
        if ($blog->author_id != apiAuth()->id) {
            abort(404);
        }
        $resource = new BlogResource($blog);
        $resource->show = true;

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
            [
                'blog' => $resource,

            ]);
    }


}
