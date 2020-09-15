<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyUserMail;
use App\Mail\DeleteUserMail;
use App\Mail\MobileVerification;
use App\Models\Vault;

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

    public function mailMobileCode(Request $request){

        $request->validate([
            'email' => 'required',
        ]);

        $user = User::where('email',$request->email)->first();
        
        Mail::to('monimh786@gmail.com')->send(new MobileVerification());

        return response()->json([
            'message' => 'Successfully sent email!'
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
    public function createVault(Request $request){
        
        $request->validate([
            'title' => 'required',
            'type_of_metal' => 'required',
            'category' => 'required',
            'manufactor' => 'required',
            'weight' => 'required',
            'purity' => 'required',
            'serial_no' => 'required',
            'filename' => 'required',
            'sending_option' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'country' => 'required',
            'user_id' => 'required',
            'currency' => 'required'
        ]);
        
        //gold rate fetch
        $json = file_get_contents('https://data-asg.goldprice.org/dbXRates/USD');
        $decoded = json_decode($json);
        $item = $decoded->items;
        $gold_price = $item[0]->xauPrice;


        $file = $request->file('filename');

        $vault = new Vault;
        $vault->title = strtolower($request->title);
        $vault->type_of_metal = $request->type_of_metal;
        $vault->category = $request->category;
        $vault->manufactor = $request->manufactor;
        $vault->weight = $request->weight;
        $vault->purity = $request->purity;
        $vault->serial_no = $request->serial_no;
        $vault->filename = $file->getClientOriginalName();
        $vault->sending_option = $request->sending_option;
        $vault->address = $request->address;
        $vault->city = $request->city;
        $vault->state = $request->state;
        $vault->zip_code = $request->zip_code;
        $vault->country = $request->country;
        $vault->user_id = $request->user_id;
        $vault->rate = $gold_price;
        $vault->currency = $request->currency;
        $vault->description = $request->description ? $request->description : '';

        $fileDestinationPath = 'storage/app/vault/'.$request->user_id.'/';
        $file->move($fileDestinationPath, $file->getClientOriginalName());

        $vault->save();
        
        return response()->json([
            'message' => 'Successfully created vault !'
        ], 201);

    }
    public function getVault(Request $request){
        $request->validate([
            'user_id' => 'required',
        ]);

        $vault = Vault::where('user_id',$request->user_id)->get();

        return response()->json($vault);
    }

    public function deleteVault(Request $request)
    {
        if ($request->id != '') {
            Vault::where('id', $request->id)->delete();
            return response()->json(['message' => 'success']);
        } else {
            return response()->json(['message' => 'id is required'], 422);
        }
    }

    public function updateVault(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $file = $request->file('filename');

        if (file_exists($file)) {
            $json = file_get_contents('https://data-asg.goldprice.org/dbXRates/USD');
            $decoded = json_decode($json);
            $item = $decoded->items;
            $gold_price = $item[0]->xauPrice;
            $fileDestinationPath = 'storage/app/vault/'.$request->user_id.'/';
            $file->move($fileDestinationPath, $file->getClientOriginalName());
        }


        $vault = Vault::find($request->id);
        $vault->title = $request->title ? strtolower($request->title) : $vault->title ;
        $vault->type_of_metal = $request->type_of_metal ? $request->type_of_metal : $vault->type_of_metal ;
        $vault->category = $request->category ? $request->category : $vault->category ;
        $vault->manufactor = $request->manufactor ? $request->manufactor : $vault->manufactor;
        $vault->weight = $request->weight ? $request->weight : $vault->weight ;
        $vault->purity = $request->purity ? $request->purity : $vault->purity ;
        $vault->serial_no = $request->serial_no ? $request->serial_no : $vault->serial_no;
        $vault->filename = file_exists($file) ? $vault->filename : $file->getClientOriginalName();
        $vault->sending_option = $request->sending_option ? : $vault->sending_option;
        $vault->address = $request->address ? : $vault->address;
        $vault->city = $request->city ? : $vault->city;
        $vault->state = $request->state ? : $vault->state;
        $vault->zip_code = $request->zip_code ? : $vault->zip_code;
        $vault->country = $request->country ? $request->country : $vault->country;
        $vault->user_id = $request->user_id ? $request->user_id : $vault->user_id;
        $vault->rate = $gold_price;
        $vault->currency = $request->currency ? $request->currency : $vault->currency;
        $vault->description = $request->description ? $request->description : $vault->description;

        if($vault->save())
            return response()->json(['message' => 'success']);
    }
}
