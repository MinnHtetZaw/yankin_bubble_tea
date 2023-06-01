<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Brand;

class BrandController extends apiBaseController
{

    public function all(Request $request){
        
        $brands = Brand::all();

        return $this->sendResponse('brands', $brands);
    }
    
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "category_id" => "required",
            "country_of_origin" => "required"
        ]);
        if($validator->fails()){
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }
        
        $brand = Brand::create([
            // "branch_id" => $request->branch_id,  // comment off because we are going to use 0 as branch id to make all brand global according to kowinko request
            "category_id" => $request->category_id,
            "name" => $request->name,
            "country_of_origin" => $request->country_of_origin??"",
            "supplier_id" => $request->supplier_id??null,
        ]);

        $brand_code = $this->generateCode('B',$brand->id);
        $brand->brand_code = $brand_code;
        $brand->save();
        
        return $this->sendResponse('brand', $brand);
    }

    public function delete(Request $request){
        $brand = Brand::find($request->brand_id);

        $brand->delete();

        return $this->sendResponse('success','Succcessfully Deleted!');
    }
    
    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            "name" => "required",
            "country_of_origin" => "required",
            "brand_id" => "required"
        ]);
        
        if($validator->fails()){
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }
        
        $brand = Brand::where("id", $request->brand_id)->first();

        if (empty($brand)) {
            return $this->sendError('Brand not found!');
        }

        $brand->name = $request->name;
        $brand->country_of_origin = $request->country_of_origin;
        
        $brand->save();
        return $this->sendResponse("brand", $brand);
        
    }
}
