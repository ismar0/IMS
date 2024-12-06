<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            //active financial period
            $active_financial_period = FinancialPeriod::where([
                'company_id' => $model->company_id,
                'status' => 'Active',
            ])->first();

            if($active_financial_period != null && $model->status == 'Active'){
                throw new \Exception('There is already an active financial period, please inactivate it before creating a new one.');
            }
        });

        static::updating(function ($model) {
            //active financial period
            $active_financial_period = FinancialPeriod::where([
                'company_id' => $model->company_id,
                'status' => 'Active',
            ])->first();

            if($model->status == 'Active'){
                if($active_financial_period != null && $active_financial_period->id != $model->id){
                    throw new \Exception('There is already an active financial period, please inactivate it before creating a new one.');
                }
            }            
        });
    }
}
