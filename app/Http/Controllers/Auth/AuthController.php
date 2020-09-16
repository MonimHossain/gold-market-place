<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\AdminUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationMail;
use App\Mail\ForgotPassword;
use App\Mail\ResetPassword;
use App\Mail\AdminRegistrationMail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Ixudra\Curl\Facades\Curl;


class AuthController extends Controller
{
    public function login(Request $request) 
    {
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
   
   public function adminLogin(Request $request) 
   {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        $admin = AdminUser::where('email', $request->email)->first();

        if(Hash::check($request->password, $admin->password)){
            // echo "Hello";
            // $response = Curl::to('/oauth/token')
            //     ->withHeader('Content-Type : multipart/form-data')
            //     ->withData([
                    // 'grant_type' => 'password',
                    // 'username' => 'monimh786@gmail.com',
                    // 'password' => '123456',
                    // 'client_id' => '5',
                    // 'client_secret' => 'T7cu1cy81fanyTDAlqH1j8xJ21EiAtNUa9U6Cljh',
                    // 'scope' => '',
            //     ])
            //     ->asJson()
            //     ->post();

                
            $data = [
                'grant_type' => 'password',
                'username' => 'monimh786@gmail.com',
                'password' => '123456',
                'client_id' => '5',
                'client_secret' => 'T7cu1cy81fanyTDAlqH1j8xJ21EiAtNUa9U6Cljh',
                'scope' => 'read-only',
            ];

            $request = Request::create('oauth/token', 'POST', $data);
            $request->headers->set('Accept', 'multipart/form-data');
            $response = Route::dispatch($request);

            // return response()->json([
            //     'access_token' => $tokenResult->accessToken,
            //     'token_type' => 'Bearer',
            //     'expires_at' => Carbon::parse(
            //         $tokenResult->token->expires_at
            //     )->toDateTimeString()
            // ]);
            return $response;
        }
        else{
            return 'Email or Password Does not Match!!!';
        }
    }
    public function register(Request $request)
    {
            return $request;
            $request->validate([
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
            ]);

            $user = new User;
            $user->first_name = strtolower($request->first_name);
            $user->last_name = strtolower($request->last_name);
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

            Mail::to('monimh786@gmail.com')->send(new RegistrationMail());

            return response()->json([
                'message' => 'Successfully created user!'
            ], 201);
    }
    public function adminRegister(Request $request)
    {
            $request->validate([
                    'username' => 'required',
                    'email' => 'required',
                    'password' => 'required',
            ]);

            $user = new AdminUser;
            $user->username = strtolower($request->username);
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            Mail::to('monimh786@gmail.com')->send(new AdminRegistrationMail());

            return response()->json([
                'message' => 'Successfully created admin!'
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

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        Mail::to('monimh786@gmail.com')->send(new ForgotPassword());

        return response()->json([
            'message' => 'Successfully sent code to email!'
        ], 201);
        
        
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        Mail::to('monimh786@gmail.com')->send(new ResetPassword());
        
        return response()->json([
            'message' => 'Successfully reset password!'
        ], 201);
        
        
    }

    public function getAdminUsers(Request $request){
        return AdminUser::all();
    }

    protected function authenticated(Request $request, $user)
    {               
        // implement your user role retrieval logic, for example retrieve from `roles` database table
        $role = $user->checkRole();

        // grant scopes based on the role that we get previously
        if ($role == 'superadmin') {
            $request->request->add([
                'scope' => 'manage' // grant manage order scope for user with admin role
            ]);
        } else {
            $request->request->add([
                'scope' => 'read-only' // read-only order scope for other user role
            ]);
        }

        // forward the request to the oauth token request endpoint
        $tokenRequest = Request::create(
            '/oauth/token',
            'post'
        );
        return Route::dispatch($tokenRequest);
    }

	

}
