<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Product;
use App\Price;
use App\Ingredient;
use App\Option;
use App\RawMaterial;
use App\CustomDiscount;

class ProductController extends apiBaseController
{
    public function all(){

        $products = Product::with('ingredients')->orderBy('display_index','ASC')->get();

        foreach ($products as $product) {
            if ($product->custom_discount_id != null) {
                $custom_discount = CustomDiscount::find($product->custom_discount_id);
                $product['discount'] = $custom_discount;
            }


            if($product->photo){

                $product->photo = url("/").'/image/product/'.$product->photo;
            }
            else{
                $product->photo = url("/").'/image/product/default.png';
            }

        }

        return $this->sendResponse('products', $products);

    }

    public function getProductData($id){
        $data = Product::with('ingredients')->find($id);

            $sizes=Price::where('product_id',$data->id)->with('ingredients')->get();

     foreach( $sizes as $sizeData){

        $size_name=$sizeData->size;
        $sellprice=$sizeData->sell_price;
        $deliprice=$sizeData->deli_price;

        foreach($data->ingredients as $ingredient){
            foreach($sizeData['ingredients'] as $singleData){
                if($singleData->id == $ingredient->id){

                    $rawMaterial=RawMaterial::find($ingredient->raw_material_id);
                    $ingredient->raw_material_name=$rawMaterial->name;
                    $ingredient->size_name= $size_name;
                    $ingredient->sellprice= $sellprice;
                    $ingredient->deliprice= $deliprice;
                }

            }

        }
    }
        $data->save();

        return response()->json(['products'=>$data]);

    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "category_id" => "required",
            "description" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasFile('photo')) {

            $image = $request->file('photo');

            $name = $image->getClientOriginalName();
           $image->move(public_path().'/image/product/', $name);
            $image = $name;
        }

        $product = Product::create([
            "name" => $request->name,
            "category_id" => $request->category_id,
            "description" => $request->description,
            "photo" => $image??'default.png',
            "display_index"=>$request->display_index ?? 0,
            "option_flag" => $request->option_flag??0,
        ]);

        return $this->sendResponse('product', $product);
    }

    public function productDetails(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
            "size" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);

        if (empty($product)) {
            return $this->sendError('Product Not Found!');
        }

        $price = Price::where('product_id',$request->product_id)
                    ->where('size',$request->size)
                    ->first();

        if (empty($price)) {
            return $this->sendError('Size for '.$product->name.' Not Found!');
        }

        $ingredients = $price->ingredients;

        return $this->sendResponse('details',$ingredients);

    }

    public function delete(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);
        $product->delete();

        return $this->sendResponse('success','Succcessfully Deleted!');

    }

    public function editProduct(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
            "name" => "required",
            "category_id" => "required",
            "description" => "required",
            "photo" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('photo')) {

            $image = $request->file('photo');
            $name = $image->getClientOriginalName();
            $image->move(public_path() . '/image/product/', $name);
            $image = $name;
        }

        $product = Product::find($request->product_id);

        if(empty($product)){
            return $this->sendError('Product not found!');
        }

        $product->name = $request->name;
        $product->display_index =$request->display_index;
        $product->category_id = intval($request->category_id);
        $product->description = $request->description;
        $product->photo = $image??'default.png';
        $product->save();

        return $this->sendResponse('product', $product);
    }

    public function editProductv2(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
            "name" => "required",
            "category_id" => "required",
            "description" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }



        $product = Product::find($request->product_id);

        if(empty($product)){
            return $this->sendError('Product not found!');
        }

        $product->name = $request->name;
        $product->display_index =$request->display_index;
        $product->category_id =  intval($request->category_id);
        $product->description = $request->description;
        $product->save();

        return $this->sendResponse('product', $product);
    }

    public function storeIngredient(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);

        $product->size_of_ingredient = json_encode($request->size_of_ingredient);
        $product->save();

        foreach ($request->size_of_ingredient as $value) {

            $price = Price::where('product_id',$product->id)
                    ->where('size',$value['size'])
                    ->first();

            if(!empty($price)){
                $price->sell_price = $value['sell_price'];
                foreach ($value['ingredients'] as $ingredient) {
                    $ingredient = Ingredient::create([
                        "product_id" => $product->id,
                        "raw_material_id" => $ingredient['raw_material_id'],
                        "unit_name" => $ingredient['unit_name'],
                        "amount" => $ingredient['amount'],
                    ]);

                    $price->assignIngredient($ingredient);
                    $price->save();
                }
            }else{
                $price = Price::create([
                    "product_id" => $product->id,
                    "size" => $value['size'],
                    "sell_price" => $value['sell_price'],
                ]);

                foreach ($value['ingredients'] as $ingredient) {
                    $ingredient = Ingredient::create([
                        "product_id" => $product->id,
                        "raw_material_id" => $ingredient['raw_material_id'],
                        "unit_name" => $ingredient['unit_name'],
                        "amount" => $ingredient['amount'],
                    ]);

                    $price->assignIngredient($ingredient);
                }
            }

        }

        $size = $price->ingredients;

        return $this->sendResponse('size_of_ingredients', $size);

    }
    public function test($id,Request $request){

        $product = $product = Product::find($id);
        $price = Price::where('product_id',$product->id)
        ->where('size',$request->size)
        ->get();

        return response()->json($price);
    }
    public function storeIngredientv2(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);


        $product->size_of_ingredient = $request->size_of_ingredient;
        $product->save();

        foreach ($request->size_of_ingredient as $value) {

            $price = Price::where('product_id',$product->id)
                    ->where('size',$value['size'])
                    ->first();


            // if(!empty($price)){
            //
            //     // foreach ($value['ingredients'] as $ingredient) {
            //     //     $ingredient = Ingredient::create([
            //     //         "product_id" => $product->id,
            //     //         "raw_material_id" => $ingredient['raw_material_id'],
            //     //         "unit_name" => $ingredient['unit_name'],
            //     //         "amount" => $ingredient['amount'],
            //     //     ]);

            //     //     $price->assignIngredient($ingredient);
            //
            //     // }
            // }else{
                if(empty($price)){
                    $price = Price::create([
                        "product_id" => $product->id,
                        "size" => $value['size'],
                        "sell_price" => $value['sell_price'],
                        "deli_price" => $value['deli_price'] ? $value['deli_price']:0,
                    ]);

                    foreach ($value['ingredients'] as $ingredient) {
                        $new_ingredient = Ingredient::create([
                            "product_id" => $product->id,
                            "raw_material_id" => $ingredient['raw_material_id'],
                            "unit_name" => $ingredient['unit_name'],
                            "amount" => $ingredient['amount'],
                        ]);

                        $price->assignIngredient($new_ingredient);

                    }
                }
                else{
                    $price->sell_price = $value['sell_price'];
                    $price->deli_price = $value['deli_price'];
                    // $price->unit_name = $value['unit_name'];
                    // $price->amount = $value['amount'];
                    $price->save();


                }

        }



        $size = $price->ingredients;

        return $this->sendResponse('size_of_ingredients', $size);

    }


    public function storeOption(Request $request){

        $validator = Validator::make($request->all(), [
            "options" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        foreach ($request->options as $option) {

            Option::create([
                'name' => $option['name'],
                'product_id' => $option['product_id'],
                // 'size' => $option['size'],
                'raw_material_id' => $option['raw_material_id'],
                'amount' => $option['amount'],
            ]);

            $product = Product::find($option['product_id']);
            $product->option_flag = 1;
            $product->save();

        }

    $options = Option::where('product_id',$product->id)->get();

    return $this->sendResponse('options',$options);

    }



    public function editIngredient(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);

        if(empty($product)){
            return $this->sendError('Product Not Found');
        }

        $product->size_of_ingredient = json_encode($request->size_of_ingredient);
        $product->save();

        $size_of_ingredients = [];
        foreach ($request->size_of_ingredient as $value) {

            $size = Price::where('product_id','=',$product->id)->where('size','=',$value['size'])->first();

            if(empty($size) == true){
                return $this->sendError('Size not found for this product');
            }

            $sizes = $size->ingredients;

            $size_of_ingredients['size'] = $sizes;
            $size_of_ingredients['size_name'] = $size->size;

            $size->size = $value['size'];
            $size->product_id = $product->id;
            $size->sell_price = $value['sell_price'];
            $size->save();
            // return response()->json($size);

            foreach ($value['ingredients'] as $ingredient) {

                $ingredients = Ingredient::find($ingredient['id']);
                $ingredients->product_id = $product->id;
                $ingredients->raw_material_id = $ingredient['raw_material_id'];
                $ingredients->amount = $ingredient['amount'];
                $ingredients->unit_name = $ingredient['unit_name'];
                $ingredients->save();
            }

        }


        return $this->sendResponse('size_of_ingredients', $size_of_ingredients);

    }

    public function editIngredientv2(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $product = Product::find($request->product_id);

        if(empty($product)){
            return $this->sendError('Product Not Found');
        }

        $product->size_of_ingredient = json_encode($request->size_of_ingredient);
        $product->save();


        $size_of_ingredients = [];
        foreach ($request->size_of_ingredient as $value) {

            $size = Price::where('product_id','=',$product->id)->where('size','=',$value['size'])->first();

            if(empty($size) == true){
                return $this->sendError('Size not found for this product');
            }

            $sizes = $size->ingredients;

            $size_of_ingredients['size'] = $sizes;
            $size_of_ingredients['size_name'] = $size->size;

            $size->size = $value['size'];
            $size->product_id = $product->id;
            $size->sell_price = $value['sell_price'];
            $size->deli_price = $value['deli_price'];
            $size->save();
            // return response()->json($size);

            foreach ($value['ingredients'] as $ingredient) {

                $ingredients = Ingredient::find($ingredient['id']);
                $ingredients->product_id = $product->id;
                $ingredients->raw_material_id = $ingredient['raw_material_id'];
                $ingredients->amount = $ingredient['amount'];
                $ingredients->unit_name = $ingredient['unit_name'];
                $ingredients->save();
            }



        }


        return $this->sendResponse('size_of_ingredients', $size_of_ingredients);

    }


    public function optionList(Request $request){

        $validator = Validator::make($request->all(), [
            "product_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $options = Option::where('product_id',$request->product_id)->get();

        foreach($options as $option){

            $raw_material = RawMaterial::find($option->raw_material_id);

            $option['raw_material'] = $raw_material;

        }

        return $this->sendResponse('options',$options);

    }

    // public function optionList(Request $request){

    //     $validator = Validator::make($request->all(), [
    //         "product_id" => "required",
    //         "size" => "required",
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
    //     }

    //     $options = Option::where('product_id',$request->product_id)
    //             ->where('size',$request->size)
    //             ->get();

    //     return $this->sendResponse('option_list',$options);

    // }

}
