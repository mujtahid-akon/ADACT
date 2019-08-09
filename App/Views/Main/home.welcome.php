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
    <?php require_once __DIR__ . '/../__Commons/head.php'; ?>
    <!-- Animate -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.1/animate.min.css" rel="stylesheet" media="screen"/>
    <!-- jQuery Easing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!-- WOW -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <!-- FitText -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FitText.js/1.2.0/jquery.fittext.min.js"></script>
    <!-- Application -->
    <!-- Special Logo CSS -->
    <style>
        .affix-top img.logo-dark { display: none; }
        .affix-top img.logo-light { display: inline-block; }
        .affix img.logo-light { display: none; }
        .affix img.logo-dark { display: inline-block; }
    </style>
</head>
<body>
<!-- Loader -->
<div class="pre-loader" style="background-color: darkslategray; color: lightyellow;">
    <i class="fa fa-spinner fa-pulse middle"></i>
    <!-- Javascript Warning -->
    <noscript class="container" style="display: block; text-align: center;">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    Javascript is required to carry out certain operations.
                    Please enable Javascript.
                </div>
            </div>
        </div>
    </noscript>
</div>
<!-- Navigation Bar -->
<nav id="mainNav" class="navbar navbar-default navbar-fixed-top palegoldenrod">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand page-scroll adact-title" href="./home">
                <img class="logo logo-light" src="./logos/ADACT_Logo@24x.png" aria-hidden="true" />
                <img class="logo logo-dark" src="./logos/ADACT_Logo@24x.png" aria-hidden="true" />
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
                    <a class="button medium thin palegoldenrod margin-5-10" href="./login">
                        <i class="fa fa-sign-in" aria-hidden="true"></i> Login
                    </a>
                    <a class="button medium thin palegoldenrod margin-5-10" href="./reg">
                        <i class="fa fa-user-plus" aria-hidden="true"></i> Register
                    </a>
                </div>
                <div class="palegoldenrod">Or, <a href="./login?guest=true" style="color: palegoldenrod; text-decoration: underline;">try it without login</a></div>
            </div>
        </div>
    </div>
    <!-- Scroll Icon -->
    <div class="local-scroll-cont">
        <a href="<?php echo $_SERVER['REQUEST_URI'] ?>#about" class="scroll-down smooth-scroll palegoldenrod">
            <div class="fa fa-angle-down"></div><span style="display: none">Go to About section</span>
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
            <div class="col-lg-4 col-md-4 col-sm-4 text-center">
                <a class="service-box override display-block" href="./login" title="On the web">
                    <i class="fa fa-4x fa-globe wow bounceIn"></i>
                    <h3>Web App</h3>
                    <p class="text-muted">Run and check results on the web</p>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-center">
                <a class="service-box override display-block" target="_blank" rel="noreferrer"
                   href="//github.com/mujtahid-akon/ADACT/wiki" title="API Documentation">
                    <i class="fa fa-4x wow bounceIn fa-braces" data-wow-delay=".1s"></i>
                    <h3>API</h3>
                    <p class="text-muted">Use our API on your application</p>
                </a>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-center">
                <a class="service-box override display-block" target="_blank" rel="noreferrer"
                   href="//github.com/mujtahid-akon/ADACT" title="Source code">
                    <i class="fa fa-4x fa-code wow bounceIn" data-wow-delay=".2s"></i>
                    <h3>Source Code</h3>
                    <p class="text-muted">Source code for this app</p>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- Contact -->
<section class="page-section bg-black golden" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <span class="pipe"><a class="text-muted" href="./about">About</a></span>
                <span class="pipe"><a class="text-muted" href="./feedback">Feedback</a></span>
                <span class="pipe"><a class="text-muted" href="//github.com/mujtahid-akon/ADACT/wiki" target="_blank" rel="noreferrer">API</a></span>
                <span><?php echo date('Y') . ' &copy; ' . \ADACT\Config::SITE_TITLE ?></span>
            </div>
        </div>
    </div>
</section>
<!-- have to load at the end -->
<script src="./js/creative.min.js"></script>
<!-- Service Worker -->
<script>
    if('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register('/sw.min.js')
            .then(function() { console.log("Service Worker Registered"); });
    }
</script>
</body>
</html>
