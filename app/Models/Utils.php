<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Utils
{

    public static function file_upload($file)
    {
        if($file == null){
            return '';
        }

        $file_extension = $file->getClientOriginalExtension();
        $filename = time().'_'.rand(100000, 999999) . '.' . $file_extension;
        $file->move(public_path('storage/images'), $filename);
        $url = 'images/' . $filename;

        return $url;
    }

    public static function get_user(Request $r)
    {
        //get logged_in_user_id from header
        $logged_in_user_id = $r->header('logged_in_user_id');

        //get user from database
        $u = User::find($logged_in_user_id);

        return $u;
    }

    public static function success($data, $message)
    {
        //set header response to json
        header('Content-Type: application/json');

        //set http status code to 200
        http_response_code(200);

        //return data
        echo json_encode([
            'code' => 1,
            'message' => $message,
            'data' => $data
        ]);
        die();
    }

    public static function error($message)
    {
        //set header response to json
        header('Content-Type: application/json');

        //set http status code to 200
        http_response_code(400);

        //return data
        echo json_encode([
            'code' => 0,
            'message' => $message,
            'data' => null
        ]);
        die();
    }

    static function getActiveFinancialPeriod($company_id)
    {
       return FinancialPeriod::where('company_id', $company_id)->where('status', 'Active')->first();
    }

    static public function generateSKU($sub_category_id)
    {
        //year-subcategory-id-serial
        $year = date('Y');
        $sub_category = StockSubCategory::find($sub_category_id);
        $serial = StockItem::where('stock_sub_category_id', $sub_category_id)->count() + 1;
        $sku = $year . '-' . $sub_category->id . '-' . $serial;

        return $sku;
    }
}
