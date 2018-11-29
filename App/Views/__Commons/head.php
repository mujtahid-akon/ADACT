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
    <base href="{$DIR}">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="The Alignment-free Dissimilarity Analysis & Comparison Tool" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="theme-color" content="#eee8aa" />
    <link rel="manifest" href="{$DIR}manifest.webmanifest" />
    <link rel="icon" href="${DIR}logos/favicon.ico" type="image/x-icon" />
    <!-- Fonts -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" media="all" />
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap -->
    <!--<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" media="all" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" />
    <!-- Modernizr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js" async></script>
    <!-- Application -->
    <script src="./js/app.min.js" defer></script>
    <link rel="stylesheet" href="./css/main.min.css" type="text/css" media="screen" />
    <!-- MD Components -->
    <link rel="stylesheet" href="./css/components-md.min.css" disabled />
    <!-- Custom -->
    <link rel="stylesheet" href="./css/style.min.css" media="screen" />
    <link rel="stylesheet" href="./css/creative.min.css" media="screen" />
    <!-- Pure Swipe -->
    <script src="./js/pure-swipe.min.js" async></script>
    <!-- Nav Side -->
    <link rel="stylesheet" href="./css/nav-side.min.css" media="screen" />
    <script src="./js/nav-side.min.js" defer></script>
    <!--<script src="https://rawgit.com/bassjobsen/affix/master/assets/js/affix.js"></script>-->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js" async></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js" async></script>
    <![endif]-->

EOF;
