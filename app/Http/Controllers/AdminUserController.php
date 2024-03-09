<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->get();
        return response()->json([
            'status' => 'success',
            'users' => $users,
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
        //
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
            $user = User::find($id);
            if ($user == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not Authorized.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'unique:users,email,'.$id],
            'first_name' => ['required'],
            'last_name' => ['required'],
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
            $session_user = Session::get('user');
            $user = User::find($id);
            if ($user == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }
            if ($session_user->id == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this user.',
                ], 403);
            }
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'status' => $request->status,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully.',
                'user' => $user,  // Include user details if needed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User updated failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function approveOrReject($action, $id){
        try {
            $user = User::find($id);
            if ($user == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }
            if ($action == 'approve') {
                $user->update([
                    'status' => 'Active',
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'User approved successfully.',
                    'user' => $user,  // Include user details if needed
                ]);
            } else if ($action == 'reject') {
                $user->update([
                    'status' => 'Inactive',
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'User rejected successfully.',
                    'user' => $user,  // Include user details if needed
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid action.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User action failed. Please try again.',
            ], 500);
        }
    }
}
