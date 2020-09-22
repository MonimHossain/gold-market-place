<?php

namespace App\Http\Controllers\Wallet;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\AddMoneyWallet;
use PDF;

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
            return response()->json(['message' => true]);
        }
        else{
            return response()->json(['message' => false]);
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

        return response()->json(['message' => true, 'data' => $data]); 
    }

    public function downloadInvoice(Request $request)
    {
        $wallet = Wallet::find($request->id);          
        $pdf = PDF::loadView('invoice/wallet-information', $wallet);  
        return $pdf->download('wallet-invoice.pdf');
    }
}
