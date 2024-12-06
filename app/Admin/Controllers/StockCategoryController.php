<?php

namespace App\Admin\Controllers;

use App\Models\StockCategory;
use Encore\Admin\Admin as AdminAdmin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StockCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Stock Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StockCategory());

        $u = Admin::user();

        $grid->model()->where('company_id', $u->company_id);
        $grid->disableBatchActions();
        $grid->quickSearch('name', 'description', 'status');

        $grid->column('id', __('Id'))->hide()->sortable();
        $grid->column('created_at', __('Created at'))->display(function ($created_at) {
            return date('d-m-Y', strtotime($created_at));
        })->sortable()->hide();
        $grid->column('name', __('Category Name'))->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('image', __('Image'))->image('', 100, 100)->hide();
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

        $grid->column('status', __('Status'))->label([
            'Active' => 'success',
            'Inactive' => 'danger'
        ])->sortable();

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
        $show = new Show(StockCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('status', __('Status'));
        $show->field('image', __('Image'));
        $show->field('buying_price', __('Buying price'));
        $show->field('expected_profit', __('Expected profit'));
        $show->field('earned_profit', __('Earned profit'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StockCategory());

        $u = Admin::user();

        $form->hidden('company_id', __('Company id'))->default($u->company_id);
        $form->text('name', __('Category Name'))->rules('required|min:3|max:255');
        
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive'
            ])->default('Active')->rules('required');
        $form->image('image', __('Image'))->uniqueName();

        $form->textarea('description', __('Description'));

        return $form;
    }
}
