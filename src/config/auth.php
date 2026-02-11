<?php

return [

    'defaults' => [
        'guard' => 'user',   // ← 一般ユーザーをデフォルトに変更
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'user' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
        // 管理者ログインを後で追加する場合はここに admin を追加する
        // 'admin' => [
        //     'driver' => 'session',
        //     'provider' => 'admins',
        // ],

    'providers' => [
        // 一般ユーザー
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 管理者モデルを後で追加する場合
        // 'admins' => [
        //     'driver' => 'eloquent',
        //     'model' => App\Models\Admin::class,
        // ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
