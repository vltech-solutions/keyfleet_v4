<?php

return [
    'version' => [
        'tiny' => '7.3.0',
        'language' => [
            // https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/
            'version' => '24.7.29',
            'package' => 'langs7',
        ],
        'licence_key' => env('TINY_LICENSE_KEY', 'no-api-key'),
    ],
    'provider' => 'cloud', // cloud|vendor
    // 'direction' => 'rtl',
    /**
     * change darkMode: 'auto'|'force'|'class'|'media'|false|'custom'
     */
    'darkMode' => 'auto',

    /** cutsom */
    'skins' => [
        // oxide, oxide-dark, tinymce-5, tinymce-5-dark
        'ui' => 'oxide',

        // dark, default, document, tinymce-5, tinymce-5-dark, writer
        'content' => 'default'
    ],

    'profiles' => [
        'default' => [
            'plugins' => 'accordion autoresize codesample directionality advlist link image lists preview pagebreak searchreplace wordcount code fullscreen insertdatetime media table emoticons',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignleft aligncenter alignright | numlist bullist outdent indent | forecolor backcolor | blockquote table toc hr | image link media codesample emoticons | wordcount fullscreen',
            'upload_directory' => null,
        ],

        'simple' => [
            'plugins' => 'autoresize directionality emoticons link wordcount',
            'toolbar' => 'removeformat | bold italic | rtl ltr | numlist bullist | link emoticons',
            'upload_directory' => null,
        ],

        'minimal' => [
            'plugins' => 'link wordcount',
            'toolbar' => 'bold italic link numlist bullist',
            'upload_directory' => null,
        ],

        'full' => [
            'plugins' => 'accordion autoresize codesample directionality advlist autolink link image lists charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist outdent indent accordion | forecolor backcolor | blockquote table toc hr | image link anchor media codesample emoticons | pagebreak | visualblocks print preview wordcount fullscreen help',
            'upload_directory' => null,
            'pagebreak_split_block' => true,
            'custom_configs' => [
                'toolbar_mode' => 'wrap',
                'content_style' => "body { margin: 5% !important; } p { margin: 0 !important;} 
                    .mce-pagebreak {
                        display: block;
                        width: 100%;
                        border-top: 2px dashed #999;
                        margin: 2rem 0;
                        text-align: center;
                        position: relative;
                    }
                    .mce-pagebreak::after {
                        content: \\\"Page Break\\\";
                        position: absolute;
                        top: -12px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: #fff;
                        padding: 0 4px;
                        font-size: 12px;
                        color: #999;
                    }",
                'paste_webkit_styles' => 'color font-size',
            ],
        ],


    ],

    /**
     * this option will load optional language file based on you app locale
     * example:
     * languages => [
     *      'fa' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/fa.min.js',
     *      'es' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/es.min.js',
     *      'ja' => asset('assets/ja.min.js')
     * ]
     */
    'languages' => [],

    'extra' => [
        'toolbar' => [
            'fontsize' => '10px 12px 13px 14px 16px 18px 20px',
            'fontfamily' => 'Poppins=poppins,sans-serif;Roboto Mono=Roboto Mono,monospace;Lobster=Lobster,cursive;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Times New Roman=times new roman,times',
        ]
    ]
];
