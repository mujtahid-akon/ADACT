<?php
use \ADACT\App\Models\Project;
/**
 * @var array $projects A project list containing 'id', 'name', 'date_created', 'result_type' and 'editable'
 */
$project_count = count($projects);
?>
<script>
    $(document).ready(function(){
        let datetime_list = $(".datetime");
        const len = datetime_list.length;
        for(let i=0; i<len; ++i){
            let d = new Date(datetime_list.eq(i).html() + " UTC");
            datetime_list.eq(i).html(d.toDateString() + " at " + d.toTimeString().slice(0,8));
        }
    });
</script>
<div class="row">
    <div class="col-md-12">
        <h1>All projects</h1>
        <h4><a href='projects/new'>Create a new project</a></h4>
        <?php
        if($project_count == 0):
        ?>
            <div>You don't have any projects.</div>
        <?php
        else:
        ?>
        <table class="table table-responsive table-hover">
            <tbody>
<?php
foreach($projects as $project):
    // Show text based on whether the project is editable or not.
    $edit_text = $project['editable'] ? '<a href="/projects/' . $project['id'] . '/edit" class="project-icon glyphicon glyphicon-edit" title="Edit Project"></a>' : '';
    // Cancel or Delete
    $delete_text = $project['result_type'] != Project::RT_PENDING ?
        "<a href=\"javascript:Project.delete({$project['id']}, '{$project['name']}')\" class=\"project-icon glyphicon glyphicon-trash text-danger\" title=\"Delete Project\"></a>" :
        "<a href=\"javascript:Project.process.cancel({$project['id']}, '{$project['name']}')\" class=\"project-icon glyphicon glyphicon-remove text-danger\" title=\"Cancel Project\"></a>";
    // Set status text on the basis of result type
    switch ($project['result_type']){
        case Project::RT_CANCELLED:
            $status_text = '<small class="alert alert-warning" style="padding: 5px;border-radius: 5px;border-color: #e8ceb1;background-color: #f5deb3;">cancelled</small>';
            $background  = 'background-color: #f5deb3;';
            break;
        case Project::RT_FAILED:
            $status_text = '<small class="alert alert-danger" style="padding: 5px;border-radius: 5px;">failed</small>';
            $background  = 'background-color: #f2dede;';
            break;
        case Project::RT_PENDING:
            $status_text = '<small class="alert alert-info" style="padding: 5px;border-radius: 5px;">pending</small>';
            $background  = 'background-color: #d9edf7;';
            break;
        default:
            $status_text = '';
            $background  = 'background-color: #dff0d8;';
    }
    // Download
    $download_text = $project['result_type'] == Project::RT_SUCCESS ?
        "<a href=\"projects/{$project['id']}/download\" class=\"project-icon glyphicon glyphicon-download-alt text-info\" title=\"Download Project\"></a>":
        '';

    // Formatted project id and names
    $project_id = ($project['result_type'] !== Project::RT_CANCELLED) ? "<a href=\"projects/{$project['id']}\">{$project['id']}</a>" : $project['id'];
    $project_name = ($project['result_type'] !== Project::RT_CANCELLED) ? "<a href=\"projects/{$project['id']}\" class=\"h4\">{$project['name']}</a>" : "<span class=\"h4\">{$project['name']}</span>";
    print <<< EOF
                <tr id="p_{$project['id']}" style="<!-- {$background} -->">
                    <td>#{$project_id}</td>
                    <td>
                    <div>{$status_text} {$project_name}</div>
                    <div><em>Date: <span class="datetime">{$project['date_created']}</span></em></div></td>
                    <td>{$edit_text}</td>
                    <td>{$delete_text}</td>
                    <td>{$download_text}</td>
                </tr>
EOF;
endforeach;
?>
            </tbody>
        </table>
        <?php endif; // Project count ?>
    </div>
</div>
<style>
    table a{
        color: inherit;
    }
    table a:hover{
        color: #333;
    }
    .project-icon{
        color: inherit;
        font-size: x-large;
    }
    .project-icon:hover, .project-icon:link, .project-icon:visited, .project-icon:active{
        text-decoration: none;
    }
</style>
