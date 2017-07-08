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
    .alert{
        margin-bottom: 10px !important;
    }
</style>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="h1" id="p_name">New Project</div>
        <div class="form form-horizontal">
            <!-- Project Name -->
            <fieldset class="form-wrapper">
                <input class="form-control" type="text" id="project_name" name="project_name"
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
                </select>
                <button class="btn btn-primary" id="change_method"
                        onclick="change_method($('#method').val())" style="vertical-align: top">Done</button>
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
                    <button class="btn btn-primary" id="analyze_gin" onclick="analyze('gin')"
                            style="vertical-align: top">Analyze</button><br />
                    <small>GI numbers must be separated by a comma with NO spaces
                        (eg. 24475906,224465210,50978625,9507198).</small>
                </div>
                <!-- ACCN -->
                <div class="fasta_method" id="input_accn" style="display: none;">
                    <label for="accn">Accession numbers</label>
                    <input class="form-control" id="accn" name="accn" placeholder="Input Accession Numbers"
                           style="width: unset; display: inline;" />
                    <button class="btn btn-primary" id="analyze_accn" onclick="analyze('accn')"
                            style="vertical-align: top">Analyze</button><br />
                    <small>Accession numbers must be separated by a comma with NO spaces
                        (eg. NM_009417,NM_001003009).</small>
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
                    <label><input type="radio" id="maw_type" name="maw_type" value="dna" checked  /> DNA</label>
                    <label><input type="radio" id="maw_type" name="maw_type" value="protein" /> Protein</label>
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
<script>
/**
 * @var Object project_config
 *
 * string project_config.project_name           Name of the project
 * string project_config.aw_type                maw|raw
 * int    project_config.kmer_min               K-mer min
 * int    project_config.kmer_max               K-mer max
 * bool   project_config.inversion              Allow inversion?
 * string project_config.maw_type               dna|protein (optional for 'raw')
 * string project_config.dissimilarity_index    Dissimilarity Index
 * array  project_config.accn_numbers           (optional for 'gin' and 'file')
 * string project_config.type                   file|gin|accn
 * array  project_config.gi_numbers             (optional for 'file')
 * array  project_config.names                  Full Species names with small description
 * array  project_config.short_names            User assigned short species names
 */
var project_config = {};
project_config['short_names'] = [];
// In the case of 'file_upload', file_method_on is set to true means file has been uploaded
var file_method_on = false;
var file_method    = 'upload_file';
var file_done      = false;

/**
 * Upload file to server
 *
 * @param form
 */
var upload = function(form){
    var conf = confirm("Are you sure want to upload this file?");
    if(!conf) return false;
    var status = $('#filef_status');
    status.show();
    $.ajax({
        url: "projects/file_upload",
        type: "POST",
        data: new FormData(form),
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'JSON',
        beforeSend: function(){
            status.html('<div class="alert alert-info"><img width="11" src="css/images/spinner.gif"> Uploading...</div>');
            $('#fasta_status').html('');
        },
        success: function(res){
            switch(res.status){
                case 0: // FILE_UPLOAD_SUCCESS
                    $('#upload_file').hide();
                    status.html('<div class="alert alert-success"><strong>Success!</strong> ' + 
                                'The file was uploaded successfully.</div>' + 
                                '<div class="btn btn-primary" id="upload_new" onclick="$(\'#filef_status\').hide();' +
                                '$(\'#upload_file\').show();$(\'#method\').removeAttr(\'disabled\');">Upload a new file</div>');
                    project_config['names'] = res.names;
                    lock_fasta_method();
                    analyze('file');
                    break;
                case 1: // FILE_SIZE_EXCEEDED
                    status.html('<div class="alert alert-danger"><strong>Upload Failed!</strong> Make sure,<br />' +
                               '<ul>' +
                               '<li>The zip file size is less than 100MB</li>' +
                               '<li>The FASTA file size is less than 20MB</li>' +
                               '</ul>' +
                               '</div>');
                    break;
                case 2: // FILE_INVALID_MIME
                    status.html('<div class="alert alert-danger"><strong>Upload Failed!</strong> Make sure the file is a valid zip file.</div>');
                    break;
                case 3: // FILE_INVALID_FILE
                    status.html('<div class="alert alert-danger"><strong>Upload Failed!</strong> Make sure,<br />' +
                               '<ul>' +
                               '<li>The zip file is valid</li>' +
                               '<li>The zip file is in right format.</li>' +
                               '<li>The zip file size is less than 100MB</li>' +
                               '<li>The FASTA file size is less than 20MB</li>' +
                               '</ul>' +
                               '</div>');
                    break;
                default: // FILE_UPLOAD_FAILED
                    status.html('<div class="alert alert-danger"><strong>Upload Failed!</strong> Please try again. There may be connection problem.</div>');
            }
            $("#filef").val('');
        },
        error: function(){
            status.html('<div class="alert alert-danger"><strong>Upload Failed!</strong> There may be connection problem.</div>');
        }
    });
};

/**
 * Restore the form: part of result()
 */
var restore = function(){
    alert("An error occured, try again");
    var btn = $('#p_btn');
    btn.removeClass('btn-default disabled');
    btn.addClass('btn-primary');
    btn.attr('onclick', 'result()');
    btn.html("Show Result");
};

/**
 * Show the result
 */
var result = function(){
    var conf = confirm("Are you sure?");
    if(!conf) return false;
    var status = true;
    /**
     * project configurations
     */
    // 1. Project Name
    project_config['project_name'] = $('#project_name').val();
    if(project_config['project_name'] === "") status = false;
    // 2. AW Type
    project_config['aw_type'] = $("input[name='aw_type'][value='raw']").is(':checked') ? 'raw' : 'maw';
    // 3. K-Mer Min
    project_config['kmer_min'] = $('#kmer_min').val();
    if(project_config['kmer_min'] === "") status = false;
    // 4. K-Mer Max
    project_config['kmer_max'] = $('#kmer_max').val();
    if(project_config['kmer_max'] === "") status = false;
    // 5. Inversion
    project_config['inversion'] = $('#inversion').is(":checked");
    // 6. Dissimilarity Index
    project_config['dissimilarity_index'] = $('#dissimilarity_index').val();
    if(project_config['dissimilarity_index'] === "") status = false;
    // 7. MAW Type
    if(project_config['aw_type'] === 'maw'){
        project_config['maw_type'] = $("input[name='maw_type'][value='protein']").is(':checked') ? 'protein' : 'dna';
    }
    if(!status || !file_done){
        alert('It seems, you\'ve left out some mandatory fields. Please fill them in.');
        return false;
    }
    
    $.ajax({
        method: 'post',
        url: 'projects/new',
        data: {config: JSON.stringify(project_config)},
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            var btn = $('#p_btn');
            btn.removeClass('btn-primary');
            btn.addClass('btn-default disabled');
            btn.attr('onclick', null);
            btn.html("<img width='11' src='css/images/spinner.gif'> Loading...");
            // TODO: Time checking
        },
        success: function(res){
            if(res !== null && res.id !== null) window.location.assign('projects/' + res.id);
            else restore();
        },
        error: function(xhr, status){
            if(status !== null) restore();
        }
    });
};

/**
 * Change FASTA file input method
 * @param id
 */
var change_method = function(id){
    $('.fasta_method').hide();
    $('#' + id).show();
//    $('#method').attr('disabled', '');
//    $('#change_method').html('Change');
    file_method = id;
};

/**
 * analyze fasta files and prompt user to input short names
 * @param type there are three types: 'accn', 'gin', 'file'
 */
var analyze = function (type) {
    project_config['type'] = type;
    switch (type){
        case 'accn':
            analyze_accn();
            break;
        case 'gin':
            analyze_gin();
            break;
        case 'file':
            build_table();
    }
};

var analyze_accn = function(){
    var selector = $('#fasta_status');
    var accn_numbers = ($('#accn').val()).trim();
    accn_numbers = accn_numbers.split(/\s*,\s*/);

    project_config['accn_numbers'] = accn_numbers;
    selector.show();
    if(accn_numbers.length < 1 || accn_numbers[0] === ""){
        selector.html("<span class='text-danger'>No Accession number is provided!</span>");
        return;
    }
    var data = accn_numbers.join('[accn]+OR+') + '[accn]'; // eg. NM_009417[accn]+OR+NM_000547[accn]
    // convert it to GI numbers
    $.ajax({
        method: 'get',
        url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi',
        data: 'db=nucleotide&term=' + data + '&retmode=json',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            selector.html("<img width='11' src='css/images/spinner.gif'> Analyzing...");
        },
        success: function(res){
            if(res.hasOwnProperty('esearchresult') && res.esearchresult.hasOwnProperty('idlist')){
                var gin = res.esearchresult.idlist;
                if(gin.length > 0) analyze_gin(gin);
                else{
                    selector.html("<span class='text-danger'>No valid Accession number is provided!</span>");
                }
            }
        },
        error: function(){
            selector.html("<span class='text-danger'>Analyzing failed! <a href='javascript:analyze(\"accn\")'>Click here</a> to try again.</span>")
        }
    });
};

var analyze_gin = function(gin){
    var selector = $('#fasta_status');
    selector.show();
    var is_accn  = (gin !== undefined);
    var gi_numbers;
    if(!is_accn){
        gi_numbers = ($('#gin').val()).trim();
        gi_numbers = gi_numbers.split(/\s*,\s*/);
    }else gi_numbers = gin;

    project_config['gi_numbers'] = gi_numbers;

    if(gi_numbers.length < 1 || gi_numbers[0] === ""){
        selector.html("<span class='text-danger'>No GI number is provided!</span>");
        return;
    }
    // Get GI related info
    $.ajax({
        method: 'get',
        url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi',
        data: 'db=nucleotide&id=' + project_config['gi_numbers'] + '&retmode=json',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            selector.html("<img width='11' src='css/images/spinner.gif'> Analyzing...")
        },
        success: function(res){
            if(res.hasOwnProperty('result')){
                var info = res.result;
                give_short_name('gin', gi_numbers, info);
            }
        },
        error: function(){
            selector.html("<span class='text-danger'>Analyzing failed! <a href='javascript:analyze(\"gin\")'>Click here</a> to try again.</span>")
        }
    });
};

/**
 *
 * @param type Can be either 'gin' or 'file'
 * @param ids  for gin: gin numbers, for file: file names
 * @param info (not needed for file) info object
 */
var give_short_name = function(type, ids, info){
    var names = [];
    if(type === 'gin'){
        for(var i = 0; i< ids.length; ++i){
            if(info.hasOwnProperty(ids[i])){
                if(info[ids[i]].hasOwnProperty('organism')){
                    names.push(info[ids[i]]['organism']);
                }
            }
        }
    }else names = ids;
    var selector = $('#fasta_status');
    selector.show();
    if(names.length > 0){
        // build table for short names
        project_config['names'] = names;
        build_table();
    }else{
        selector.html("<span class='text-danger'>Nothing's found! Try again.</span>");
    }
};

var build_table = function(){
    // First lock the FASTA method
    lock_fasta_method();

    var names = project_config['names'];
    var c_names = names.length;
    var selector = $('#fasta_status');
    selector.show();
    selector.html("<img width='11' src='css/images/spinner.gif'> Analyzing...");
    var html = "<p class='text-success'>Found: " + c_names + " Species</p>"
        + "<input type='hidden' id='count_names' value='" + c_names + "'/>"
        + "<p>Add short names using the table below:"
        + "(A short name should be less than 15 characters with no spaces)</p>"
        + "<small class='text-info'>Double click on any Full Name to copy it to Short Name.</small>"
        + "<table class='table table-bordered table-striped table-hover'>"
        + "<thead><tr><th>Full Name</th><th>Short Name</th></tr></thead>";
    for(var i = 0; i<c_names; ++i){
        html += "<tr><td ondblclick='copy_to_sn($(this))'>" + names[i] + "</td><td><input type='text' id='sn_" + i + "' class='short_name form-control' /></td></tr>";
    }
    html += "</table>"
        + "<button class=\"btn btn-primary\" id=\"fasta_check_out\" "
        + "onclick=\"check_out()\" style=\"vertical-align: top\">Done</button>";
    selector.html(html);
};

var lock_fasta_method = function(){
    file_method_on = true;
    $('#method').attr('disabled', '');
    var selector = $('#change_method');
    selector.attr('disabled', '');
    selector.removeClass('btn-primary');
    selector.addClass('btn-default');
};

var check_out = function(){
    project_config['short_names'] = [];
    var c_names = project_config['names'].length;
    var status  = true;
    var i;
    for(i = 0; i<c_names; ++i){
        var s_name = $('#sn_' + i).val();
        (project_config['short_names']).push(s_name);
        if(s_name === null || s_name === "") status = false;
    }
    if(status === false) alert("You must fill all the short names!");
    else{
        var conf = confirm("Are you sure?");
        if(conf){
            var chk = $('#fasta_check_out');
            chk.attr('disabled', '');
            chk.removeClass('btn-primary');
            chk.addClass('btn-default');
            switch(project_config['type']){
                case 'file':
                    chk = $('#upload_new');
                    break;
                case 'accn':
                    chk = $('#analyze_accn');
                    break;
                case 'gin':
                    chk = $('#analyze_gin');
            }
            chk.attr('disabled', '');
            chk.removeClass('btn-primary');
            chk.addClass('btn-default');

            for(i = 0; i<c_names; ++i){
                var selector = $('#sn_' + i).parent();
                selector.html(project_config['short_names'][i]);
            }
            file_done = true;
        }
    }
};

var copy_to_sn = function(selector){
    selector.parents().get(0).children[1].children[0].value = selector.text();
}
</script>