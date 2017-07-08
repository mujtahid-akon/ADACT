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
 */

$url = $_SERVER['PHP_SELF'] . '/get';
$dir = \AWorDS\Config::PROJECT_DIRECTORY . '/' . $project_id;
// Get Distant Matrix
$elements = array_reverse(file($dir . '/Output.txt'));
// Get Species Relation
$relation = file($dir . '/SpeciesRelation.txt');
// Get Species names FIXME: Use $config instead
$species = get_species_from_species_relation($relation); //file($dir . '/SpeciesFull.txt');
$count_species = count($species);
// 118

function get_species_from_species_relation($relations){
    $species = [];
    foreach ($relations as $relation){
        preg_match('/[\w\s]+(?=\:)/', $relation, $matches);
        array_push($species, $matches[0]);
    }
    return $species;
}
?>

<h3>Project: <?php print(ucwords($config['project_name'])); ?></h3>
<h4><a href="<?php print $_SERVER['PHP_SELF'] . '/edit' ?>">Edit</a></h4>

<button onclick="$('#project_info').toggle()" class="btn btn-default">Toggle Project Info</button>
<button class="btn btn-default" disabled>Fork This Project</button>
<br/>
<table id="project_info" class="table table-bordered table-striped table-hover" style="display: none;">
    <caption>Project Info</caption>
    <?php // FIXME: More enhancement is needed
    foreach($config as $key => $value){
        print "<tr><th>".ucwords(preg_replace('/_/', ' ', $key))."</th><td>" . $value . "</td></tr>";
    }
    ?>
</table>
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