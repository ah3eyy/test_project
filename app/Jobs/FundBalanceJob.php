<?php

namespace App\Jobs;

use App\Balance;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FundBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reference;

    /**
     * Create a new job instance.
     *
     * @param $reference
     */
    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // fetch logged transaction
        $transaction = Transaction::where('reference', $this->reference)->first();

        if ($transaction) {
            // fetch user balance to fund
            $balance = Balance::where('account', $transaction->account)->first();

            $update_balance = $balance->amount + $transaction->amount;

            Balance::where('account', $transaction->account)->update([
                'amount' => $update_balance
            ]);
        }

    }
}
