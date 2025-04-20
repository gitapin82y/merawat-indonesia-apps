<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Optional filtering
        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        // Optional search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $users = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,yayasan,donatur',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string|max:500',
            'social' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Prepare user data
        $userData = $request->except(['password', 'avatar', 'thumbnail']);
        $userData['password'] = Hash::make($request->password);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $userData['thumbnail'] = $thumbnailPath;
        }

        // Create user
        $user = User::create($userData);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::with(['donations', 'fundraisings'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,yayasan,donatur',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string|max:500',
            'social' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        $userData = $request->except(['password', 'avatar', 'thumbnail']);

        // Update password if provided
        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Handle avatar upload and delete old file
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && $user->avatar != 'default/default-avatar.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        // Handle thumbnail upload and delete old file
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($user->thumbnail) {
                Storage::disk('public')->delete($user->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $userData['thumbnail'] = $thumbnailPath;
        }

        $user->update($userData);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->avatar && $user->avatar != 'default/default-avatar.png') {
            Storage::disk('public')->delete($user->avatar);
        }

        if ($user->thumbnail) {
            Storage::disk('public')->delete($user->thumbnail);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    // Additional methods
    public function getUserDonations($id)
    {
        $user = User::findOrFail($id);
        $donations = $user->donations()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $donations
        ]);
    }

    public function getUserFundraisings($id)
    {
        $user = User::findOrFail($id);
        $fundraisings = $user->fundraisings()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $fundraisings
        ]);
    }
}