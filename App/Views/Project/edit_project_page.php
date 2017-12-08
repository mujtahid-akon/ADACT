<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/29/17
 * Time: 2:36 PM
 */
use \ADACT\App\Models\FileManager;
use \ADACT\App\Models\ProjectConfig;
use \ADACT\App\Models\Project;
/**
 * Variables exported from Project controller
 *
 * @var int   $project_id          Current project id
 * @var array $dissimilarity_index Dissimilarity index array
 */

// load config
$config = new ProjectConfig((new FileManager($project_id))->get(FileManager::CONFIG_JSON));
// Project type
$isAFileIOProject = $config->type === Project::INPUT_TYPE_FILE;
// Base url
$base_url = $_SERVER['PHP_SELF'];
// Transform Absent Words type to uppercase
$config->aw_type = strtoupper($config->aw_type);

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
        // Then, blank columns
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
    <h3>Editing: <?php print ucwords($config->project_name); ?></h3>
    <script>
        // Initialize values
        $(document).ready(function () {
            // Set Absent Word type
            const aw_type = "<?php print strtolower($config->aw_type) ?>";
            $('input[name=\'aw_type\'][value=\'' + aw_type + '\']').attr('checked', true);
            // Set K-Mer
            $('#kmer_min').val(<?php print $config->kmer['min'] ?>);
            $('#kmer_max').val(<?php print $config->kmer['max'] ?>);
            // Set reverse complement
            $('#inversion').attr('checked', <?php print ($config->inversion ? 'true' : 'false'); ?>);
            // Show Dissimilarity Index based on Absent Word type
            if(aw_type === 'maw'){
                $('.maw_dissimilarity').show();$('.raw_dissimilarity').hide();
            }else{
                $('.maw_dissimilarity').hide();$('.raw_dissimilarity').show();
            }
            // Set Dissimilarity Index based on Absent Word type
            $('option[value=\'<?php print $config->dissimilarity_index ?>\']').attr('selected', true);
        });

        // Manipulate project
        Project.edit = {
            MAW: 'maw',
            RAW: 'raw',
            info: {},
            collect: function(){
                // Similar to Project.result.prepare()
                this.info = {
                    aw_type: $("input[name='aw_type'][value='raw']").is(':checked') ? this.RAW : this.MAW, // #1
                    kmer: { // #2
                        min: parseInt($('#kmer_min').val()),
                        max: parseInt($('#kmer_max').val())
                    },
                    inversion: $('#inversion').is(":checked"), // #3
                    dissimilarity_index: $('#dissimilarity_index').val(), // #4
                };
            },
            send: function () {
                this.collect();
                //
            }
        };
    </script>
    <div>
        <button onclick="Project.edit(<?php print $project_id. ', \'' .$config->project_name . '\'' ?>)" class="btn btn-primary">Run & Show Result</button>
        <a class="btn btn-default" href="/projects/<?php print $project_id; ?>">Go back</a>
    </div>
    <div id="project_info">
        <table class="table table-bordered table-striped table-hover">
            <caption>Overview</caption>
            <tbody>
            <?php
            print "<tr><th>Project Name</th><td>".ucwords($config->project_name)."</td></tr>";
            print "<tr><th>Sequence Type</th><td>".ucwords($config->sequence_type)."</td></tr>";
            ?>
            <tr>
                <th>Absent Word Type</th>
                <td>
                    <label>
                        <input type="radio" name="aw_type" value="maw" onchange="$('.maw_dissimilarity').show();$('.raw_dissimilarity').hide();" />
                        <abbr title="Minimal Absent Words">MAW</abbr>
                    </label>
                    <label>
                        <input type="radio" name="aw_type" value="raw" onchange="$('.maw_dissimilarity').hide();$('.raw_dissimilarity').show();"/>
                        <abbr title="Relative Absent Words">RAW</abbr>
                    </label>
                </td>
            </tr>
            <tr>
                <th>K-Mer</th>
                <td>
                    <input class="form-control" type="number" id="kmer_min" name="kmer_min" min="1" style="width: 100px;display: inline-block;" placeholder="Min" required />
                    <input class="form-control" type="number" id="kmer_max" name="kmer_max" min="1" style="width: 100px;display: inline-block" placeholder="Max" required />
                </td>
            </tr>
            <tr>
                <th><label for="inversion">Reverse Complement</label></th>
                <td><input type="checkbox" id="inversion" name="inversion" /></td>
            </tr>
            <tr>
                <th><label for="dissimilarity_index">Dissimilarity Index</label></th>
                <td>
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
                </td>
            </tr>
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
    </div>