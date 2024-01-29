<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminBlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::with('user')->latest()->get();
        return response()->json([
            'status' => 'success',
            'blogs' => $blogs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'unique:blogs'],
            'category_id' => ['required'],
            'description' => ['required'],
            'status' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $user = Session::get('user');
            $blog = Blog::create([
                'title' => $request->title,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $user->id,
                'slug' => \Str::slug($request->title),
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Blog created successfully.',
                'user' => $blog,  // Include user details if needed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Blog created failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $blog = Blog::find($id);
            if ($blog == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Blog not found.',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'blog' => $blog,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'unique:blogs,title,'.$id],
            'category_id' => ['required'],
            'description' => ['required'],
            'status' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $user = Session::get('user');
            $blog = Blog::find($id);
            if ($blog == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Blog not found.',
                ], 404);
            }
            if ($blog->user_id != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this blog.',
                ], 403);
            }
            $blog->update([
                'title' => $request->title,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $user->id,
                'slug' => \Str::slug($request->title),
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Blog updated successfully.',
                'user' => $blog,  // Include user details if needed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Blog updated failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Session::get('user');
        try {
            $blog = Blog::find($id);
            if ($blog == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Blog not found.',
                ], 404);
            }
            if ($blog->user_id != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this blog.',
                ], 403);
            }
            $blog->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Blog deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }
    }
}
