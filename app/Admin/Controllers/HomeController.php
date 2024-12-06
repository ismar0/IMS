<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\StockRecord;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $u = Admin::user();
        $company = Company::find($u->company_id);

        return $content
            ->title($company->name.' Dashboard')
            ->description('Hello, '.$u->name)
            //->row(Dashboard::title())
            ->row(function (Row $row) {

                $row->column(3, function (Column $column) {

                    $count = User::where('company_id', Admin::user()->company_id)->count();

                    $box = new Box('Employees', '<h3 style="text-align: center; margin: 0px; font-weight: bold">'.$count.'</h3>');
                    $box->solid()
                        ->style('danger');
                    
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    
                    $company = Company::find(Admin::user()->company_id);
                    $now = Carbon::now();
                    $total_sales = StockRecord::where('company_id', Admin::user()->company_id)->whereDate('created_at', $now)->sum('total_sales');

                    //weekly
                    // $total_sales = StockRecord::where('company_id', Admin::user()->company_id)->whereBetween('created_at', [
                    //     $now->startOfWeek()->format('Y-m-d'), $now->endOfWeek()->format('Y-m-d')
                    // ])->sum('total_sales');

                    $currency = "";

                    if($company->currency == "IDR") {
                        $currency = "Rp. ";
                    }else {
                        $currency = $company->currency." ";
                    }

                    $box = new Box('Todays sales', '<h3 style="text-align: center; margin: 0px; font-weight: bold">'.$currency.number_format($total_sales, 0).'</h3>');
                    $box->solid()
                        ->style('success');
                    
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    
                    $company = Company::find(Admin::user()->company_id);
                    $now = Carbon::now();

                    $total_sales = StockRecord::where('company_id', Admin::user()->company_id)->whereBetween('created_at', [
                        $now->startOfWeek()->format('Y-m-d'), $now->endOfWeek()->format('Y-m-d')
                    ])->sum('total_sales');

                    $currency = "";

                    if($company->currency == "IDR") {
                        $currency = "Rp. ";
                    }else {
                        $currency = $company->currency." ";
                    }

                    $box = new Box('Weekly sales', '<h3 style="text-align: center; margin: 0px; font-weight: bold">'.$currency.number_format($total_sales, 0).'</h3>');
                    $box->solid()
                        ->style('warning');
                    
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    
                    $company = Company::find(Admin::user()->company_id);
                    $now = Carbon::now();

                    $total_sales = StockRecord::where('company_id', Admin::user()->company_id)->whereMonth('created_at', $now->month)->sum('total_sales');

                    $currency = "";

                    if($company->currency == "IDR") {
                        $currency = "Rp. ";
                    }else {
                        $currency = $company->currency." ";
                    }

                    $box = new Box('Monthly sales', '<h3 style="text-align: center; margin: 0px; font-weight: bold">'.$currency.number_format($total_sales, 0).'</h3>');
                    $box->solid()
                        ->style('info');
                    
                    $column->append($box);
                });
                
            });
    }
}
