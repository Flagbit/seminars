<?php
defined('TYPO3_MODE') or die();

return [
    'ctrl' => [
        'title' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_categories',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => 'EXT:seminars/Resources/Public/Icons/Category.gif',
        'searchFields' => 'title',
    ],
    'interface' => [
        'showRecordFieldList' => 'title, icon, single_view_page',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_categories.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ],
        ],
        'icon' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_categories.icon',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'gif,png,jpeg,jpg',
                'max_size' => 256,
                'uploadfolder' => 'uploads/tx_seminars',
                'show_thumbs' => 1,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'single_view_page' => [
            'exclude' => 1,
            'label' => \OliverKlee\Seminars\BackEnd\TceForms::getPathToDbLL() . 'tx_seminars_categories.single_view_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, icon;;;;2-2-2, single_view_page'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
