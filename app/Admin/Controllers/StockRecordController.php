<?php

namespace App\Admin\Controllers;

use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockRecord;
use App\Models\StockSubCategory;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Out Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockRecord());

        $u = Admin::user();

        $grid->model()->where('company_id', $u->company_id)->orderBy('name', 'asc');
        $grid->disableBatchActions();
        $grid->quickSearch('sku', 'name')->placeholder('Search by sku or name');

        $grid->column('id', __('Id'))->hide()->sortable();

        $grid->column('sku', __('Sku'))->sortable();
        $grid->column('name', __('Product Name'))->sortable();

        $grid->column('stock_category_id', __('Category'))->display(function ($stock_category_id) {
            $categories = StockCategory::find($stock_category_id);

            if($categories == null){
                return '';
            }

            return $categories->name;
        })->sortable();
        $grid->column('stock_sub_category_id', __('Sub category'))->display(function ($stock_sub_category_id) {
            $sub_categories = StockSubCategory::find($stock_sub_category_id);

            if($sub_categories == null){
                return '';
            }

            return $sub_categories->name;
        })->sortable()
            ->totalRow(function () {
                return "<b>Total:</b>";
            });

        $grid->column('description', __('Description'))->hide()->sortable();
        $grid->column('type', __('Type'))->sortable();
        $grid->column('quantity', __('Quantity'))
            ->sortable()
            ->totalRow(function ($amount) {
                return "<b><span class='text-info'>" . number_format($amount) . "</span></b>";
            });
        $grid->column('measurement_unit', __('Unit'))->sortable();
        $grid->column('selling_price', __('Selling price'))->display(function ($selling_price) {
            return number_format($selling_price);
        })->sortable()
            ->totalRow(function ($amount) {
                return "<b><span class='text-info'>" . number_format($amount) . "</span></b>";
            });
        $grid->column('total_sales', __('Total sales'))->display(function ($total_sales) {
            return number_format($total_sales);
        })->sortable()
            ->totalRow(function ($amount) {
                return "<b><span class='text-info'>" . number_format($amount) . "</span></b>";
            });

        $grid->column('created_by_id', __('Created by'))->display(function ($created_by_id) {
            $user = User::find($created_by_id);

            if($user == null){
                return '';
            }

            return $user->name;
        })->sortable();

        $grid->column('created_at', __('Created'))->display(function ($created_at) {
            return date('d-m-Y', strtotime($created_at));
        })->filter('range', 'date')->sortable();

        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(StockRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('stock_category_id', __('Stock category id'));
        $show->field('stock_sub_category_id', __('Stock sub category id'));
        $show->field('stock_item_id', __('Stock item id'));
        $show->field('sku', __('Sku'));
        $show->field('name', __('Name'));
        $show->field('measurement_unit', __('Measurement unit'));
        $show->field('description', __('Description'));
        $show->field('type', __('Type'));
        $show->field('quantity', __('Quantity'));
        $show->field('selling_price', __('Selling price'));
        $show->field('total_sales', __('Total sales'));
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Admin::user();
        
        $financial_period = Utils::getActiveFinancialPeriod($u->company_id);
        
        if($financial_period == null){
            return admin_error('There is no active financial period');
        }
        
        $form = new Form(new StockRecord());

        $form->hidden('company_id', __('Company id'))->default($u->company_id);

        $form->hidden('financial_period_id', __('Financial period id'))->default($financial_period->id);

        if ($form->isCreating()){
        $form->hidden('created_by_id', __('Created by id'))->default($u->id);
        }

        $stock_item_ajax_url = url('api/stock-items') . '?company_id=' . $u->company_id;

        $form->radio('type', __('Type'))
        ->options([
            'Sale' => 'Sale',
            'Damage' => 'Damage',
            'Expired' => 'Expired',
            'Lost' => 'Lost',
            'Internal Use' => 'Internal Use',
            'Other' => 'Other'
            ])->rules('required');

        $form->select('stock_item_id', __('Stock item'))
        ->ajax($stock_item_ajax_url)
        ->options(function ($id) {
            $stock_item = StockItem::find($id);
            if($stock_item){
                return [
                    $stock_item->id => $stock_item->name
                ];
            }else{
                return [];
            }
        })->rules('required');


        $form->decimal('quantity', __('Quantity'))->rules('required');

        $form->textarea('description', __('Description'));

        return $form;
    }
}
