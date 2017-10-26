<?php
/**
 * @var bool  $logged_in
 * @var array $dissimilarity_index
 * @var int   $project_id
 */

if(!$logged_in){
    exit();
}

$isForked = isset($project_id);
if($isForked){
    $config = new \AWorDS\App\Models\ProjectConfig((new \AWorDS\App\Models\FileManager($project_id))->get(\AWorDS\App\Models\FileManager::CONFIG_JSON));
    /** @var string[] $ids */
    $ids = [];
    foreach ($config->data as $datum){
        array_push($ids, $datum['id']);
    }
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
<!--script src="/js/app.js"></script-->

<script>
    $(document).ready(function(){
        // Set K-Mer
        $('#kmer_min').val(<?php print ($isForked ? $config->kmer['min'] : 9) ?>);
        $('#kmer_max').val(<?php print ($isForked ? $config->kmer['max'] : 13) ?>);
        // Set Absent Word type
        var aw_type = "<?php print ($isForked ? $config->aw_type : 'maw') ?>";
        $('input[name=\'aw_type\'][value=\'' + aw_type + '\']').attr('checked', true);

        <?php if ($isForked): ?>

        // Show Dissimilarity Index based on Absent Word type
        if(aw_type === 'maw'){
            $('.maw_dissimilarity').show();$('.raw_dissimilarity').hide();
        }else{
            $('.maw_dissimilarity').hide();$('.raw_dissimilarity').show();
        }
        // Set project name
        $('#project_name').val("<?php print ($isForked ? $config->project_name : '') ?>");
        // Set Dissimilarity Index based on Absent Word type
        $('option[value=\'<?php print ($isForked ? $config->dissimilarity_index : '') ?>\']').attr('selected', true);
        // Set input method
        $('option[value=\'input_accn_gin\']').attr('selected', true);
        InputMethod.setCurrent($('#method').val());
        $('#accn_gin').val('<?php print ($isForked ? implode(', ', $ids) : '') ?>');
        // Set sequence type
        var seq_type = "<?php print $config->sequence_type ?>";
        $('input[value=\'' + seq_type + '\']').attr('checked', true);
        if(seq_type === 'nucleotide')   $('#inversion_box').show();
        else if(seq_type === 'protein') $('#inversion_box').hide();
        // Set reverse complement
        $('#inversion').attr('checked', <?php print ($config->inversion ? 'true' : 'false'); ?>);
        <?php endif; ?>

    });
</script>

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
                              style="width: 100%; display: block;" ></textarea>
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
                        <input type="radio" name="aw_type" value="maw"
                               onchange="$('.maw_dissimilarity').show();$('.raw_dissimilarity').hide();" />
                        <abbr title="Minimal Absent Words">MAW</abbr>
                    </label>
                    <label>
                        <input type="radio" name="aw_type" value="raw"
                               onchange="$('.maw_dissimilarity').hide();$('.raw_dissimilarity').show();"/>
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
                <!-- MAW Type -->
                <fieldset>
                    <label>Sequence Type *: </label>
                    <label><input type="radio" class="seq_type" name="seq_type" value="nucleotide" onchange="$('#inversion_box').show()" checked  /> Nucleotide</label>
                    <label><input type="radio" class="seq_type" name="seq_type" value="protein" onchange="$('#inversion_box').hide()" /> Protein</label>
                </fieldset>
                <!-- Inversion -->
                <fieldset id="inversion_box">
                    <label><input type="checkbox" id="inversion" name="inversion" /> Use Reverse Complement</label>
                </fieldset>
                <!-- Dissimilarity Index -->
                <fieldset>
                    <label for="dissimilarity_index">Dissimilarity Index *</label>
                    <select id="dissimilarity_index" name="dissimilarity_index" class="form-control" style="display: inline-block;width: unset;">
                        <option value="" disabled selected>Select One</option>
                        <?php
                        // MAW Dissimilarity Indexes
                        foreach ($dissimilarity_index['MAW'] as $short_form => $full_form){
                            print "<option class=\"maw_dissimilarity\" value=\"{$short_form}\">{$full_form}</option>\n";
                        }
                        // RAW Dissimilarity Indexes
                        foreach ($dissimilarity_index['RAW'] as $short_form => $full_form){
                            print "<option style=\"display: none;\" class=\"raw_dissimilarity\" value=\"{$short_form}\">{$full_form}</option>\n";
                        }
                        ?>
                    </select>
                </fieldset>
            </fieldset>
            <div id="p_btn" class="btn btn-primary" onclick="Project.result.send()">Show Result</div>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
