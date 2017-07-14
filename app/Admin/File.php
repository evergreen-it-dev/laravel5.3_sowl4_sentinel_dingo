<?php

use App\File;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(File::class, function (ModelConfiguration $model) {
    $model->setTitle('Файлы');

    $model->onDisplay(function () {
        $display = AdminDisplay::table()->setColumns([
            AdminColumn::text('name')->setLabel('Имя файла'),
        ]);
        $display->paginate(15);
        return $display;
    });

    $model->onEdit(function ($id) {
        $form = AdminForm::panel()->addBody(
            AdminFormElement::custom()->setDisplay(function ($instance) {

                $s3 = \Storage::disk('s3');
                $client = $s3->getDriver()->getAdapter()->getClient();
                $expiry = "+5 minutes";

                $command = $client->getCommand('GetObject', [
                    'Bucket' => config('app.aws_bucket'),
                    'Key'    => $instance->name
                ]);

                $presigned_request = $client->createPresignedRequest($command, $expiry);

                return '
                    <strong>Имя файла:</strong><br/>' . $instance->name . '<br/><br/>
                    <strong>Временная ссылка на файл (время существования истекает через 5 минут):</strong><br/><a href="' . (string) $presigned_request->getUri() . '" target="_blank">' . (string) $presigned_request->getUri() . '</a>
                ';
            })
        );
        return $form;
    });
})
    ->addMenuPage(File::class, 0)
    ->setIcon('fa fa-file');