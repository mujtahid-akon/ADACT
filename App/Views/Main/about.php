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
        <style>
            ol.content-list {
                list-style: none;
                padding-left: 0;
                counter-reset: content-main-counter;
            }
            ol.content-list > li {
                counter-increment: content-main-counter;
            }
            ol.content-list > li::before {
                content: counter(content-main-counter) " ";
                font-size: 10px;
            }
            ol.content-sub-list {
                list-style: none;
                padding-left: 10px;
                counter-reset: content-sub-counter;
            }
            ol.content-sub-list > li {
                counter-increment: content-sub-counter;
            }
            ol.content-sub-list > li::before {
                content: "3." counter(content-sub-counter) " ";
                font-size: 10px;
            }

            article a {
                color: #000;
            }
            article {
                counter-reset: section;
            }
            article > section {
                counter-increment: section;
            }
            article > section > h2 {
                border-bottom: 1px solid;
                margin-bottom: 5px;
            }
            article > section > h2:before {
                content: counter(section) ' ';
                font-size: 15px;
            }
            article > section {
                counter-reset: subsection;
            }
            article > section > section {
                counter-increment: subsection;
            }
            article > section > section > h3 {
                margin-top: 10px;
                border-bottom: 1px solid;
                margin-bottom: 5px;
            }

            article > section > section > h3:before {
                content: "3." counter(subsection) ' ';
                font-size: 12px;
            }
        </style>
        <div class="">
            <h3>Contents</h3>
            <ol class="content-list">
                <li><a href="./about#About-ADACT">About ADACT</a></li>
                <li><a href="./about#How-to-Start">How to Start</a></li>
                <li><a href="./about#Creating-a-Project">Creating a Project</a>
                    <ol class="content-sub-list">
                        <li><a href="./about#Accepted-Input-Formats">Accepted Input Formats</a></li>
                        <li><a href="./about#FASTA-File-Source">FASTA File Source</a></li>
                        <li><a href="./about#Using-Short-Names">Using Short Names</a></li>
                        <li><a href="./about#Absent-Word-Type">Absent Word Type</a></li>
                        <li><a href="./about#K-mer-Size">K-mer Size</a></li>
                        <li><a href="./about#Sequence-Type">Sequence Type</a></li>
                        <li><a href="./about#Reverse-Complement">Reverse Complement</a></li>
                        <li><a href="./about#Dissimilarity-Index">Dissimilarity Index</a></li>
                        <li><a href="./about#Outputs">Outputs</a></li>
                    </ol>
                </li>
                <li><a href="./about#Terminologies">Terminologies</a></li>
                <li><a href="./about#ADACT-Development-Team">ADACT Development Team</a></li>
            </ol>
        </div>
    </div>
    <article class="col-md-9">
        <section id="About-ADACT">
            <h2>About ADACT</h2>
            <p>
                ADACT (The Alignment-free Dissimilarity Analysis & Comparison Tool) is a completely free,
                open source sequence comparison tool which measures dissimilarities among several species
                in an alignment-free manner. ADACT takes several genome sequences and some parameters
                (e.g. K-mer size, absent word type, dissimilarity index, reverse complement) as input and
                outputs distance matrix, sorted species relationship and phylogenetic trees.
            </p>
        </section>
        <section id="How-to-Start">
            <h2>How to Start</h2>
            <p>
                If a bioinformatician wants to run a test in ADACT he must create an account on ADACT otherwise
                he must use his existing account. After starting a run on ADACT, he can close the tab even shut
                down his pc.
                His project will automatically run on our server without further assistance from user.
                After successful completion of project, notification will be sent via email to the user.
                The project's data will be temporarily stored on our server. Besides, project's metadata will be
                permanently saved.
            </p>
        </section>
        <section id="Creating-a-Project">
            <h2>Creating a Project</h2>
            <section id="Accepted-Input-Formats">
                <h3>Accepted Input Formats</h3>
                <p>
                    This tool respects <a href="//www.ncbi.nlm.nih.gov/BLAST/fasta.shtml">NCBI's FASTA format</a>.
                    A FASTA file may contain multiple sequences separated by the description line (ie. a single
                    line begins with a "greater than" sign).
                </p>
            </section>
            <section id="FASTA-File-Source">
                <h3>FASTA File Source</h3>
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
                    <dt>Input sequence</dt>
                    <dd>
                        Directly input protein/nucleotide sequence instead of uploading a file or inserting ACCNs
                        or gi numbers. The restrictions stated above are also applicable for this.
                    </dd>
                </dl>
            </section>
            <section id="Using-Short-Names">
                <h3>Using Short Names</h3>
                <p>
                    Short names must be set against each sequence. Short names should not contain more than
                    <?php print \ADACT\Config::MAX_CHAR_ALLOWED ?>.
                </p>
            </section>
            <section id="Absent-Word-Type">
                <h3>Absent Word Type</h3>
                <dl>
                    <dt>MAW</dt>
                    <dd>Minimal Absent Word</dd>
                    <dt>RAW</dt>
                    <dd>Relative Absent Word</dd>
                </dl>
            </section>
            <section id="K-mer-Size">
                <h3>K-mer Size</h3>
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
            </section>
            <section id="Sequence-Type">
                <h3>Sequence Type</h3>
                <dl>
                    <dt>Nucleotide</dt>
                    <dd>Input sequences are nucleotide</dd>
                    <dt>Protein</dt>
                    <dd>Input sequences are protien</dd>
                </dl>
                <p>
                    <strong>NOTE: </strong> When GI/Accession numbers are provided as input, sequence type is generated automatically.
                </p>
            </section>
            <section id="Reverse-Complement">
                <h3>Reverse Complement</h3>
                <p>
                    Reverse complement can be used if necessary.
                </p>
            </section>
            <section id="Dissimilarity-Index">
                <h3>Dissimilarity Index</h3>
                <div>
                    Dissimilarities among species are measured according to the following metrics&ndash;
                <ul>
                    <li>GC Content (on both MAW and RAW)</li>
                    <li>Jaccard Distance (only on MAW)</li>
                    <li>Length Weighted Index (on both MAW and RAW)</li>
                    <li>Total Variation Distance (only on MAW)</li>
                </ul>
                More on this topic can be found <a href="//bmcresnotes.biomedcentral.com/articles/10.1186/s13104-016-1972-z">here</a>.
                </div>
            </section>
            <section id="Outputs">
                <h3>Outputs</h3>
                <div>
                    The following outputs are generated by ADACT after a successful run
                    <ul>
                        <li>Distance matrix of species</li>
                        <li>Sorted species relation</li>
                        <li>UPGMA (Unweighted Pair Group Method with Arithmetic Mean) tree</li>
                        <li>NJ (Neighbour Joining) tree</li>
                    </ul>
                </div>
            </section>
        </section>
        <section id="Terminologies">
            <style>
                var {
                    font-weight: bold;
                }
            </style>
            <h2>Terminologies</h2>
            <dl>
                <dt>Minimal Absent Word</dt>
                <dd>A Minimal Absent Word (MAW) of a sequence is an absent word whose proper factors (longest
                    prefix and longest suffix) all occur in the sequence. More formally, the string <var>y</var>
                    will be an absent word of the string <var>x</var> if the string <var>y</var> is not a factor
                    of the string <var>x</var> but all proper factors of the string <var>y</var> is present in
                    the string <var>x</var>.
                </dd>
                <dt>Relative Absent Word</dt>
                <dd>Relative absent of the string <var>x</var> with respect to the string <var>y</var> are the
                    minimal strings that cannot be found in the string <var>x</var> but are present in the string
                    <var>y</var>.
                </dd>
                <dt>The length Weighted Index</dt>
                <dd>The length Weighted Index (LWI) provides a measure of the similarity/dissimilarity of two
                    sets by considering the length of each member in the symmetric difference or intersection
                    of these sets.
                </dd>
                <dt>Jaccard Distance</dt>
                <dd>Jaccard Distance index is a statistical measure to use as a similarity coefficient between
                    sample sets. Jaccard Distance is evaluated as the ratio of MAWs uncommon between two strings.
                </dd>
                <dt>Total Variation Distance</dt>
                <dd>Total Variation Distance (TVD) is used to assess pairwise variance. To calculate TVD between
                    two sequences <var>x</var> and <var>y</var>, at first calculate the number of MAWs in
                    <var>MAW<sub>x</sub></var> and <var>MAW<sub>y</sub></var> for each word length and then
                    convert this histogram to a normalized version that can be explained as a probability
                    distribution.
                </dd>
                <dt>GC Content</dt>
                <dd>This index is based on the content of MAW sets not on number statistics of the MAW sets.
                    In particular, it focuses on the compositional bias or GC Content  (overall fraction of G
                    plus C nucleotides) of the MAW sets. GC Content is calculated considering both
                    symmetric difference and intersection on MAW sets.
                </dd>
                <dt>Accession number</dt>
                <dd>An accession number is an unique identifier assigned to each DNA or protein sequence record
                    to ease for following of different versions of that sequence record and the associated
                    sequence over time in a single data repository such as NCBI.
                </dd>
                <dt>GI number</dt>
                <dd>A GI number (for GenInfo Identifier, normally written in lower case, "gi") is an arbitrary
                    sequence of numbers which are allowed consecutively to all sequence record gathered by
                    National Center for Biotechnology Information (NCBI). The GI number carries no information
                    about the version number of the sequence record.
                </dd>
                <dt>Reverse Complement</dt>
                <dd>The reverse complement of a DNA sequence is formed by reversing the letters, and then
                    swapping A with T; C with G or vice versa. Thus, the reverse complement of the string
                    &ldquo;ACCTGAG&rdquo; is &ldquo;CTCAGGT&rdquo;.
                </dd>
                <dt>Short name</dt>
                <dd>A short name is an easy-to-remember &ldquo;nick&rdquo; name for a species that is used
                    in place of a species name to increase readability as well as to reduce the size of the
                    phylogenetic trees. For example, the short name for &ldquo;Homo sapiens&rdquo; can be
                    &ldquo;human&rdquo;. A short name contains a to z (uppercase or lower case) letters,
                    underscores, hyphens or commas and consists of at most 15 characters.
                </dd>
            </dl>
        </section>
        <section id="ADACT-Development-Team">
            <style>
                .profile-img{
                    height: 40%;
                    width: 40%;
                    object-fit: contain;
                    border-radius: 20px;
                }
            </style>
            <h2>ADACT Development Team</h2>
            <div class="row" style="text-align: center;">
                <div class="col-md-4">
                    <img src="./profile/mujtahid.jpg" class="profile-img">
                    <div><a href="//github.com/mujtahid-akon">Mujtahid Akon</a></div>
                </div>
                <div class="col-md-4">
                    <img src="./profile/mahi.jpg" class="profile-img">
                    <div><a href="//github.com/mahi045">Mohimenul Kabir</a></div>
                </div>
                <div class="col-md-4">
                    <img src="./profile/muntashir.jpg" class="profile-img">
                    <div><a href="//github.com/MuntashirAkon">Muntashir Al-Islam</a></div>
                </div>
            </div>
        </section>
    </article>
</div>
