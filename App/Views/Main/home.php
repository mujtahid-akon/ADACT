<?php
/**
 * @var bool   $logged_in
 * @var string $title
 */
if(isset($logged_in) && $logged_in){ ?>
    <div class="row">
        <div class="col-md-12">
            <h1>Welcome!</h1>
            <h3><a href="/projects/new">Create a new project (if doesn't have any project) to get started</a></h3>
            <h3><a class="h3" href="/projects">Previous projects (if have any)</a></h3>
        </div>
    </div>
<?php }else{ ?>
    <!--small>The Alignment-free Disimilarity Analysis & Comparison Tool</small>
    <!--div class="row">
    <div class="col-md-6">
        <h1 class="title">Welcome!</h1>
        <p>
            Welcome to the Alignmentfree Dissimilarity Analysis & Comparison Tool; ADACT in short.
            This tool produces the distance matrix, species relation, phylogenetic trees
            based on a number of indices.
        </p>
    </div>
    <div class="col-md-6">
        <h3>Register</h3>
        <?php 
            if(isset($_SESSION['register_error'])){
                print "<div class=\"alert alert-danger\">{$_SESSION['register_error']}</div>";
                unset($_SESSION['register_error']);
            }
        ?>
        <small class="text-danger"><em>Please fill out all the boxes.</em></small>
        <form class="form-vertical" id="reg_form" method="post" action="/reg">
            <input class="form-control" name="name" placeholder="Full name with title" required /><br />
            <input class="form-control" name="email" placeholder="E-mail address" required /><br />
            <input class="form-control" type="password" name="pass" placeholder="Password" required /><br />
            <input class="btn btn-primary" type="submit" value="register" />
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        ADACT is supported by Bangladesh University of Engineering & Technology
    </div>
</div-->
    <!--form class="navbar-form form-horizontal navbar-right" id="login_form" method="post" action="/login">
        <input class="form-control" type="text" name="email" placeholder="E-mail address" required />
        <input class="form-control" type="password" name="pass" placeholder="Password" required />
        <input class="btn btn-primary" type="submit" value="login" />
        <a href="/reset_pass">Forget password?</a>
    </form-->
    <div class="row" style="margin: 0 auto 20px auto;">
        <div class="col-md-6 col-sm-12">
            <img src="/logos/logo.png"
                 style='vertical-align: middle'>
        </div>
        <div class="col-md-6 col-sm-12">
                <p>
                    <span style="color: darkgreen;font-size: 21px;">ADACT</span> (The Alignment-free Dissimilarity Analysis & Comparison Tool)
                    is a completely free, open source sequence comparison tool which
                    measures dissimilarities among several species in an alignment-free manner. ADACT takes several genome
                    sequences and some parameters (e.g. K-mer size, absent word type, dissimilarity index, RC-setting)
                    as input. On the other hand, it outputs distance matrix, sorted species relationship and phylogenetic tree among species.
                </p>
                <p><a href="/about" class="h2">More</a></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 center">
            <a href="https://www.youtube.com/">
                <img src="/logos/start.png"
                     style='height: 40%; width: 40%; object-fit: contain;' >
            </a>
            <div>
                <a href="https://www.youtube.com/" class="button">Getting Started</a>
            </div>
        </div>
        <div class="col-md-4 center">
            <a href="https://github.com/mujtahid-akon/ADACT/wiki">
                <img src="/logos/api.png"
                     style='height: 40%; width: 40%; object-fit: contain;' >
            </a>
            <div>
                <a href="https://github.com/mujtahid-akon/ADACT/wiki" class="button">API Documentation</a>
            </div>
        </div>
        <div class="col-md-4 center">
            <a href="https://github.com/mujtahid-akon/ADACT">
                <img src="/logos/support.png"
                     style='height: 40%; width: 40%; object-fit: contain;' >
            </a>
            <div>
                <a href="https://github.com/mujtahid-akon/ADACT" class="button">Support Us</a>
            </div>
        </div>
    </div>
<?php } ?>