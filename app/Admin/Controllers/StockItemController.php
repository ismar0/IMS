<?php

namespace App\Admin\Controllers;

use App\Models\FinancialPeriod;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockSubCategory;
use App\Models\User;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        // $item = StockItem::find(1);
        // die("done");

        $grid = new Grid(new StockItem());

        $u = Admin::user();

        $grid->model()->where('company_id', $u->company_id)->orderBy('name', 'asc');
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');

        $grid->column('id', __('Id'))->hide()->sortable();

        $grid->column('financial_period_id', __('Financial period'))->display(function ($financial_period_id) {
            $financial_period = FinancialPeriod::find($financial_period_id);

            if($financial_period == null){
                return '';
            }

            return $financial_period->name;
        })->hide()->sortable();

        $grid->column('image', __('Photo'))->lightbox(['width' => 50, 'height' => 50])->sortable();
        $grid->column('sku', __('SKU'))->sortable();
        $grid->column('name', __('Name'))->sortable();

        $grid->column('stock_category_id', __('Category'))->display(function ($stock_category_id) {
            $categories = StockCategory::find($stock_category_id);

            if($categories == null){
                return '';
            }

            return $categories->name;
        })->sortable()->hide();

        $grid->column('stock_sub_category_id', __('Sub category'))->display(function ($stock_sub_category_id) {
            $sub_cat = StockSubCategory::find($stock_sub_category_id);

            if($sub_cat == null){
                return '';
            }

            return $sub_cat->name;
        })->sortable();

        $grid->column('description', __('Description'))->sortable();
        $grid->column('barcode', __('Barcode'))->sortable()->hide();
        $grid->column('gallery', __('Gallery'))->gallery(['width' => 70, 'height' => 70])->sortable()->hide();
        $grid->column('buying_price', __('Buy price'))->display(function ($buying_price) {
            return number_format($buying_price);
        })->sortable();
        $grid->column('selling_price', __('Sell price'))->display(function ($selling_price) {
            return number_format($selling_price);
        })->sortable();
        $grid->column('original_quantity', __('Original Qty'))->display(function ($original_quantity) {
            return number_format($original_quantity);
        })->sortable();
        $grid->column('current_quantity', __('Current Qty'))->display(function ($current_quantity) {
            return number_format($current_quantity);
        })->sortable();

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
        $show = new Show(StockItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('stock_category_id', __('Stock category id'));
        $show->field('stock_sub_category_id', __('Stock sub category id'));
        $show->field('financial_period_id', __('Financial period id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
        $show->field('barcode', __('Barcode'));
        $show->field('sku', __('Sku'));
        $show->field('generate_sku', __('Generate sku'));
        $show->field('update_sku', __('Update sku'));
        $show->field('gallery', __('Gallery'));
        $show->field('buying_price', __('Buying price'));
        $show->field('selling_price', __('Selling price'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));

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

        $form = new Form(new StockItem());

        $form->hidden('company_id', __('Company id'))->default($u->company_id);

        if ($form->isCreating()){
            $form->hidden('created_by_id', __('Created by id'))->default($u->id);
        }

        $sub_cat_ajax_url = url('api/stock-sub-categories');
        $sub_cat_ajax_url = $sub_cat_ajax_url . '?company_id=' . $u->company_id;

        $form->select('stock_sub_category_id', __('Stock category'))
            ->ajax($sub_cat_ajax_url)
            ->options(function ($id) {
                $sub_cat = StockSubCategory::find($id);
                if($sub_cat){
                    return [$sub_cat->id => $sub_cat->name_text.' ('.$sub_cat->measurement_unit.')'];
                }else{
                    return [];
                }
            })->rules('required');
        $form->text('name', __('Name'))->rules('required');
        $form->image('image', __('Image'))->uniqueName();

        if($form->isEditing()){
            $form->radio('update_sku', __('Update SKU (Batch No)'))
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No'
                ])->when('Yes', function (Form $form) {
                    $form->text('sku', __('Enter SKU (Batch No)'))->rules('required');
                })->default('No');
        }else{
            $form->radio('generate_sku', __('Generate SKU (Batch No)'))
            ->options([
                'Manual' => 'Manual',
                'Auto' => 'Auto'
            ])->when('Manual', function (Form $form) {
                $form->text('sku', __('Enter SKU (Batch No)'))->rules('required');
            })->rules('required');
        }

        $form->multipleImage('gallery', __('Item Gallery'))->removable()->uniqueName()->downloadable();

        $form->decimal('buying_price', __('Buying price'))->default(0.00)->rules('required');
        $form->decimal('selling_price', __('Selling price'))->default(0.00)->rules('required');
        $form->decimal('original_quantity', __('Original quantity (in units)'))->default(0.00)->rules('required');

        $form->textarea('description', __('Description'));
        //$form->textarea('barcode', __('Barcode'));

        return $form;
    }
}
