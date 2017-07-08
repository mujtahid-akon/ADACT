<?php
/**
 * @var array $projects a project list containing 'id', 'name', 'date_created' and 'editable'
 */
$project_count = count($projects);
?>
<script>
var delete_project = function(id, p_name){
//     var conf = confirm("Are you sure want to delete " + p_name + "?");
//     if(!conf) return false;
    
    $.ajax({
        method: 'post',
        url: 'projects/' + id + '/delete',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
            return confirm("Are you sure want to delete " + p_name + "?");
        },
        success: function(res){
            switch(res.status){
                case 0:
                    $('#p_' + id).remove();
                    alert('Your project was successfully deleted.');
                    break;
                case 2:
                    alert('Couldn\'t delete the project, it doesn\'t exists or might have alreay been deleted.');
                    break;
                default:
                    alert('Sorry, due an error the project couldn\'t be deleted. Please, try again.');
            }
        },
        error: function(xhr, status){
            if(status !== null) alert('Sorry, due an error the project couldn\'t be deleted. Please, try again.');
        }
    });
};
</script>
<div class="row">
    <div class="col-md-12">
        <h1>All projects</h1>
        <h4><a href='projects/new'>Create a new project</a></h4>
        <?php
        if($project_count == 0){
        ?>
            <div>You don't have any projects.</div>
        <?php
        }else{
        ?>
        <table class="table table-responsive table-hover">
            <tbody>
<?php
foreach($projects as $project){
// Format Date
$datetime = DateTime::createFromFormat('Y-m-d H:i:s', $project['date_created']);
$project['date_created'] = $datetime->format('jS M, Y') . ' at ' . $datetime->format('H:i:s');
// Show text based on wether the project is editable or not.
$edit_text = $project['editable'] ? '<a href="projects/' . $project['id'] . '/edit" class="project-icon glyphicon glyphicon-edit" title="Edit Project"></a>' : '';

print <<<EOF
                <tr id="p_{$project['id']}">
                    <td>#<a href="projects/{$project['id']}">{$project['id']}</a></td>
                    <td><a href="projects/{$project['id']}" class="h4">{$project['name']}</a><div><em>Date: {$project['date_created']}</em></div></td>
                    <td>{$edit_text}</td>
                    <td><a href="javascript:delete_project({$project['id']}, '{$project['name']}')" class="project-icon glyphicon glyphicon-trash text-danger" title="Delete Project"></span></td>
                    <td><a href="projects/{$project['id']}/download" class="project-icon glyphicon glyphicon-download-alt text-info" title="Download Project"></a></td>
                </tr>

EOF;
} ?>
            </tbody>
        </table>
        <?php } ?>
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