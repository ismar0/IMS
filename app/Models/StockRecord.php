<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    //boot
    protected static function boot(){
        parent::boot();

        //creating
        static::creating(function ($model) {

            $stock_item = StockItem::find($model->stock_item_id);

            if($stock_item == null){
                Throw new \Exception('Invalid stock item');
            }

            $model->company_id = $stock_item->company_id;
            $model->stock_category_id = $stock_item->stock_category_id;
            $model->stock_sub_category_id = $stock_item->stock_sub_category_id;
            $model->sku = $stock_item->sku;
            $model->name = $stock_item->name;
            $model->measurement_unit = $stock_item->stockSubCategory->measurement_unit;

            if($model->description == null){
                $model->description = $stock_item->description;
            }

            $quantity = abs($model->quantity);

            if($quantity < 1){
                throw new \Exception('Invalid quantity');
            }

            $model->selling_price = $stock_item->selling_price;
            $model->total_sales = $model->selling_price * $quantity;
            $model->quantity = $quantity;
            
            $user = User::find($model->created_by_id);

            if($user == null){
                Throw new \Exception('Invalid user');
            }

            $financial_period = Utils::getActiveFinancialPeriod($user->company_id);

            if($financial_period == null){
                Throw new \Exception('Invalid financial period');
            }

            $model->financial_period_id = $financial_period->id;
                

            //profit

            if($model->type == "Sale" || $model->type == "Internal Use"){
                $model->total_sales = abs($model->total_sales);
                $model->profit = $model->total_sales - ($stock_item->buying_price * $quantity);
            }else{
                $model->total_sales = 0;
                $model->profit = 0;
            }

            $current_quantity = $stock_item->current_quantity;

            if($current_quantity < $quantity){
                throw new \Exception('Not enough stock');
            }

            $new_quantity = $current_quantity - $quantity;
            $stock_item->current_quantity = $new_quantity;
            $stock_item->save();

            return $model;
        });

        //created
        static::created(function ($model) {

            $stock_item = StockItem::find($model->stock_item_id);

            if($stock_item == null){
                Throw new \Exception('Invalid stock item');
            }

            $stock_item->stockSubCategory->update_self();
            $stock_item->stockSubCategory->stockCategory->update_self();
        });

        //deleted
        static::deleted(function ($model) {

            $stock_item = StockItem::find($model->stock_item_id);

            if($stock_item == null){
                Throw new \Exception('Invalid stock item');
            }

            $new_quantity = $model->quantity + $stock_item->current_quantity;
            $stock_item->current_quantity = $new_quantity;
            $stock_item->save();

            $stock_item->stockSubCategory->update_self();
            $stock_item->stockSubCategory->stockCategory->update_self();
        });
    }
    /*
						
     */
}
