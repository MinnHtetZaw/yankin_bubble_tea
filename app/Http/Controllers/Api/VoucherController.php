<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use App\Product;
use App\RawMaterial;
use App\Price;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;
use App\Voucher;
use App\Profit;
use App\Option;
use App\RawMaterialProfit;
use App\LoyaltyCard;
use App\Foc;
use App\Customer;
use App\FocLog;
use App\VipCard;
use App\VoucherTest;
use App\CustomPromotion;

class VoucherController extends apiBaseController
{
    public function all(Request $request){

        $vouchers = Voucher::get('voucher_data');

        return $this->sendResponse('vouchers', $vouchers);
    }

    public function profit(Request $request){

        $month = date('m');
        $today = date('Y-m-d');

        $total_sales = DB::table('vouchers')->select(DB::raw('COALESCE(SUM(voucher_grand_total)) as total'))->get();
        $monthly = DB::table('vouchers')->select(DB::raw('COALESCE(SUM(voucher_grand_total)) as total'))->whereMonth('date',$month)->get();
        $product_sales = DB::table('profits')->select(DB::raw('COALESCE(SUM(total_profits)) as total'))->whereDate('voucher_date',$today)->get();
        $popping_sales = DB::table('raw_material_profits')->select(DB::raw('COALESCE(SUM(total_profits)) as total'))->get();
        $today_sales = DB::table('vouchers')->select(DB::raw('COALESCE(SUM(voucher_grand_total)) as total'))->whereDate('date',$today)->get();

        $product_qty_revenues = Profit::whereDate('voucher_date',$today)->orderBy('qty','desc')->get();

        foreach($product_qty_revenues as $revenue) {
            $product = Product::find($revenue->product_id);
            $size = Price::find($revenue->price_id);
            $revenue['product_name'] = $product->name;
            $revenue['size_name'] = $size->size;
            $revenue['product_image'] = url("/").'/image/product/'.$product->photo;
        }

        return response()->json([

            'total_sales' => getIntValue($total_sales),
            'today_sales' => getIntValue($today_sales),
            'monthly_sales' => getIntValue($monthly),
            'product_sales' => getIntValue($product_sales),
            'popping_sales' => getIntValue($popping_sales),
            'product_qty' => $product_qty_revenues,
            'success' => true,
            'message' => "successful"

        ]);

    }

    public function getDailyBestSale(Request $request){

        $validator = Validator::make($request->all(), [
            "date" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }


        $product_qty_revenues = Profit::whereDate('voucher_date',$request->date)->orderBy('qty','desc')->get();

        foreach($product_qty_revenues as $revenue) {
            $product = Product::find($revenue->product_id);
            $size = Price::find($revenue->price_id);
            $revenue['product_name'] = $product->name;
            $revenue['size_name'] = $size->size;
            $revenue['product_image'] = url("/").'/image/product/'.$product->photo;
        }

        return $this->sendResponse('data',$product_qty_revenues);
    }

    public function getReorderList(Request $request) {
        $raw_material = [];
        $raw_materials = RawMaterial::all();

        foreach($raw_materials as $raw) {
            if($raw->reorder_qty > $raw->instock_qty) {
                array_push($raw_material,$raw);
            }
        }

        return $this->sendResponse('data',$raw_material);
    }

    public function getMonthlySales(Request $request){

        $monthly = date('m',strtotime($request->month));

        $monthly_sales = DB::table('vouchers')->select(DB::raw('COALESCE(SUM(voucher_grand_total)) as total'))->whereMonth('date',$monthly)->get();

        $monthly_products_sales = DB::table('profits')->select(DB::raw('COALESCE(SUM(total_profits)) as total'))->whereMonth('voucher_date',$monthly)->get();

        $monthly_popping_sales = DB::table('raw_material_profits')->select(DB::raw('COALESCE(SUM(total_profits)) as total'))->whereMonth('voucher_date',$monthly)->get();
        $monthly_popping_sale = DB::table('raw_material_profits')->select(DB::raw('COALESCE(SUM(total_profits)) as total ,MONTH(voucher_date) as month, YEAR(voucher_date) as year'))->groupBy(DB::raw('YEAR(voucher_date) ASC, MONTH(voucher_date) ASC'))->get();

        return response()->json([

            'monthly_sales' => getIntValue($monthly_sales),
            'monthly_products_sales' => getIntValue($monthly_products_sales),
            'monthly_popping_sales' => getIntValue($monthly_popping_sales),
            'monthly_popping_sale' => $monthly_popping_sale,
            'success' => true,
            'message' => "successful"

        ]);

    }

    public function reduceRawMaterial($product_id){

        $product = Product::find($product_id);

        $sizes = Price::where('size','Medium')
            ->where('product_id',$product->id)
            ->first();

        if ($sizes->ingredients != null) {

            foreach ($sizes->ingredients as $ingredient) {

                $raw_qty = $ingredient->amount;

                $raw_material = RawMaterial::find($ingredient->raw_material_id);

                if ( $raw_qty > $raw_material->instock_qty ) {
                    return $this->sendError('Stock Error');
                }else{

                    $raw_material->instock_qty -= $raw_qty;
                    $raw_material->save();
                }

            }
        }

    }

    //Check how many Foc Giveaway for today
    public function focLog($card_number, $product_id){

        FocLog::create([
            'loyalty_card_number' => $card_number,
            'product_id' => $product_id,
            'pay_date' => date('Y-m-d'),
        ]);

    }

    public function store(Request $request){

        /*$validator = Validator::make($request->all(), [
            "name" => "required",
            "category_id" => "required",
            "description" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }*/

        $grand_total = 0;
        $raw_qty = 0;
        $total_profits = 0;
        $order_qty = 0;


        foreach ($request->voucher as $value) {
            $grand_total += $value['totalPrice'];

            $today = date('Y-m-d');

            $total_profits = $value['totalPrice'];

            $product = Product::find($value['id']);

            $order_qty += $value['order_qty'];



            $size = Price::where('size',$value['size'])
                ->where('product_id',$value['id'])
                ->first();

            //retreat available profit
            $profits = Profit::where('product_id',$value['id'])
                        ->where('price_id',$size->id)
                        ->where('voucher_date',$request->backdate??$today)
                        ->first();

            if (empty($profits)) {
                Profit::create([
                    'product_id' => $value['id'],
                    'price_id' => $size->id,
                    'qty' => $value['order_qty'],
                    'total_profits' => $value['selling_price'] * $value['order_qty'],
                    'voucher_date' => $today,
                ]);
            }else{
                $profits->total_profits += $value['selling_price'] * $value['order_qty'];
                $profits->qty += $value['order_qty']; //add qty to product revenue if available profit
                $profits->save();
            }

            //decreasing from raw material instock qty when product is with option id if not null option
            if ($value['option_flag'] == 1) {
                $option = Option::find($value['option_id']);

                $option_qty = $option->amount * $value['order_qty'];

                $raw_material = RawMaterial::find($option->raw_material_id);
                $raw_material->instock_qty -= $option_qty;
                // $raw_material->save();
            }

            //decreasing from raw material when voucher ingredient of product
            foreach ($size->ingredients as $ingredient) {

                $raw_qty = $ingredient->amount * $value['order_qty'];

                $raw_material = RawMaterial::find($ingredient->raw_material_id);

                if ( $raw_qty > $raw_material->instock_qty ) {
                    return $this->sendError('Stock Error');
                }else{

                    $raw_material->instock_qty -= $raw_qty;
                    $raw_material->save();
                }

            }

            //decresing from raw material when poping is in voucher
            if (!empty($value['poping_list'])) {
                foreach ($value['poping_list'] as $poping) {

                    $raw_material_qty = $poping['raw_material_qty'];

                    $raw_material = RawMaterial::find($poping['raw_material_id']);

                    $raw_sales = $poping['raw_material_price'];

                    if ( $raw_material->instock_qty < $raw_material_qty ) {
                        return $this->sendError('Stock Error');
                    }else{

                        $raw_material->instock_qty -= $raw_material_qty;
                        $raw_material->save();

                    }

                    $raw_profits = RawMaterialProfit::where('raw_material_id',$poping['raw_material_id'])
                            ->where('voucher_date',$today)
                            ->first();

                    if (empty($raw_profits)) {
                        RawMaterialProfit::create([
                            'raw_material_id' => $poping['raw_material_id'],
                            'total_profits' => $raw_sales,
                            'voucher_date' => $today,
                        ]);
                    }else{
                        $raw_profits->total_profits += $raw_sales;
                        $raw_profits->save();
                    }

                }
            }

        }

        // if (isset($request->card_number)) {

        //     $card_number = $request->card_number;

        //     $loyalty_cards = LoyaltyCard::where('card_number',$card_number)
        //                 ->count();

        //     if ($loyalty_cards == 0) {

        //         $loyalty_card = LoyaltyCard::create([
        //             'card_number' => $card_number,
        //             'customer_id' => $request->customer_id??null,
        //             'customer_name' => $request->customer_name??null,
        //             'product_id' => $request->promotion_id,
        //             'promotion_id' => $request->promotion_id??1,
        //             'count' => $order_qty,
        //             'status' => 0,
        //         ]);

        //         $focs = Foc::all();
        //         $loyalty_card->assignFoc($focs);

        //     }else{

        //         $loyalty_card = LoyaltyCard::where('card_number',$card_number)
        //                     ->where('status',0)
        //                     ->first();

        //         $count = $loyalty_card->count;

        //         $loyalty_card->count += $order_qty;

        //         $loyalty_card->save();

        //         if ( $loyalty_card->count >= 7 && $loyalty_card->count < 14) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(1);
        //                     $this->focLog($loyalty_card->card_number,1);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();
        //             }


        //         }elseif ( $loyalty_card->count >= 14 && $loyalty_card->count < 21 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(1);
        //                     $this->focLog($loyalty_card->card_number,1);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();

        //             }

        //         }elseif ( $loyalty_card->count >= 21 && $loyalty_card->count < 28 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(2);
        //                     $this->focLog($loyalty_card->card_number,2);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();
        //             }

        //         }elseif ( $loyalty_card->count >= 28 && $loyalty_card->count < 35 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(2);
        //                     $this->focLog($loyalty_card->card_number,2);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();

        //             }

        //         }elseif ( $loyalty_card->count >= 35 ) {

        //             $this->reduceRawMaterial(3);
        //             $this->focLog($loyalty_card->card_number,3);

        //             $loyalty_card->status = 1;
        //             $loyalty_card->save();

        //             if ($request->vip != null) {

        //                 $first_card = VipCard::create([
        //                     'loyalty_number' => $loyalty_card->card_number,
        //                     'card_number' => $request->card_number,
        //                     'customer_name' => $request->customer_name,
        //                     'customer_id' => $request->customer_id??null,
        //                     'discount' => 5,
        //                 ]);

        //                 Customer::create([
        //                     'customer_name' => $request->customer_name,
        //                     'customer_id' => $request->customer_id??null,
        //                     'vipcard_number' => $first_card->card_number,
        //                     'email' => $request->email??null,
        //                     'discount_percent' => $request->discount_percent,
        //                     'address' => $request->address??null,
        //                 ]);

        //             }

        //         }

        //     }

        // }

        // if ($request->vip_card != null) {

        //     $vip_card = VipCard::where('card_number',$request->vip_card)->first();
        //     $vip_card->consume = $grand_total;
        //     $vip_card->save();

        // }

        $voucher = Voucher::create([
            'voucher_data' => json_encode($request->voucher),
            'voucher_grand_total' => $request->grand_total,
            'total' => $request->total??0,
            'customer_id' => $request->customer_id??null,
            'promotion_id' => $request->promotion_id??null,
            'employee_name' => $request->employee_name,
            'sold_by' => $request->sold_by,
            'date' => $request->backdate_flag==0?date('Y-m-d'):$request->backdate,
            'cashback_flag' => $request->cashback_flag??0,
            'cashback' => $request->cashback??0,
        ]);

        $voucher->voucher_number = sprintf("%05s", $voucher->id);
        $voucher->save();

        $customer = Customer::find($voucher->customer_id);

        if($request->promotion_id) {
            $promotion = CustomPromotion::select('id','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$request->promotion_id)->first();
        }

        $customer = Customer::find($voucher->customer_id);

        return response()->json([
            'voucher_number' => $voucher->voucher_number,
            'promotion' => $promotion??null,
            'voucher' => $voucher->voucher_data,
            'customer' => $customer??'No Customer',
            'success' => true,
            'message' => 'Successfully print Voucher',
        ]);

        // return $this->sendResponse('voucher',$voucher->voucher_data);

    }

    public function storev2(Request $request){

        /*$validator = Validator::make($request->all(), [
            "name" => "required",
            "category_id" => "required",
            "description" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }*/

        $grand_total = 0;
        $raw_qty = 0;
        $total_profits = 0;
        $order_qty = 0;


        foreach ($request->voucher as $value) {
            $grand_total += $value['totalPrice'];

            $today = date('Y-m-d');

            $total_profits = $value['totalPrice'];

            $product = Product::find($value['id']);

            $order_qty += $value['order_qty'];



            $size = Price::where('size',$value['size'])
                ->where('product_id',$value['id'])
                ->first();

            //retreat available profit
            $profits = Profit::where('product_id',$value['id'])
                        ->where('price_id',$size->id)
                        ->where('voucher_date',$request->backdate??$today)
                        ->first();

            if (empty($profits)) {
                Profit::create([
                    'product_id' => $value['id'],
                    'price_id' => $size->id,
                    'qty' => $value['order_qty'],
                    'total_profits' => $value['selling_price'] * $value['order_qty'],
                    'voucher_date' => $today,
                ]);
            }else{
                $profits->total_profits += $value['selling_price'] * $value['order_qty'];
                $profits->qty += $value['order_qty']; //add qty to product revenue if available profit
                $profits->save();
            }

            //decreasing from raw material instock qty when product is with option id if not null option
            if ($value['option_flag'] == 1) {
                $option = Option::find($value['option_id']);

                $option_qty = $option->amount * $value['order_qty'];

                $raw_material = RawMaterial::find($option->raw_material_id);
                $raw_material->instock_qty -= $option_qty;
                // $raw_material->save();
            }

            //decreasing from raw material when voucher ingredient of product
            foreach ($size->ingredients as $ingredient) {

                $raw_qty = $ingredient->amount * $value['order_qty'];

                $raw_material = RawMaterial::find($ingredient->raw_material_id);

                if ( $raw_qty > $raw_material->instock_qty ) {
                    return $this->sendError('Stock Error');
                }else{

                    $raw_material->instock_qty -= $raw_qty;
                    $raw_material->save();
                }

            }

            //decresing from raw material when poping is in voucher
            if (!empty($value['poping_list'])) {
                foreach ($value['poping_list'] as $poping) {

                    $raw_material_qty = $poping['raw_material_qty'];

                    $raw_material = RawMaterial::find($poping['raw_material_id']);

                    $raw_sales = $poping['raw_material_price'];

                    if ( $raw_material->instock_qty < $raw_material_qty ) {
                        return $this->sendError('Stock Error');
                    }else{

                        $raw_material->instock_qty -= $raw_material_qty;
                        $raw_material->save();

                    }

                    $raw_profits = RawMaterialProfit::where('raw_material_id',$poping['raw_material_id'])
                            ->where('voucher_date',$today)
                            ->first();

                    if (empty($raw_profits)) {
                        RawMaterialProfit::create([
                            'raw_material_id' => $poping['raw_material_id'],
                            'total_profits' => $raw_sales,
                            'voucher_date' => $today,
                        ]);
                    }else{
                        $raw_profits->total_profits += $raw_sales;
                        $raw_profits->save();
                    }

                }
            }

        }

        // if (isset($request->card_number)) {

        //     $card_number = $request->card_number;

        //     $loyalty_cards = LoyaltyCard::where('card_number',$card_number)
        //                 ->count();

        //     if ($loyalty_cards == 0) {

        //         $loyalty_card = LoyaltyCard::create([
        //             'card_number' => $card_number,
        //             'customer_id' => $request->customer_id??null,
        //             'customer_name' => $request->customer_name??null,
        //             'product_id' => $request->promotion_id,
        //             'promotion_id' => $request->promotion_id??1,
        //             'count' => $order_qty,
        //             'status' => 0,
        //         ]);

        //         $focs = Foc::all();
        //         $loyalty_card->assignFoc($focs);

        //     }else{

        //         $loyalty_card = LoyaltyCard::where('card_number',$card_number)
        //                     ->where('status',0)
        //                     ->first();

        //         $count = $loyalty_card->count;

        //         $loyalty_card->count += $order_qty;

        //         $loyalty_card->save();

        //         if ( $loyalty_card->count >= 7 && $loyalty_card->count < 14) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(1);
        //                     $this->focLog($loyalty_card->card_number,1);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();
        //             }


        //         }elseif ( $loyalty_card->count >= 14 && $loyalty_card->count < 21 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(1);
        //                     $this->focLog($loyalty_card->card_number,1);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();

        //             }

        //         }elseif ( $loyalty_card->count >= 21 && $loyalty_card->count < 28 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(2);
        //                     $this->focLog($loyalty_card->card_number,2);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();
        //             }

        //         }elseif ( $loyalty_card->count >= 28 && $loyalty_card->count < 35 ) {

        //             if ($request->reward == "accept") {

        //                     $this->reduceRawMaterial(2);
        //                     $this->focLog($loyalty_card->card_number,2);

        //             }elseif ( $request->reward == "add" ) {

        //                 $loyalty_card->count += 1;
        //                 $loyalty_card->save();

        //             }

        //         }elseif ( $loyalty_card->count >= 35 ) {

        //             $this->reduceRawMaterial(3);
        //             $this->focLog($loyalty_card->card_number,3);

        //             $loyalty_card->status = 1;
        //             $loyalty_card->save();

        //             if ($request->vip != null) {

        //                 $first_card = VipCard::create([
        //                     'loyalty_number' => $loyalty_card->card_number,
        //                     'card_number' => $request->card_number,
        //                     'customer_name' => $request->customer_name,
        //                     'customer_id' => $request->customer_id??null,
        //                     'discount' => 5,
        //                 ]);

        //                 Customer::create([
        //                     'customer_name' => $request->customer_name,
        //                     'customer_id' => $request->customer_id??null,
        //                     'vipcard_number' => $first_card->card_number,
        //                     'email' => $request->email??null,
        //                     'discount_percent' => $request->discount_percent,
        //                     'address' => $request->address??null,
        //                 ]);

        //             }

        //         }

        //     }

        // }

        // if ($request->vip_card != null) {

        //     $vip_card = VipCard::where('card_number',$request->vip_card)->first();
        //     $vip_card->consume = $grand_total;
        //     $vip_card->save();

        // }

        $voucher = Voucher::create([
            'voucher_data' => json_encode($request->voucher),
            'voucher_grand_total' => $request->grand_total,
            'total' => $request->total??0,
            'customer_id' => $request->customer_id??null,
            'promotion_id' => $request->promotion_id??null,
            'employee_name' => $request->employee_name,
            'sold_by' => $request->sold_by,
            'date' => $request->backdate_flag==0?date('Y-m-d'):$request->backdate,
            'cashback_flag' => $request->cashback_flag??0,
            'cashback' => $request->cashback??0,
            'tax_flag' => $request->tax_flag??0,
            'tax_amount' => $request->taxamount??0,
        ]);

        $voucher->voucher_number = sprintf("%05s", $voucher->id);
        $voucher->save();

        $customer = Customer::find($voucher->customer_id);

        if($request->promotion_id) {
            $promotion = CustomPromotion::select('id','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$request->promotion_id)->first();
        }

        $customer = Customer::find($voucher->customer_id);

        return response()->json([
            'voucher_number' => $voucher->voucher_number,
            'promotion' => $promotion??null,
            'voucher' => $voucher->voucher_data,
            'customer' => $customer??'No Customer',
            'success' => true,
            'message' => 'Successfully print Voucher',
        ]);

        // return $this->sendResponse('voucher',$voucher->voucher_data);

    }

    public function storeTest(Request $request){

        $grand_total = 0;

        foreach ($request->voucher as $value) {
            $grand_total += $value['totalPrice'];

            $today = date('Y-m-d');

        }

        $voucher = VoucherTest::create([
            'voucher_data' => json_encode($request->voucher),
            'voucher_grand_total' => $request->grand_total,
            'total' => $request->total??0,
            'customer_id' => $request->customer_id??null,
            'promotion_id' => $request->promotion_id??null,
            'employee_name' => $request->employee_name,
            'sold_by' => $request->sold_by,
            'date' => $request->backdate_flag==0?date('Y-m-d'):$request->backdate,
            'cashback_flag' => $request->cashback_flag??0,
            'cashback' => $request->cashback??0,
        ]);

        $voucher->voucher_number = sprintf("%05s", $voucher->id);
        $voucher->save();

        if($request->promotion_id) {
            $promotion = CustomPromotion::select('id','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$request->promotion_id)->first();
        }

        $customer = Customer::find($voucher->customer_id);

        return response()->json([
            'voucher_number' => $voucher->voucher_number,
            'promotion' => $promotion??null,
            'voucher' => $voucher->voucher_data,
            'customer' => $customer??'No Customer',
            'success' => true,
            'message' => 'Successfully print Voucher',
        ]);

    }

    public function deleteVoucher(Request $request){

        $validator = Validator::make($request->all(), [
            "voucher_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }


        $voucher = Voucher::find($request->voucher_id);
        if(empty($voucher)) {
            return $this->sendError('204','Voucher not found!');
        }

        $grand_total = 0;
        $raw_qty = 0;
        $total_profits = 0;
        $order_qty = 0;


        foreach ($voucher->voucher_data as $value) {
            // $grand_total += $value['totalPrice'];

            $today = date('Y-m-d');

            $total_price = $value->selling_price * $value->order_qty; //total price of product

            $promotion = CustomPromotion::find($voucher->promotion_id);

            $percent = 0; // assign percent if promotion is available

            //calc promotion for total_price if available promotion
            if(!empty($promotion)){
                if($promotion->reward_flag == 0){
                    $total_price = $total_price - $promotion->cashback_amount;
                }elseif($promotion->reward_flag == 1){
                    $percent = $promotion->discount_percent/100 * $total_price;

                    $total_price = $total_price - $percent;

                }
            }

            $customer = Customer::find($voucher->customer_id);

            if(!empty($customer)) {

                $total_price = round($total_price + ($customer->tax_percent/100 * $total_price));

            }

            $grand_total += $total_price;

            $total_profits = $value->selling_price;

            $product = Product::find($value->id);

            $order_qty += $value->order_qty;



            $size = Price::where('size',$value->size)
                ->where('product_id',$value->id)
                ->first();

            //retreat available profit
            $profits = Profit::where('product_id',$value->id)
                        ->where('price_id',$size->id)
                        ->where('voucher_date',$voucher->date)
                        ->first();

            if (!empty($profits)) {
                $profits->total_profits -= $total_price;
                $profits->qty -= $value->order_qty;
                $profits->save();
            }

            //decreasing from raw material instock qty when product is with option id if not null option
            if ($value->option_flag == 1) {
                $option = Option::find($value->option_id);

                $option_qty = $option->amount * $value->order_qty;

                $raw_material = RawMaterial::find($option->raw_material_id);
                $raw_material->instock_qty += $option_qty;
                $raw_material->save();
            }

            //decreasing from raw material when voucher ingredient of product
            foreach ($size->ingredients as $ingredient) {

                $raw_qty = $ingredient->amount * $value->order_qty;

                $raw_material = RawMaterial::find($ingredient->raw_material_id);

                if ( $raw_qty > $raw_material->instock_qty ) {
                    return $this->sendError('Stock Error');
                }else{

                    $raw_material->instock_qty += $raw_qty;
                    $raw_material->save();
                }

            }

            //decresing from raw material when poping is in voucher
            if (!empty($value->poping_list)) {
                foreach ($value->poping_list as $poping) {

                    $raw_material_qty = $poping->raw_material_qty;

                    $raw_material = RawMaterial::find($poping->raw_material_id);

                    $raw_sales = $poping->raw_material_price;

                    if ( $raw_material->instock_qty < $raw_material_qty ) {
                        return $this->sendError('Stock Error');
                    }else{

                        $raw_material->instock_qty += $raw_material_qty;
                        $raw_material->save();

                    }

                    $raw_profits = RawMaterialProfit::where('raw_material_id',$poping->raw_material_id)
                            ->where('voucher_date',$voucher->date)
                            ->first();

                    if (!empty($raw_profits)) {

                        $promotion = CustomPromotion::find($voucher->promotion_id);

                        if(!empty($promotion)) {
                            if($promotion->reward_flag == 1) {
                                $raw_sales = $raw_sales - ($promotion->discount_percent/100 * $raw_sales);
                            }
                        }

                        $customer = Customer::find($voucher->customer_id);

                        if(!empty($customer)) {
                            $raw_sales = round($raw_sales + ($customer->tax_percent/100 * $raw_sales));
                        }

                        $grand_total += $raw_sales;

                        $raw_profits->total_profits -= $raw_sales;
                        $raw_profits->save();
                    }

                }
            }

        }
        $voucher->delete();
        return $this->sendResponse('success','Succcessfully Deleted!');

    }


    public function storeVipCard(Request $request){

        $first_card = VipCard::create([
            'loyalty_number' => $request->loyalty_card_number,
            'card_number' => $request->card_number,
            'customer_name' => $request->customer_name,
            'customer_id' => $request->customer_id??null,
            'discount' => 5,
        ]);

        Customer::create([
            'name' => $request->customer_name,
            'customer_id' => $request->customer_id??null,
            'customer_code' => $request->customer_code??null,
            'vipcard_number' => $first_card->card_number,
            'email' => $request->email??null,
            'discount_percent' => $request->discount_percent,
            'address' => $request->address??null,
            'phone' => $request->phone,
        ]);

        return $this->sendResponse('vip_card',$first_card);

    }

    public function getCupCount(Request $request){

        $validator = Validator::make($request->all(), [
            'card_number' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $loyalty_card = LoyaltyCard::where('card_number',$request->card_number)->first();

        if (empty($loyalty_card)) {
            return $this->sendError('Card Not Found');
        }
        $foc = $loyalty_card->focs;

        $count = $loyalty_card->count;

        switch ($count) {
            case $count >= 7 && $count <= 13:
                return response()->json([
                            'foc' => $foc[0],
                            'count' => $count,
                            'success' => true,
                            'message' => 'successful',
                        ]);
                break;
            case $count >= 14  && $count <= 20:
                return response()->json([
                            'foc' => $foc[1],
                            'count' => $count,
                            'success' => true,
                            'message' => 'successful',
                        ]);
                break;
            case $count >= 21  && $count <= 27:
                return response()->json([
                            'foc' => $foc[2],
                            'count' => $count,
                            'success' => true,
                            'message' => 'successful',
                        ]);
                break;
            case $count >= 28  && $count <= 34:
                return response()->json([
                            'foc' => $foc[3],
                            'count' => $count,
                            'success' => true,
                            'message' => 'successful',
                        ]);
                break;
            case $count >= 35 && $count <=40:
                return response()->json([
                            'foc' => $foc[4],
                            'count' => $count,
                            'success' => true,
                            'message' => 'successful',
                        ]);
                break;

            default:
                return $this->sendError('This Card is Already Done for Free of charges');
                break;
        }

    }

    public function voucherHistory(Request $request){

        $validator = Validator::make($request->all(), [
            'start_timetick' => 'required',
            'end_timetick' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if($request->has('page')){
            $page_no = $request->page;
        }else{
            $page_no = 1;
        }

        $start_timetick = $request->start_timetick;
        $end_timetick = $request->end_timetick; //add to end_timetick because of android endtimetick is the 12:00 AM of the end date

        $limit = 20;
        $offset = ($page_no*$limit)-$limit;

        $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))
        ->orderBy('vouchers.date')->get();

        // $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))
        // ->offset($offset)->take($limit)->orderBy('vouchers.date')->get();

        foreach($vouchers as $voucher) {
            $customer = Customer::find($voucher->customer_id);
            $promotion = CustomPromotion::select('id','name','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$voucher->promotion_id)->first();

            if(!empty($promotion)){
                $product = Product::find($promotion->reward_product_id);
                $promotion['reward_product_name'] = $product->name??null;

            }

            $voucher['customer_name'] = $customer->name??null;
            $voucher['customer_tax'] = $customer->tax_percent??null;
            $voucher['promotion'] = $promotion??null;
        }

        return $this->sendResponse('vouchers', $vouchers);

    }
    public function voucherHistoryTest(Request $request){

        $validator = Validator::make($request->all(), [
            'start_timetick' => 'required',
            'end_timetick' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if($request->has('page')){
            $page_no = $request->page;
        }else{
            $page_no = 1;
        }

        $start_timetick = $request->start_timetick;
        $end_timetick = $request->end_timetick; //add to end_timetick because of android endtimetick is the 12:00 AM of the end date

        $limit = 20;
        $offset = ($page_no*$limit)-$limit;

        $vouchers = VoucherTest::whereBetween('voucher_tests.date', array($start_timetick, $end_timetick))
        ->offset($offset)->take($limit)->orderBy('voucher_tests.date')->get();

        foreach($vouchers as $voucher) {
            $customer = Customer::find($voucher->customer_id);
            $promotion = CustomPromotion::select('id','name','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$voucher->promotion_id)->first();

            if(!empty($promotion)){
                $product = Product::find($promotion->reward_product_id);
                $promotion['reward_product_name'] = $product->name??null;

            }

            $voucher['customer_name'] = $customer->name??null;
            $voucher['customer_tax'] = $customer->tax_percent??null;
            $voucher['promotion'] = $promotion??null;
        }

        return $this->sendResponse('vouchers', $vouchers);

    }
    public function voucherDetail(Request $request){
        // $validator = Validator::make($request->all(), [
        //     'voucher_id' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        // }

        // $voucher = Voucher::find($request->voucher_id);
        // $customer = Customer::find($voucher->customer_id);
        // $voucher['customer_name'] = $customer->name??null;
        // $voucher['customer_tax'] = $customer->tax_percent??null;

        // return $this->sendResponse('voucher',$voucher);
        $validator = Validator::make($request->all(), [
            'voucher_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $voucher = Voucher::find($request->voucher_id);
        $customer = Customer::find($voucher->customer_id);
        $promotion = CustomPromotion::select('id','name','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$voucher->promotion_id)->first();

        if(!empty($promotion)){
            $product = Product::find($promotion->reward_product_id);
            $promotion['reward_product_name'] = $product->name??null;

        }

        $voucher['customer_name'] = $customer->name??null;
        $voucher['customer_tax'] = $customer->tax_percent??null;
        $voucher['promotion'] = $promotion??null;

        return $this->sendResponse('voucher',$voucher);
    }
    public function voucherDetailTest(Request $request){
        $validator = Validator::make($request->all(), [
            'voucher_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $voucher = VoucherTest::find($request->voucher_id);
        $customer = Customer::find($voucher->customer_id);
        $promotion = CustomPromotion::select('id','name','reward_flag','cashback_amount','discount_percent','reward_product_id')
                    ->where('id',$voucher->promotion_id)->first();

        if(!empty($promotion)){
            $product = Product::find($promotion->reward_product_id);
            $promotion['reward_product_name'] = $product->name??null;

        }

        $voucher['customer_name'] = $customer->name??null;
        $voucher['customer_tax'] = $customer->tax_percent??null;
        $voucher['promotion'] = $promotion??null;

        return $this->sendResponse('voucher',$voucher);
    }
}
