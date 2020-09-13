<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
             'email' => 'required|email',
             'password' => 'required'
           ]);

        $credentials = request(['email', 'password']);
        // print_r($credentials);die;

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ],401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);

   }

    public function register(Request $request)
    {
            // return $request;
            $request->validate([
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
            ]);

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->phone = $request->phone;
            if($request->id_no == 'national_id'){
                $user->national_id = $request->national_id;
                $user->passport_id = '';
            }else{
                $user->passport_id = $request->passport_id;
                $user->national_id = '';
            }

            $user->save();

            return response()->json([
                'message' => 'Successfully created user!'
            ], 201);
    }

    public function verifyPhoneNumber(Request $request){
        $request->validate([
            'id' => 'required',
            'phone_verification_code' => 'required',
        ]);

        $user = User::find($request->id);

        if($user == ''){
            return response()->json([
                'message' => 'Error: Invalid id!'
            ], 201);
        }
        
        $user->phone_verification_code = $request->phone_verification_code;
        $user->save();

        return response()->json([
            'message' => 'Successfully verfied phone number!'
        ], 201);

    }

    public function scheduleCallTimeForContact(Request $request){

        $user = User::find($request->id);

            if($user == ''){
                return response()->json([
                    'message' => 'Error: Invalid id!'
                ], 201);
            }
        
        if($request->contact_call_type == 'available'){
            
            $request->validate([
                'id' => 'required',
            ]);

            $user->contact_call_type = $request->contact_call_type;
            $user->save();

            return response()->json([
                'message' => 'Successfully schedule date and time!'
            ], 201);

        }else{
            $request->validate([
                'id' => 'required',
                'schedule_date' => 'required',
                'schedule_time' => 'required'
            ]);

            $user->contact_call_type = $request->contact_call_type;
            $user->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
            $user->time =  date('H:i:s', strtotime($request->schedule_time));
            $user->save();

            return response()->json([
                'message' => 'Successfully schedule date and time!'
            ], 201);

        }

    }

    public function imageUpload(Request $request)
    {
        $file = $request->file('image_upload');

        $request->validate([
            'id' => 'required',

        ]);
        $user = User::find($request->id);

        if($user == ''){
            return response()->json([
                'message' => 'Error: Invalid id!'
            ], 201);
        }

        if($request->id_no == 'national_id'){
            $request->validate([
                'id' => 'required',
                'image_upload' => 'required',
            ]);
            
            $user->nationalId_filename = $file->getClientOriginalName();
            $user->save();

            $fileDestinationPath = 'storage/app/nationalID/';
            $file->move($fileDestinationPath, $file->getClientOriginalName());

            return response()->json([
                'message' => 'Successfully uploaded the file!'
            ], 201);
            
        }else{
            $request->validate([
                'id' => 'required',
                'image_upload' => 'required',
            ]);

            $user->passport_filename = $request->image_upload;
            $user->save();

            $fileDestinationPath = 'storage/app/passportID/';
            $file->move($fileDestinationPath, $file->getClientOriginalName());

            return response()->json([
                'message' => 'Successfully uploaded the file!'
            ], 201);
        }
        
        
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
        'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

}
