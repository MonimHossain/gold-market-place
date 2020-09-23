<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\VerifyUserMail;
use App\Mail\DeleteUserMail;
use App\Mail\MobileVerification;
use App\Mail\VerifyPhoneNumber;
use App\Mail\CreateVault;
use App\Mail\UpdateVault;
use App\Mail\DeleteVault;
use App\Mail\ApprovedVault;
use App\Mail\DispprovedVault;
use App\Mail\TurnOnSaleUser;
use App\Mail\TurnOffSale;
use App\Mail\VaultDeliveryMail;
use App\Mail\GetDeliveredUser;
use App\Models\Vault;
use App\Models\VaultHistory;
use App\Models\VaultDelivery;
use PDF;
use GuzzleHttp\Client;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $users = User::where('status', '!=', 'deleted')->get();
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
            'status' => true,
            'message' => 'Successfully verified the user!'
        ], 201);
    }

    public function declineUsers(Request $request){

        $request->validate([
            'id' => 'required',
        ]);

        $user = User::find($request->id);

        $user->status = 'declined';
        if($user->save())
            Mail::to('monimh786@gmail.com')->send(new DeleteUserMail());

        return response()->json([
            'status' => true,
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
        
        if($user->save())
            Mail::to('monimh786@gmail.com')->send(new VerifyPhoneNumber());

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
        // $client = new Client();
        // $res = $client->get('https://metals-api.com/api/latest', ['access_key' => '18bftjsqr3rgwngw2d8ijtcldjulzca7gio7mt10n99m25gbjvdsj0unt02e']);
        // echo $res->getStatusCode(); // 200
        // echo $res->getBody();
        // die;
        // $client = new Client();

        // $response = $client->post('https://metals-api.com/api/latest?access_key=18bftjsqr3rgwngw2d8ijtcldjulzca7gio7mt10n99m25gbjvdsj0unt02e');
        //     return $response;
        //gold rate fetch
        $json = file_get_contents('https://data-asg.goldprice.org/dbXRates/USD');
        $decoded = json_decode($json);
        $item = $decoded->items;
        $gold_price = $item[0]->xauPrice;

        // sku
        $sku = "GM-".date("Y-m-d-H-i-s");

        $file = $request->file('filename');

        $vault = new Vault;
        $vault->title = strtolower($request->title);
        $vault->type_of_metal = strtolower($request->type_of_metal);
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
        $vault->sku = $sku;
        $vault->rate = $gold_price;
        $vault->currency = $request->currency;
        $vault->description = $request->description ? $request->description : '';

        $fileDestinationPath = 'storage/app/vault/'.$request->user_id.'/';
        $file->move($fileDestinationPath, $file->getClientOriginalName());

        if($vault->save())
            Mail::to('monimh786@gmail.com')->send(new CreateVault());

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
            Mail::to('monimh786@gmail.com')->send(new DeleteVault());
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

        if($vault->save()){
            Mail::to('monimh786@gmail.com')->send(new UpdateVault());
            return response()->json(['message' => 'success']);
        }
    }

    public function vaultApproval(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);
        
        $vault = Vault::where('id', $request->id)->first();

        $vault->approval_status = $request->status;
        $vault->save();

        if($request->status=='approved'){
            Mail::to('monimh786@gmail.com')->send(new ApprovedVault());

        }
        else
            Mail::to('monimh786@gmail.com')->send(new DispprovedVault());

        return response()->json(['message' => 'success']);
        
    }

    public function getVaultItem(Request $request)
    {
        $vault = Vault::with('user')->get();
        return response()->json($vault);       
    }

    public function getDetailVaultItem(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $vault = Vault::find($request->id);
        return response()->json($vault);       
    }

    public function getSummaryVaultItem(Request $request)
    {
        $vault = Vault::select(DB::raw('type_of_metal, SUM(weight) as weight'))
                        ->groupBy('type_of_metal')
                        ->get();    

        $vault_today = Vault::select(DB::raw('type_of_metal, SUM(weight) as weight'))
                        ->groupBy('type_of_metal')
                        ->whereDate('created_at', Carbon::today())
                        ->get();

        $vault_yesterday = Vault::select(DB::raw('type_of_metal, SUM(weight) as weight'))
                        ->groupBy('type_of_metal')
                        ->whereDate('created_at', Carbon::yesterday())
                        ->get();    
                        
        return response()->json([
            'vaultTotalSummary' => $vault,
            'vaultTodaySummary' => $vault_today,
            'vaultYesterdaySummary' => $vault_yesterday,
        ]);;
    }

    public function getVaultSearch(Request $request)
    {
        $vault = DB::table('vaults')
        ->select('vaults.*','users.first_name', 'users.last_name')
        ->join('users','vaults.user_id','=','users.id')
        ->where('vaults.approval_status',$request->status)
        // ->orWhere('vaults.id',$request->id)
        // ->orWhere('vaults.type_of_metal', $request->type)
        ->get();

        return response()->json($vault);
    }
    public function printPDF(Request $request)
    {
        $data = [
            'title' => '',
            'heading' => '',
            'content' => ''        
              ];
          
          $pdf = PDF::loadView('invoice/vault-create', $data);  
          return $pdf->download('vault-invoice.pdf');
    }
    public function turnOnSale(Request $request)
    {          
        $request->validate([
            'id' => 'required',
        ]);

        $vault = Vault::find($request->id);
        $vault->state_status = 'sale_on';
        if($vault->save()){
            Mail::to('monimh786@gmail.com')->send(new TurnOnSaleUser());
        }

        return response()->json(['message' => 'success']);
    }

    public function modifySaleAdmin(Request $request)
    {          
        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        $vault = Vault::find($request->id);

        if($request->status == 'on'){
            $vault->state_status = 'sale_on';  
            $vault->save();  
            Mail::to('monimh786@gmail.com')->send(new TurnOnSaleUser());
        }else{
            $vault->state_status = 'sale_off';
            $vault->save();
            Mail::to('monimh786@gmail.com')->send(new TurnOffSale());
        }
        
        return response()->json(['message' => 'success']);
    }
    public function getDeliveredFromUser(Request $request)
    {          
        $request->validate([
            'id' => 'required',
        ]);

        $vault = Vault::find($request->id);
        $vault->state_status = 'get_delivered';
        if($vault->save()){
            Mail::to('monimh786@gmail.com')->send(new GetDeliveredUser());
        }

        return response()->json(['message' => 'success']);
    }

    public function userVaultSummary(Request $request)
    {
        $vault = Vault::select(DB::raw('type_of_metal, SUM(weight) as weight'))
                        ->groupBy('type_of_metal')
                        ->get();    

        $vault_item_count = Vault::select(DB::raw('Count(id) as count'))
                        ->get();   

        $vault_today = Vault::select(DB::raw('type_of_metal, SUM(weight) as weight'))
                        ->groupBy('type_of_metal')
                        ->whereDate('created_at', Carbon::today())
                        ->get();  
                        
        //pricing
                        
        return response()->json([
            'vaultTotalSummary' => $vault,
            'vaultTodaySummary' => $vault_today,
            'totalVaultItem' => $vault_item_count,
        ]);
    }

    public function userVaultHistory(Request $request)
    {
        $vault = VaultHistory::all(); 

        return response()->json($vault);
    }
    public function modifyDeliveryAdmin(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        if($request->status == 'cancel'){
            $vault = Vault::find($request->id);
            $vault->state_status = 'sale_off';
            $vault->approval_status = 'disapproved';
            $vault->save();
        }
        $data = VaultDelivery::find($request->id);
        
        if($data != ''){
            $data->vault_id = $request->id;
            $data->status = $request->status;
            $data->save();  
        }
        else{
            $delivery = new VaultDelivery();
            $delivery->vault_id = $request->id;
            $delivery->status = $request->status;
            $delivery->save();
        }

        
        //it can be decline or dispatch
        Mail::to('monimh786@gmail.com')->send(new VaultDeliveryMail());

        return response()->json(['message' => 'success']);

    }
}
