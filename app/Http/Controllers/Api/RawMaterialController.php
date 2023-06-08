<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\RawMaterial;
use App\Purchase;
use App\Supplier;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends apiBaseController
{
    public function all(Request $request){

        $raw_materials = RawMaterial::all();

        return $this->sendResponse('raw_materials', $raw_materials);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "brand_id" => "required",
            "category_id" => "required",
            "supplier_id" => "required",
            "instock_qty" => "required",
            "reorder_qty" => "required",
            "unit" => "required",
            "purchase_price" => "required",
            "currency" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('topping_photo_path')) {

			$image = $request->file('topping_photo_path');
			$name = $image->getClientOriginalName();
			$image->move(public_path() . '/image/', $name);
			$image = $name;
		}

        $raw_material = RawMaterial::create([
            "name" => $request->name,
            "category_id" => $request->category_id,
            "brand_id" => $request->brand_id,
            "supplier_id" => $request->supplier_id,
            "instock_qty" => $request->instock_qty,
            "reorder_qty" => $request->reorder_qty,
            "unit" => $request->unit,
            "purchase_price" => $request->purchase_price,
            "currency" => $request->currency,
            "special_flag" => $request->special_flag??0,
            "topping_flag" => $request->topping_flag??0,
        ]);

        if ($request->topping_flag == 1) {
        	$raw_material->topping_sales_amount = $request->topping_sales_amount;
        	$raw_material->topping_sales_price = $request->topping_sales_price;
        	$raw_material->topping_photo_path = $image;
        	$raw_material->save();
        }

        return $this->sendResponse('raw_material', $raw_material);
    }

    public function storev2(Request $request){

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "brand_id" => "required",
            "category_id" => "required",
            "supplier_id" => "required",
            "instock_qty" => "required",
            "reorder_qty" => "required",
            "unit" => "required",
            "purchase_price" => "required",
            "currency" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('topping_photo_path')) {

			$image = $request->file('topping_photo_path');
			$name = $image->getClientOriginalName();
			$image->move(public_path() . '/image/', $name);
			$image = $name;
		}

        $raw_material = RawMaterial::create([
            "name" => $request->name,
            "category_id" => $request->category_id,
            "brand_id" => $request->brand_id,
            "supplier_id" => $request->supplier_id,
            "instock_qty" => $request->instock_qty,
            "reorder_qty" => $request->reorder_qty,
            "unit" => $request->unit,
            "purchase_price" => $request->purchase_price,
            "currency" => $request->currency,
            "special_flag" => $request->special_flag??0,
            "topping_flag" => $request->topping_flag??0,
        ]);

        if ($request->topping_flag == 1) {
        	$raw_material->topping_sales_amount = $request->topping_sales_amount;
        	$raw_material->topping_sales_price = $request->topping_sales_price;
        	$raw_material->topping_deli_price = $request->topping_deli_price;
        	$raw_material->topping_photo_path = $image;
        	$raw_material->save();
        }

        return $this->sendResponse('raw_material', $raw_material);
    }

    public function update(Request $request){

        $validator = Validator::make($request->all(), [
            "raw_material_id" => "required",
            "name" => "required",
            "brand_id" => "required",
            "category_id" => "required",
            "supplier_id" => "required",
            "instock_qty" => "required",
            "reorder_qty" => "required",
            "unit" => "required",
            "purchase_price" => "required",
            "currency" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('topping_photo_path')) {

            $image = $request->file('topping_photo_path');
            $name = $image->getClientOriginalName();
            $image->move(public_path() . '/image/', $name);
            $image = $name;
        }

        $raw_material = RawMaterial::find($request->raw_material_id);

        if (empty($raw_material)) {
            return $this->sendError('RawMaterial Not Found');
        }

        $raw_material->name = $request->name;
        $raw_material->category_id = $request->category_id;
        $raw_material->brand_id = $request->brand_id;
        $raw_material->topping_photo_path = $request->image??null;
        $raw_material->supplier_id = $request->supplier_id;
        $raw_material->instock_qty = $request->instock_qty;
        $raw_material->reorder_qty = $request->reorder_qty;
        $raw_material->unit = $request->unit;
        $raw_material->purchase_price = $request->purchase_price;
        $raw_material->currency = $request->currency;
        $raw_material->special_flag = $request->special_flag??0;
        $raw_material->topping_flag = $request->topping_flag??0;
        $raw_material->save();

        if ($request->topping_flag == 1) {
            $raw_material->topping_sales_amount = $request->topping_sales_amount;
            $raw_material->topping_sales_price = $request->topping_sales_price;
            $raw_material->topping_photo_path = $image;
            $raw_material->save();
        }

        return $this->sendResponse('raw_material', $raw_material);

    }
    public function storePurchase(Request $request) {


        $purchase_by = $request->purchase_by;

        $total_amount = $request->total_amount;

    	$purchase = Purchase::create([

            'raw_material_id' => $request->raw_material_id??null,
            'purchase_qty' => $request->purchase_qty,
            'supplier_id' => $request->supplier_id,
            'timetick' => time(),
            'purchase_price' => $request->purchase_price??null,
            'purchase_by' => $request->purchase_by,
            'purchase_date' => $request->purchase_date,
            'total_amount' => $total_amount??0,
    	]);

    	$raw_material = RawMaterial::find($request->raw_material_id);

        if(empty($raw_material)) {
            return $this->sendError('Raw material not found');
        }

        $raw_material->instock_qty += $request->purchase_qty;
        $raw_material->save();

    	return $this->sendResponse('purchase', $purchase);
    }

    public function stockUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            "raw_material_id" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $raw_material = RawMaterial::find($request->raw_material_id);

        if(empty($raw_material)) {
            return $this->sendError('Raw material not found');
        }

        $raw_material->instock_qty += $request->income_qty;
        $raw_material->save();

        return $this->sendResponse('data',$raw_material);
    }

    public function purchaseList() {
        $purchase = Purchase::orderBy('purchase_date','desc')->get();

        /*$pur = Purchase::orderBy('id','desc')
            ->join('raw_materials', 'purchases.raw_material_id', '=', 'raw_materials.id')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select('purchases.*', 'raw_materials.instock_qty', 'suppliers.name')
            ->get();*/

        foreach($purchase as $p) {
            $raw_material = RawMaterial::find($p->raw_material_id);
            $supplier = Supplier::find($p->supplier_id);

            $p['raw_maetrial_name'] = $raw_material->name??null;
            $p['supplier_name'] = $supplier->name??null;
         }

         return $this->sendResponse('data',$purchase);
    }
}
