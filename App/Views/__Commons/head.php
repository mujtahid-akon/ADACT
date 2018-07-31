<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 6/11/18
 * Time: 4:50 AM
 */

/**
 * @var string $title
 */
$DIR = \ADACT\Config::WEB_DIRECTORY;
if(!isset($title)) $title = \ADACT\Config::SITE_TITLE;

print <<< EOF
    <title>{$title}</title>
    <meta charset="utf-8" />
    <base href="{$DIR}">
    <meta name="Description" content="The Alignment-free Dissimilarity Analysis & Comparison Tool" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="icon" href="/logos/favicon_white.ico" type="image/x-icon">
    <!-- FONTS -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <!--link href="https://www.fontify.me/wf/7d6c4da9e6ebf1836a1c32879c63dbfc" rel="stylesheet" type="text/css" /-->
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" />
    <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" />
    <!-- Application -->
    <script src="./js/app.js"></script>
    <link rel="stylesheet" href="./css/main.css" type="text/css" />
    <!-- Touch Spin -->
    <script src="./js/jquery.bootstrap-touchspin.min.js"></script>
    <link rel="stylesheet" href="./css/jquery.bootstrap-touchspin.min.css" />
    <!-- Underscore -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <!-- MD Components -->
    <link rel="stylesheet" href="./css/components-md.css" />
    <!-- Custom -->
    <link rel="stylesheet" href="./css/style.css" />
    <!-- Modernizr -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
    <!-- Pure Swipe -->
    <script src="./js/pure-swipe.min.js"></script>
    <!-- Nav Sie -->
    <link rel="stylesheet" href="./css/nav-side.css" />
    <script src="./js/nav-side.js"></script>
EOF;
