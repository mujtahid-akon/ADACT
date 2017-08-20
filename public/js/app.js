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
 * array  project_config.uniprot_accn           ()
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
var databases      = [];

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
        async: false,
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
                    //analyze('file');
                    build_table();
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
        async: false,
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
 * Get databases
 */
var get_db = function(){
    $.ajax({
        method: 'get',
        url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/einfo.fcgi',
        data: 'retmode=json',
        cache: true,
        dataType: 'json',
        async: false,
        success: function(res){
            if(res.hasOwnProperty('einforesult') && res.einforesult.hasOwnProperty('dblist')){
                databases = res.einforesult.dblist;
            }
        },
        error: function(){
            databases = [];
        }
    });
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
        async: false,
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
                give_short_name(InputMethod.GIN, gi_numbers, info);
            }
        },
        error: function(){
            selector.html("<span class='text-danger'>Analyzing failed! <a href='javascript:analyze(\"gin\")'>Click here</a> to try again.</span>")
        }
    });
};

var analyze_uniprot = function () {
    var selector = $('#fasta_status');
    selector.show();
    var uniprot_accn = ($('#uniprot').val()).trim();
    uniprot_accn = uniprot_accn.split(/\s*,\s*/);

    project_config['uniprot_accn'] = uniprot_accn;
    if(uniprot_accn.length < 1 || uniprot_accn[0] === ""){
        selector.html("<span class='text-danger'>No UniProt ACCN number is provided!</span>");
        return;
    }
    // Get UniProt ACCN related info
    var count  = uniprot_accn.length;
    var names = [];
    while(count--){
        $.ajax({
            method: 'get',
            url: 'http://www.uniprot.org/uniprot/',
            data: 'query=accession:' + uniprot_accn[count] + '&format=xml',
            cache: false,
            dataType: 'xml',
            async: false,
            beforeSend: function(){
                selector.html("<img width='11' src='css/images/spinner.gif'> Analyzing...")
            },
            success: function(res){
                var name = $(res).find('fullName').text();
                // console.log(name);
                names.push(name);
            },
            error: function(){
                selector.html("<span class='text-danger'>Analyzing failed! <a href='javascript:analyze(\"gin\")'>Click here</a> to try again.</span>")
            }
        });
    }
    give_short_name(InputMethod.UNIPROT, uniprot_accn, names);
};

/**
 *
 * @param {String} type  InputMethod.FILE|InputMethod.GIN|InputMethod.UNIPROT
 * @param {Array}  ids   for GIN: gin numbers, for FILE: file names, for UNIPROT: uniprot accn
 * @param {Array}  info  (not needed for file) info object
 */
var give_short_name = function(type, ids, info){
    var names = [];
    if(type === InputMethod.GIN){
        for(var i = 0; i< ids.length; ++i){
            if(info.hasOwnProperty(ids[i])){
                if(info[ids[i]].hasOwnProperty('organism')){
                    names.push(info[ids[i]]['organism']);
                }
            }
        }
    }else if(type === InputMethod.UNIPROT){
        names = info;
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
        + "<p>Add short names using the table below: "
        + "(A short name should be less than 15 characters with no spaces)</p>"
        + "<table class='table table-bordered table-striped table-hover'>"
        + "<thead><tr><th>Full Name</th><th>Short Name</th></tr></thead>";
    for(var i = 0; i<c_names; ++i){
        html += "<tr><td>" + names[i] + "</td><td><input type='text' id='sn_" + i + "' class='short_name form-control' value='" + names[i] + "'/></td></tr>";
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
                    break;
                case 'uniprot':
                    chk = $('#analyze_uniprot');
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


// TODO: use a class instead of using arbitrary functions
// FIXME: To allow multiple projects for one user, use different project id (may not necessary)

/**
 * InputMethod Object
 *
 * Defines FASTA source
 *
 * @type {{}}
 */
var InputMethod = {
    FILE    : "file",
    ACCN    : "accn",
    GIN     : "gin",
    UNIPROT : "uniprot", // A3R4N4, A5HBD7
    current : null,
    /**
     * Set FASTA file input method
     * @param {string} id
     */
    setCurrent : function(id){
        $('.fasta_method').hide();
        $('#' + id).show();
        switch (id){
            case "upload_file":
                this.current = this.FILE;
                break;
            case "input_gin":
                this.current = this.GIN;
                break;
            case "input_accn":
                this.current = this.ACCN;
                break;
            case "input_uniprot":
                this.current = this.UNIPROT;
        }

        if(this.current === this.UNIPROT) {
            $(".maw_type[value=protein]").prop("checked", true);
            $(".maw_type").prop("disabled", true);
        }else{
            $(".maw_type[value=dna]").prop("checked", true);
            $(".maw_type").prop("disabled", false);
        }
        // FIXME: Project.config.type
        project_config['type'] = this.current;
    },
    /**
     * Get current FASTA file input method
     */
    getCurrent : function(){
        return this.current;
    }
};

/**
 * InputAnalyzer Object
 *
 * Analyzes user input
 *
 * @type {{init: InputAnalyzer.init, accn: InputAnalyzer.accn}}
 */
var InputAnalyzer = {
    init: function () {
        switch (InputMethod.getCurrent()){
            case InputMethod.ACCN:
                analyze_accn();
                break;
            case InputMethod.GIN:
                analyze_gin();
                break;
            case InputMethod.UNIPROT:
                analyze_uniprot();
        }
    },
    accn: function () {
        //
    }
};

/**
 * Project Object
 * @type {{}}
 */
var Project = {};

/**
 * @var Object Project.config
 *
 * string Project.config.project_name           Name of the project
 * string Project.config.aw_type                maw|raw
 * int    Project.config.kmer_min               K-mer min
 * int    Project.config.kmer_max               K-mer max
 * bool   Project.config.inversion              Allow inversion?
 * string Project.config.maw_type               dna|protein (optional for 'raw')
 * string Project.config.dissimilarity_index    Dissimilarity Index
 * array  Project.config.accn_numbers           (optional for 'gin' and 'file')
 * string Project.config.type                   file|gin|accn
 * array  Project.config.gi_numbers             (optional for 'file')
 * array  Project.config.names                  Full Species names with small description
 * array  Project.config.short_names            User assigned short species names
 */
//Project.config = {};

