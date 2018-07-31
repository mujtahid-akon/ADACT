<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 6/11/18
 * Time: 7:14 AM
 */

/**
 * @var string $title
 */
$DIR = \ADACT\Config::WEB_DIRECTORY;
if(!isset($title)) $title = \ADACT\Config::SITE_TITLE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="The Alignment-free Dissimilarity Analysis & Comparison Tool">
    <title><?php echo $title ?></title>
    <base href="<?php echo $DIR ?>">
    <link rel="icon" href="/logos/favicon_white.ico" type="image/x-icon">
    <!-- FONTS -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" />
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
    <!-- Custom CSS/JS -->
    <link rel="stylesheet" href="./css/creative.css" type="text/css">
    <link rel="stylesheet" href="./css/main.css" type="text/css">
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Special Logo CSS -->
    <style>
        .affix-top img.logo-dark { display: none;}
        .affix-top img.logo-light { display: inline-block; }
        .affix img.logo-light { display: none; }
        .affix img.logo-dark { display: inline-block; }
    </style>
    <script>
        // Run loader
        $(document).ready(function(){
            $(window).load(function() {
                $(".pre-loader").fadeOut("slow").promise();
            });
        });
    </script>
</head>
<body>
<!-- Loader -->
<div class="pre-loader"
     style="background-color: darkslategray; color: lightyellow;">
    <i class="fa fa-spinner fa-pulse middle"></i>
</div>
<!-- Navigation Bar -->
<nav id="mainNav" class="navbar navbar-default navbar-fixed-top palegoldenrod">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand page-scroll adact-title" href="./home">
                <img class="logo logo-light" src="./logos/ADACT_Logo_white_32x32.png" aria-hidden="true" />
                <img class="logo logo-dark" src="./logos/ADACT_Logo_black_32x32.png" aria-hidden="true" />
                <?php echo $title ?>
            </a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./login">Login</a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./reg">Register</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="./login">Login</a></li>
                <li><a href="./reg">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- Welcome Screen -->
<section id="top" class="adact-welcome sm-img-bg-fullscr" data-stellar-background-ratio="0.5">
    <div class="container sm-content-cont text-center js-height-fullscr" style="height: 649px;">
        <div class="sm-cont-middle">
            <div class="opacity-scroll2" style="opacity: 1;">
                <!-- Title -->
                <div class="uppercase light-50-wide sm-mb-15 sm-mt-20 palegoldenrod">
                    The <strong>A</strong>lignment-free <strong>D</strong>issimilarity <strong>A</strong>nalysis & <strong>C</strong>omparison <strong>T</strong>ool
                </div>
                <!-- Subtitle -->
                <div class="norm-16-wide sm-mb-50 uppercase palegoldenrod">
                    The distance matrix, sorted species relationships, phylogenetic trees generator
                </div>
                <!-- Button -->
                <div class="center-0-478">
                    <a class="button medium thin palegoldenrod" href="./login">
                        <i class="fa fa-sign-in" aria-hidden="true"></i> Login
                    </a>
                    <a class="button medium thin palegoldenrod ml-20" href="./reg">
                        <i class="fa fa-user-plus" aria-hidden="true"></i> Register
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Scroll Icon -->
    <div class="local-scroll-cont font-white">
        <a href="<?php echo $_SERVER['REQUEST_URI'] ?>#about" class="scroll-down smooth-scroll">
            <div class="fa fa-angle-down"></div>
        </a>
    </div>
</section>
<!-- About screen -->
<section class="page-section bg-maroon golden" id="about">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading golden-rod">The <span class="adact-title">ADACT</span> App</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 text-center wow fadeInDown" style="visibility: visible; animation-name: fadeInDown;">
                <p class="">
                    <span class="adact-title" style="font-size: 21px;">ADACT</span>
                    (The Alignment-free Dissimilarity Analysis & Comparison Tool) measures dissimilarities
                    among several species in an alignment-free manner. ADACT takes several genome sequences
                    and some parameters (e.g. K-mer size, absent word type, dissimilarity index, RC-setting)
                    as input, and outputs distance matrix, sorted species relationship and phylogenetic trees
                    (neighbor joining tree and UPGMA tree) among species.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-lg-offset-2">
                <div class="row wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="fes4-box">
                            <div class="fes4-title-cont">
                                <div class="fes4-box-icon golden-rod">
                                    <div class="fa fa-code"></div>
                                </div>
                                <h3><span class="bold uppercase golden-rod">Open source</span></h3>
                                <p>
                                    Fully open sourced application
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="fes4-box">
                            <div class="fes4-title-cont">
                                <div class="fes4-box-icon golden-rod">
                                    <div class="fa fa-check"></div>
                                </div>
                                <h3 class="golden-rod"><span class="bold uppercase">Simple</span></h3>
                                <p>
                                    Simple web interface with various input method.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="fes4-box">
                            <div class="fes4-title-cont">
                                <div class="fes4-box-icon golden-rod">
                                    <div class="fa fa-thumbs-o-up"></div>
                                </div>
                                <h3 class="golden-rod"><span class="bold uppercase">Easy to use</span></h3>
                                <p>
                                    Just click &ldquo;<strong><i class="fa fa-paper-plane" aria-hidden="true"></i> Run</strong>&rdquo;
                                    and the result will be generated automatically.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        <div class="fes4-box">
                            <div class="fes4-title-cont">
                                <div class="fes4-box-icon golden-rod">
                                    <div class="fa fa-archive"></div>
                                </div>
                                <h3 class="golden-rod"><span class="bold uppercase">Stores results</span></h3>
                                <p>Result is stored for later use or can be exported.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Services screen -->
<section class="page-section bg-black golden" id="services">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 text-center">
                <a class="service-box override display-block" href="./login" title="On the web">
                    <i class="fa fa-4x fa-globe wow bounceIn"></i>
                    <h3>Web App</h3>
                    <p class="text-muted">Run and check results on the web</p>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 text-center">
                <a class="service-box override display-block" href="//github.com/mujtahid-akon/ADACT/wiki" title="API Documentation">
                    <i class="fa fa-4x wow bounceIn" data-wow-delay=".1s">{}</i>
                    <h3>API</h3>
                    <p class="text-muted">Use our API on your application</p>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 text-center">
                <a class="service-box override display-block" href="//github.com/mujtahid-akon/ADACT" title="Source code">
                    <i class="fa fa-4x fa-code wow bounceIn" data-wow-delay=".2s"></i>
                    <h3>Source Code</h3>
                    <p class="text-muted">Source code for this app</p>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- Contact -->
<section class="page-section bg-black golden" id="contact" style="background-color: papayawhip">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <span class="pipe"><a class="text-muted" href="./about">About</a></span>
                <span class="pipe"><a class="text-muted" href="./feedback">Feedback</a></span>
                <span class="pipe"><a class="text-muted" href="//github.com/mujtahid-akon/ADACT/wiki">API</a></span>
                <span><?php echo date('Y') . ' &copy; ' . \ADACT\Config::SITE_TITLE ?></span>
            </div>
        </div>
    </div>
</section>
<!-- have to load at the end -->
<script src="./js/creative.js"></script>
</body>
</html>
