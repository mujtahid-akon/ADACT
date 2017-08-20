<style>
    #filef{
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
    .form-wrapper{
        margin-bottom: 10px;
    }
    /*
    .alert{
        margin-bottom: 10px !important;
    }*/
</style>
<script src="/js/app.js"></script>

<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="h1" id="p_name">New Project</div>
        <div class="form form-horizontal">
            <!-- Project Name -->
            <fieldset class="form-wrapper">
                <input class="form-control" id="project_name" name="project_name"
                       placeholder="Project Name" />
            </fieldset>
            <!-- FASTA File Source -->
            <fieldset class="form-wrapper">
                <label for="method">FASTA file source</label>
                <select id="method" name="method" class="form-control"
                        style="display: inline-block;width: unset;">
                    <option value="" selected disabled>Choose One</option>
                    <option value="upload_file">Upload a zip file</option>
                    <option value="input_gin">GI numbers</option>
                    <option value="input_accn">Accession numbers</option>
                    <option value="input_uniprot">UniProt ACCN numbers</option>
                </select>
                <button class="btn btn-primary" id="change_method"
                        onclick="InputMethod.setCurrent($('#method').val())" style="vertical-align: top">Done</button>
            </fieldset>

            <!-- FASTA File Handling -->
            <fieldset class="form-wrapper">
                <!-- FILE UPLOAD -->
                <form class="fasta_method" id="upload_file" action="" method="post"
                      enctype="multipart/form-data" onsubmit="return false;" style="display: none;">
                    <input type="hidden" name="MAX_FILE_SIZE"
                           value="<?php print \AWorDS\Config::MAX_UPLOAD_SIZE ?>" />
                    <label for="filef" class="btn btn-primary">Upload a zip file...</label><br />
                    <small>Zip file consists of a number of FASTA (the extension can be of any type)
                        files with no directories.</small>
                    <input class="" type="file" id="filef" name="filef"
                           onchange="upload(this.form)" accept="application/zip" />
                </form>
                <div class="fasta_method" id="filef_status" style="display: none;"></div>
                <!-- GIN -->
                <div class="fasta_method" id="input_gin" style="display: none;">
                    <label for="gin">GI numbers</label>
                    <input class="form-control" id="gin" name="gin" placeholder="Input GI Numbers"
                           style="width: unset; display: inline;" />
                    <button class="btn btn-primary" id="analyze_gin" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button><br />
                    <small>GI numbers must be separated by commas
                        (eg. 24475906, 224465210, 50978625, 9507198).</small>
                </div>
                <!-- ACCN -->
                <div class="fasta_method" id="input_accn" style="display: none;">
                    <label for="accn">Accession numbers</label>
                    <input class="form-control" id="accn" name="accn" placeholder="Input Accession Numbers"
                           style="width: unset; display: inline;" />
                    <button class="btn btn-primary" id="analyze_accn" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button><br />
                    <small>Accession numbers must be separated by commas
                        (eg. NM_009417, NM_001003009).</small>
                </div>
                <!-- UniProt ACCN -->
                <div class="fasta_method" id="input_uniprot" style="display: none;">
                    <label for="uniprot">UniProt Accession numbers</label>
                    <input class="form-control" id="uniprot" name="uniprot" placeholder="Input Uniprot Accession Numbers"
                           style="width: unset; display: inline;" />
                    <button class="btn btn-primary" id="analyze_uniprot" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button><br />
                    <small>UniProt accession numbers must be separated by commas
                        (eg. A3R4N4, A3R4N5, A5HBD7, A5HBG1, A8Y984, A8Y985).</small>
                </div>
                <!-- Analyze result table -->
                <div id="fasta_status" style="display: none"></div>
            </fieldset>

            <!-- Configurations -->
            <fieldset class="form-wrapper">
                <!-- Absent Word Type -->
                <fieldset>
                    <label>Absent Word Type: </label>
                    <label>
                        <input type="radio" id="aw_type" name="aw_type" value="maw" checked
                               onchange="$('#maw_type_toggle, .maw_dissimilarity').show();$('.raw_dissimilarity').hide();" />
                        <abbr title="Minimal Absent Words">MAW</abbr>
                    </label>
                    <label>
                        <input type="radio" id="aw_type" name="aw_type" value="raw"
                               onchange="$('#maw_type_toggle, .maw_dissimilarity').hide();$('.raw_dissimilarity').show();" />
                        <abbr title="Relative Absent Words">RAW</abbr>
                    </label>
                </fieldset>
                <!-- K-Mer Size -->
                <fieldset>
                    <label>K-Mer Size: </label>
                    <input class="form-control" type="number" id="kmer_min" name="kmer_min" min="1"
                           style="width: 100px;display: inline-block;" placeholder="Min" required />
                    <input class="form-control" type="number" id="kmer_max" name="kmer_max" min="1"
                           style="width: 100px;display: inline-block" placeholder="Max" required />
                </fieldset>
                <!-- Inversion -->
                <fieldset>
                    <label><input type="checkbox" id="inversion" name="inversion" /> Use inversion</label>
                </fieldset>
                <!-- MAW Type -->
                <fieldset id="maw_type_toggle">
                    <label><abbr title="Minimal Absent Words">MAW</abbr> Type: </label>
                    <label><input type="radio" class="maw_type" name="maw_type" value="dna" checked  /> DNA</label>
                    <label><input type="radio" class="maw_type" name="maw_type" value="protein" /> Protein</label>
                </fieldset>
                <!-- Dissimilarity Index -->
                <fieldset>
                    <label for="dissimilarity_index">Dissimilarity Index</label>
                    <select id="dissimilarity_index" name="dissimilarity_index" class="form-control" style="display: inline-block;width: unset;">
                        <option value="" disabled selected>Select One</option>
                        <!-- MAW Dissimilarity Indexes -->
                        <option class="maw_dissimilarity" value="MAW_LWI_SDIFF">MAW_LWI_SDIFF</option>
                        <option class="maw_dissimilarity" value="MAW_LWI_INTERSECT">MAW_LWI_INTERSECT</option>
                        <option class="maw_dissimilarity" value="MAW_GCC_SDIFF">MAW_GCC_SDIFF</option>
                        <option class="maw_dissimilarity" value="MAW_GCC_INTERSECT">MAW_GCC_INTERSECT</option>
                        <option class="maw_dissimilarity" value="MAW_JD">MAW_JD</option>
                        <option class="maw_dissimilarity" value="MAW_TVD">MAW_TVD</option>
                        <!-- RAW Dissimilarity Indexes -->
                        <option style="display: none;" class="raw_dissimilarity" value="RAW_LWI">RAW_LWI</option>
                        <option style="display: none;" class="raw_dissimilarity" value="RAW_GCC">RAW_GCC</option>
                    </select>
                </fieldset>
            </fieldset>
            <div id="p_btn" class="btn btn-primary" onclick="result()">Show Result</div>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
