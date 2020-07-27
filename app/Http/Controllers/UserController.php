<?php

namespace App\Http\Controllers;

use App\Jobs\FundBalanceJob;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function transfer(Request $request)
    {

        try {

            DB::beginTransaction();

            $to = $request->input('to');

            $from = $request->input('from');

            $amount = $request->input('amount');

            $rule = array(
                'to' => 'Required',
                'from' => 'Required',
                'amount' => 'Required'
            );

            $validator = Validator::make($request->all(), $rule);

            $error = $validator->errors();

            if ($validator->failed())
                throw new \Exception($error->first(), 400);

            // log transaction
            $reference = Str::random(10);

            while (Transaction::where('reference', $reference)->count() > 0) {
                $reference = Str::random(10);
            }

            $transaction = new Transaction();

            $transaction->amount = $amount;

            $transaction->reference = $reference;

            $transaction->account = $to;

            $transaction->save();

            FundBalanceJob::dispatch($reference);

            DB::commit();

            return response()->json(array(
                'message' => 'Transfer successful'
            ), 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                array(
                    'message' => 'An error occurred processing transfer',
                    'short_description' => $e->getMessage()
                ),
                $e->getCode()
            );
        }

    }
}
