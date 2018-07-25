<?php
if(preg_match('/^\/(logos|profile|sponsor|Treant|vendor|css|ico|fonts|js|test\.php)/', $_SERVER['REQUEST_URI'], $matches)){
    // Local directories
    $file = __DIR__ . '/public' . $_SERVER['REQUEST_URI'];
    if(file_exists($file) AND !is_dir($file)){
        $mime = null;
        switch(get_mime($file)){
            case 'css': $mime = 'text/css'; break;
            case 'js' : $mime = 'application/javascript'; break;
            default:
                $mime = mime_content_type($file);
        }
        header('Content-Type: ' . $mime);
        header('Cache-Control: Public, max-age: 3600');
        /** @noinspection PhpIncludeInspection */
        require_once $file;
        exit();
    }
}
// Otherwise, handle page via index.php
include __DIR__ . '/public/index.php';


function get_mime($file){
    preg_match('/\.(\w+)$/', basename($file), $matches);
    return isset($matches[1]) ? $matches[1] : null;
}