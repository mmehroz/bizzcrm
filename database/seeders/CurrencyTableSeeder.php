<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currency')->truncate();
        DB::table('currency')->insert([
            'currency_id'   => 1,
			'currency_name' => 'USD',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 2,
			'currency_name' => 'CAD',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 3,
			'currency_name' => 'EUR',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 4,
			'currency_name' => 'GBP',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 5,
			'currency_name' => 'AUD',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 6,
			'currency_name' => 'TRY',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 7,
			'currency_name' => 'SAR',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 8,
			'currency_name' => 'PKR',
			'status_id'     => 1,
        ]);
        DB::table('currency')->insert([
            'currency_id'   => 9,
			'currency_name' => 'INR',
			'status_id'     => 1,
        ]);
    }
}

// AED
// AFN
// ALL
// AMD
// ANG
// AOA
// ARS
// AUD
// AWG
// AZN
// BAM
// BBD
// BDT
// BGN
// BHD
// BIF
// BMD
// BND
// BOB
// BOV
// BRL
// BSD
// BTN
// BWP
// BYN
// BZD
// CAD
// CDF
// CHE
// CHF
// CHW
// CLF
// CLP
// CNY
// COP
// COU
// CRC
// CUC
// CUP
// CVE
// CZK
// DJF
// DKK
// DOP
// DZD
// EGP
// ERN
// ETB
// EUR
// FJD
// FKP
// GBP
// GEL
// GHS
// GIP
// GMD
// GNF
// GTQ
// GYD
// HKD
// HNL
// HRK
// HTG
// HUF
// IDR
// ILS
// INR
// IQD
// IRR
// ISK
// JMD
// JOD
// JPY
// KES
// KGS
// KHR
// KMF
// KPW
// KRW
// KWD
// KYD
// KZT
// LAK
// LBP
// LKR
// LRD
// LSL
// LYD
// MAD
// MDL
// MGA
// MKD
// MMK
// MNT
// MOP
// MRU
// MUR
// MVR
// MWK
// MXN
// MXV
// MYR
// MZN
// NAD
// NGN
// NIO
// NOK
// NPR
// NZD
// OMR
// PAB
// PEN
// PGK
// PHP
// PKR
// PLN
// PYG
// QAR
// RON
// RSD
// RUB
// RWF
// SAR
// SBD
// SCR
// SDG
// SEK
// SGD
// SHP
// SLL
// SOS
// SRD
// SSP
// STN
// SVC
// SYP
// SZL
// THB
// TJS
// TMT
// TND
// TOP
// TRY
// TTD
// TWD
// TZS
// UAH
// UGX
// USD
// USN
// UYI
// UYU
// UYW
// UZS
// VES
// VND
// VUV
// WST
// XAF
// XAG
// XAU
// XBA
// XBB
// XBC
// XBD
// XCD
// XDR
// XOF
// XPD
// XPF
// XPT
// XSU
// XTS
// XUA
// XXX
// YER
// ZAR
// ZMW
// ZWL
