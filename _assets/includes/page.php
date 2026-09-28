<?php

if (!function_exists('begin_page')) {
    function begin_page(string $title, string $style): void
    {
        ?>
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <link rel="stylesheet" href="/_assets/css/index.css">
            <link rel="stylesheet" href="/<?=ltrim($style, '/')?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?=$title?></title>
        </head>
        <body>
        <?php
    }
}

if (!function_exists('end_page')) {
    function end_page(): void
    {
        ?>
        </body>
        </html>
        <?php
    }
}
