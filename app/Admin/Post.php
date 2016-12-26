<?php

use App\Post;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Post::class, function (ModelConfiguration $model) {
    $model->setTitle('Публикации');
// Display
    $model->onDisplay(function () {
        $display = AdminDisplay::table()->setColumns([
            AdminColumn::text('title')->setLabel('Заголовок'),
        ]);
        $display->paginate(15);
        return $display;
    });
// Create And Edit

    $model->onCreateAndEdit(function () {
        $form = AdminForm::panel()->addBody(
            AdminFormElement::text('title', 'Заголовок')->required(),
            AdminFormElement::textarea('content', 'Содержимое')->required()
        );
        return $form;
    });
})
    ->addMenuPage(Post::class, 0)
    ->setIcon('fa fa-pencil');