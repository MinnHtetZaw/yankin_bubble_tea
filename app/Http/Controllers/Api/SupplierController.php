<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Supplier;
use App\SupplierPaymentHistory;
use App\SupplierRePaymentHistory;

class SupplierController extends apiBaseController
{

	public function all(Request $request){
        
        $suppliers = Supplier::all();

        return $this->sendResponse('suppliers', $suppliers);
    }

    public function store(Request $request){
        
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "address" => "required",
            "phone" => "required",
            "credit_amount" => "required",
            "repayment_period" => "required",
            "repayment_date" => "required",
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }
        
        $supplier = Supplier::create([
            "name" => $request->name,
            "address" => $request->address,
            "phone" => $request->phone,
            "brand_id" => $request->brand_id??null,
            "credit_amount" => $request->credit_amount,
            "repayment_period" => $request->repayment_period,
            "repayment_date" => $request->repayment_date,
        ]);
        
        $supplier_code = $this->generateCode('S',$supplier->id);
        $supplier->supplier_code = $supplier_code;
        $supplier->save();
            
        //John Edit for Zin Wah request
        $brand_lists = explode(",", $request->brand_id);
        
        foreach ($brand_lists as $brand) {
            
            $supplier->assignBrand($brand);
            
        }
        
        return $this->sendResponse('supplier', $supplier);
    }
    
    public function update(Request $request){

        $validator = Validator::make($request->all(), [
            "supplier_id" => "required",
            "name" => "required",
            "address" => "required",
            "phone" => "required",
            "credit_amount" => "required",
            "repayment_period" => "required",
            "repayment_date" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $supplier = Supplier::find($request->supplier_id);

        if(empty($supplier)){
            return $this->sendError('Supplier not found!');
        }
        
        $supplier->name = $request->name;
        $supplier->address = $request->address;
        $supplier->phone = $request->phone;
        $supplier->credit_amount = $request->credit_amount;
        $supplier->repayment_date = $request->repayment_date;
        $supplier->brand_id = $request->brand_id;

        $supplier->save();

        return $this->sendResponse('supplier' ,$supplier);
    }
    
    public function createPaymentHistory(Request $request){
        
        $supplier = Supplier::where("id", "=", $request->supplier_id)->first();
        
        SupplierPaymentHistory::create([
                "supplier_id" => $supplier->id,
                "purchase_id" => $request->purchase_id,
                "total_credit_amount" => $request->total_credit_amount,
                "remaining_amount" => $request->total_credit_amount,
                "payment_due_date" => $this->calculatePaymentDueDate($supplier->allow_credit_period, $request->purchase_timetick)
            ]);
    }
    
    public function getPaymentHistory(Request $request){
        
        
        $payments = SupplierPaymentHistory::all();
        return $this->sendResponse("payment_history", $payments);
    }
    
    public function storeRepaymentHistory(Request $request){
        $validator = Validator::make($request->all(), [
            "payment_id" => "required",
            "paid_amount" => "required"
            ]);
         if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }
        
        $payment = SupplierPaymentHistory::where("id", "=", $request->payment_id)->first();
        
        if(empty($payment)){
            return $this->sendError("Payment not found");
        }
        
        if($payment->remaining_amount < $request->paid_amount){
            return $this->sendError("Paid amount cannot be more than the remaining credit amount");
        }
        $repayment = SupplierRepaymentHistory::on($db_name)->create([
            "supplier_id" => $payment->supplier_id,
            "payment_id" => $request->payment_id,
            "paid_amount" => $request->paid_amount,
            "paid_timetick" => $request->paid_timetick
            ]);
        $payment->total_paid_amount = $payment->total_paid_amount + (int)$request->paid_amount;
        $payment->remaining_amount = $payment->remaining_amount - (int)$request->paid_amount;
        $payment->save();
        
        return $this->sendResponse("repayment_history", $repayment);
    }
}