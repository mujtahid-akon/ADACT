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
    <link rel="icon" href="/logos/favicon_white.ico" type="image/x-icon">
    <!-- Fonts -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap -->
    <!--<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" />
    <!--<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" />-->
    <!-- Animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.1/animate.min.css">
    <!-- jQuery Easing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- WOW -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <!-- FitText -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FitText.js/1.2.0/jquery.fittext.min.js"></script>
    <!-- Modernizr -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
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
    <link rel="stylesheet" href="./css/creative.css" />
    <!-- Pure Swipe -->
    <script src="./js/pure-swipe.min.js"></script>
    <!-- Nav Side -->
    <link rel="stylesheet" href="./css/nav-side.css" />
    <script src="./js/nav-side.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

EOF;
