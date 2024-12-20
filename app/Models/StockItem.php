<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    //boot
    protected static function boot(){
        parent::boot();

        static::creating(function ($model) {
            $model = self::prepare($model);
            $model->current_quantity = $model->original_quantity;

            return $model;
        });

        static::updating(function ($model) {
            $model = self::prepare($model);
            return $model;
        });

        static::created(function ($model) {
            $stock_category = StockCategory::find($model->stock_category_id);
            $stock_category->update_self();

            $stock_sub_category = StockSubCategory::find($model->stock_sub_category_id);
            $stock_sub_category->update_self();
        });

        static::updated(function ($model) {
            $stock_category = StockCategory::find($model->stock_category_id);
            $stock_category->update_self();

            $stock_sub_category = StockSubCategory::find($model->stock_sub_category_id);
            $stock_sub_category->update_self();
        });

        static::deleted(function ($model) {
            $stock_category = StockCategory::find($model->stock_category_id);
            $stock_category->update_self();

            $stock_sub_category = StockSubCategory::find($model->stock_sub_category_id);
            $stock_sub_category->update_self();
        });
    }

    static public function prepare($model){
        $sub_category = StockSubCategory::find($model->stock_sub_category_id);

        if($sub_category == null){
            Throw new \Exception('Invalid sub category');
        }

        $model->stock_category_id = $sub_category->stock_category_id;

        $user = User::find($model->created_by_id);

        if($user == null){
            Throw new \Exception('Invalid user');
        }

        $financial_period = Utils::getActiveFinancialPeriod($user->company_id);

        if($financial_period == null){
            Throw new \Exception('Invalid financial period');
        }

        $model->financial_period_id = $financial_period->id;
        $model->company_id = $user->company_id;

        if($model->sku == null || strlen($model->sku) < 2){
            $model->sku = Utils::generateSKU($model->company_id);
        }

        if($model->update_sku == 'Yes' && $model->generate_sku == 'Auto'){
            $model->sku = Utils::generateSKU($model->company_id);
            $model->generate_sku = 'Manual';
            $model->update_sku = 'No';
        }

        return $model;
    }

    //getter for gallery
    public function getGalleryAttribute($value)
    {
        if($value != null && strlen($value) > 3){
            return json_decode($value);
        }

        return [];
    }

    //setter for gallery
    public function setGalleryAttribute($value)
    {
        $this->attributes['gallery'] = json_encode($value, true);
    }

    //appends for name_text
    protected $appends = ['name_text'];

    //getter for name_text
    public function getNameTextAttribute(){
        $name_text = $this->name;
        if($this->stockSubCategory != null){
            $name_text = $name_text . ' - ' . $this->stockSubCategory->name;
        }

        //add current qty on name
        $name_text = $name_text . " (" . number_format($this->current_quantity) . " " . $this->stockSubCategory->measurement_unit . ")";
        return $name_text;
    }

    //stockSubCategory relation
    public function stockSubCategory(){
        return $this->belongsTo(StockSubCategory::class);
    }
}
