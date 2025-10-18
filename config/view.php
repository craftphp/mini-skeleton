<?php
return [
    'view_path' => ROOT_DIR . 'resource/view/',
    'engine' => 'blade',
    'drives' => [
        'blade' => [
            'class' => \Craft\Application\Drive\View\BladeOneDrive::class,
            'options' => [
                // Các tùy chọn cho BladeOne nếu cần
            ]
        ]
    ]
];