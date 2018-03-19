<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/5/17
 * Time: 2:12 PM
 */

/**
 * @var array $projects A project list containing 'id', 'name', 'date_created', 'pending' and 'editable'
 */
$project_count = count($projects);
?>
<script>
    $(document).ready(function(){
        $('.projects').each(function(){
            let project    = $(this);
            let project_id = $(this).attr('data-id');
            // Modify datetime
            let datetime   = project.find('.datetime');
            let date_created = new Date(datetime.html() + " UTC");
            datetime.html(date_created.toDateString() + " at " + date_created.toTimeString().slice(0,8));
            // Show elapsed time
            let selector = project.find('.elapsed_time');
            elapsed_time(selector, date_created);
            (function (selector, date_created) {
                setInterval(function(){
                    elapsed_time(selector, date_created);
                }, 1000);
            })(selector, date_created);
            // Show status
            selector = project.find('.process_status');
            Project.process.status(selector, project_id);
            (function (selector, project_id) {
                setInterval(function(){
                    Project.process.status(selector, project_id);
                }, 10000);
            })(selector, project_id);
        });
    });
</script>
<div class="row">
    <div class="col-md-12">
        <h1>Pending projects</h1>
        <h4><a href='./projects/new'>Create a new project</a></h4>
        <?php
        if($project_count == 0):
            ?>
            <div>You don't have any pending projects.</div>
            <?php
        else:
            ?>
            <table class="table table-responsive table-hover">
                <tbody>
                <?php
                foreach($projects as $index => $project):
                    $delete_text = "<a href=\"javascript:Project.process.cancel({$project['id']}, '{$project['name']}')\" class=\"project-icon glyphicon glyphicon-remove text-danger\" title=\"Cancel Project\"></a>";
                    print <<< EOF
                <tr class="projects" id="p_{$project['id']}" data-id="{$project['id']}">
                    <td>#<a href="./projects/{$project['id']}">{$project['id']}</a></td>
                    <td>
                    <div>
                        <a href="./projects/{$project['id']}" class="h4">{$project['name']}</a>
                    </div>
                    <div><em>Date: <span class="datetime">{$project['date_created']}</span></em></div>
                    <div><em>Elapsed time: <span class="elapsed_time"></span></em></div>
                    <div><em>Status: <span class="process_status"></span></em></div></td>
                    <td><a href="javascript:Project.process.cancel({$project['id']}, '{$project['name']}')" class="project-icon glyphicon glyphicon-remove text-danger" title="Cancel Project"></a></td>
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
