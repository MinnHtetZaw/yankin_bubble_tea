<?php

return [

	//Merchant id and secret key for 2c2p
	'merchant_id' => env('2C2P_MERCHANT_ID', 'JT02'),
	'secret_key' => env('2C2P_SECRET_KEY', 'YDRbw14OtHw3'),

	//Card SecureMode 
	'cardSecureMode_force' => "F",
	'cardSecureMode_yes' => "Y",
	'cardSecureMode_no' => "N",

	//payment url for 2c2p
	'sandbox' => "https://demo2.2c2p.com/2C2PFrontend/PaymentActionV2",
	'production' => "https://t.2c2p.com/paymentActionV2",

];