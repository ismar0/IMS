<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EmployeesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Employees';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $u = Admin::user();

        $grid->model()->where('company_id', $u->company_id);
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->quickSearch('name', 'email')->placeholder('Search by name or email');

        $grid->column('id', __('Id'))->hide();
        $grid->column('avatar', __('Avatar'))->lightbox(['width' => 70, 'height' => 70]);
        $grid->column('name', __('Name'))->sortable();
        $grid->column('username', __('Username'))->sortable();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('phone_number_2', __('Phone number 2'))->hide()->sortable();
        $grid->column('address', __('Address'))->sortable();
        $grid->column('sex', __('Gender'))->filter(['Male' => 'Male', 'Female' => 'Female'])->sortable();
        $grid->column('dob', __('DoB'))->display(function ($dob) {
            return date('d-m-Y', strtotime($dob));
        })->filter('range', 'date')->sortable();
        $grid->column('status', __('Status'))->label([
            'Active' => 'success',
            'Inactive' => 'danger',
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('name', __('Name'));
        $show->field('avatar', __('Avatar'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('first_name', __('First name'));
        $show->field('last_name', __('Last name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('address', __('Address'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $u = Admin::user();

        $form->hidden('company_id', __('Company id'))->default($u->company_id);

        $form->tab('Personal', function (Form $form) {
            
            $form->text('first_name', __('First name'))->rules('required');
            $form->text('last_name', __('Last name'))->rules('required');
            $form->date('dob', __('Date of birth'))->rules('required');
            $form->radio('sex', __('Gender'))->options(['Male' => 'Male', 'Female' => 'Female'])->rules('required');
            $form->mobile('phone_number', __('Phone no.'))->rules('required');
            $form->mobile('phone_number_2', __('Phone no. 2'));
            $form->text('address', __('Address'));
            $form->radio('status', __('Status'))->options([
                'active' => 'Active', 
                'inactive' => 'Inactive'
                ])->default('active');
        })->tab('Account', function (Form $form) {
            $form->image('avatar', __('Avatar'));
        
            $form->email('email', __('Username'))->rules('required');
            $form->password('password', __('Password'))->rules('required');
        });

        return $form;
    }
}
