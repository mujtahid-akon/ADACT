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
 * @var int $id
 * @var string $name
 * @var string $date_created
 * @var bool $editable
 * @var bool $last
 * @var int $result_type
 */
extract($project_info);
// FM
$fm = new FM($project_id);
// load config
$config = new ProjectConfig($fm->get(FM::CONFIG_JSON));
// Project type
$isAFileIOProject = $config->type === Project::INPUT_TYPE_FILE;
// Base url
$base_url = $_SERVER['PHP_SELF'];
// Transform Absent Words type to uppercase
$config->aw_type = strtoupper($config->aw_type);

// Preparing the outputs if the project isn't a pending one
if(!$isAPendingProject){
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
 * @param FM    $fm
 * @return array Each member is a table row
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
        for($col_i = 0; $col_i <= $row_i; ++$col_i) $table_row .= "<td></td>";
        // Now, the rest
        for(/* $col_i has already been set above */; $col_i < $total_species; ++$col_i){
            $table_row .= "<td>{$distance_matrix[$dm_i++]}</td>";
        }
        $table_row .= "</tr>\n";
        // Print the row
        array_push($table_rows, $table_row);
    }
    return $table_rows;
}

// Output begin
?>
<h3>Project: <?php print ucwords($config->project_name); ?></h3>
<?php
if($editable):
    print "<h4><a href=\"{$base_url}/edit\">Edit</a></h4>";
endif; // editable

if($isAPendingProject):
?>
    <p class="text text-danger"><em>The project is currently running...</em></p>
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
            }, 10000);
        });
    </script>
<?php
endif; // isAPendingProject
?>

<section>
    <?php
    if($isAPendingProject):
    ?>
        <button onclick="Project.process.cancel(<?php print $project_id. ', \'' .$config->project_name . '\'' ?>)" class="btn btn-default">Cancel Project</button>
    <?php
    elseif($result_type === Project::RT_SUCCESS): // !isAPendingProject
    ?>
        <button onclick="$('#project_info').toggle()" class="btn btn-default">Toggle Project Info</button>
    <?php
    else:
    ?>
        <button onclick="Project.delete(<?php print $project_id. ', \'' .$config->project_name . '\'' ?>)" class="btn btn-default">Delete Project</button>
    <?php
    endif; // isAPendingProject
    ?>
    <a class="btn btn-default" <?php print (($isAFileIOProject OR $isAPendingProject) ? "disabled" : "href=\"/projects/{$project_id}/fork\"") ?>>Fork This Project</a>
</section>

<section id="project_info" <?php print ($result_type !== Project::RT_SUCCESS ? '' : 'style="display: none;"') ?>>
    <table class="table table-bordered table-striped table-hover">
        <caption>Overview</caption>
        <tbody>
        <?php
        print "<tr><th>Project Name</th><td>".ucwords($config->project_name)."</td></tr>";
        if($isAPendingProject){
            print "<tr><th>Status</th><td id='process_status'></td></tr>";
            print "<tr><th>Elapsed Time</th><td id='elapsed_time'></td></tr>";
        }
        print "<tr><th>Sequence Type</th><td>".ucwords($config->sequence_type)."</td></tr>";
        print "<tr><th>Absent Word Type</th><td>".($config->aw_type === 'MAW' ? "Minimal" : "Relative")." Absent Words ({$config->aw_type})</td></tr>";
        print "<tr><th>K-Mer</th><td>Min: {$config->kmer['min']}, Max: {$config->kmer['max']}</td></tr>";
        print "<tr><th>Reverse Complement</th><td>".($config->inversion ? "Yes" : "No")."</td></tr>";
        print "<tr><th>Dissimilarity Index</th><td>{$dissimilarity_index[$config->aw_type][$config->dissimilarity_index]}</td></tr>";
        ?>
        </tbody>
    </table>
    <table id="project_info" class="table table-bordered table-striped table-hover">
        <caption>Species Info</caption>
        <thead>
        <tr><th><?php print ($isAFileIOProject ? "#" : "ID"); ?></th><th>Title/Header</th><th>Short Name</th></tr>
        </thead>
        <tbody>
        <?php
        if($isAFileIOProject) $id = 0;

        foreach ($config->data as $data) {
            /** @var int $id */
            $id = ($isAFileIOProject) ? ($id + 1) : $data['id'];
            print "<tr><th>{$id}</th><td>".ucwords($data['title'])."</td><td>{$data['short_name']}</td></tr>";
        }
        ?>
        </tbody>
    </table>
</section>
<br />
<?php
// Generate results if the project was executed successfully
if($result_type === Project::RT_SUCCESS):
    $tree = new Tree($project_id);
?>
<script src="Treant.js"></script>
<script src="vendor/raphael.js"></script>
<script src="vendor/jquery.easing.js"></script>
<script>
    let file = document.createElement("link");
    file.setAttribute("rel", "stylesheet");
    file.setAttribute("type", "text/css");
    file.setAttribute("href", "Treant.css");
    document.head.appendChild(file);

    const upgma = '<?php print json_encode(($tree->generate_tree($tree::UPGMA))->getFormattedLabels()); ?>';
    const nj    = '<?php print json_encode(($tree->generate_tree($tree::NJ))->getFormattedLabels()); ?>';
    const upgma_config = {
        chart: {
            container: "#upgma_tree_view",
            nodeAlign: "BOTTOM",
            levelSeparation: 30,
            siblingSeparation: InputAnalyzer.CHAR_LIMIT * 10,
            subTeeSeparation: InputAnalyzer.CHAR_LIMIT * 10,
            connectors: {
                type: "step",
                style: {
                    "stroke-width": 1,
                    "stroke": "#ccc"
                }
            }
        },
        nodeStructure: {
            children: JSON.parse(upgma)
        }
    };

    const nj_config = {
        chart: {
            container: "#nj_tree_view",
            nodeAlign: "TOP",
            levelSeparation: 30,
            siblingSeparation: InputAnalyzer.CHAR_LIMIT * 10,
            subTeeSeparation: InputAnalyzer.CHAR_LIMIT * 10,
            connectors: {
                type: "step",
                style: {
                    "stroke-width": 1,
                    "stroke": "#ccc"
                }
            }
        },
        nodeStructure: {
            children: JSON.parse(nj)
        }
    };

    /**
     * Show the requested tab
     * @param {String} [tab]
     */
    function show_result_tab(tab){
        $('.output').hide();
        let tab_id, toc_id; // toc = table of content
        switch(tab){
            default:
                tab_id = '#distance_matrix';
                toc_id = 0;
                break;
            case 'sorted_species_relation':
                tab_id = '#sorted_species_relation';
                toc_id = 1;
                break;
            case 'neighbour_tree':
                tab_id = '#neighbour_tree';
                toc_id = 2;
                new Treant(nj_config);
                break;
            case 'upgma_tree':
                tab_id = '#upgma_tree';
                toc_id = 3;
                new Treant(upgma_config);
        }
        $(tab_id).show();
        $('.views').removeClass('active');
        $('#tab_toc').children().eq(toc_id).addClass('active');
    }

    $(document).ready(function () {
        const url = document.URL.split('#')[1];
        show_result_tab(url);
    });
</script>
<section id="tab_toc" style="padding-bottom: 10px;">
    <a href="/projects/<?php print $project_id ?>#distance_matrix" onclick="show_result_tab()" class="views btn btn-default active">Distance Matrix</a>
    <a href="/projects/<?php print $project_id ?>#sorted_species_relation" onclick="show_result_tab('sorted_species_relation')" class="views btn btn-default">Sorted Species Relation</a>
    <a href="/projects/<?php print $project_id ?>#neighbour_tree" onclick="show_result_tab('neighbour_tree')" class="views btn btn-default">Neighbour Joining Tree</a>
    <a href="/projects/<?php print $project_id ?>#upgma_tree" onclick="show_result_tab('upgma_tree')" class="views btn btn-default">UPGMA Tree</a>
</section>
<section>
    <?php
    /** @var string $download_url */
    /** @var array  $species */
    /** @var array  $species_relations */
    $neighbourTree = $download_url . '/' . FM::NEIGHBOUR_TREE;
    $UPGMATree     = $download_url . '/' . FM::UPGMA_TREE;
    ?>
    <!-- Neighbour Tree -->
    <div id="neighbour_tree" class="output" style="display: none;">
        <a href="<?php print $neighbourTree ?>">Download Neighbour Joining Tree</a><br />
        <!--img src="<?php print $neighbourTree ?>" /-->
        <div id="nj_tree_view"></div>
    </div>
    <!-- UPGMA Tree -->
    <div id="upgma_tree" class="output" style="display: none;">
        <a href="<?php print $UPGMATree ?>">Download UPGMA Tree</a><br />
        <!--img src="<?php print $UPGMATree ?>" /-->
        <div id="upgma_tree_view"></div>
    </div>
    <!-- Sorted Species Relation -->
    <div id="sorted_species_relation" style="text-align: left; display: none;" class="output">
        <a href="<?php print $download_url . '/' . FM::SPECIES_RELATION ?>">Download Sorted Species Relation</a><br />
        <table class="table table-striped table-hover">
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
                print "<th style=\"border-right: 1px solid #ddd\">{$_species}</th>";
                // Print the relations
                print "<td>" . implode('</td><td>&xrarr;</td><td>', $_relation) . "</td>";
                print "</tr>\n";
            }
            ?>
            </tbody>
        </table>
    </div>
    <!-- Distance Matrix -->
    <div id="distance_matrix" class="output">
        <a href="<?php print $download_url . '/' . FM::DISTANCE_MATRIX ?>">Download Distance Matrix</a><br />
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th></th>
                <?php foreach($species as $col) print '<th>' . $col . '</th>' ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach (get_distance_matrix($species, $fm) as $distance_matrix) print $distance_matrix ?>
            </tbody>
        </table>
    </div>
</section>
<?php
endif; // isAPendingProject
// Output end
