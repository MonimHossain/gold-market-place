<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyUserMail;
use App\Mail\DeleteUserMail;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $users = User::all();
        return response()->json($users);
    }

    public function filterUsers(Request $request)
    {
        $users = User::where('status', $request->status)->get();
        return response()->json($users);
    }

    public function searchUsers(Request $request)
    {
        if($request->email != null)
            $users = User::where('email', 'like', '%'.$request->email.'%')->get();
        else if($request->passport != null)
            $users = User::where('passport_id', 'like', '%'.$request->passport.'%')->get();
        else if($request->national != null)
            $users = User::where('national_id', 'like', '%'.$request->national.'%')->get();
        else
            $users = User::where('id', $request->id)->get();

        return response()->json($users);
    }

    public function getUserBySlug(Request $request)
    {
        $user  = User::where('first_name', $request->name)->first();
        return response()->json($user);
    }

    public function verifyUsers(Request $request){

        $request->validate([
            'id' => 'required',
        ]);

        $user = User::find($request->id);

        $user->status = 'active';
        if($user->save())
            Mail::to('monimh786@gmail.com')->send(new VerifyUserMail());

        return response()->json([
            'message' => 'Successfully verified the user!'
        ], 201);
    }

    public function deleteUsers(Request $request){

        $request->validate([
            'id' => 'required',
        ]);

        $user = User::find($request->id);

        $user->status = 'delete';
        if($user->save())
            Mail::to('monimh786@gmail.com')->send(new DeleteUserMail());

        return response()->json([
            'message' => 'Successfully deleted the user!'
        ], 201);
    }
}
