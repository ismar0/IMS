<?php

namespace App\Admin\Controllers;

use App\Models\StockCategory;
use App\Models\StockSubCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockSubCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Sub Category';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockSubCategory());

        $u = Admin::user();

        $grid->model()->where('company_id', $u->company_id)->orderBy('name', 'asc');
        $grid->disableBatchActions();
        $grid->quickSearch('name', 'description', 'status');

        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('image', __('Image'))->lightbox(['width' => 100, 'height' => 100]);
        $grid->column('created_at', __('Created at'))->display(function ($created_at) {
            return date('d-m-Y', strtotime($created_at));
        })->sortable()->hide();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('stock_category_id', __('Category'))->display(function ($stock_category_id) {
            $categories = StockCategory::find($stock_category_id);

            if($categories == null){
                return '';
            }

            return $categories->name;
        })->sortable();

        $grid->column('buying_price', __('Buying price'))->display(function ($buying_price) {
            return number_format($buying_price);
        })->sortable();
        $grid->column('selling_price', __('Selling price'))->display(function ($selling_price) {
            return number_format($selling_price);
        })->sortable();
        $grid->column('expected_profit', __('Expected profit'))->display(function ($expected_profit) {
            return number_format($expected_profit);
        })->sortable();
        $grid->column('earned_profit', __('Earned profit'))->display(function ($earned_profit) {
            return number_format($earned_profit);
        })->sortable();

        $grid->column('current_quantity', __('Current Qty'))->display(function ($current_quantity) {
            return number_format($current_quantity);
        })->sortable();
        
        $grid->column('reorder_level', __('Reorder level'))->display(function ($reorder_level) { 
            return number_format($reorder_level); 
        })->sortable()->editable();

        $grid->column('measurement_unit', __('Unit'))->sortable();

        $grid->column('description', __('Description'))->hide();

        //in_stock
        $grid->column('in_stock', __('In Stock'))->dot([
            'Yes' => 'success',
            'No' => 'danger',
        ])->sortable()->filter([
            'Yes' => 'In Stock',
            'No' => 'Out of Stock',
        ]);

        $grid->column('status', __('Status'))->label([
            'Active' => 'success',
            'Inactive' => 'danger',
        ])->sortable()->filter([
            'Active' => 'Active',
            'Inactive' => 'Inactive',
        ]);

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
        $show = new Show(StockSubCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('stock_category_id', __('Stock category id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('status', __('Status'));
        $show->field('image', __('Image'));
        $show->field('buying_price', __('Buying price'));
        $show->field('selling_price', __('Selling price'));
        $show->field('expected_profit', __('Expected profit'));
        $show->field('earned_profit', __('Earned profit'));
        $show->field('measurement_unit', __('Measurement unit'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('reorder_level', __('Reorder level'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockSubCategory());

        $u = Admin::user();
        
        $categories = StockCategory::where([
            'company_id' => $u->company_id,
            'status' => 'Active'
        ])->get()->pluck('name', 'id');

        $form->hidden('company_id', __('Company id'))->default($u->company_id);
        $form->select('stock_category_id', __('Stock Category'))->options($categories)->rules('required');
        $form->text('name', __('Name'))->rules('required|min:3|max:255');
        $form->textarea('description', __('Description'));
        
        $form->image('image', __('Image'))->uniqueName();
        $form->text('measurement_unit', __('Measurement unit'))->rules('required');
        $form->decimal('reorder_level', __('Reorder level (Units)'))->rules('required');

        $form->radio('status', __('Status'))->options([
            'Active' => 'Active',
            'Inactive' => 'Inactive'
        ])->default('Active')->rules('required');

        return $form;
    }
}
