<?php
/**
 * Created by PhpStorm.
 * User: mujtahid
 * Date: 6/7/17
 * Time: 6:40 PM
 */
use \AWorDS\App\Constants;
use \AWorDS\Config;
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
 */

// load config
$config = ($isAPendingProject) ?
    json_decode(file_get_contents((new \AWorDS\App\Models\Directories($project_id))->get(Constants::CONFIG_JSON)), true):
    json_decode(file_get_contents(Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . Constants::CONFIG_JSON), true);

// Project type
$isAFileIOProject = $config['type'] === Constants::PROJECT_TYPE_FILE;
// Base url
$base_url = $_SERVER['PHP_SELF'];
// Transform Absent Words type to uppercase
$config['aw_type'] = strtoupper($config['aw_type']);

// Preparing the outputs if the project isn't a pending one
if(!$isAPendingProject){
    // Info downloading url
    $download_url = $base_url . '/get';
    $project_dir = \AWorDS\Config::PROJECT_DIRECTORY . '/' . $project_id;
    // Get Species Relation
    $relation_file = $project_dir . '/SpeciesRelation.json';
    $species_relations = json_decode(file_get_contents($project_dir . '/SpeciesRelation.json'), true);
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
 * @param array  $species
 * @param string $project_dir
 * @return array Each member is a table row
 */
function get_distance_matrix($species, $project_dir){
    $total_species = count($species); // Number of rows and columns is the same as this + 1 for header
    $distance_matrix = file($project_dir . '/DistanceMatrix.txt');
    $dm_i = 0; // Distance matrix pointer
    $table_rows = [];
    for($row_i  = 0; $row_i < $total_species; ++$row_i){
        $table_row = "<tr>";
        // Header first
        $table_row .= "<th>{$species[$row_i]}</th>";
        // Blank columns
        for($col_i = 0; $col_i <= $row_i; ++$col_i){
            $table_row .= "<td></td>";
        }
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
<h3>Project: <?php print ucwords($config['project_name']); ?></h3>
<?php
if($isTheLastProject AND !$isAPendingProject):
    print "<h4><a href=\"{$base_url}/edit\">Edit</a></h4>";
endif; // isTheLastProject

if($isAPendingProject):
?>
    <p class="text text-danger"><em>The project is currently running...</em></p>
<?php
endif; // isAPendingProject
?>

<div>
    <?php
    if($isAPendingProject):
    ?>
        <script src="/js/app.js"></script>
        <button onclick="Project.process.cancel(<?php print $project_id. ',' .$config['project_name'] ?>)" class="btn btn-default">Cancel Project</button>
    <?php
    else:
    ?>
        <button onclick="$('#project_info').toggle()" class="btn btn-default">Toggle Project Info</button>
    <?php
    endif; // isAPendingProject
    ?>
    <a class="btn btn-default" <?php print (($isAFileIOProject OR $isAPendingProject) ? "disabled" : "href=\"\"") ?>>Fork This Project</a>
</div>

<div id="project_info" <?php print ($isAPendingProject ? '' : 'style="display: none;"') ?>>
    <table class="table table-bordered table-striped table-hover">
        <caption>Overview</caption>
        <tbody>
        <?php
        print "<tr><th>Project Name</th><td>".ucwords($config['project_name'])."</td></tr>";
        if($isAPendingProject){
            print "<tr><th>Status</th><td>Pending...</td></tr>";
            print "<tr><th>Elapsed Time</th><td>1:00</td></tr>";
        }
        print "<tr><th>Sequence Type</th><td>".ucwords($config['sequence_type'])."</td></tr>";
        print "<tr><th>Absent Word Type</th><td>".($config['aw_type'] === 'MAW' ? "Minimal" : "Relative")." Absent Words ({$config['aw_type']})</td></tr>";
        print "<tr><th>K-Mer</th><td>Min: {$config['kmer']['min']}, Max: {$config['kmer']['max']}</td></tr>";
        print "<tr><th>Reverse Complement</th><td>".($config['inversion'] ? "Yes" : "No")."</td></tr>";
        print "<tr><th>Dissimilarity Index</th><td>{$dissimilarity_index[$config['aw_type']][$config['dissimilarity_index']]}</td></tr>";
        ?>
        </tbody>
    </table>
    <table id="project_info" class="table table-bordered table-striped table-hover">
        <caption>Species Info</caption>
        <thead>
        <tr><th><?php print ($isAFileIOProject ? "SL" : "ID"); ?></th><th>Title/Header</th><th>Short Name</th></tr>
        </thead>
        <tbody>
        <?php
        if($isAFileIOProject) $id = 0;

        foreach ($config['data'] as $data) {
            /** @var int $id */
            $id = ($isAFileIOProject) ? ($id + 1) : $data['id'];
            print "<tr><th>{$id}</th><td>".ucwords($data['title'])."</td><td>{$data['short_name']}</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<br />
<?php
if(!$isAPendingProject):
?>
<div style="padding-bottom: 10px;">
    <button onclick="$('.output').hide();$('#distance_matrix').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default active">Distance Matrix</button>
    <button onclick="$('.output').hide();$('#sorted_species_relation').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">Sorted Species Relation</button>
    <button onclick="$('.output').hide();$('#neighbour_tree').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">Neighbour tree</button>
    <button onclick="$('.output').hide();$('#upgma_tree').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">UPGMA tree</button>
</div>
<div>
    <div id="neighbour_tree" class="output" style="display: none;">
        <a href="<?php print $download_url . '/NeighbourTree.jpg' ?>">Download Neighbour Tree</a><br />
        <img src="<?php print $download_url . '/NeighbourTree.jpg' ?>" />
    </div>
    <div id="upgma_tree" class="output" style="display: none;">
        <a href="<?php print $download_url . '/UPGMATree.jpg' ?>">Download UPGMA Tree</a><br />
        <img src="<?php print $download_url . '/UPGMATree.jpg' ?>" />
    </div>
    <div id="sorted_species_relation" style="text-align: left; display: none;" class="output">
        <a href="<?php print $download_url . '/SpeciesRelation.txt' ?>">Download Sorted Species Relation</a><br />
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
                print "<tr>";
                print "<th style=\"border-right: 1px solid #ddd\">{$_species}</th>";

                print "<td>" . implode('</td><td>&xrarr;</td><td>', $_relation) . "</td>";
                print "</tr>\n";
            }
            ?>
            </tbody>
        </table>
    </div>
    <div id="distance_matrix" class="output">
        <a href="<?php print $download_url . '/DistantMatrix.txt' ?>">Download Distance Matrix</a><br />
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th></th>
                <?php
                foreach($species as $col){
                    print '<th>' . $col . '</th>';
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach (get_distance_matrix($species, $project_dir) as $distance_matrix){
                print $distance_matrix;
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
endif; // isAPendingProject
// Output end
