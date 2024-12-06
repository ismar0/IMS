<?php

namespace App\Admin\Controllers;

use App\Models\FinancialPeriod;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinancialPeriodController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Period';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinancialPeriod());

        $u = Admin::user();
        $grid->model()->where('company_id', $u->company_id)->orderBy('start_date', 'desc');
        $grid->disableBatchActions();
        $grid->quickSearch('name');

        $grid->column('id', __('Id'))->hide();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('start_date', __('Start date'))->display(function ($start_date) {
            return date('d-m-Y', strtotime($start_date));
        })->sortable();
        $grid->column('end_date', __('End date'))->display(function ($end_date) {
            return date('d-m-Y', strtotime($end_date));
        })->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('total_investment', __('Total investment'))->display(function ($total_investment) {
            return number_format($total_investment, 2);
        })->sortable();
        $grid->column('total_sales', __('Total sales'))->display(function ($total_sales) {
            return number_format($total_sales, 2);
        })->sortable();
        $grid->column('total_profit', __('Total profit'))->display(function ($total_profit) {
            return number_format($total_profit, 2);
        })->sortable();
        $grid->column('total_expense', __('Total expense'))->display(function ($total_expense) {
            return number_format($total_expense, 2);
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
        $show = new Show(FinancialPeriod::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('name', __('Name'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('total_investment', __('Total investment'));
        $show->field('total_sales', __('Total sales'));
        $show->field('total_profit', __('Total profit'));
        $show->field('total_expense', __('Total expense'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FinancialPeriod());

        $u = Admin::user();

        $form->hidden('company_id', __('Company id'))->default($u->company_id);
        $form->text('name', __('Name'))->rules('required');
        $form->date('start_date', __('Start date'))->default(date('Y-m-d'));
        $form->date('end_date', __('End date'))->default(date('Y-m-d'));
        $form->textarea('description', __('Description'));
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active', 
                'Inactive' => 'Inactive'
            ])->default('Active');

        return $form;
    }
}
