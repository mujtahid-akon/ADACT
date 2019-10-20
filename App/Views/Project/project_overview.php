<?php
/**
 * Created by PhpStorm.
 * User: mujtahid
 * Date: 6/7/17
 * Time: 6:40 PM
 */
use \ADACT\App\Models\FileManager as FM;
use \ADACT\App\Models\ProjectConfig;
use \ADACT\App\Models\Project;
use \ADACT\App\Models\Tree;
/**
 * @var bool $logged_in
 */
if(!$logged_in) exit();

/**
 * Variables exported from Project controller
 *
 * @var int   $project_id          Current project id
 * @var bool  $isTheLastProject    If it's the last project (that means editable)
 * @var array $dissimilarity_index Dissimilarity index array
 * @var bool  $isAPendingProject   If it's a pending project (that means show status, elapsed time, etc.)
 * @var array $project_info        If it's a pending project (that means show status, elapsed time, etc.)
 */

/**
 * Variables extracted by project_info
 * @var int     $id
 * @var string  $name
 * @var string  $date_created
 * @var bool    $editable
 * @var bool    $last
 * @var int     $result_type
 * @var int     $exec_duration
 */
extract($project_info);
// FM
$fm = new FM($project_id);
// load config
$config = new ProjectConfig($fm->get(FM::CONFIG_JSON));
// Project type
$isAFileIOProject = $config->type === Project::INPUT_TYPE_FILE;
// Base url
$base_url = './projects/'.$project_id;
// Transform Absent Words type to uppercase
$config->aw_type = strtoupper($config->aw_type);
// Species count
$species_count = count($config->data);
// Preparing the outputs if the project isn't a pending one
if($result_type === Project::RT_SUCCESS){
    // Info downloading url
    $download_url = $base_url . '/get';
    // Get Species Relation
    $relation_file = $fm->get(FM::SPECIES_RELATION_JSON);
    $species_relations = json_decode(file_get_contents($relation_file), true);
    // Get Species names
    $species = get_species_from_species_relation($species_relations);
}

/**
 * Get a list of species from the species relations
 * @param array $species_relations
 * @return array
 */
function get_species_from_species_relation($species_relations){
    $species_list = [];
    foreach ($species_relations as $species => $relation){
        array_push($species_list, $species);
    }
    return $species_list;
}

/**
 * Get distance matrix HTML table
 *
 * @param array $species
 * @param FM $fm
 * @return array Each member is a table row
 * @throws \ADACT\App\Models\FileException
 */
function get_distance_matrix($species, $fm){
    $total_species = count($species); // Number of rows and columns is the same as this + 1 for header
    $distance_matrix = file($fm->get(FM::DISTANCE_MATRIX));
    $dm_i = 0; // Distance matrix pointer
    $table_rows = [];
    for($row_i  = 0; $row_i < $total_species; ++$row_i){
        $table_row = "<tr>";
        // Header first
        $table_row .= "<th>{$species[$row_i]}</th>";
        // Blank columns
        for($col_i = 0; $col_i <= $row_i; ++$col_i) $table_row .= "<td title='({$species[$row_i]}, {$species[$col_i]})'></td>";
        // Now, the rest
        for(/* $col_i has already been set above */; $col_i < $total_species; ++$col_i){
            $table_row .= "<td title='({$species[$row_i]}, {$species[$col_i]})'>"
                . trim($distance_matrix[$dm_i++])
                . "</td>";
        }
        $table_row .= "</tr>";
        // Print the row
        array_push($table_rows, $table_row);
    }
    return $table_rows;
}

// Generate results if the project was executed successfully
if($result_type === Project::RT_SUCCESS):
$tree = new Tree($project_id);

/** @var string $download_url */
/** @var array  $species */
/** @var array  $species_relations */
$neighbourTree = $download_url . '/' . str_replace(' ', '+', FM::NEIGHBOUR_TREE);
$UPGMATree     = $download_url . '/' . str_replace(' ', '+', FM::UPGMA_TREE);
endif;

// Output begin
?>
<!-- Project output begin -->
<h3 class="title">Project: <?php print ucwords($config->project_name); ?></h3>
<!-- Include Scripts and styles -->
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js" charset="utf-8"></script>
<script src="./js/phylotree.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="./css/tabs.responsive.bootstrap.min.css" />
<script>
    (function($) {

        'use strict';

        $(document).on('show.bs.tab', '.nav-tabs-responsive [data-toggle="tab"]', function(e) {
            var $target = $(e.target);
            var $tabs = $target.closest('.nav-tabs-responsive');
            var $current = $target.closest('li');
            var $parent = $current.closest('li.dropdown');
            $current = $parent.length > 0 ? $parent : $current;
            var $next = $current.next();
            var $prev = $current.prev();
            var updateDropdownMenu = function($el, position){
                $el
                    .find('.dropdown-menu')
                    .removeClass('pull-xs-left pull-xs-center pull-xs-right')
                    .addClass( 'pull-xs-' + position );
            };

            $tabs.find('>li').removeClass('next prev');
            $prev.addClass('prev');
            $next.addClass('next');

            updateDropdownMenu( $prev, 'left' );
            updateDropdownMenu( $current, 'center' );
            updateDropdownMenu( $next, 'right' );
        });

    })(jQuery);
</script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap.min.js"></script>
<style id="phylotree_css">
    <?php echo file_get_contents(__DIR__ . '/../../../public/css/phylotree.min.css') ?>
</style>
<style>
    .fa-rotate-45 {
        -webkit-transform: rotate(45deg);
        -moz-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        -o-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    .fa-rotate-135 {
        -webkit-transform: rotate(135deg);
        -moz-transform: rotate(135deg);
        -ms-transform: rotate(135deg);
        -o-transform: rotate(135deg);
        transform: rotate(135deg);
    }

    /*@media (max-width: 1075px) {
        .container {
            padding-top: 50px;
        }
    }*/
</style>
<style>
    @media (min-width: 479px) {
        .nav-tabs { border-bottom: 2px solid #DDD; }
        .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover { border-width: 0; }
        .nav-tabs > li > a { border: none; color: #666; }
        .nav-tabs > li.active > a, .nav-tabs > li > a:hover { border: none; color: #4285F4 !important; background: transparent; }
        .nav-tabs > li > a::after { content: ""; background: #4285F4; height: 2px; position: absolute; width: 100%; left: 0px; bottom: -1px; transition: all 250ms ease 0s; transform: scale(0); }
        .nav-tabs > li.active > a::after, .nav-tabs > li:hover > a::after { transform: scale(1); }
        .tab-nav > li > a::after { background: #21527d none repeat scroll 0% 0%; color: #fff; }
    }
    .tab-pane { padding: 0; }
    .tab-content { margin-top: 10px; }

    .btn-toolbar > div {
        margin-bottom: 5px;
    }

    @media screen and (max-width: 479px) {
        .nav-tabs {
            border-bottom: 0 !important;
        }
    }
    #contact .container { padding-top: 0 !important; padding-bottom: 0 !important; }
</style>
<!-- Toolbar -->
<div id="project_overview_toolbar" class="btn-toolbar" role="toolbar" style="margin-bottom: 5px; display: flex;">
    <div class="btn-group">
        <?php if(false and $editable): // FIXME: Temporarily disabled edit option ?>
        <a class="btn button small blue" href="<?php echo $base_url ?>/edit" title="Edit project">
            <i class='fa fa-edit'></i> Edit
        </a>
        <?php endif; // editable ?>
        <?php if($isAPendingProject): ?>
        <button type="button" class="btn button small orange" onclick="Project.process.cancel(<?php print $project_id. ', \'' .$config->project_name . '\'' ?>)" title="Cancel project">
            <i class="fa fa-remove" aria-hidden="true"></i> Cancel
        </button>
        <?php endif; // isAPendingProject ?>
        <?php if($result_type == Project::RT_SUCCESS): ?>
        <a class="btn button small gray" href="<?php echo $base_url ?>/download" title="Download entire project">
            <i class='fa fa-download'></i> Download
        </a>
        <?php endif; // RT_SUCCESS ?>
        <?php if(!$isAPendingProject): ?>
        <button type="button" class="btn button small deeporange" onclick="Project.delete(<?php print $project_id. ', \'' .$config->project_name . '\'' ?>, true)" title="Delete project">
            <i class="fa fa-trash" aria-hidden="true"></i> Delete
        </button>
        <?php endif; // !isAPendingProject ?>
        <button type="button" class="btn button small whitish" <?php print (($isAFileIOProject OR $isAPendingProject) ? "disabled" : "href=\"./projects/{$project_id}/fork\"") ?> title="Fork this project">
            <i class="fa fa-code-fork" aria-hidden="true"></i> Fork
        </button>
    </div>
</div>
<?php if($isAPendingProject): ?>
<!-- For pending project -->
<p class="text text-danger"><i class="fa fa-spinner fa-pulse" aria-hidden="true"></i> <em>The project is currently running...</em></p>
<script>
    $(document).ready(function(){
        let date_created = new Date("<?php print $date_created ?> UTC").getTime();
        let selector = $("#elapsed_time");
        // Show elapsed time
        elapsed_time(selector, date_created);
        setInterval(function(){
            elapsed_time(selector, date_created);
        }, 1000);
        // Show status
        Project.process.status($("#process_status"), <?php print $id ?>);
        setInterval(function(){
            Project.process.status($("#process_status"), <?php print $id ?>);
        }, 5000);
    });
</script>
<?php endif; // isAPendingProject ?>
<!-- Tab List -->
<ul id="project_overview_tab" class="nav nav-tabs nav-tabs-responsive" role="tablist" style="display: flex;">
    <li role="presentation" class="active">
        <a href="#project_overview" role="tab" data-toggle="tab" aria-controls="overview" aria-expanded="true">
            <span class="text">Overview</span>
        </a>
    </li>
    <li role="presentation" class="next">
        <a href="#species_info" role="tab" data-toggle="tab" aria-controls="species_info">
            <span class="text">Species Info</span>
        </a>
    </li>
    <?php if($result_type === Project::RT_SUCCESS): ?>
    <li role="presentation">
        <a href="#distance_matrix" role="tab" data-toggle="tab" aria-controls="distance_matrix">
            <span class="text">Distance Matrix</span>
        </a>
    </li>
    <li role="presentation">
        <a href="#sorted_species_relation" role="tab" data-toggle="tab" aria-controls="sorted_species_relation">
            <span class="text">Sorted Species Relations</span>
        </a>
    </li>
    <li role="presentation">
        <a href="#phy_tree" role="tab" data-toggle="tab" aria-controls="tab_nj_tree" id="tab_nj_tree">
            <span class="text">Neighbour Joining Tree</span>
        </a>
    </li>
    <li role="presentation">
        <a href="#phy_tree" role="tab" data-toggle="tab" aria-controls="tab_nj_tree" id="tab_upgma_tree">
            <span class="text">UPGMA Tree</span>
        </a>
    </li>
    <?php endif; ?>
</ul>
<!-- Tab Content -->
<div class="tab-content">
    <!-- Project Overview -->
    <div id="project_overview" class="tab-pane fade active in" role="tabpanel">
        <?php if($result_type === Project::RT_SUCCESS): ?>
        <script>
            $(document).ready(function(){
                show_formatted_date($("#exec_duration"), <?php print $exec_duration ?>)
            });
        </script>
        <?php endif; ?>
        <table id="table_po" class="table table-bordered table-striped table-hover">
            <tbody>
            <?php
            print "<tr><th>Project Name</th><td>".ucwords($config->project_name)."</td></tr>";
            if($result_type === Project::RT_SUCCESS):
                $aw_exec_info = $config->exec_info['AW'];
                $dm_exec_info = $config->exec_info['DM'];
            ?>
                <tr><th>Execution Details</th>
                    <td>
                        <strong>Total Duration:</strong> <span id='exec_duration'><?php echo $exec_duration ?> seconds</span>
                        <dl>
                            <dt style="text-decoration: underline">Absent word:</dt>
                            <dd>
                                <strong>CPU:</strong> <?php echo $aw_exec_info['cpu'] ?>%
                                <strong>RAM:</strong> <?php echo $aw_exec_info['memory'] ?>KB
                                <strong>Execution Time:</strong> <?php echo $aw_exec_info['time'] ?>s
                            </dd>
                            <dt style="text-decoration: underline">Distance Matrix:</dt>
                            <dd>
                                <strong>CPU:</strong> <?php echo $dm_exec_info['cpu'] ?>%
                                <strong>RAM:</strong> <?php echo $dm_exec_info['memory'] ?>KB
                                <strong>Execution Time:</strong> <?php echo $dm_exec_info['time'] ?>s
                            </dd>
                        </dl>
                    </td>
                </tr>
            <?php endif; ?>
            <?php
            if($isAPendingProject){
                print "<tr><th>Status</th><td id='process_status'></td></tr>";
                print "<tr><th>Elapsed Time</th><td id='elapsed_time'></td></tr>";
            }
            print "<tr><th>Sequence Type</th><td>".ucwords($config->sequence_type)."</td></tr>";
            print "<tr><th>Absent Word Type</th><td>".($config->aw_type === 'MAW' ? "Minimal" : "Relative")." Absent Words ({$config->aw_type})</td></tr>";
            print "<tr><th>K-Mer Size</th><td>Min: {$config->kmer['min']}, Max: {$config->kmer['max']}</td></tr>";
            print "<tr><th>Reverse Complement</th><td>".($config->inversion ? "Yes" : "No")."</td></tr>";
            print "<tr><th>Dissimilarity Index</th><td>{$dissimilarity_index[$config->aw_type][$config->dissimilarity_index]}</td></tr>";
            ?>
            </tbody>
        </table>
    </div>
    <!-- Species Info -->
    <section id="species_info" class="tab-pane fade" role="tabpanel">
        <script>
            $(document).ready(function() {
                $('#table_pi').DataTable({
                    responsive: true,
                    scroller: true
                });
            });
        </script>
        <table id="table_pi" class="table table-bordered table-striped table-hover">
            <thead>
                <tr><th><?php print ($isAFileIOProject ? "#" : "ID"); ?></th><th>Title/Header</th><th>Short Name</th></tr>
            </thead>
            <tbody>
            <?php
            if($isAFileIOProject) $id = 0;
            foreach ($config->data as $data){
                /** @var int $id */
                $id = ($isAFileIOProject) ? ($id + 1) : $data['id'];
                print "<tr><th>{$id}</th><td>".ucwords($data['title'])."</td><td>{$data['short_name']}</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </section>
    <?php if($result_type === Project::RT_SUCCESS): ?>
    <!-- Sorted Species Relation -->
    <section id="sorted_species_relation" class="tab-pane fade" role="tabpanel" style="text-align: left;width: 100%;">
        <div style="margin-bottom: 5px">
            <a class="btn btn-default btn-sm" href="<?php print $download_url . '/' . FM::SPECIES_RELATION ?>">
                <i class="fa fa-download"></i> Sorted Species Relation
            </a>
        </div>
        <div style="overflow-x: auto;">
            <table id="table_ssr" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th style="border-right: 1px solid #ddd">Species</th>
                    <th colspan="<?php print 2 * count($species) - 1 ?>">Relation</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach($species_relations as $_species => $_relation){
                    // Print head of the row
                    print "<tr>";
                    print "<th title='{$_species}' style='border-right: 1px solid #ddd'>{$_species}</th>";
                    // Print the relations
                    print "<td title='{$_species}'>" . implode('</td><td title=\'' . $_species .'\'>&xrarr;</td><td title=\'' . $_species .'\'>', $_relation) . "</td>";
                    print "</tr>\n";
                }
                ?>
                </tbody>
            </table>
        </div>
    </section>
    <!-- PhyloTree -->
    <section id="phy_tree" class="tab-pane fade" role="tabpanel" style="width: 100%;">
        <!-- Toolbar -->
        <div id="phy_tree_toolbar" class="row" style="display: flex;">
            <div class="col-md-12">
                <div class="btn-toolbar" role="toolbar">
                    <!-- Spacing Tools -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm" data-direction="vertical" data-amount="1" title="Expand vertical spacing">
                            <i class="fa fa-arrows-v" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" data-direction="vertical" data-amount="-1" title="Compress vertical spacing">
                            <i class="fa fa-compress fa-rotate-135" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" data-direction="horizontal" data-amount="1" title="Expand horizontal spacing">
                            <i class="fa fa-arrows-h" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" data-direction="horizontal" data-amount="-1" title="Compress horizontal spacing">
                            <i class="fa fa-compress fa-rotate-45" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="sort_ascending" title="Sort deepest clades to the bottom">
                            <i class="fa fa-sort-amount-asc" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="sort_descending" title="Sort deepest clades to the top">
                            <i class="fa fa-sort-amount-desc" ></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="sort_original" title="Restore original order">
                            <i class="fa fa-sort" ></i>
                        </button>
                    </div>
                    <!-- Layout Modes -->
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active btn-sm">
                            <input type="radio" name="options" class="phylotree-layout-mode" data-mode="linear" autocomplete="off" checked title="Layout left-to-right"> Linear
                        </label>
                        <label class="btn btn-default  btn-sm">
                            <input type="radio" name="options" class="phylotree-layout-mode" data-mode = "radial" autocomplete="off" title="Layout radially"> Radial
                        </label>
                    </div>
                    <!-- Download button -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" title="Download">
                            <i class="fa fa-download" aria-hidden="true"></i> Download <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" id="download_dropdown">
                            <li>
                                <a onclick="saveSvg(document.getElementsByTagName('svg')[0], current_tree + '.svg')">
                                    SVG Format
                                </a>
                            </li>
                            <li>
                                <a onclick="saveNewick(current_tree + '.newick.txt')">
                                    Newick Format
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tree -->
        <div class="row">
            <div class="col-md-12">
                <div id="tree_container" class="tree-widget" style="margin-top: 10px;"></div>
            </div>
        </div>
        <!-- Tree Script -->
        <script>
            "use strict";
            const   container_id = '#tree_container',
                    font_size = 14,
                    spacing_x = 25;
            let svg = d3.select(container_id).append("svg");
            let tree;
            let current_tree = null;
            // Load NJ Tree on click
            const nj_tree = "<?php echo $tree->getNewickFormat(Tree::NJ) ?>";
            $('#tab_nj_tree').on('click', function (e) {
                default_tree_settings();
                current_tree = 'NJ Tree';
                showNewick(current_tree);
            });
            // Load UPGMA Tree on click
            const upgma_tree = "<?php echo $tree->getNewickFormat(Tree::UPGMA) ?>";
            $('#tab_upgma_tree').on('click', function (e) {
                default_tree_settings();
                current_tree = 'UPGMA Tree';
                showNewick(current_tree);
            });

            // Tree branch type FIXME Not implemented
            // - Straight
            $("#display_tree").on("click", function (e) {
                tree.options ({'branches' : 'straight'}, true);
            });
            // - Step
            $("#display_dengrogram").on ("click", function (e) {
                tree.options({'branches' : 'step'}, true);
            });

            // Label
            $("#mp_label").on ("click", function (e) {
                tree.max_parsimony (true);
            });

            $ ("[data-direction]").on ("click", function (e) {
                let which_function = $(this).data("direction") === 'vertical' ? tree.spacing_x : tree.spacing_y;
                which_function(which_function() + (+ $(this).data("amount"))).update();
            });

            // Change layout mode
            $(".phylotree-layout-mode").on("change", function (e) {
                if ($(this).is(':checked')) {
                    if (tree.radial () !== ($(this).data ("mode") === "radial")) {
                        tree.radial (!tree.radial ()).placenodes().update ();
                    }
                }
            });

            // Sort nodes
            /**
             * Sort nodes
             * @param asc
             */
            function sort_nodes (asc) {
                tree.traverse_and_compute (function (n) {
                    let d = 1;
                    if (n.children && n.children.length) {
                        d += d3.max (n.children, function (d) { return d["count_depth"];});
                    }
                    n["count_depth"] = d;
                });
                tree.resort_children (function (a,b) {
                    return (a["count_depth"] - b["count_depth"]) * (asc ? 1 : -1);
                });
            }
            // - Original order
            $("#sort_original").on ("click", function (e) {
                tree.resort_children (function (a, b) {
                    return a["original_child_order"] - b["original_child_order"];
                });
            });
            // - Ascending order
            $("#sort_ascending").on ("click", function (e) {
                sort_nodes (true);
            });
            // - Descending order
            $("#sort_descending").on ("click", function (e) {
                sort_nodes (false);
            });

            /**
             * Default Tree settings
             *
             * All these settings have to be set every time tree
             * is changed and new tree is loaded
             */
            function default_tree_settings () {
                try {
                    tree = d3.layout.phylotree();
                    tree.options({
                        // 'left-right-spacing': 'fit-to-size',
                        // 'top-bottom-spacing': 'fit-to-size',
                        'selectable': false,
                        'collapsible': false,
                        'transitions': false,
                        'hide': false,
                        'reroot': false,
                        zoom: true
                    }, false);
                    tree.size([480, 720]);
                    tree.font_size(font_size);
                    tree.spacing_x(spacing_x);
                    tree.branch_length(null);
                    tree.branch_name(null);
                    tree.node_span('equal');
                    tree.node_circle_size(undefined);
                    tree.radial(false);
                } catch (e) {}
            }

            function showNewick(name){
                let newick_tree = name[0] === "U" ? upgma_tree : nj_tree;
                tree(newick_tree).svg(svg).layout();
                $('#tree_container svg')
                    .width($('#project_overview_tab').width())
                    .height(<?php echo $species_count; ?> * (spacing_x + font_size - 14));
            }

            function saveNewick(name) {
                let newickTree = name[0] === "U" ? upgma_tree : nj_tree;
                let treeBlob = new Blob([newickTree], {type:"text/plain;charset=utf-8"});
                let treeUrl = URL.createObjectURL(treeBlob);
                let downloadLink = document.createElement("a");
                downloadLink.href = treeUrl;
                downloadLink.download = name;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }

            /**
             * Save svg document as SVG format
             *
             * Source: https://stackoverflow.com/a/46403589/4147849
             *
             * @param svgEl SVG element
             * @param name  Filename to save
             */
            function saveSvg(svgEl, name) {
                default_tree_settings();
                showNewick(current_tree);
                copyCSSInsideSVG();
                svgEl.setAttribute("xmlns", "http://www.w3.org/2000/svg");
                const svgData = svgEl.outerHTML;
                const preface = '<\?xml version="1.0" standalone="no"?>\r\n';
                let svgBlob = new Blob([preface, svgData], {type:"image/svg+xml;charset=utf-8"});
                let svgUrl = URL.createObjectURL(svgBlob);
                let downloadLink = document.createElement("a");
                downloadLink.href = svgUrl;
                downloadLink.download = name;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }

            /**
             * Copy PhyloTree CSS inside SVG
             *
             * Without this, the svg file will have weird style
             */
            function copyCSSInsideSVG() {
                if(document.getElementsByTagName('svg')[0].getElementsByTagName('style').length === 1)
                    return;
                let svg_css = document.createElement('style');
                svg_css.innerText = document.getElementById('phylotree_css').innerText.replace(/\n/g, '');
                document.getElementsByTagName('svg')[0].appendChild(svg_css);
            }
        </script>
    </section>
    <!-- Distance Matrix -->
    <section id="distance_matrix" class="tab-pane fade" role="tabpanel" style="width: 100%;">
        <div style="margin-bottom: 5px">
            <a class="btn btn-default btn-sm" href="<?php print $download_url . '/' . FM::DISTANCE_MATRIX ?>">
                <i class="fa fa-download"></i> Distance Matrix
            </a>
        </div>
        <div style="overflow-x: auto;">
            <table id="table_dm" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th></th><?php foreach($species as $col) print '<th>' . $col . '</th>' ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (get_distance_matrix($species, $fm) as $distance_matrix) print $distance_matrix ?>

                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</div>
