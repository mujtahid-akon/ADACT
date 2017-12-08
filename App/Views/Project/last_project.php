<?php
/**
 * @var int $status
 */
if($status == \ADACT\App\HttpStatusCode::NOT_FOUND) {
    ?>
    <p>You don't have any &lsquo;last project&rsquo;. It is the last project created
        by you and it is the only project which can be edited.</p>
    <p>You can <a href="/projects/new">create a new project</a> if you want.</p>
    <button onclick="window.location.assign('/projects')" class="btn btn-primary">View all projects</button>
    <?php
}