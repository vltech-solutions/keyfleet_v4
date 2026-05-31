<?php

return [
    'resources' => [
        'label'                  => 'Transaction Log',
        'plural_label'           => 'Transaction Logs',
        'navigation_item'        => true,
        'navigation_group'       => null,
        'navigation_icon'        => 'heroicon-o-shield-check',
        'navigation_sort'        => null,
        'default_sort_column'    => 'id',
        'default_sort_direction' => 'desc',
        'navigation_count_badge' => false,
        'resource'               => \Rmsramos\Activitylog\Resources\ActivitylogResource::class,
    ],
    'datetime_format' => 'F d, Y h:i A',
];
