<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Http\Controllers\Api\Responses\ApiResponse;
use App\Http\Resources\TagResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::all();
        return ApiResponse::success(TagResource::collection($tags), 'Tags retrieved successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tags,name',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
        ]);

        return ApiResponse::success(new TagResource($tag), 'Tag created successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Tag not found', 404);
        }

        $request->validate([
            'name' => 'required|string|unique:tags,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
        ]);

        return ApiResponse::success(new TagResource($tag), 'Tag updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Tag not found', 404);
        }

        $tag->delete();
        return ApiResponse::success(null, 'Tag deleted successfully', 204);
    }

    /**
     * Show a specific resource.
     */
    public function show($id)
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Tag not found', 404);
        }

        return ApiResponse::success(new TagResource($tag), 'Tag retrieved successfully', 200);
    }
}