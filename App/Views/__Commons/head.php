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
	<meta name="Description" content="ADACT" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<!--link href="https://www.fontify.me/wf/7d6c4da9e6ebf1836a1c32879c63dbfc" rel="stylesheet" type="text/css" /-->
	<link rel="stylesheet" href="./css/bootstrap.min.css" type="text/css" />
	<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" /-->
	<link rel="stylesheet" href="./css/main.css" type="text/css" />
	<script src="./js/jquery.min.js"></script>
	<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script-->
	<script src="./js/bootstrap.min.js"></script>
	<script src="./js/app.js"></script>
	<!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script-->

EOF;
