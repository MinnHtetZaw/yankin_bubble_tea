<?php

namespace App\Http\Controllers\Api;

use App\Product;
use App\Voucher;
use App\Customer;
use App\CustomPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Client\ResponseSequence;

class PromotionController extends apiBaseController
{
    public function all(Request $request)
    {

        $promotions = CustomPromotion::all();

        return $this->sendResponse('promotions', $promotions);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "promotion_period_from" => "required",
            "promotion_period_to" => "required",
            "description" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('photo')) {

            $image = $request->file('topping_photo_path');
            $name = $image->getClientOriginalName();
            $image->move(public_path() . '/image/', $name);
            $image = $name;
        }

        $promotion = CustomPromotion::create([
            "name" => $request->name,
            "promotion_period_from" => $request->promotion_period_from,
            "promotion_period_to" => $request->promotion_period_to,
            "description" => $request->description,
            "photo" => $image ?? null,
            "condition" => $request->condition,
            "reward_flag" => $request->reward_flag,
            "discount_flag" => $request->discount_flag,
            "link_customer_flag" => $request->link_customer_flag ?? 0,
            "announce_customer_flag" => $request->announce_customer_flag ?? 0,
        ]);

        //Promotion for amount percent and product id
        if ($request->condition == 0) {
            $promotion->condition_amount = $request->condition_amount;
            $promotion->save();
        } elseif ($request->condition == 1) {
            $promotion->condition_product_id = $request->condition_product_id;
            $promotion->condition_product_qty = $request->condition_product_qty;

            $producut = Product::find($request->condition_product_id);
            $product->discount_promotion_id = $promotion->id;

            $product->save();
            $promotion->save();
        }

        if ($request->reward_flag == 0) {
            $promotion->cashback_amount = $request->cashback_amount;
            $promotion->save();
        } elseif ($request->reward_flag == 1) {

            if ($request->discount_flag == 0) {
                $promotion->custom_discount_id = $request->custom_discount_id;
                $promotion->save();
            } elseif ($request->discount_flag == 2) {
                $promotion->discount_percent = $request->discount_percent;
                $promotion->save();
            }
        } elseif ($request->reward_flag == 2) {
            $promotion->reward_product_id = $request->reward_product_id;
            $promotion->save();
        }

        $promotions = CustomPromotion::all();


        return $this->sendResponse('promotions', $promotions);
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "promotion_id" => "required",
            "reward_flag" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $promotion = CustomPromotion::find($request->promotion_id);

        $promotion->name = $request->name;
        $promotion->promotion_period_from = $request->promotion_period_from;
        $promotion->promotion_period_to = $request->promotion_period_to;
        $promotion->description = $request->description;

        $promotion->save();

        if ($request->reward_flag == 0) {

            $promotion->reward_flag = $request->reward_flag;

            $promotion->cashback_amount = $request->cashback_amount;

            $promotion->save();
        } elseif ($request->reward_flag == 1) {

            $promotion->reward_flag = $request->reward_flag;

            if ($request->discount_flag == 0) {

                $promotion->custom_discount_id = $request->custom_discount_id;

                $promotion->save();
            } elseif ($request->discount_flag == 2) {

                $promotion->discount_percent = $request->discount_percent;

                $promotion->save();
            }
        } elseif ($request->reward_flag == 2) {

            $promotion->reward_flag = $request->reward_flag;

            $promotion->reward_product_id = $request->reward_product_id;

            $promotion->save();
        }

        return $this->sendResponse('promotion', $promotion);
    }

    public function getFoc(Request $request)
    {

        $focs = Foc::all();

        return $this->sendResponse('foc_list', $focs);
    }

    public function editFoc(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "foc_id" => "required",
            "product_id" => "required",
            "count" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $foc = Foc::find($request->foc_id);
        $foc->product_id = $request->product_id;
        $foc->count = $request->count;
        $foc->save();

        return $this->sendResponse('foc_list', $foc);
    }

    public function cbPromoHistory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'start_timetick' => 'required',
            'end_timetick' => 'required',
            'cb_or_promo_flag' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }



        if ($request->has('page')) {
            $page_no = $request->page;
        } else {
            $page_no = 1;
        }
        $start_timetick = $request->start_timetick;
        $end_timetick = $request->end_timetick; //add to end_timetick because of android endtimetick is the 12:00 AM of the end date
        $cb_or_promo_flag = $request->cb_or_promo_flag;

        //$limit = 20;
        //$offset = ($page_no*$limit)-$limit;

        if ($cb_or_promo_flag == 1) {
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->where('vouchers.cashback', 1)->orderBy('vouchers.date')->get();
        } else {
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->whereNotNull('vouchers.promotion_id')->where('vouchers.promotion_id', '!=', 0)->orderBy('vouchers.date')->get();
        }

        // $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))
        // ->offset($offset)->take($limit)->orderBy('vouchers.date')->get();

        $cb_promo_collection = array();

        foreach ($vouchers as $voucher) {
            $customer = Customer::find($voucher->customer_id);

            $promotion = CustomPromotion::select('id', 'name', 'reward_flag', 'cashback_amount', 'discount_percent', 'reward_product_id')
                ->where('id', $voucher->promotion_id)->first();

            if (!empty($promotion)) {
                if ($promotion->reward_flag == 2) {
                    $product = Product::find($promotion->reward_product_id);
                    $promotion['reward_product_name'] = $product->name ?? null;
                }
            }
            if ($cb_or_promo_flag == 1) {
                $cb_promo_item = array(
                    "cb_promo_flag" => 1,
                    "voucher_id" => $voucher->id,
                    "voucher_number" => $voucher->voucher_number,
                    "voucher_date" => $voucher->date,
                    "voucher_total" => $voucher->voucher_grand_total,
                    "cashback_amount" => $voucher->cashback,
                    "customer_name" => $customer->name ?? null,
                );
            } else {
                if ($promotion->reward_flag == 1) {
                    $cb_promo_item = array(
                        "cb_promo_flag" => 2,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "discount_percent" => $promotion->discount_percent,
                        "discount_amount" => $voucher->voucher_grand_total * ($promotion->discount_percent / 100),
                        "customer_name" => $customer->name ?? null ,
                    );
                } else if ($promotion->reward_flag == 2) {
                    $cb_promo_item = array(
                        "cb_promo_flag" => 3,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "reward_product_name" => $promotion->reward_product_name,
                        "customer_name" => $customer->name ?? null,
                    );
                }
            }
            array_push($cb_promo_collection, $cb_promo_item);
        }
        return $this->sendResponse('cb_promo_collection', $cb_promo_collection);
    }
    
    public function cbPromoHistory_v2(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'start_timetick' => 'required',
            'end_timetick' => 'required',
            'cb_or_promo_flag' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }



        if ($request->has('page')) {
            $page_no = $request->page;
        } else {
            $page_no = 1;
        }
        $start_timetick = $request->start_timetick;
        $end_timetick = $request->end_timetick; //add to end_timetick because of android endtimetick is the 12:00 AM of the end date
        $cb_or_promo_flag = $request->cb_or_promo_flag;

        //$limit = 20;
        //$offset = ($page_no*$limit)-$limit;

        if ($cb_or_promo_flag == 1) {
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->where('vouchers.cashback_flag',1)->orderBy('vouchers.date')->get();
        }
        else if ($cb_or_promo_flag == 2 || $cb_or_promo_flag==3) {
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->whereNotNull('vouchers.promotion_id')->where('vouchers.promotion_id', '!=', 0)->orderBy('vouchers.date')->get();
        }
        else if ($cb_or_promo_flag == 4) {
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->where('vouchers.tax_flag',1)->orderBy('vouchers.date')->get();
        }
        else{
            $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))->orderBy('vouchers.date')->get();
        }

        // $vouchers = Voucher::whereBetween('vouchers.date', array($start_timetick, $end_timetick))
        // ->offset($offset)->take($limit)->orderBy('vouchers.date')->get();
        $cb_promo_collection = array();

        foreach ($vouchers as $voucher) {
            $customer = Customer::find($voucher->customer_id);

            $promotion = CustomPromotion::select('id', 'name', 'reward_flag', 'cashback_amount', 'discount_percent', 'reward_product_id')
                ->where('id', $voucher->promotion_id)->first();

            if (!empty($promotion)) {
                if ($promotion->reward_flag == 2) {
                    $product = Product::find($promotion->reward_product_id);
                    $promotion['reward_product_name'] = $product->name ?? null;
                }
            }
            if ($cb_or_promo_flag == 1) {
                $cb_promo_item = array(
                    "cb_promo_flag" => 1,
                    "voucher_id" => $voucher->id,
                    "voucher_number" => $voucher->voucher_number,
                    "voucher_date" => $voucher->date,
                    "voucher_total" => $voucher->voucher_grand_total,
                    "cashback_amount" => $voucher->cashback,
                    "tax_amount" => null,
                    "promotion_id" => null,
                    "promotion_name" => null,
                    "discount_percent" => null,
                    "discount_amount" => null,
                    "reward_product_name" => null,
                    "customer_name" => $customer->name ?? null,
                );
            array_push($cb_promo_collection, $cb_promo_item);

            } 
            else if ($cb_or_promo_flag == 2) {

                if ($promotion->reward_flag == 1) {
                    $cb_promo_item = array(
                       "cb_promo_flag" => 2,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "cashback_amount" => null,
                        "tax_amount" => null,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "discount_percent" => $promotion->discount_percent,
                        "discount_amount" => $voucher->voucher_grand_total * ($promotion->discount_percent / 100),
                        "reward_product_name" => null,
                        "customer_name" => $customer->name ?? null,
                    );
                } 
            array_push($cb_promo_collection, $cb_promo_item);

            }
            else if ($cb_or_promo_flag == 3) {
            
                if ($promotion->reward_flag == 2) {
                    $cb_promo_item = array(
                        "cb_promo_flag" => 3,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "cashback_amount" => null,
                        "tax_amount" => null,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "discount_percent" => null,
                        "discount_amount" => null,
                        "reward_product_name" => $promotion->reward_product_name,
                        "customer_name" => $customer->name ?? null,
                    );
            array_push($cb_promo_collection, $cb_promo_item);

                }
            }
            else if ($cb_or_promo_flag == 4) {
                $cb_promo_item = array(
                    "cb_promo_flag" => 1,
                    "voucher_id" => $voucher->id,
                    "voucher_number" => $voucher->voucher_number,
                    "voucher_date" => $voucher->date,
                    "voucher_total" => $voucher->voucher_grand_total,
                    "cashback_amount" => null,
                    "tax_amount" => $voucher->tax_amount,
                    "promotion_id" => null,
                    "promotion_name" => null,
                    "discount_percent" => null,
                    "discount_amount" => null,
                    "reward_product_name" => null,
                    "customer_name" => $customer->name ?? null,
                );
            array_push($cb_promo_collection, $cb_promo_item);

            }
         else if ((int)$cb_or_promo_flag == 5) {
            
            if ($voucher->cashback_flag==1) {
                $cb_promo_item = array(
                    "cb_promo_flag" => 1,
                    "voucher_id" => $voucher->id,
                    "voucher_number" => $voucher->voucher_number,
                    "voucher_date" => $voucher->date,
                    "voucher_total" => $voucher->voucher_grand_total,
                    "cashback_amount" => $voucher->cashback,
                    "tax_amount" => null,
                    "promotion_id" => null,
                    "promotion_name" => null,
                    "discount_percent" => null,
                    "discount_amount" => null,
                    "reward_product_name" => null,
                    "customer_name" => $customer->name ?? null,
                );
            } 
            else if($voucher->promotion_id){

                if ($promotion->reward_flag == 1) {
                    $cb_promo_item = array(
                       "cb_promo_flag" => 2,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "cashback_amount" => null,
                        "tax_amount" => null,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "discount_percent" => $promotion->discount_percent,
                        "discount_amount" => $voucher->voucher_grand_total * ($promotion->discount_percent / 100),
                        "reward_product_name" => null,
                        "customer_name" => $customer->name ?? null,
                    );
                } else if ($promotion->reward_flag == 2) {
                    $cb_promo_item = array(
                        "cb_promo_flag" => 3,
                        "voucher_id" => $voucher->id,
                        "voucher_number" => $voucher->voucher_number,
                        "voucher_date" => $voucher->date,
                        "voucher_total" => $voucher->voucher_grand_total,
                        "cashback_amount" => null,
                        "tax_amount" => null,
                        "promotion_id" => $voucher->promotion_id,
                        "promotion_name" => $promotion->name,
                        "discount_percent" => null,
                        "discount_amount" => null,
                        "reward_product_name" => $promotion->reward_product_name,
                        "customer_name" => $customer->name ?? null,
                    );
                }
            }
            else{
                $cb_promo_item = array(
                    "cb_promo_flag" => 4,
                    "voucher_id" => $voucher->id,
                    "voucher_number" => $voucher->voucher_number,
                    "voucher_date" => $voucher->date,
                    "voucher_total" => $voucher->voucher_grand_total,
                    "cashback_amount" => null,
                    "tax_amount" => null,
                    "promotion_id" => null,
                    "promotion_name" => null,
                    "discount_percent" => null,
                    "discount_amount" => null,
                    "reward_product_name" => null,
                    "customer_name" => $customer->name ?? null,
                );
            }
            array_push($cb_promo_collection, $cb_promo_item);

        }

        }
        return $this->sendResponse('cb_promo_collection', $cb_promo_collection);
    }
}
