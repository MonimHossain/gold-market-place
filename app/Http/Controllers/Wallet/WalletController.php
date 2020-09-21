<?php

namespace App\Http\Controllers\Wallet;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function addMoneyToWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transection_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'bank_recipt' => 'mimes:jpeg,png,gif,svg,pdf',
        ]);

    if ($validator->fails()) {
        return response()->json(['status' => false,'message' => $validator->errors()], 422);
    }

    $file = $request->file('bank_recipt');

    if (file_exists($file)) {
        $fileDestinationPath = 'storage/app/wallet/Recipt/'.$request->transection_id.'/';
        $file->move($fileDestinationPath, $file->getClientOriginalName());
    }
    
    return $file->getClientOriginalName();
    $wallet = new Wallet;

    }
}
