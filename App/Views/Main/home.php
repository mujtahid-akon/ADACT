<?php
/**
 * @var bool   $logged_in
 * @var string $title
 */
if(isset($logged_in) && $logged_in){ ?>
    <div class="row">
        <div class="col-md-12">
            <h1>Welcome!</h1>
            <h3><a href="./projects/new">Create a new project (if doesn't have any project) to get started</a></h3>
            <h3><a class="h3" href="./projects">Previous projects (if have any)</a></h3>
        </div>
    </div>
<?php }else{ ?>
    <div class="row" style="margin: 0 auto 20px auto;">
        <div class="col-md-6 col-sm-12">
            <img src="./logos/logo.png"
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
                <p><a href="./about" class="h2">More</a></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 center">
            <a href="//www.youtube.com/">
                <img src="./logos/start.png" style='height: 40%; width: 40%; object-fit: contain;' />
            </a>
            <div>
                <a href="//www.youtube.com/" class="button">Getting Started</a>
            </div>
        </div>
        <div class="col-md-4 center">
            <a href="//github.com/mujtahid-akon/ADACT/wiki">
                <img src="./logos/api.png" style='height: 40%; width: 40%; object-fit: contain;' />
            </a>
            <div>
                <a href="//github.com/mujtahid-akon/ADACT/wiki" class="button">API Documentation</a>
            </div>
        </div>
        <div class="col-md-4 center">
            <a href="//github.com/mujtahid-akon/ADACT">
                <img src="./logos/support.png" style='height: 40%; width: 40%; object-fit: contain;' />
            </a>
            <div>
                <a href="//github.com/mujtahid-akon/ADACT" class="button">Support Us</a>
            </div>
        </div>
    </div>
<?php } ?>