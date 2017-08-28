<?php
/**
 * Created by PhpStorm.
 * User: mujtahid
 * Date: 6/7/17
 * Time: 6:40 PM
 */

/**
 * @var array $config configurations extracted from config.json
 * @var int   $project_id
 * @var bool  $is_last_project_id
 * @var array $dissimilarity_index
 */
$base_url = $_SERVER['PHP_SELF'];
$url = $base_url . '/get';
$dir = \AWorDS\Config::PROJECT_DIRECTORY . '/' . $project_id;
// Get Distant Matrix
$elements = array_reverse(file($dir . '/Output.txt'));
// Get Species Relation
$relation = file($dir . '/SpeciesRelation.txt');
// Get Species names FIXME: Use $config instead
$species = get_species_from_species_relation($relation); //file($dir . '/SpeciesFull.txt');
$count_species = count($species);
// Project type
$isAFileIOProject = $config['type'] === \AWorDS\App\Constants::PROJECT_TYPE_FILE;
// Transform Absent Words type to uppercase
$config['aw_type'] = strtoupper($config['aw_type']);

function get_species_from_species_relation($relations){
    $species = [];
    foreach ($relations as $relation){
        preg_match('/[\w\s]+(?=\:)/', $relation, $matches);
        array_push($species, $matches[0]);
    }
    return $species;
}
?>

<h3>Project: <?php print ucwords($config['project_name']); ?></h3>
<?php
if($is_last_project_id):
?>
<h4><a href="<?php print $base_url . '/edit' ?>">Edit</a></h4>
<?php
endif;
?>

<button onclick="$('#project_info').toggle()" class="btn btn-default">Toggle Project Info</button>
<a class="btn btn-default" <?php print ($isAFileIOProject ? "disabled" : "href=\"\"") ?>>Fork This Project</a>
<br/>
<div id="project_info" style="display: none;">
    <table class="table table-bordered table-striped table-hover">
        <caption>Overview</caption>
        <tbody>
        <?php
        print "<tr><th>Project Name</th><td>".ucwords($config['project_name'])."</td></tr>";
        print "<tr><th>Sequence Type</th><td>".ucwords($config['sequence_type'])."</td></tr>";
        print "<tr><th>Absent Word Type</th><td>".($config['aw_type'] === 'MAW' ? "Minimal" : "Relative")." Absent Words ({$config['aw_type']})</td></tr>";
        print "<tr><th>k-Mer</th><td>Min: {$config['kmer']['min']}, Max: {$config['kmer']['max']}</td></tr>";
        print "<tr><th>Inversion</th><td>".($config['inversion'] ? "Yes" : "No")."</td></tr>";
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
?>
<div style="padding-bottom: 10px;">
    <button onclick="$('.output').hide();$('#distance_matrix').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default active">Distance Matrix</button>
    <button onclick="$('.output').hide();$('#sorted_species_relation').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">Sorted Species Relation</button>
    <button onclick="$('.output').hide();$('#neighbour_tree').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">Neighbour tree</button>
    <button onclick="$('.output').hide();$('#upgma_tree').show();$('.views').removeClass('active');$(this).addClass('active');" class="views btn btn-default">UPGMA tree</button>
</div>
<div>
    <div id="neighbour_tree" class="output" style="display: none;">
        <a href="<?php print $url . '/NeighbourTree.jpg' ?>">Download Neighbour Tree</a><br />
        <img src="<?php print $url . '/NeighbourTree.jpg' ?>" />
    </div>
    <div id="upgma_tree" class="output" style="display: none;">
        <a href="<?php print $url . '/UPGMATree.jpg' ?>">Download UPGMA Tree</a><br />
        <img src="<?php print $url . '/UPGMATree.jpg' ?>" />
    </div>
    <div id="sorted_species_relation" style="text-align: left; display: none;" class="output">
        <a href="<?php print $url . '/SpeciesRelation.txt' ?>">Download Sorted Species Relation</a><br />
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th style="border-right: 1px solid #ddd">Species</th>
                <th colspan="<?php print 2 * count($relation) - 1 ?>">Relation</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($relation as $sp_rl){
                $sp_rl = trim($sp_rl);
                // Get each species title
                preg_match('/^([\w\s]+)\:/', $sp_rl, $matches);
                $sp_title = $matches[1];
                // Set species relation by trimming the title
                $sp_rl = preg_replace('/^([\w\s]+)\:\s*->\s*/', '', $sp_rl);
                print "<tr>";
                print "<th style=\"border-right: 1px solid #ddd\">{$sp_title}</th>";
                print "<td>" . preg_replace('/\s*->\s*/', '</td><td>&xrarr;</td><td>', $sp_rl) . "</td>";
                print "</tr>\n";
            }
            ?>
            </tbody>
        </table>
    </div>
    <div id="distance_matrix" class="output">
        <a href="<?php print $url . '/DistantMatrix.txt' ?>">Download Distance Matrix</a><br />
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
            for($i = 0; $i < $count_species; ++$i){
                $element = explode(',', $elements[$i]);
                print "<tr>";
                print "<th>{$species[$i]}</th>";
                for($j = $count_species-1; $j >= 0; --$j){
                    print "<td>";
                    if(isset($element[$j])) print trim($element[$j]);
                    print "</td>";
                }
                print "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>