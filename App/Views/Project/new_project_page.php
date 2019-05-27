<?php
/**
 * @var bool  $logged_in
 * @var array $dissimilarity_index
 * @var int   $project_id
 */

if(!$logged_in){
    exit();
}

use \ADACT\App\Models\ProjectConfig;
use \ADACT\App\Models\FileManager as FM;

$isForked = isset($project_id);
$config = null;
if($isForked){
    try{
        $config = new ProjectConfig((new FM($project_id))->get(FM::CONFIG_JSON));
        /** @var string[] $ids */
        $ids = [];
        foreach ($config->data as $datum){
            array_push($ids, $datum['id']);
        }
    } catch (\Exception $e){
        error_log($e->getCode() . ': ' . $e->getMessage());
        // Something's wrong which shouldn't be.
        // FIXME: Use this in the Project Controller instead of here
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
</style>

<script>
    $(document).ready(function(){
        // Set K-Mer
        <?php
            if($isForked) {
                echo "$('#kmer_min').val({$config->kmer['min']});\n";
                echo "$('#kmer_max').val({$config->kmer['max']});\n";
            }
        ?>
        // Set Absent Word type
        const aw_type = "<?php print ($isForked ? $config->aw_type : 'maw') ?>";
        $('input[name=\'aw_type\'][value=\'' + aw_type + '\']').attr('checked', true);

        <?php if($isForked){ ?>
        // Show Dissimilarity Index based on Absent Word type
        if(aw_type === 'maw'){
            $('.maw_dissimilarity').addClass('active').show();
            $('.raw_dissimilarity').removeClass('active').hide();
        }else{
            $('.maw_dissimilarity').removeClass('active').hide();
            $('.raw_dissimilarity').addClass('active').show();
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
        const seq_type = "<?php print $config->sequence_type ?>";
        $('input[value=\'' + seq_type + '\']').attr('checked', true);
        if(seq_type === 'nucleotide'){
            $('#inversion_box').show();
            $('.active.dis_gcc').show();
        } else if (seq_type === 'protein'){
            $('#inversion_box').hide();
            $('.active.dis_gcc').hide();
        }
        // Set reverse complement
        $('#inversion').attr('checked', <?php print ($config->inversion ? 'true' : 'false'); ?>);
        <?php } ?>
        $('[data-toggle="tooltip"]').tooltip({
            placement : 'right'
        });
    });
</script>
<script src="./js/examples.min.js" defer></script>
<style>
    .tooltip-inner {
        max-width: 350px;
        width: inherit;
    }
</style>

<div class="row">
    <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
        <h3 class="title" id="p_name">New Project</h3>
        <small class="text text-danger">[Fields with * (star) sign are mandatory.]</small>
        <div style="margin: 5px 0">
            <button type="button" class="btn-4 button small whitish uppercase" onclick="ExampleOne()">Example 1</button>
            <button type="button" class="btn-4 button small whitish uppercase" onclick="ExampleTwo()">Example 2</button>
        </div>
        <div class="form form-horizontal">
            <!-- Project Name -->
            <fieldset class="form-wrapper">
                <input class="form-control input-sm" id="project_name" name="project_name"
                       placeholder="Project Name *" />
            </fieldset>
            <!-- FASTA File Source -->
            <fieldset class="form-wrapper">
                <label for="method" class="control-label">FASTA file source *</label>
                <select id="method" name="method" class="form-control input-sm"
                        style="display: inline-block;width: unset;" onchange="InputMethod.setCurrent($(this).val())">
                    <option value="" selected disabled>Choose One</option>
                    <option value="upload_file">Upload a file</option>
                    <option value="input_seq">Input Sequence</option>
                    <option value="input_accn_gin">Accession/GI numbers</option>
                </select>
            </fieldset>
            <!-- FASTA File Handling -->
            <fieldset class="form-wrapper">
                <!-- FILE UPLOAD -->
                <form class="fasta_method" id="upload_file" action="" method="post"
                      enctype="multipart/form-data" onsubmit="return false;" style="display: none;">
                    <input type="hidden" name="MAX_FILE_SIZE"
                           value="<?php print \ADACT\Config::MAX_UPLOAD_SIZE ?>" />
                    <label for="filef" class="btn-4 button small gray" style="display: inline-flex;">Upload a file...</label>
                    <i data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                       title="The zip file consists of a number of FASTA (the extension can be of any type)
                        files with no directories. Text file can contain multiple FASTA files separated by the standard header,
                        ie. text string followed by a ‘>’ sign."></i>
                    <input class="" type="file" id="filef" name="filef"
                           onchange="InputAnalyzer.init(this.form)" accept="*/*" />
                </form>
                <div class="fasta_method" id="filef_status" style="display: none;"></div>
                <!-- SEQUENCE TEXT -->
                <div class="fasta_method" id="input_seq" style="display: none;">
                    <label for="accn_gin" style="vertical-align: top;">Sequence in FASTA Format *</label>
                    <i data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                       title="The sequence must be in FASTA format, ie. separated by a header `>` and no hyphens."></i>
                    <textarea class="form-control" id="seq_text" name="seq_text" placeholder="Sequence in FASTA Format"
                              style="width: 100%; display: block;" rows="10" ></textarea><br />
                    <button class="btn-4 button small gray" id="analyze_seq_text" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button>
                </div>
                <!-- ACCN/GIN -->
                <div class="fasta_method" id="input_accn_gin" style="display: none;">
                    <label for="accn_gin" style="vertical-align: top;">Accession/GI numbers *</label>
                    <i data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                       title="Accession/GI numbers must be separated by commas
                        (eg. NM_009417, NM_001003009, 224465210, 50978625, 9507198, A3R4N5)."></i>
                    <textarea class="form-control" id="accn_gin" name="accn_gin" placeholder="Input Accession/GI Numbers"
                              style="width: 100%; display: block;" ></textarea><br />
                    <button class="btn-4 button small gray" id="analyze_accn_gin" onclick="InputAnalyzer.init()"
                            style="vertical-align: top">Analyze</button>
                </div>
                <!-- Analyze result table -->
                <div id="fasta_status" style="display: none"></div>
            </fieldset>
            <!-- Configurations -->
            <fieldset class="form-wrapper">
                <!-- Absent Word Type -->
                <fieldset>
                    <label class="control-label">Absent Word Type *: </label>
                    <div class="radio radio-adact" style="display: inline-block;">
                        <input id="aw_type_maw" type="radio" name="aw_type" value="maw"
                               onchange="$('.maw_dissimilarity').addClass('active').show();$('.raw_dissimilarity').removeClass('active').hide();$('#dissimilarity_index').val('');" />
                        <label for="aw_type_maw"><abbr title="Minimal Absent Words">MAW</abbr></label>
                    </div>
                    <div class="radio radio-adact" style="display: inline-block;">
                        <input id="aw_type_raw" type="radio" name="aw_type" value="raw"
                               onchange="$('.maw_dissimilarity').removeClass('active').hide();$('.raw_dissimilarity').addClass('active').show();$('#dissimilarity_index').val('');"/>
                        <label for="aw_type_raw"><abbr title="Relative Absent Words">RAW</abbr></label>
                    </div>
                </fieldset>
                <!-- K-Mer Size -->
                <fieldset>
                    <label class="control-label">
                        <span>K-Mer Size *: </span>
                        <i data-toggle="tooltip" class="glyphicon glyphicon-info-sign"
                           title="Be careful when choosing kmer size: they are not checked on the server side!"></i>
                    </label>
                    <input class="form-control input-sm" type="number" id="kmer_min" name="kmer_min" min="1"
                           style="width: 100px;display: inline-block;" placeholder="Min" required />
                    <input class="form-control input-sm" type="number" id="kmer_max" name="kmer_max" min="1"
                           style="width: 100px;display: inline-block" placeholder="Max" required />
                </fieldset>
                <!-- MAW Type -->
                <fieldset style="margin-bottom: 10px">
                    <label class="control-label">Sequence Type *: </label>
                    <div class="radio radio-adact" style="display: inline-block;">
                        <input type="radio" id="seq_type_nu" class="seq_type" name="seq_type" value="nucleotide"
                               onchange="$('#inversion_box').show();$('.active.dis_gcc').show();" checked  />
                        <label for="seq_type_nu">Nucleotide</label>
                    </div>
                    <div class="radio radio-adact" style="display: inline-block;">
                        <input type="radio" id="seq_type_pr" class="seq_type" name="seq_type" value="protein"
                                      onchange="$('#inversion_box').hide();$('.active.dis_gcc').hide();" />
                        <label for="seq_type_pr">Protein</label>
                    </div>
                </fieldset>
                <!-- Inversion -->
                <fieldset id="inversion_box">
                    <div class="material-switch pull-left">
                        <input id="inversion" name="inversion" type="checkbox"/>
                        <label for="inversion" class="label-adact"></label>
                    </div>
                    <label for="inversion" class="control-label" style="padding-left: 10px; padding-top: 0;">Reverse Complement</label>
                </fieldset>
                <!-- Dissimilarity Index -->
                <fieldset>
                    <label class="control-label" for="dissimilarity_index">Dissimilarity Index *</label>
                    <select id="dissimilarity_index" name="dissimilarity_index" class="form-control input-sm">
                        <option value="" disabled selected>Select One</option>
                        <?php
                        // MAW Dissimilarity Indexes
                        foreach ($dissimilarity_index['MAW'] as $short_form => $full_form){
                            $isGCC = strpos($short_form, 'GCC') !== false ? ' dis_gcc' : '';
                            print "<option class=\"maw_dissimilarity active{$isGCC}\" value=\"{$short_form}\">{$full_form}</option>\n";
                        }
                        // RAW Dissimilarity Indexes
                        foreach ($dissimilarity_index['RAW'] as $short_form => $full_form){
                            $isGCC = strpos($short_form, 'GCC') !== false ? ' dis_gcc' : '';
                            print "<option style=\"display: none;\" class=\"raw_dissimilarity{$isGCC}\" value=\"{$short_form}\">{$full_form}</option>\n";
                        }
                        ?>
                    </select>
                </fieldset>
            </fieldset>
            <button type="button" id="p_btn" class="btn-4 button small gray" onclick="Project.result.send()">
                <i class="fa fa-paper-plane" aria-hidden="true"></i> Run
            </button>
        </div>
    </div>
</div>
