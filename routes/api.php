<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'Api\LoginController@login');
Route::post('updatePassword','Api\LoginController@updatePassword');
Route::post('Main_login','Api\LoginController@mainLogin');

//Category
Route::post('/categories', 'Api\CategoryController@all');
Route::post('/categories/store', 'Api\CategoryController@store');
Route::post('/categories/update', 'Api\CategoryController@update');

//Brand
Route::post('/brands', 'Api\BrandController@all');
Route::post('/brands/store', 'Api\BrandController@store');
Route::post('/brands/update', 'Api\BrandController@update');
Route::post('/brands/delete', 'Api\BrandController@delete');

//Supplier
Route::post('/suppliers', 'Api\SupplierController@all');
Route::post('/suppliers/store', 'Api\SupplierController@store');
Route::post('/suppliers/update', 'Api\SupplierController@update');

Route::post('/suppliers/createPaymentHistory', 'Api\SupplierController@createPaymentHistory');
Route::post('/suppliers/getPaymentHistory', 'Api\SupplierController@getPaymentHistory');

Route::post('/suppliers/storeRepaymentHistory', 'Api\SupplierController@storeRepaymentHistory');
Route::post('/suppliers/getRepaymentHistory', 'Api\SupplierController@getRepaymentHistory');

// Route::post('/suppliers/update', 'Api\SupplierController@update');

//Raw Material
Route::post('/raw_materials', 'Api\RawMaterialController@all');
Route::post('/raw_materials/store', 'Api\RawMaterialController@store');
Route::post('/raw_materials/update', 'Api\RawMaterialController@update');
Route::post('/raw_materials/store-purchase', 'Api\RawMaterialController@storePurchase');
Route::post('/raw_materials/stock-update', 'Api\RawMaterialController@stockUpdate');
Route::post('/raw_materials/purchase', 'Api\RawMaterialController@purchaseList');

//Product
Route::post('/products', 'Api\ProductController@all');
Route::get('/products/{id}', 'Api\ProductController@getProductData');
Route::post('/products/store', 'Api\ProductController@store');
Route::post('/products/delete', 'Api\ProductController@delete');
Route::post('/products/update', 'Api\ProductController@editProduct');
Route::post('/products/updatev2', 'Api\ProductController@editProductv2');
Route::post('/products/productDetails', 'Api\ProductController@productDetails');
Route::post('/products/storeIngredient', 'Api\ProductController@storeIngredient');
Route::post('/products/storeIngredientv2', 'Api\ProductController@storeIngredientv2');
Route::post('/products/storeOption', 'Api\ProductController@storeOption');
Route::post('/products/optionList'  , 'Api\ProductController@optionList');
Route::post('/products/editIngredient', 'Api\ProductController@editIngredient');
Route::post('/products/editIngredientv2', 'Api\ProductController@editIngredientv2');
Route::post('/test/product/{id}','Api\ProductController@test');
//Voucher
Route::post('/vouchers', 'Api\VoucherController@all');
Route::post('/vouchers/store', 'Api\VoucherController@store');
Route::post('/vouchers/storev2', 'Api\VoucherController@storev2');
Route::post('/vouchers/storeTest', 'Api\VoucherController@storeTest');
Route::post('/vouchers/deleteVoucher', 'Api\VoucherController@deleteVoucher');
Route::post('/vouchers/deleteVouchers', 'Api\VoucherController@deleteVouchers');
Route::post('/vouchers/voucherHistory', 'Api\VoucherController@voucherHistory');
Route::post('/vouchers/voucherDetail', 'Api\VoucherController@voucherDetail');
Route::post('/vouchers/voucherHistoryTest', 'Api\VoucherController@voucherHistoryTest');
Route::post('/vouchers/voucherDetailTest', 'Api\VoucherController@voucherDetailTest');
Route::post('/vouchers/getCupCount', 'Api\VoucherController@getCupCount');
Route::post('/vouchers/storeVipCard', 'Api\VoucherController@storeVipCard');
Route::post('/vouchers/profit', 'Api\VoucherController@profit');
Route::post('/vouchers/getMonthlySales', 'Api\VoucherController@getMonthlySales');
Route::post('/vouchers/best-sale', 'Api\VoucherController@getDailyBestSale');
Route::post('/vouchers/reorder', 'Api\VoucherController@getReorderList');

//Discount
Route::get('/discounts', 'Api\DiscountController@all');
Route::post('/discounts/store', 'Api\DiscountController@store');
Route::post('/discounts/discountForAllProduct', 'Api\DiscountController@discountForAllProduct');

//Promotion
Route::get('/promotions', 'Api\PromotionController@all');
Route::post('/promotions/store', 'Api\PromotionController@store');
Route::post('/promotions/update', 'Api\PromotionController@update');
Route::post('/promotions/getFoc', 'Api\PromotionController@getFoc'); // Loyalty Card Foc List
Route::post('/promotions/editFoc', 'Api\PromotionController@editFoc'); // Loyalty Card Foc Edit
Route::post('/promotions/cbpromohistory', 'Api\PromotionController@cbPromoHistory');
Route::post('/promotions/cbpromohistoryv2', 'Api\PromotionController@cbPromoHistory_v2');

//Customer
Route::get('/customers', 'Api\CustomerController@all');
Route::post('/customers/store', 'Api\CustomerController@store');
Route::post('/customers/update', 'Api\CustomerController@update');

//Employee
Route::post('/employees', 'Api\EmployeeController@all');
Route::post('/employees/store', 'Api\EmployeeController@store');
Route::post('/employees/update', 'Api\EmployeeController@update');

//get Payment Token from 2c2p
Route::post('/paymentToken','Api\CustomerController@paymentToken');
Route::post('/paymentTokenWith3ds','Api\CustomerController@paymentTokenWith3ds');
Route::post('/paymentInquiry','Api\CustomerController@paymentInquiry');
