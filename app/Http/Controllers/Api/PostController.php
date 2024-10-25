<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;



use App\Http\Controllers\Api\Responses\ApiResponse;


class PostController extends Controller
{
    /**
     * Display a listing of the resource (only authenticated user's posts).
     */
    public function index()
    {
        $posts = auth()->user()->posts()->with('tags')->orderBy('pinned', 'desc')->get();
        return ApiResponse::success(PostResource::collection($posts), 'Posts retrieved successfully');
    }

    
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $post = auth()->user()->posts()->with('tags')->findOrFail($id);
            return ApiResponse::success(new PostResource($post), 'Post retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Post not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */

     public function store(PostRequest $request)
     {
        
         try {
             if (!Storage::exists('public/cover_images')) {
                 Storage::makeDirectory('public/cover_images');
             }
 
             // Store the cover image if provided
             $coverImagePath = $request->file('cover_image') ? $request->file('cover_image')->store('cover_images', 'public') : null;
 
             // Create the post using validated data
             $post = Post::create([
                 'title' => $request->title,
                 'body' => $request->body,
                 'cover_image' => $coverImagePath,
                 'pinned' => $request->pinned,
                 'user_id' => auth()->id(),
             ]);
 
             if ($request->has('tags')) {
                 $post->tags()->attach($request->tags);
             }
 
             return ApiResponse::success(new PostResource($post->load('tags')), 'Post created successfully', 201);
         } catch (\Exception $e) {
             throw new HttpResponseException(response()->json([
                 'success' => false,
                 'message' => 'Failed to create post: ' . $e->getMessage(),
             ], 500));
         }
     }
 
     /**
      * Update the specified resource in storage.
      */
      public function update(PostRequest $request, $id)
      {
          try {
              $post = auth()->user()->posts()->findOrFail($id);
              
              $updateData = $request->validated();
      
              if ($request->hasFile('cover_image')) {
                  if ($post->cover_image) {
                    Storage::delete('public/cover_images/' . $post->cover_image); // Corrected path
                }
                  $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');
                  $updateData['cover_image'] = $coverImagePath; 
              }
      
              // Update the post with validated data
              $post->update($updateData);
      
              // Sync tags if provided
              if ($request->has('tags')) {
                  $post->tags()->sync($request->tags);
              }
      
              return ApiResponse::success(new PostResource($post->load('tags')), 'Post updated successfully');
          } catch (ModelNotFoundException $e) {
              return ApiResponse::error('Post not found', 404);
          } catch (\Exception $e) {
              return ApiResponse::error('Failed to update post: ' . $e->getMessage(), 500);
          }
      }
      

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
        try {
            $post = auth()->user()->posts()->findOrFail($id);
            $post->delete();

            return ApiResponse::success(null, 'Post deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Post not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View deleted posts.
     */
    public function trashed()
    {
        $posts = auth()->user()->posts()->onlyTrashed()->with('tags')->get();
        return ApiResponse::success(PostResource::collection($posts), 'Deleted posts retrieved successfully');
    }
    

    /**
     * Restore a deleted post.
     */
    public function restore($id)
    {
        try {
            $post = auth()->user()->posts()->onlyTrashed()->findOrFail($id);
            $post->restore();

            return ApiResponse::success(new PostResource($post->load('tags')), 'Post restored successfully');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Post not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to restore post: ' . $e->getMessage(), 500);
        }
    }
}
