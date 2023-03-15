<?php

use App\Config\Project;

ini_set('display_errors', 1);


include dirname(__DIR__, 1) . "/vendor/autoload.php";


$project = Project::getInstance();



$project->run();