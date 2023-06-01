<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Category;

class CategoryController extends apiBaseController
{
    public function all(Request $request)
    {
        
        $categories = Category::all();

        return $this->sendResponse("categories", $categories);
    }

    public function store(Request $request){

        $validator = $this->validator($request);

        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

    	$category = Category::create([
    		'name' => $request->name,
    		'type' => $request->type,
            'unit' => $request->unit,
    	]);
    	$category_code = $this->generateCode('C',$category->id);
    	$category->category_code = $category_code;
    	$category->save();
    	return response()->json([
            'category' => $category,
            'success' => true,
            'message' => 'successful'
        ]);
    }

    public function update(Request $request){
        
        $validator = Validator::make($request->all(), [
            "category_id" => "required",
            "name" => "required",
            "type" => "required",
            "unit" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $category_id = $request->category_id;
        $category_name = $request->name;
        $unit = $request->unit;
        $type = $request->type;

        $category = Category::where('id', '=', $category_id)->first();
        if(empty($category)){
            return $this->sendError("Category not found");
        }
        
        $category->name = $category_name;
        $category->type = $type;
        $category->unit = $unit;
        $category->save();
        return response()->json([
            "category" => $category,
            "success" => true,
            "message" => "Successful"
        ]);
    }

    // public function getSubCategoryList(Request $request){
    //     $validator = Validator::make($request->all(), [
    //             'category_id' => 'required'
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
    //     }
    // 	$db_name = $this->getDB($request);
    //     if (empty($db_name)) {
    //         return $this->sendError('Resource not found');
    //     }
    //     $user_code = $request->user_code;
    //     if ($db_name == "") {
    //         return response()->json(['success' => false, 'Message' => 'Wrong db']);
    //     }
    //     $sub_categories = SubCategory::on($db_name)->where('category_id', '=', $request->category_id)->get();
    //     return response()->json([
    //         'sub_categories' => $sub_categories,
    //         'success' => true,
    //         'message' => 'successful'
    //     ]);
        
    // }
    
    public function delete(Request $request){
        $validator = Validator::make($request->all(), [
                'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }
    	$db_name = $this->getDB($request);
        if (empty($db_name)) {
            return $this->sendError('Resource not found');
        }
        $user_code = $request->user_code;
        if ($db_name == "") {
            return response()->json(['success' => false, 'message' => 'Wrong db']);
        }
        $category = Category::on($db_name)->find($request->category_id);
        if(empty($category)){
            return response()->json(['success' => false, 'message' => 'Item does not exist' ]);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'successful'
        ]);
    }
    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'unit' => 'required',
        ]);
    }
}
