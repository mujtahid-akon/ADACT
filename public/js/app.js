/**
 * ProgressBar class constructor
 *
 * @param {*|jQuery}         selector
 * @param {int|undefined}    [max_value]
 * @param {int|undefined}    [init_value]
 * @param {string|undefined} [text]
 * @constructor
 */
function ProgressBar(selector, max_value, init_value, text){
    /**
     * Progressbar selector
     * @type {*|jQuery|null}
     */
    this.selector   = selector;
    /**
     * Maximum value for progressbar
     * @type {int|undefined}
     */
    this.max_value  = max_value;
    /**
     * Initial value for progressbar
     * @type {int|undefined}
     */
    this.init_value = init_value;
    /**
     * Set value for progress bar
     * @param {int} value
     */
    this.value = function(value){
        if(this.selector === null) return false;
        this.selector.val(value);
        return true;
    };
    /**
     * Increment progress bar
     * @param {int} [byWhat]
     */
    this.increment = function(byWhat){
        if(this.selector === null) return false;
        this.selector.val(this.selector.val() + (byWhat || 1));
        return true;
    };
    // __construct
    const caption = text || " " || "<div>" + text + "</div>";
    if(this.max_value){
        this.selector.html(caption + "<progress max=\"" + this.max_value + "\" value=\"" + this.init_value + "\" style=\"width: 100%\" />");
        this.selector = this.selector.children().eq((text ? 1 : 0));
    }else{
        this.selector.html(caption + "<progress style=\"width: 100%\" />");
        this.selector = null;
    }
}

/**
 * Message Object
 */
Object.freeze(Messages = {
    ShortName : {
        CHAR_CONSTRAINT     : "Short names must contain a to z (uppercase or lower case) letters, underscores, hyphens or commas.",
        /** @return {string} */
        CHAR_LIMIT_EXCEEDED : function(max){ return "Short names can contain at most " + max + " characters!"; },
        UNFILLED_FIELDS     : "You must fill all the short names!",
        DUPLICATE_ENTRIES   : "There are duplicate values. You must set unique short names!"
    },
    InputAnalyzer : {
        ANALYZING_TEXT : "Analyzing...",
        UPLOADING_TEXT : "Uploading...",
        PostProcess    : {
            MIXED_INPUTS   : "It looks like you are trying to use nucleotides & proteins at the same time. Please use only nucleotide or only proteins.",
            /** @return {string} */
            INVALID_INPUTS : function (invalid_ids) {
                return (invalid_ids.length > 1 ?
                    "These Accession/GI numbers appear to be invalid: " :
                    "This Accession/GI number appears to be invalid: ")
                    + invalid_ids.join(', ');
            }
        },
        Upload : {
            UPLOAD_NEW_TEXT : "Upload a new file",
            SUCCESS_TEXT : "Upload success!",
            SUCCESS_MESSAGE : "The file was uploaded successfully.",
            FAILURE_ALERT : "Upload failed!",
            CONNECTION_PROBLEM : this.CONNECTION_PROBLEM,
            MAKE_SURE : "Make sure,",
            /** @return {Array} */
            FAILURE_MESSAGE : function (file_limit) {
                return [
                'The text/zip file is valid and in the right format',
                'In case of zip file, the size must be less than 100 MB',
                'The size of each sequence is less than 20 MB',
                'There cannot be more than ' + file_limit + ' sequence in a zip/text file'
                ];
            }
        },
        BuildTable : {
            /** @return {String} */
            SEQ_FOUND : function (seq_count) { return "Found: " + seq_count + " FASTA Sequences"; },
            SHORT_NAME_MESSAGE : "Add short names using the table below: (A short name can only contain a to z (uppercase or lower case) letters, underscores, hyphens or commas)",
            ID_TEXT : "ID",
            HEADER_TEXT : "Title/Header",
            SHORT_NAME_TEXT : "Short Name"
        }
    },
    Project: {
        UNFILLED_FIELDS : "It seems, you've left out some mandatory fields. Please fill them in.",
        LOADING_TEXT : "Loading...",
        FAILURE_ALERT : "An error occurred, try again",
        Status : {
            FETCH_TEXT : "Fetching last status...",
            FAILURE_ALERT : "Something's wrong with your project. Please try again.",
            FAILURE_TEXT : "Error fetching last status. Trying again..."
        },
        Cancel : {
            /** @return {String} */
            CANCEL_MESSAGE : function (project_name) { return "Are you sure want to cancel " + project_name + "?"; },
            SUCCESS_MESSAGE : "Project has been cancelled successfully! If you're editing the project, we'll try to keep the previous results if possible.",
            FAILURE_MESSAGE : "Couldn't cancel the project, it may have already been processed, or it doesn't exists or may have already been cancelled."
        },
        Delete : {
            /** @return {String} */
            DELETE_MESSAGE : function (project_name) { return "Are you sure want to delete " + project_name + "?"; },
            FAILURE_MESSAGE : "Couldn't delete the project, it doesn't exists or may have already been deleted."
        },
        Notification : {
            FETCH_TEXT : "Fetching notifications...",
            NO_NOTIFICATION : "No new notification.",
            CONNECTION_PROBLEM: "Connection problem!"
        }
    },
    FAILURE_ALERT : "Failed!",
    CONNECTION_PROBLEM : "Please try again. There may be connection problem."
});

/**
 * InputMethod Object
 *
 * Defines FASTA source
 *
 * @type {{FILE: string, ACCN_GIN: string, current: null|string, setCurrent: InputMethod.setCurrent, getCurrent: InputMethod.getCurrent}}
 */
InputMethod = {
    // Constants
    FILE     : "file",
    ACCN_GIN : "accn_gin",
    /**
     * Current InputMethod
     * NOTE: don't access it directly, use InputMethod.getCurrent() instead
     * @type {string|null}
     */
    current : null,
    /**
     * set current method
     *
     * @param {String} id
     */
    setCurrent : function(id){
        // Hide all except the requested method
        $('.fasta_method').hide();
        $('#fasta_status').hide();
        $('#' + id).show();
        // set current method
        switch (id){
            case "upload_file":
                this.current = this.FILE;
                break;
            case "input_accn_gin":
                this.current = this.ACCN_GIN;
        }
    },
    /**
     * Get current FASTA file input method
     * @return {string|null}
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
 * @type {{GIN: string, ACCN: string, NUCLEOTIDE: string, PROTEIN: string, DB_NUCCORE: string, DB_PROTEIN: string, inputs: Array, selector: null, progress: null, results: Array, file_id: null, init: InputAnalyzer.init, addShortNames: InputAnalyzer.addShortNames, getMetaData: InputAnalyzer.getMetaData, renderer: InputAnalyzer.renderer, upload: InputAnalyzer.upload, buildTable: InputAnalyzer.buildTable, getShortName: InputAnalyzer.getShortName}}
 */
InputAnalyzer = {
    // ID Constants
    GIN  : "gin",
    ACCN : "accn",
    // SEQ_TYPE constants
    NUCLEOTIDE  : "nucleotide",
    PROTEIN: "protein",
    // DB Constants
    DB_NUCCORE: "nuccore",
    DB_PROTEIN: "protein",
    // Char limit
    CHAR_LIMIT: 15,
    /**
     * Store input values from the input fields
     * (only for InputMethod.ACCN_GIN)
     * @type {Array}
     * @type {string[]}
     */
    inputs: [],
    /** @type {*|jQuery|null} */
    selector: null,
    /** @type {null|ProgressBar} */
    progress: null,
    /** @type {{id: int|string, id_type: string, title: string|null, type: string|null, gin: int|null, short_name: string|null}[]} */
    results: [],
    /** @type {string|null} File id (only for InputMethod.FILE) */
    file_id: null,
    /** @type boolean Whether analyzing input finished */
    done: false,
    /** @param [form] form Initialize analyzer based on current input method */
    init: function (form) {
        const parent = this;
        this.selector = $('#fasta_status');
        // show status
        this.selector.html("");
        this.selector.show();

        if(InputMethod.getCurrent() === InputMethod.ACCN_GIN){
            // Get & process the input IDs
            this.inputs = $("#accn_gin").val();
            this.inputs = this.inputs.split(/\s*,\s*/);
            this.results = [];
            //console.log(this.inputs);
            this.progress = new ProgressBar(this.selector, this.inputs.length, 0, Messages.InputAnalyzer.ANALYZING_TEXT);
            $.each(this.inputs, function (i, id) {
                /** @type {string} Which type of ID the user inserted */
                const id_type = /^[\d]+$/.test(id) ? parent.GIN : parent.ACCN;
                parent.getMetaData(id, id_type);
            });
        }else if(InputMethod.getCurrent() === InputMethod.FILE){
            // Upload file
            this.upload(form);
        }
    },
    addShortNames: function(){
        // FIXME: Also filter these in PHP file
        const CHAR_LIMIT_EXCEEDED = 1;
        const UNFILLED_FIELDS     = 2;
        const DUPLICATE_ENTRIES   = 3;
        const CHAR_CONSTRAINT     = 4;

        /**
         * Count total entries
         * @type {Number}
         */
        const c_entries = this.results.length;
        /**
         * Status code for filters
         * Code    Constant            Meaning
         * ----    --------            -------
         * 0       (default)           Success
         * 1       CHAR_LIMIT_EXCEEDED The 15 character limit exceeded
         * 2       UNFILLED_FIELDS     There are some unfilled fields
         * 3       DUPLICATE_ENTRIES   There are duplicate entries
         * 4       CHAR_CONSTRAINT     Used any character other than /\w-\s/
         * @type {int}
         */
        let status = 0;
        const parent = this;

        // Get current short name values
        for(let i = 0; i < c_entries; ++i){
            this.results[i].short_name = $('#sn_' + i).val();
        }

        // Checks
        // 1. Check if all the fields are set
        // 2. Check for Character limits
        // 4. Character usage
        for(let i = 0; i<c_entries; ++i){
            const s_name = $('#sn_' + i).val();
            this.results[i].short_name = s_name;
            if(!s_name){
                status = UNFILLED_FIELDS;
                break;
            }
            if(s_name.length > this.CHAR_LIMIT){
                status = CHAR_LIMIT_EXCEEDED;
                break;
            }
            if(!(/^[\w,-]+$/.test(s_name))){
                status = CHAR_CONSTRAINT;
                break;
            }
        }
        // 4. Check for duplicate values
        const duplicates = getDuplicates(getShortNames());
        if(duplicates.length > 0) status = DUPLICATE_ENTRIES;

        switch(status){
            case CHAR_CONSTRAINT:
                alert(Messages.ShortName.CHAR_CONSTRAINT);
                break;
            case CHAR_LIMIT_EXCEEDED:
                alert(Messages.ShortName.CHAR_LIMIT_EXCEEDED(this.CHAR_LIMIT));
                break;
            case UNFILLED_FIELDS:
                alert(Messages.ShortName.UNFILLED_FIELDS);
                break;
            case DUPLICATE_ENTRIES:
                alert(Messages.ShortName.DUPLICATE_ENTRIES);
                break;
            default: // Success = 0
                this.done = true;
                let chk = $('#fasta_check_out');
                chk.attr('disabled', '');
                chk.removeClass('btn-primary');
                chk.addClass('btn-default');
                switch(InputMethod.getCurrent()){
                    case InputMethod.FILE:
                        chk = $('#upload_new');
                        break;
                    case InputMethod.ACCN_GIN:
                        chk = $('#analyze_accn_gin');
                }
                chk.attr('disabled', '');
                chk.removeClass('btn-primary');
                chk.addClass('btn-default');

                for(let i = 0; i<c_entries; ++i){
                    const selector = $('#sn_' + i).parent();
                    selector.html(this.results[i].short_name);
                }
        }

        /**
         * Get short names
         * @return {Array}
         */
        function getShortNames() {
            const short_names = [];
            for(let i = 0; i < parent.results.length; ++i){
                if(parent.results[i].short_name !== null) short_names.push(parent.results[i].short_name);
            }
            return short_names;
        }

        /**
         * Get duplicate values
         *
         * @param {Array} array
         * @return {Array}
         */
        function getDuplicates(array) {
            let i = 0, m = [];
            return array.filter(function (n) {
                return !m[n] * ~array.indexOf(n, m[n] = ++i);
            });
        }
    },
    // DON'T Call these functions! They are private!
    /**
     * Get meta data using AJAX
     *
     * @param {string} id
     * @param {string} id_type
     */
    getMetaData: function(id, id_type){
        const parent = this;
        /**
         * Response data
         * @type {{id: int|string, id_type: string, title: string|null, type: string|null, gin: int|null, short_name: string|null}}
         */
        const response = {
            id: id,
            id_type: id_type,
            title: null,
            type: null,
            gin: null,
            short_name: null
        };
        /**
         * Process JSON data
         *
         * @param {string} db
         * @param {{result}} data
         */
        function processData(db, data) {
            /**
             * @var {uids|Object[]}  data.result
             * @var {int[]} data.result.uids
             */
            if(data.result && data.result.uids){
                /** @type {int[]} GI Numbers */
                const gin = data.result.uids;
                if(gin.length === 1){
                    response.gin   = gin[0];
                    response.title = data.result[gin].title;
                    response.type  = (db === parent.DB_NUCCORE) ? parent.NUCLEOTIDE : parent.PROTEIN;
                    /** @var {{organism: String}} data.result */
                    response.short_name = data.result[gin].organism;
                }
            }
        }
        
        function postProcess() {
            parent.results.push(response);
            parent.progress.increment();
            if(parent.inputs.length === parent.results.length){
                //console.log(this.results);
                // There might be three scenario: nucleotide, protein, mixed
                // Nucleotides only or Proteins only are correct, but not the mixed one

                // Set SEQ_TYPE based on retrieved info
                const len  = parent.results.length;
                if(len <= 0) return;
                // Get the common type
                const type = parent.results[0].type;
                // Get invalid ACCN/GID
                const invalid_ids = [];
                // Analyze
                for(let i = 0; i < len; ++i){
                    if(parent.results[i].type === null){
                        invalid_ids.push(parent.results[i].id);
                    }else if(parent.results[i].type !== type){
                        alert(Messages.InputAnalyzer.PostProcess.MIXED_INPUTS);
                        parent.selector.hide();
                        return;
                    }
                }
                // Show warning for invalid ACCN/GID
                if(invalid_ids.length > 0){
                    alert(Messages.InputAnalyzer.PostProcess.INVALID_INPUTS(invalid_ids));
                    parent.selector.hide();
                    return;
                }
                // UI Changes
                if(type === parent.PROTEIN) {
                    $(".seq_type[value=protein]").prop("checked", true);
                    $(".seq_type").prop("disabled", true);
                }else if(type === parent.NUCLEOTIDE){
                    $(".seq_type[value=nucleotide]").prop("checked", true);
                    $(".seq_type").prop("disabled", true);
                }

                // build table
                parent.buildTable();
            }
        }

        this.renderer(this.DB_NUCCORE, id, function (db, data) {
            processData(parent.DB_NUCCORE, data); // Is it a nucleotide?
            if(response.gin === null){ // Oh, Maybe it's a protein
                parent.renderer(parent.DB_PROTEIN, id, function (db, data) {
                    processData(parent.DB_PROTEIN, data);
                    postProcess();
                });
            }else{
                postProcess();
            }
        });
    },
    /**
     * AJAX handler for getting meta data from ncbi's website
     *
     * @param {string} db
     * @param {string} id
     * @param {function} callBack
     */
    renderer: function(db, id, callBack){
        const parent = this;
        $.ajax({
            method: 'get',
            url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi',
            data: 'db='+db+'&id='+id+'&retmode=json',
            cache: false,
            dataType: 'json',
            success: function (data) {
                callBack(db, data);
            },
            error: function(){
                parent.selector.html('<div class="alert alert-danger">' +
                    '<strong>' + Messages.FAILURE_ALERT + '</strong> ' +
                    Messages.CONNECTION_PROBLEM +
                    '</div>');
            }
        });
    },
    /**
     * Upload file to server
     * @param form
     */
    upload: function (form) { // TODO: check if this user previously uploaded any files in the same project and delete it
        const upload_sel = $('#filef_status');
        upload_sel.show();
        const parent = this;

        $.ajax({
            url: "./projects/file_upload",
            type: "POST",
            data: new FormData(form),
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'JSON',
            beforeSend: function(){
                new ProgressBar(upload_sel, undefined, undefined, Messages.InputAnalyzer.UPLOADING_TEXT);
            },
            /**
             * Do this on success
             * @param {{status: int, [data]: Array, [id]: string}} res Data return only if the status is FILE_UPLOAD_SUCCESS
             */
            success: function(res){
                switch(res.status){
                    case 0: // FILE_UPLOAD_SUCCESS
                        $('#upload_file').hide();
                        upload_sel.html('<div class="alert alert-success">' +
                            '<strong>' + Messages.InputAnalyzer.Upload.SUCCESS_TEXT + '</strong> ' +
                            Messages.InputAnalyzer.Upload.SUCCESS_MESSAGE +
                            '</div>' +
                            '<div class="btn btn-primary" id="upload_new" onclick="$(\'#filef_status\').hide();' +
                            '$(\'#upload_file\').show();$(\'#method\').removeAttr(\'disabled\');">' +
                            Messages.InputAnalyzer.Upload.UPLOAD_NEW_TEXT +
                            '</div>');
                        /**
                         * @var {{id: int, header: string}[]} res.data
                         */
                        /**
                         * Result as status is FILE_UPLOAD_SUCCESS
                         * @type {{id: int, header: string}[]}
                         */
                        const results    = res.data;
                        parent.file_id = res.id;
                        parent.results = [];
                        for(let i = 0; i < results.length; ++i){
                            let result = {
                                id: results[i].id,
                                id_type: 'file',
                                title: results[i].header,
                                short_name: null,
                                type: InputMethod.FILE,
                                gin: null
                            };
                            parent.results.push(result);
                        }
                        parent.buildTable();
                        break;
                    default: // FILE_UPLOAD_FAILED
                        upload_sel.html('<div class="alert alert-danger">' +
                            '<strong>' + Messages.InputAnalyzer.Upload.FAILURE_ALERT + '</strong> ' +
                            Messages.InputAnalyzer.Upload.MAKE_SURE + '<br />' +
                            '<ul>' +
                            '<li>' + Messages.InputAnalyzer.Upload.FAILURE_MESSAGE(parent.CHAR_LIMIT).join('</li><li>') + '</li>' +
                            '</ul>' +
                            '</div>');
                }
                $("#filef").val('');
            },
            error: function(){
                upload_sel.html('<div class="alert alert-danger">' +
                    '<strong>' + Messages.InputAnalyzer.Upload.FAILURE_ALERT + '</strong> ' +
                    Messages.InputAnalyzer.Upload.CONNECTION_PROBLEM +
                    '</div>');
            }
        });
    },
    /**
     * Build table to input short names
     */
    buildTable: function () {
        /**
         * Count total species
         * @type {int}
         */
        const c_species = this.results.length;
        /**
         * HTML output
         * @type {string}
         */
        let html = "<p class='text-success'>" + Messages.InputAnalyzer.BuildTable.SEQ_FOUND(c_species) + "</p>"
            + "<p>" + Messages.InputAnalyzer.BuildTable.SHORT_NAME_MESSAGE + "</p>"
            + "<table class='table table-bordered table-striped table-hover'>"
            + "<thead>"
            + "<tr>"
            + "<th>" + Messages.InputAnalyzer.BuildTable.ID_TEXT + "</th>"
            + "<th>" + Messages.InputAnalyzer.BuildTable.HEADER_TEXT + "</th>"
            + "<th>" + Messages.InputAnalyzer.BuildTable.SHORT_NAME_TEXT + "</th>"
            + "</tr>"
            + "</thead>";
        for(let i = 0; i < c_species; ++i){
            html += "<tr>"
                + "<td>" + (InputMethod.getCurrent() === InputMethod.FILE ? i + 1 : this.results[i].id) + "</td>"
                + "<td>" + this.results[i].title + "</td>"
                + "<td>" + "<input id='sn_" + i + "' class='short_name form-control' value='" + (InputMethod.getCurrent() === InputMethod.FILE ? i + 1 : this.getShortName(this.results[i])) + "'  style='min-width: 155px;' /></td>"
                + "</tr>";
        }
        html += "</table>"
            + "<button class=\"btn btn-primary\" id=\"fasta_check_out\" "
            + "onclick=\"InputAnalyzer.addShortNames()\" style=\"vertical-align: top\">Done</button>";
        this.selector.html(html);
    },
    getShortName: function (seq_info) {
        if(seq_info.short_name === null) return "";
        /** @type {{organism: String}|string} */
        const name = seq_info.short_name;
        if(name.length > this.CHAR_LIMIT){
            const name_parts = name.split(' ');
            if(name_parts[0].length > this.CHAR_LIMIT){
                return name_parts[0].substring(0, this.CHAR_LIMIT);
            }else{
                let name = [];
                let count = 0;
                for(let parts of name_parts){
                    count += parts.length;
                    if(count <= this.CHAR_LIMIT) name.push(parts);
                }
                return name.join('_');
            }
        }else{
            return name.replace(/\s/g, '_');
        }
    }
};

/**
 * Project Object
 * @type {{config:Project.config, result: {Project.result}, process: {Project.process}, delete: {Project.delete}}}
 */
Project = {};

/**
 * Project Result
 *
 * @type {{MAW: string, RAW: string, submit_btn: (*|jQuery|HTMLElement), config: {}, prepare: Project.result.prepare, verify: Project.result.verify, send: Project.result.send, restore: Project.result.restore}}
 */
Project.result = {
    MAW: 'maw',
    RAW: 'raw',
    submit_btn: null,
    config: {},
    project_id: null,
    /**
     * Prepare before publishing result
     *
     * - set project configurations
     * - check if everything's in order
     */
    prepare: function () {
        // Project config
        this.config = {
            project_name: $('#project_name').val(), // #1
            aw_type: $("input[name='aw_type'][value='raw']").is(':checked') ? this.RAW : this.MAW, // #2
            kmer: { // #3
                min: parseInt($('#kmer_min').val()),
                max: parseInt($('#kmer_max').val())
            },
            inversion: $('#inversion').is(":checked"), // #4
            dissimilarity_index: $('#dissimilarity_index').val(), // #5
            sequence_type: $("input[name='seq_type'][value='protein']").is(':checked') ? InputAnalyzer.PROTEIN : InputAnalyzer.NUCLEOTIDE, // #6
            data: InputAnalyzer.results, // #7
            type: InputMethod.getCurrent() // #8
        };

        // Special for InputMethod.FILE
        if(InputMethod.getCurrent() === InputMethod.FILE){
            this.config.file_id = InputAnalyzer.file_id; // #9
            if(this.config.file_id === null) return false;
        }

        return ( this.config.project_name
            && (0 <= this.config.kmer.min <= this.config.kmer.max)
            && this.config.dissimilarity_index
            && InputAnalyzer.done);
    },
    /**
     * Verify inputs before sending
     *
     * @returns {boolean}
     */
    verify: function(){
        if(!this.prepare()){
            alert(Messages.Project.UNFILLED_FIELDS);
            return false;
        }
        return true;
    },
    /**
     * Send request for result
     */
    send: function(){
        if(!this.verify()) return false;
        this.submit_btn = $('#p_btn');

        const parent = this;
        $.ajax({
            method: 'post',
            url: './projects/new',
            data: {config: JSON.stringify(this.config)},
            cache: false,
            dataType: 'json',
            beforeSend: function() {
                const btn = parent.submit_btn;
                btn.removeClass('btn-primary');
                btn.addClass('btn-default disabled');
                btn.attr('onclick', null);
                btn.html("<img width='11' src='./css/images/spinner.gif'>&nbsp;" + Messages.Project.LOADING_TEXT);
            },
            success: function(res){
                if(res && res.id){
                    parent.project_id = res.id;
                    const url = './projects/' + res.id;
                    const form = $('<form action="' + url + '" method="get"></form>');
                    $('body').append(form);
                    form.submit();
                }else{
                    parent.restore();
                }
            },
            error: function(){
                parent.restore();
            }
        });
    },
    /**
     * Restore form if an error occurs
     */
    restore: function(){
        alert(Messages.Project.FAILURE_ALERT);
        const btn = this.submit_btn;
        btn.removeClass('btn-default disabled');
        btn.addClass('btn-primary');
        btn.attr('onclick', 'Project.result.send()');
        btn.html("Show Result");
    }
};

/**
 * Do the process
 *
 * @type {{status: Project.process.status, cancel: Project.process.cancel}}
 */
Project.process = {
    /**
     *
     * @param {*}      selector
     * @param {Number} project_id
     */
    status: function (selector, project_id) {
        $.ajax({
            method: 'post',
            url: './projects/get_status',
            data: {project_id: project_id},
            cache: false,
            dataType: 'json',
            beforeSend: function(){
                selector.html(Messages.Project.Status.FETCH_TEXT);
            },
            /**
             * @param {{status: string, status_code: int}} res
             */
            success: function(res){
                selector.html(res.status);
                switch (res.status_code){
                    case 0: // SUCCESS
                        window.location.reload();
                        break;
                    case 1: // FAILED
                        alert(Messages.Project.Status.FAILURE_ALERT);
                        window.location.assign('./projects');
                        break;
                }
            },
            error: function(){
                selector.html(Messages.CONNECTION_PROBLEM);
            }
        });
    },
    cancel: function (project_id, project_name) {
        $.ajax({
            method: 'post',
            url: './projects/cancel_process',
            data: {project_id: project_id},
            cache: false,
            dataType: 'json',
            beforeSend: function(){
                return confirm(Messages.Project.Cancel.CANCEL_MESSAGE(project_name));
            },
            success: function(res){
                switch(res.status){
                    case 0: // SUCCESS
                        alert(Messages.Project.Cancel.SUCCESS_MESSAGE);
                        window.location.assign('./projects');
                        break;
                    default:
                        alert(Messages.Project.Cancel.FAILURE_MESSAGE);
                        break;
                }
            },
            error: function(){
                alert(Messages.CONNECTION_PROBLEM);
            }
        });
    }
};

/**
 * Delete a project
 *
 * @param {int}    project_id
 * @param {string} project_name
 * @param {bool}   reload Whether to reload the page
 */
Project.delete = function (project_id, project_name, reload) {
    $.ajax({
        method: 'post',
        url: './projects/' + project_id + '/delete',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            return confirm(Messages.Project.Delete.DELETE_MESSAGE(project_name));
        },
        success: function(res){
            switch(res.status){
                case 0:
                    if(reload) window.location.assign('./projects');
                    $('#p_' + project_id).remove();
                    break;
                default:
                    alert(Messages.Project.Delete.FAILURE_MESSAGE);
            }
        },
        error: function(){
            alert(Messages.CONNECTION_PROBLEM);
        }
    });
};

/**
 * Handles notifications
 */
Project.notification_handler = function () {
    let selector  = $("#notification_bar");
    let count_sel = $("#notification_count");
    // Get unseen
    $.ajax({
        method: 'post',
        url: './projects/get_unseen',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            selector.html("<li style=\"padding: 5px 10px\"><em>" + Messages.Project.Notification.FETCH_TEXT + "</em></li>");
            return true;
        },
        /** @param {object[]} res.projects */
        success: function(res){
            if(res.projects && res.projects.length > 0){
                let rows = [];
                count_sel.text(res.projects.length); // class='unread-count'
                count_sel.addClass('unread-count');
                /**
                 * @var {int}    project.id
                 * @var {string} project.name
                 * @var {string} project.date_created
                 */
                for(let project of res.projects){
                    rows.push("<li><a href='./projects/" + project.id + "'>#" + project.id + " " + project.name + "</a></li>");
                }
                selector.html(rows.join("<li class='divider'></li>"));
            }else{
                count_sel.html("");
                count_sel.removeClass('unread-count');
                selector.html("<li style=\"padding: 5px 10px\"><em>" + Messages.Project.Notification.NO_NOTIFICATION + "</em></li>");
            }
        },
        error: function(){
            selector.html("<li style=\"padding: 5px 10px\"><em>" + Messages.Project.Notification.CONNECTION_PROBLEM + "</em></li>");
        }
    });
};

/**
 * Show elapsed time
 *
 * @param selector
 * @param date_created
 */
function elapsed_time(selector, date_created) {
    const now = new Date().getTime();
    const distance = now - date_created;
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    selector.html(days + "d " + hours + "h " + minutes + "m " + seconds + "s ");
}
