<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\LinksCollection;
use App\Models\User;
use App\Models\Link;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return response()->json(new UserResource(auth()->user()), 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the specified user in storage.
     */
    public function get(string $user_name)
    {
        try {
            $user_info = [];
            $user_list = User::where('name', $user_name)->get();
            if (count($user_list) < 1) {
                return response()->json(['error' => 'User not found'], 404);
            } else {
                foreach ($user_list as $item) {
                   $user = new UserResource($item);
                }

                $links = Link::where('user_id', $user->id)->get();
                $link_map = new LinksCollection($links);
                $user_info = (new UserResource($user))->toArray(new Request());
                $user_info["links"] =  $link_map;
            }

            return response()->json($user_info, 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|max:25',
            'bio' => 'sometimes|max:80',
        ]);

        try {
            $user->name = $request->input('name');
            $user->bio = $request->input('bio');
            $user->save();

            return response()->json('USER DETAILS UPDATED', 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
