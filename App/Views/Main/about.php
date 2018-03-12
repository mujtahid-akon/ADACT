<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/29/18
 * Time: 12:15 AM
 */
?>
<div class="row">
    <div class="col-md-3">
        <div class="">
            <h3>Contents</h3>
            <ul class="nav">
                <li><a href="/about#About-ADACT">1 <span>About ADACT</span></a></li>
                <li><a href="/about#How-to-Start">2 How to Start</a></li>
                <li><a href="/about#Creating-a-Project">3 Creating a Project</a>
                    <ul class="nav">
                        <li><a href="/about#Accepted-Input-Formats">3.1 Accepted Input Formats</a></li>
                        <li><a href="/about#FASTA-File-Source">3.2 FASTA File Source</a></li>
                        <li><a href="/about#Using-Short-Names">3.3 Using Short Names</a></li>
                        <li><a href="/about#Absent-Word-Type">3.4 Absent Word Type</a></li>
                        <li><a href="/about#K-mer-Size">3.5 K-mer Size</a></li>
                        <li><a href="/about#Sequence-Type">3.6 Sequence Type</a></li>
                        <li><a href="/about#Reverse-Complement">3.7 Reverse Complement</a></li>
                        <li><a href="/about#Dissimilarity-Index">3.8 Dissimilarity Index</a></li>
                        <li><a href="/about#Outputs">3.9 Outputs</a></li>
                    </ul>
                </li>
                <li><a href="/about#Terminologies">4 <span>Terminologies</span></a></li>
                <li><a href="/about#ADACT-Development-Team">5 <span>ADACT Development Team</span></a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-9">
        <article id="About-ADACT">
            <h3>1 About ADACT</h3>
            <p>
                ADACT (The Alignment-free Dissimilarity Analysis & Comparison Tool) is a completely free,
                open source sequence comparison tool which measures dissimilarities among several species
                in an alignment-free manner. ADACT takes several genome sequences and some parameters
                (e.g. K-mer size, absent word type, dissimilarity index, reverse complement) as input and
                outputs distance matrix, sorted species relationship and phylogenetic trees.
            </p>
        </article>
        <article id="How-to-Start">
            <h3>2 How to Start</h3>
            <p>
                If a bioinformatician wants to run a test in ADACT he must create an account on ADACT otherwise
                he must use his existing account. After starting a run on ADACT, he can close the tab even shut
                down his pc.
                His project will automatically run on our server without further assistance from user.
                After successful completion of project, notification will be sent via email to the user.
                The project's data will be temporarily stored on our server. Besides, project's metadata will be
                permanently saved.
            </p>
        </article>
        <article id="Creating-a-Project">
            <h3>3 Creating a Project</h3>
            <article id="Accepted-Input-Formats">
                <h4>3.1 Accepted Input Formats</h4>
                <p>
                    This tool respects <a href="//www.ncbi.nlm.nih.gov/BLAST/fasta.shtml">NCBI's FASTA format</a>.
                    A FASTA file may contain multiple sequences separated by the description line (ie. a single
                    line begins with a "greater than" sign).
                </p>
            </article>
            <article id="FASTA-File-Source">
                <h4>3.2 FASTA File Source</h4>
                <dl>
                    <dt>Upload file</dt>
                    <dd>
                        Either a FASTA file (containing multiple genome sequences) or a Zip file (containing
                        multiple FASTA files) can be uploaded. However, the size of the Zip file must not cross
                        <?php print (\ADACT\Config::MAX_UPLOAD_SIZE/1000000); ?> MB and each sequence must be
                        below <?php print (\ADACT\Config::MAX_FILE_SIZE/1000000); ?> MB. Also, only
                        <?php print (\ADACT\Config::MAX_FILE_ALLOWED); ?> sequences can be processed at a time.
                    </dd>
                    <dt>Accession/GI numbers</dt>
                    <dd>
                        Input Accession or GI numbers instead of uploading FASTA files. The Accession or GI numbers
                        should be separated by commas. Also, they can be mixed. The restrictions stated in the
                        previous section are also applied here.
                    </dd>
                </dl>
            </article>
            <article id="Using-Short-Names">
                <h4>3.3 Using Short Names</h4>
                <p>
                    Short names must be set against each sequence. Short names should not contain more than
                    <?php print \ADACT\Config::MAX_CHAR_ALLOWED ?>.
                </p>
            </article>
            <article id="Absent-Word-Type">
                <h4>3.4 Absent Word Type</h4>
                <dl>
                    <dt>MAW</dt>
                    <dd>Minimal Absent Word</dd>
                    <dt>RAW</dt>
                    <dd>Relative Absent Word</dd>
                </dl>
            </article>
            <article id="K-mer-Size">
                <h4>3.5 K-mer Size</h4>
                <dl>
                    <dt>Min</dt>
                    <dd>Minimum K-mer size</dd>
                    <dt>Max</dt>
                    <dd>Maximum K-mer size</dd>
                </dl>
                <p>
                    <strong>NOTE:</strong> K-mer size are not checked on the server side. Invalid
                    K-mer size may generate invalid phylogenetic trees.
                </p>
            </article>
            <article id="Sequence-Type">
                <h4>3.6 Sequence Type</h4>
                <dl>
                    <dt>Nucleotide</dt>
                    <dd>Input sequences are nucleotide</dd>
                    <dt>Protein</dt>
                    <dd>Input sequences are protien</dd>
                </dl>
                <p>
                    <strong>NOTE: </strong> When GI/Accession numbers are provided as input, sequence type is generated automatically.
                </p>
            </article>
            <article id="Reverse-Complement">
                <h4>3.7 Reverse Complement</h4>
                <p>
                    Reverse complement can be used if necessary.
                </p>
            </article>
            <article id="Dissimilarity-Index">
                <h4>3.8 Dissimilarity Index</h4>
                <div>
                    Dissimilarities among species are measured according to following metrics
                <ul>
                    <li>Length weighted index (both on MAW and RAW)</li>
                    <li>Jaccard Distance (only on MAW)</li>
                    <li>Total Variation Distance (only on MAW)</li>
                    <li>GC content (both on MAW and RAW)</li>
                </ul>
                More about this can be found <a href="https://bmcresnotes.biomedcentral.com/articles/10.1186/s13104-016-1972-z">here</a>.
                </div>
            </article>
            <article id="Outputs">
                <h4>3.9 Outputs</h4>
                <div>
                    The following outputs are generated by ADACT after a successful run
                    <ul>
                        <li>Distance matrix of species</li>
                        <li>Sorted species relation</li>
                        <li>UPGMA (Unweighted Pair Group Method with Arithmetic Mean) tree</li>
                        <li>NJ (Neighbour Joining) tree</li>
                    </ul>
                </div>
            </article>
        </article>
        <article id="Terminologies">
            <h3>4 Terminologies</h3>
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#maw">MAW</a></li>
                <li><a data-toggle="tab" href="#raw">RAW</a></li>
                <li><a data-toggle="tab" href="#lwi">LWI</a></li>
                <li><a data-toggle="tab" href="#jaccard">Jaccard</a></li>
                <li><a data-toggle="tab" href="#tvd">TVD</a></li>
                <li><a data-toggle="tab" href="#gcc">GCC</a></li>
                <li><a data-toggle="tab" href="#acc">Accession number</a></li>
                <li><a data-toggle="tab" href="#gi">GI number</a></li>
                <li><a data-toggle="tab" href="#rc">RC</a></li>
                <li><a data-toggle="tab" href="#short">Short name</a></li>
            </ul>

            <div class="tab-content">
                <div id="maw" class="tab-pane fade in active">
                    <h3>MAW</h3>
                    <p>A Minimal Absent Word of a sequence is an absent word whose proper factors (longest prefix and longest suffix)
                        all occur in the sequence. More formally, string <i>y</i> will be an absent word of string <i>x</i> if <i>y</i> is not a factor
                        of string <i>x</i> but all proper factors of <i>y</i> is present in string <i>x</i>.</p>
                </div>
                <div id="raw" class="tab-pane fade">
                    <h3>RAW</h3>
                    <p>Relative absent of string <i>x</i> with respect to string <i>y</i> are the minimal strings that can't be found in <i>x</i>
                        but are present in <i>y</i>.</p>
                </div>
                <div id="lwi" class="tab-pane fade">
                    <h3>Length Weighted Index</h3>
                    <p>The length Weighted Index (LWI) provides a measure of the similarity/dissimilarity of two sets by considering the length of
                        each member in the symmetric difference or intersection of these sets.</p>
                </div>
                <div id="jaccard" class="tab-pane fade">
                    <h3>Jaccard Distance</h3>
                    <p>Jaccard Distance index is a statistical measure to use as a similarity coefficient between sample sets. Jaccard Distance
                        is evaluated as the ratio of MAWs uncommon between two strings.</p>
                </div>
                <div id="tvd" class="tab-pane fade">
                    <h3>Total Variation Distance</h3>
                    <p>Total Variation Distance (TVD) is used to assess pairwise variance. To calculate TVD between two sequences <i>x</i> and <i>y</i>, we
                        first calculate the number of MAWs in <i>MAW<sub>x</sub></i> and <i>MAW<sub>y</sub></i>  for each word length and then convert this histogram to a normalized
                        version that can be explained as a probability distribution.
                    </p>
                </div>
                <div id="gcc" class="tab-pane fade">
                    <h3>GC Content</h3>
                    <p>This index is based on the content of MAW sets not on number statistics of the MAW sets.
                        In particular, it focuses on the compositional bias or GC Content  (overall fraction of G
                        plus C nucleotides) of the MAW sets. GC Content is calculated considering both symmetric difference and intersection on MAW sets.
                    </p>
                </div>
                <div id="acc" class="tab-pane fade">
                    <h3>Accession number</h3>
                    <p>In bioinformatics, an accession number is an unique identifier assigned to each DNA or protein sequence record to ease for following of different versions of
                        that sequence record and the associated sequence over time in a single data repository such as NCBI.
                    </p>
                </div>
                <div id="gi" class="tab-pane fade">
                    <h3>GI number</h3>
                    <p>A GI number (for GenInfo Identifier, normally written in lower case, "gi") is an arbitrary sequence of numbers which are allowed consecutively to all sequence
                        record gathered by National Center for Biotechnology Information (NCBI). The GI number carries no information about the version number of the sequence record.
                    </p>
                </div>
                <div id="rc" class="tab-pane fade">
                    <h3>Reverse Complement</h3>
                    <p>The reverse complement of a DNA sequence is formed by reversing the letters and then swapping A and T and swapping C and G. Thus the reverse complement
                        of string "ACCTGAG" is "CTCAGGT".
                    </p>
                </div>
                <div id="short" class="tab-pane fade">
                    <h3>Short name</h3>
                    <p>We provide the user the option to choose short names according to their will. It must be short enough to ensure better-looking short phylogenetic trees.
                    </p>
                </div>

            </div>
        </article>
        <article id="ADACT-Development-Team">
            <style>
                .profile-img{
                    height: 40%;
                    width: 40%;
                    object-fit: contain;
                }
            </style>
            <h3>5 ADACT Development Team</h3>
            <div class="row">
                <div class="col-md-4 center">
                    <img src="/profile/mujtahid.jpg" class="profile-img">
                    <div><a href="//github.com/mujtahid-akon">Mujtahid Akon</a></div>
                </div>
                <div class="col-md-4 center">
                    <img src="/profile/mahi.jpg" class="profile-img">
                    <div><a href="//github.com/mahi045">Mohimenul Kabir</a></div>
                </div>
                <div class="col-md-4 center">
                    <img src="/profile/muntasir.jpg" class="profile-img">
                    <div><a href="//github.com/MuntashirAkon">Muntashir Al-Islam</a></div>
                </div>
            </div>
            <!--div class="row">
                <div class="col-md-2 center">
                </div>
                <div class="col-md-4 center">
                    <img src="/profile/avatar.jpg"
                         style='height: 40%; width: 40%; object-fit: contain;' >
                </div>
                <div class="col-md-4 center">
                    <img src="/profile/avatar.jpg"
                         style='height: 40%; width: 40%; object-fit: contain;' >
                </div>
                <div class="col-md-2 center">
                </div>
            </div-->
        </article>
    </div>
</div>
