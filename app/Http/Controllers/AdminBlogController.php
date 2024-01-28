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
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
