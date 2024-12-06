<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\StockSubCategory;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiController
{

    public function file_uploading(Request $r)
    {
        $path = Utils::file_upload($r->file('photo'));

        if($path == null){
            Utils::error('File not uploaded');
        }

        Utils::success($path, "File uploaded successfully");
    }

    public function manifest(Request $r)
    {
        $u = Utils::get_user($r);

        if($u == null){
            Utils::error('Unauthenticated user');
        }

        $roles = DB::table('admin_role_users')->where('user_id', $u->id)->get();
        $company = Company::find($u->company_id);
        
        $data = [
            'name' => 'Inventory APPS',
            'short_name' => 'IMS',
            'description' => 'Inventory Management System',
            'version' => '1.0.0',
            'author' => 'Ismarwanto',
            'email' => 'is.ismarwanto@gmail.com',
            'user' => $u,
            'roles' => $roles,
            'company' => $company
        ];

        Utils::success($data, "Successfully");
    }
    
    public function my_list(Request $r, $model)
    {
        $u = Utils::get_user($r);

        if($u == null){
            Utils::error('Unauthenticated user');
        }

        $model = "App\Models\\" . $model;

        $data = $model::where('company_id', $u->company_id)->limit(100000)->get();

        Utils::success($data, "Listed successfully");
    }


    public function my_update(Request $r, $model)
    {
        $u = Utils::get_user($r);

        if($u == null){
            Utils::error('Unauthenticated user');
        }

        $model = "App\Models\\" . $model;

        $object = $model::find($r->id);
        $isEdit = true;
        
        if($object == null){
            $object = new $model();
            $isEdit = false;
        }

        $table_name = $object->getTable();
        $cols = Schema::getColumnListing($table_name);
        $except = ['id', 'created_at', 'updated_at'];
        $data = $r->all();

        foreach ($data as $key => $value) {
            if(!in_array($key, $cols)){
                continue;
            }
            if(in_array($key, $except)){
                continue;
            }
            $object->$key = $value;
        }
            $object->company_id = $u->company_id;

            //temp_image_field
            if($r->temp_file_field != null){
                if(strlen($r->temp_file_field) > 1){
                    $file = $r->file('photo');

                    if($file != null){
                        $path = "";
                        
                        try {
                            $path = Utils::file_upload($file);
                        } catch (\Exception $e) {
                            Utils::error($e->getMessage());
                        }

                        if(strlen($path) > 3){
                            $field_name = $r->temp_file_field;
                            $object->$field_name = $path;
                        }
                    }
                }
            }

        try {
            $object->save();
        } catch (\Exception $e) {
            Utils::error($e->getMessage());
        }

        $new_object = $model::find($object->id);

        if($isEdit){
            Utils::success($new_object, 'Updated successfully');
        }else{
            Utils::success($new_object, 'Created successfully');
        }
    }

    public function login(Request $r)
    {
        if($r->email == null){
            Utils::error('email is required');
        }

        if(!filter_var($r->email, FILTER_VALIDATE_EMAIL)){
            Utils::error('email is invalid');
        }

        if($r->password == null){
            Utils::error('password is required');
        }

        $u = User::where('email', $r->email)->first();
        if($u == null){
            Utils::error('email is not registered');
        }

        if(!password_verify($r->password, $u->password)){
            Utils::error('password is incorrect');
        }

        $company = Company::find($u->company_id);

        Utils::success([
            'user' => $u,
            'company' => $company
        ], 'Logged in successfully');
    }

    public function register(Request $r)
    {
        if($r->first_name == null){
            Utils::error('first_name is required');
        }

        if($r->last_name == null){
            Utils::error('last_name is required');
        }

        if($r->email == null){
            Utils::error('email is required');
        }

        if(!filter_var($r->email, FILTER_VALIDATE_EMAIL)){
            Utils::error('email is invalid');
        }

        //check if email exists
        $u = User::where('email', $r->email)->first();
        if($u != null){
            Utils::error('email is already registered');
        }

        if($r->password == null){
            Utils::error('password is required');
        }

        //check if company exists
        if($r->company_name == null){
            Utils::error('company name is required');
        }

        if($r->currency == null){
            Utils::error('currency is required');
        }

        $new_user = new User();
        $new_user->first_name = $r->first_name;
        $new_user->last_name = $r->last_name;
        $new_user->username = $r->email;
        $new_user->email = $r->email;
        $new_user->password = password_hash($r->password, PASSWORD_DEFAULT);
        $new_user->name = $r->first_name.' '.$r->last_name;
        $new_user->phone_number = $r->phone_number;
        $new_user->company_id = 1;
        $new_user->status = "Active";
        
        Try{
            $new_user->save();
        }catch(\Exception $e){
            Utils::error($e->getMessage());
        }

        $registered_user = User::find($new_user->id);

        if($registered_user == null){
            Utils::error('Failed to register user');
        }
        
        //creating company
        $company = new Company();
        $company->owner_id = $registered_user->id;
        $company->name = $r->company_name;
        $company->email = $r->email;
        $company->phone_number = $r->phone_number;
        $company->status = "Active";
        $company->currency = $r->currency;
        $company->license_expire = date('Y-m-d', strtotime('+1 year'));

        try{
            $company->save();
        }catch(\Exception $e){
            Utils::error($e->getMessage());
        }

        $registered_company = Company::find($company->id);

        if($registered_company == null){
            Utils::error('Failed to register company');
        }

        //DB insert into admin_role_users
        DB::table('admin_role_users')->insert([
            'user_id' => $registered_user->id,
            'role_id' => 2
        ]);

        Utils::success([
            'user' => $registered_user,
            'company' => $registered_company,
        ], 'Registered successfully');

    }
}
