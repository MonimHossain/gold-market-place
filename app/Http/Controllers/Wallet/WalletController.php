<?php

namespace App\Http\Controllers\Wallet;

use PDF;
use App\Models\Bank;
use App\Models\Wallet;
use App\Models\Withdraw;
use App\Mail\AddMoneyWallet;
use Illuminate\Http\Request;
use App\Mail\WithdrawalVerified;
use App\Mail\ApproveWiredTransfer;
use App\Mail\DeclineWiredTransfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\ApproveWithdrawalMoney;
use App\Mail\DeclineWithdrawalMoney;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawMoneyFromWallet;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function addMoneyToWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'bank_recipt' => 'mimes:jpeg,png,gif,svg,pdf',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'message' => $validator->errors()], 422);
        }

        $file = $request->file('bank_recipt');

        if (file_exists($file)) {
            $fileDestinationPath = 'storage/app/wallet/Recipt/'.$request->transection_id.'/';
            $file->move($fileDestinationPath, $file->getClientOriginalName());
        }
        
        $wallet = new Wallet;
        $wallet->transaction_id = $request->transaction_id;
        $wallet->user_id = $request->user_id;
        $wallet->currency = $request->currency;
        $wallet->amount = $request->amount;
        $wallet->bank_recipt = $file->getClientOriginalName();
        $wallet->date = date('Y-m-d', strtotime($request->date));
        $wallet->bank_id = 1;

        if($wallet->save()){
            Mail::to('monimh786@gmail.com')->send(new AddMoneyWallet());
            return response()->json(['status' => true]);
        }
        else{
            return response()->json(['status' => false]);
        }
    }

    public function showWalletHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'message' => $validator->errors()], 422);
        }

        $data = Wallet::where('user_id', $request->id)->get();

        return response()->json(['status' => true, 'data' => $data]); 
    }

    public function downloadInvoice(Request $request)
    {
        $wallet = Wallet::find($request->id);      
        
        $pdf = PDF::loadView('invoice/wallet-information', $wallet);  
        return $pdf->download('wallet-invoice.pdf');
    }

    public function withDrawWalletMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => false,'message' => $validator->errors()]);
        }

        $wallet = Wallet::select(DB::raw('user_id, SUM(amount) as amount'))
                ->where('user_id', $request->user_id)
                ->groupBy('user_id')
                ->first();

        $withdraw = Withdraw::select(DB::raw('user_id, SUM(amount) as amount'))
                ->where('user_id',$request->user_id)
                ->where('type_of_transaction', $request->type)
                ->first();

        $availableAmount = $wallet->amount - $withdraw->amount;

        if($availableAmount >= $request->amount)
        {
            $withdraw = new Withdraw;
            $withdraw->transaction_id = $request->transaction_id;
            $withdraw->user_id = $request->user_id;
            $withdraw->currency = $request->currency;
            $withdraw->amount = $request->amount;
            $withdraw->type_of_transaction = $request->type;
            $withdraw->bank_id = 1;
            $withdraw->mobile_token = rand(100000,999999);


            if($withdraw->save()){
                Mail::to('monimh786@gmail.com')->send(new WithdrawMoneyFromWallet());
                return response()->json(['status' => true]);
            }
            else{
                return response()->json(['status' => false]);
            }
        }
        else
        {
            return response()->json(['status' => false,'message' => 'Insufficent Balance!']);
        }          
    
    }

    public function editBankInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_address' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => false,'message' => $validator->errors()]);
        }

        $bank = Bank::find($request->bank_id);
        $bank->account_number = $request->account_number;
        $bank->account_name = $request->account_name;
        $bank->bank_address = $request->bank_address;
        
        if($bank->save()){
            return response()->json(['status' => true]);
        }
        else{
            return response()->json(['status' => false]);
        }        
    
    }
    public function verifyWithdrawMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => false,'message' => $validator->errors()]);
        }

        $withdraw = Withdraw::where('id',$request->id)->where('mobile_token', $request->code)->first();
        if($withdraw != ''){
            $withdraw->verification_status = 1;
            if($withdraw->save()){
                Mail::to('monimh786@gmail.com')->send(new WithdrawalVerified());
            }
            return response()->json(['status' => true]);
        }
        else{
            return response()->json(['status' => false, 'message' => 'Mobile verification failed!']);
        }        
    
    }
    public function changeWiredTransferStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => false,'message' => $validator->errors()]);
        }

        $wallet = Wallet::find($request->id);
        $wallet->status = $request->status;
        $wallet->save();

        if($request->status == 1)
        {
            Mail::to('monimh786@gmail.com')->send(new ApproveWiredTransfer());
            return response()->json(['status' => true,'message' => 'Wired Transfer Approved!']);
        }
        else
        {
            Mail::to('monimh786@gmail.com')->send(new DeclineWiredTransfer());
            return response()->json(['status' => false,'message' => 'Wired Transfer Decline!']);
        }
    }
    public function adminWiredTransferDepositHistory(Request $request){
        $data = Wallet::all();
        return response()->json(['status' => true,'data' => $data]);
    }
    public function adminWithdrawalHistory(Request $request){
        $data = Withdraw::all();
        return response()->json(['status' => true,'data' => $data]);
    }
    public function changeWalletStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => false,'message' => $validator->errors()]);
        }

        $wallet = Withdraw::find($request->id);
        $wallet->status = $request->status;
        $wallet->save();

        if($request->status == 1)
        {
            Mail::to('monimh786@gmail.com')->send(new ApproveWithdrawalMoney());
            return response()->json(['status' => true,'message' => 'Wired Transfer Approved!']);
        }
        else
        {
            Mail::to('monimh786@gmail.com')->send(new DeclineWithdrawalMoney());
            return response()->json(['status' => false,'message' => 'Wired Transfer Decline!']);
        }
    }
}
