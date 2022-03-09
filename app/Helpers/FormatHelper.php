<?php
namespace App\Helpers;

class FormatHelper
{
    public static function CurrencyFormat($amount = 0)
    {
        $number = new \NumberFormatter( 'en_US', \NumberFormatter::DECIMAL );
        return $number->format( floatval($amount) );
    }

    public static function SafeType($type = null)
    {
        $types = [
            '' => 'Type:-',
            'spend' => 'Spend',
            'cash_in' => 'Cash-In',
            'cash_out' => 'Cash-Out',
            'deposit' => 'Deposit',
            'withdraw' => 'Withdraw',
            'debit' => 'Debit',
            'credit' => 'Credit',
            'customer_exchange' => 'Customer-Exchange',
            'wallet_exchange' => 'Wallet-Exchange',
            'customer_transfer' => 'Customer-Transfer',
            'wallet_transfer' => 'Wallet-Transfer'
        ];
        if($type == null) return $types;

        if(isset($types[$type])) return $types[$type];

        return $type;
    }
}
