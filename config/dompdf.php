<?php

return [

    'font_dir' => storage_path('fonts/'),

    'options' => [
        'font_height_ratio' => 1.1,
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'chroot' => realpath(base_path()),
    ],

];

