<?php
/**
 * @var bool $logged_in
 */
if(!$logged_in){
    exit();
}
?>
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
        <small class="text text-danger">[Fields with * (star) signs are mandatory.]</small>
        <div class="form form-horizontal">
            <!-- Project Name -->
            <fieldset class="form-wrapper">
                <input class="form-control" id="project_name" name="project_name"
                       placeholder="Project Name *" />
            </fieldset>
            <!-- FASTA File Source -->
            <fieldset class="form-wrapper">
                <label for="method">FASTA file source *</label>
                <select id="method" name="method" class="form-control"
                        style="display: inline-block;width: unset;">
                    <option value="" selected disabled>Choose One</option>
                    <option value="upload_file">Upload a zip file</option>
                    <option value="input_accn_gin">Accession/GI numbers</option>
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
                           onchange="InputAnalyzer.init(this.form)" accept="application/zip" />
                </form>
                <div class="fasta_method" id="filef_status" style="display: none;"></div>
                <!-- ACCN/GIN -->
                <div class="fasta_method" id="input_accn_gin" style="display: none;">
                    <label for="accn_gin" style="vertical-align: top;">Accession/GI numbers *</label>
                    <textarea class="form-control" id="accn_gin" name="accn_gin" placeholder="Input Accession/GI Numbers"
                              style="width: unset; display: inline;" ></textarea><br />
                    <small>Accession/GI numbers must be separated by commas
                        (eg. NM_009417, NM_001003009, 224465210, 50978625, 9507198, A3R4N5).</small><br />
                    <button class="btn btn-primary" id="analyze_accn_gin" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button>
                </div>
                <!-- Analyze result table -->
                <div id="fasta_status" style="display: none"></div>
            </fieldset>

            <!-- Configurations -->
            <fieldset class="form-wrapper">
                <!-- Absent Word Type -->
                <fieldset>
                    <label>Absent Word Type *: </label>
                    <label>
                        <input type="radio" id="aw_type" name="aw_type" value="maw" checked />
                        <abbr title="Minimal Absent Words">MAW</abbr>
                    </label>
                    <label>
                        <input type="radio" id="aw_type" name="aw_type" value="raw" />
                        <abbr title="Relative Absent Words">RAW</abbr>
                    </label>
                </fieldset>
                <!-- K-Mer Size -->
                <fieldset>
                    <label>K-Mer Size *: </label>
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
                <fieldset>
                    <label>Sequence Type *: </label>
                    <label><input type="radio" class="seq_type" name="seq_type" value="nucleotide" checked  /> Nucleotide</label>
                    <label><input type="radio" class="seq_type" name="seq_type" value="protein" /> Protein</label>
                </fieldset>
                <!-- Dissimilarity Index -->
                <fieldset>
                    <label for="dissimilarity_index">Dissimilarity Index *</label>
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
            <div id="p_btn" class="btn btn-primary" onclick="Project.result.send()">Show Result</div>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
