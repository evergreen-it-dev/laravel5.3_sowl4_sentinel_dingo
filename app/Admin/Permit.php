<?php
use App\Role;
use App\Permit;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Permit::class, function (ModelConfiguration $model) {
    $model->setTitle('Права');
// Display
    $model->onDisplay(function () {
        $display = AdminDisplay::table()->setColumns([
            AdminColumn::text('name')->setLabel('Название права'),
            AdminColumn::text('slug')->setLabel('Slug'),
        ]);
        $display->paginate(15);
        return $display;
    });
// Create And Edit

    $model->onCreateAndEdit(function () {
        $form = AdminForm::panel()->addBody(
            AdminFormElement::text('name', 'Название права')->required()->unique(),
            AdminFormElement::text('slug', 'Slug')->required()->unique()
        );
        return $form;
    });
});