<?php
use App\User;
use App\Role;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(User::class, function (ModelConfiguration $model) {
    $model->setTitle('Пользователи');
    $model->onDisplay(function () {
        $display = AdminDisplay::table()->setColumns([
            AdminColumn::link('email')->setLabel('email')->setWidth('400px'),
            AdminColumn::text('first_name')->setLabel('Имя'),
            AdminColumn::text('last_name')->setLabel('Фамилия'),
        ]);
        $display->paginate(15);
        return $display;
    });
    $model->onEdit(function () {
        $form = AdminForm::panel()->addBody(
            AdminFormElement::text('first_name', 'Имя'),
            AdminFormElement::text('last_name', 'Фамилия'),
            AdminFormElement::text('facebook_id', 'Facebook аккаунт'),
            AdminFormElement::text('google_id', 'Google+ аккаунт'),
            AdminFormElement::text('vkontakte_id', 'VKontakte аккаунт'),
            AdminFormElement::text('email', 'Email')->unique()->required()->addValidationRule('email'),
            AdminFormElement::multiselect('theroles', 'Роли')->setModelForOptions(new Role())->setDisplay('name')
        );
        return $form;
    });
});