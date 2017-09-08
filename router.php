<?php
if(preg_match('/^\/(css|ico|fonts|js|test\.php)/', $_SERVER['REQUEST_URI'], $matches)){
    // Local directories
    $file = __DIR__ . '/public' . $_SERVER['REQUEST_URI'];
    if(file_exists($file)){
        $mime = null;
        switch(get_mime($file)){
            case 'css': $mime = 'text/css'; break;
            case 'js' : $mime = 'application/javascript'; break;
            default:
                $mime = mime_content_type($file);
        }
        header('Content-Type: ' . $mime);
        include $file;
    }
}else{ // Handle via index.php
    include __DIR__ . '/public/index.php';
}

function get_mime($file){
    preg_match('/\.(\w+)$/', basename($file), $matches);
    return isset($matches[1]) ? $matches[1] : null;
}