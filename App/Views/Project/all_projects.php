<?php
/**
 * @var array $projects A project list containing 'id', 'name', 'date_created', 'pending' and 'editable'
 */
$project_count = count($projects);
?>
<script src="/js/app.js"></script>
<script>
    $(document).ready(function(){
        var datetime_list = $(".datetime");
        var len = datetime_list.length;
        for(var i=0; i<len; ++i){
            var d = new Date(datetime_list.eq(i).html() + " UTC");
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
foreach($projects as $project){
// Show text based on whether the project is editable or not.
$edit_text = $project['editable'] ? '<a href="/projects/' . $project['id'] . '/edit" class="project-icon glyphicon glyphicon-edit" title="Edit Project"></a>' : '';
$delete_text = !$project['pending'] ?
    "<a href=\"javascript:Project.delete({$project['id']}, '{$project['name']}')\" class=\"project-icon glyphicon glyphicon-trash text-danger\" title=\"Delete Project\"></a>" :
    "<a href=\"javascript:Project.process.cancel({$project['id']}, '{$project['name']}')\" class=\"project-icon glyphicon glyphicon-remove text-danger\" title=\"Cancel Project\"></a>";
$pending_text = $project['pending'] ? '<small class="alert-warning" style="padding: 5px;border-radius: 5px;text-shadow: 1px 1px wheat;background-color: wheat;">pending</small>' : '';
$download_text = $project['pending'] ?
    '' :
    "<a href=\"projects/{$project['id']}/download\" class=\"project-icon glyphicon glyphicon-download-alt text-info\" title=\"Download Project\"></a>";

print <<< EOF
                <tr id="p_{$project['id']}">
                    <td>#<a href="projects/{$project['id']}">{$project['id']}</a></td>
                    <td>
                    <div>
                        <a href="projects/{$project['id']}" class="h4">{$project['name']}</a>
                        {$pending_text}
                    </div>
                    <div><em>Date: <span class="datetime">{$project['date_created']}</span></em></div></td>
                    <td>{$edit_text}</td>
                    <td>{$delete_text}</td>
                    <td>{$download_text}</td>
                </tr>
EOF;
}
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
