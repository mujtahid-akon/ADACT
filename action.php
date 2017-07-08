<?php
set_time_limit(0);
session_start();

require_once __DIR__ . '/Config.php';
/**
 * Created by PhpStorm.
 * Date: 3/27/17
 * Time: 9:08 AM
 */

/**
 * TODO: Upload limit isn't set
 */
// If new 
if(isset($_GET['new'])) destroy_session();
// If new maw type is requested
if(isset($_GET['type'])) unset($_SESSION['project']['type']);
// If new upload is requested
if(isset($_GET['upload'])){
    unset($_SESSION['project']['directory']);
    unset($_SESSION['project']['type']);
}
// Download files
if(isset($_GET['down'], $_SESSION['project']['distance_matrix'])){
    $dir = $_SESSION['project']['distance_matrix'];
    header('Content-Type: image/jpg');
    switch($_GET['down']){
        case 'Neighbour':   $file = "Neighbour Tree.jpg"; break;
        case 'UPGMA':       $file = "UPGMA Tree.jpg"; break;
        case 'SSR':         $file = "SpeciesRelation.txt"; break;
        case 'DM':          $file = "Output.txt"; break;
        default:            $file = false;
    }
    if($file === false){
        goto_home();
    }else{
        header("Content-Disposition: attachment; filename='{$file}'");
        readfile("{$dir}/{$file}");
    }
    exit();
}

// Warning Generator
$_SESSION['warnings'] = array();

// Formatted Project Name, eg. project_name
if(isset($_POST['project_name'])){
    $_SESSION['project']['name'] = strtolower(preg_replace('/[\/\s\.]/', '_', $_POST['project_name']));
    goto_home();
}

/**
 * Define Global Constants
 */

define('WORKING_DIRECTORY', '/tmp'); // trailing slash shouldn't be used

define('MAW', 'MAW');
define('RAW', 'RAW');

// DO NOT modify the values!
define('DNA', 'DNA');
define('PROTEIN', 'PROT');

define('PROJECT_NAME', $_SESSION['project']['name']);

if(!isset($_SESSION['project']['wd'])) $_SESSION['project']['wd'] = PROJECT_NAME . time();
define('PROJECT_WORKING_DIRECTORY', $_SESSION['project']['wd']);

// Define project directories
define('PROJECT_DIRECTORY', WORKING_DIRECTORY . '/' . PROJECT_WORKING_DIRECTORY);    // Main Project directory
define('INPUT_DIRECTORY', PROJECT_DIRECTORY . '/' . PROJECT_NAME);      // Unzipped files
define('GENERATED_DIRECTORY', PROJECT_DIRECTORY . '/generated');        // Generated RAW/MAW
define('MAW_DIRECTORY', GENERATED_DIRECTORY . '/MAW');                  // MAW directory under generated
define('RAW_DIRECTORY', GENERATED_DIRECTORY . '/RAW');                  // RAW directory under generated

// Create project directory
if(!is_dir(PROJECT_DIRECTORY)) mkdir(PROJECT_DIRECTORY);

// Upload, extract a zip
if(isset($_FILES['zip'])){
    // Flash the previous one if exists
    if(is_dir(INPUT_DIRECTORY)) passthru('rm -R ' . INPUT_DIRECTORY);
    mkdir(INPUT_DIRECTORY);
    // Get the zip file
    //array_push($_SESSION[''])
    $zip_file = get_zip_path($_FILES['zip']);
    // Unzip to INPUT_DIRECTORY
    if($zip_file === false) goto_home('Invalid Zip file!');
    if(!unzip_and_verify($zip_file, INPUT_DIRECTORY)) goto_home('Invalid Zip file or the file structure did not meet the requirement!');
    $_SESSION['project']['directory'] = PROJECT_DIRECTORY;
    goto_home();
}

if(!isset($_POST['type'])) goto_home();

// Create directory if not exists
// For RAWs and MAWs, re-create directories
if(!is_dir(GENERATED_DIRECTORY)) mkdir(GENERATED_DIRECTORY);
if(file_exists(MAW_DIRECTORY)) passthru('rm -R ' . MAW_DIRECTORY);
if(file_exists(RAW_DIRECTORY)) passthru('rm -R ' . RAW_DIRECTORY);
mkdir(MAW_DIRECTORY);
mkdir(RAW_DIRECTORY);

// File operation type: generate MAWs or RAWs
define('OP_TYPE', isset($_POST['type']) && $_POST['type'] == 'maw' ? MAW : RAW);

// k-mer sizes
define('K_MER_MAX', filter_input(INPUT_POST, 'k_mer_max', FILTER_SANITIZE_NUMBER_INT));
define('K_MER_MIN', filter_input(INPUT_POST, 'k_mer_min', FILTER_SANITIZE_NUMBER_INT));

// Check if K-Mer sizes are in order
if(K_MER_MAX <= 0 || K_MER_MIN <= 0) goto_home("K-Mer Minimum or Minimum can't be zero or negative!");
if(K_MER_MAX < K_MER_MIN) goto_home("K-Mer Minimum is greater than K-Mer Maximum!");

// Use Inversion, default: False
define('INVERSION', isset($_POST['inverse']) ? true : false);

// Dissimilarity Index
// For MAW:
define('MAW_LWI_SDIFF', 'MAW_LWI_SDIFF');
define('MAW_LWI_INTERSECT', 'MAW_LWI_INTERSECT');
define('MAW_GCC_SDIFF', 'MAW_GCC_SDIFF');
define('MAW_GCC_INTERSECT', 'MAW_GCC_INTERSECT');
define('MAW_JD', 'MAW_JD');
define('MAW_TVD', 'MAW_TVD');
// For RAW:
define('RAW_LWI', 'RAW_LWI');
define('RAW_GCC', 'RAW_GCC');

$maw_dis_i = [MAW_LWI_SDIFF, MAW_LWI_INTERSECT, MAW_GCC_SDIFF, MAW_GCC_INTERSECT, MAW_JD, MAW_TVD];
$raw_dis_i = [RAW_LWI, RAW_GCC];

// if MAW: DNA or PROTEIN
if(OP_TYPE == MAW){
    $which_one = $_POST['which_one'] == 'dna' ? DNA : PROTEIN;
}

define('DISSIMILARITY_INDEX', in_array($_POST['d_i'], OP_TYPE == MAW ? $maw_dis_i : $raw_dis_i) ? $_POST['d_i'] : null);

/** === Session Data === */

$_SESSION['project']['type'] = OP_TYPE == MAW ? 'MAW' : 'RAW';
$_SESSION['project']['K-Mer_minimum'] = K_MER_MIN;
$_SESSION['project']['K-Mer_maximum'] = K_MER_MAX;
$_SESSION['project']['use_inversion'] = INVERSION ? 'YES' : 'NO';
$_SESSION['project']['dissimilarity_index'] = DISSIMILARITY_INDEX;


// Generate .fasta using EAGLE or maw (based on requirement) and save it at /wd/project_name/generated
generate_fasta();


header('Location: index.php');

/** === Functions === */

/**
 * function generate_fasta
 *
 * Generate fasta from the input fasta or fna
 */

function generate_fasta(){
    $s = time();    // Running time end
    // Include globals
    global $which_one;

    // Lists all the .fasta & .fna files
    $files = preg_grep('/(S|s)pecies.*/', dir_list(INPUT_DIRECTORY), PREG_GREP_INVERT);

    // $s = time();    // Running time begin
    if(OP_TYPE == MAW){
        foreach($files as $file){
            $output_file = MAW_DIRECTORY . '/' . maw_name($file);
            exec("./exec/maw -a {$which_one} -i '{$file}' -o '{$output_file}' -k ".K_MER_MIN." -K " . K_MER_MAX . (INVERSION ? ' -r 1' : ''));
        }
    }elseif(OP_TYPE == RAW){
        sort($files);
        generate_raw_fasta($files, 0);
    }
    // $e = time();    // Running time end
    // error_log("Time taken: " . ($e - $s));

    // Copy SpeciesOrder.txt file
    $dest = OP_TYPE == MAW ? MAW_DIRECTORY : RAW_DIRECTORY;
    passthru('cp -R ' . INPUT_DIRECTORY . '/SpeciesOrder.txt ' . $dest);
    // Create SpeciesFull.txt
    gen_species_txt($dest . '/SpeciesOrder.txt');
    // Run Distance Matrix Generator
    exec('./exec/dm ' . OP_TYPE . ' ' . DISSIMILARITY_INDEX . ' ' . $dest . ' ' . $dest, $output);
    error_log(implode("\n", $output));
    $_SESSION['project']['distance_matrix'] = $dest;
    passthru('cd ./exec/ && java Match7 '. $dest . '/');
    $e = time();    // Running time end
    error_log("Time taken: " . ($e - $s));
}

function generate_raw_fasta($files, $ref_file_index){
    $modified_files = $files;
    if($ref_file_index < count($modified_files)){
        $ref_file = $modified_files[$ref_file_index];
        unset($modified_files[$ref_file_index]);
    }else return null;
    $ref_file_name = file_name_without_ext($ref_file);
    $ref_file_dir = RAW_DIRECTORY . "/{$ref_file_name}";
    if(!file_exists($ref_file_dir)) mkdir($ref_file_dir);
    foreach($modified_files as $file){
        exec('./exec/EAGLE -min ' . K_MER_MIN . ' -max ' . K_MER_MAX . (INVERSION ? ' -i' : '') . " -r {$ref_file} {$file}", $output);
        error_log(implode("\n", $output));                                      // This will show EAGLE output
        passthru("mv '" . INPUT_DIRECTORY . "'/*.raw.txt '{$ref_file_dir}'");   // Move the *.raw.txt file
    }
    return generate_raw_fasta($files, $ref_file_index + 1);
}

/**
 * function get_zip_path
 *
 * Get the path of the uploaded zip file
 *
 * NOTE: This method of verifying a zip file isn't fail-safe (checks only mime),
 *       further filtering is applied in the unzip_and_verify function
 *
 * @param array $zip ie. $_FILES['zip']
 * @return bool|string false if not a zip file (NOT fail-safe) otherwise the tmp directory
 */
function get_zip_path($zip){
    //array_push($_SESSION['warnings'], $zip);
    return ($zip['type'] == 'application/zip' || $zip['type'] == 'application/octet-stream') ? $zip['tmp_name'] : false;
}

/**
 * function unzip_and_verify
 *
 * unzips to /wd/project_name/project_name and verifies
 *  if the files are in order ie. It verifies that
 *  - the file is a valid zip file
 *  - it contains some contig files and one /Species+/ file
 *
 * @param string $zip_file zip file location
 * @param string $zip_dir  location to the zip file extraction
 * @return bool true on success
 */
function unzip_and_verify($zip_file, $zip_dir){
    $zip = new ZipArchive();
    if($zip->open($zip_file) === true){
        $zip->extractTo($zip_dir);
        $zip->close();
        $files = dir_list($zip_dir, false);
        return (count($files) > 1 && count(preg_grep('/^SpeciesOrder\.txt$/', $files)) > 0) ? true : false;
    }
    return false;
}

/**
 * function maw_name
 *
 * generates maw file name
 *
 * @param string $file full path to a MAW file
 * @return string file_name.maw.txt
 */
function maw_name($file){
    return file_name_without_ext($file) . '.maw.txt';
}

/**
 * function file_name_without_ext
 *
 * generates file name without any extension
 * eg. file.ext1.ext2.ext3 -> file
 *
 * @param string $file full path to a file
 * @return string file name without extension
 */
function file_name_without_ext($file){
    return preg_replace('/(\.\w+)+$/', '', basename($file));
}

/**
 * function dir_list
 *
 * @param string $directory any directory
 * @param bool $full Whether to include full directory (takes more time)
 * @return array List of directories or empty array if not a directory
 */
function dir_list($directory, $full = true){
    $files = is_dir($directory) ? array_diff(scandir($directory), array('..', '.')) : [];
    if($full){
        foreach($files as &$file) $file = $directory . '/' . $file;
    }
    return $files;
}

/**
 * SpeciesOrder.txt -> SpeciesFull.txt
 */
function gen_species_txt($in){
    $lines = file($in);
    $short = [];
    foreach($lines as &$line){
        array_push($short, preg_replace('/^\w+,/', '', $line));
        $line = preg_replace('/,\w+$/', '', $line);
    }
    if(!preg_match('/\s/', end($lines))) array_push($lines, ' ');    // Won't work without this!!!
    //array_push($_SESSION['warnings'], preg_match('/\s/', end($lines)));
    file_put_contents(dirname($in) . '/SpeciesFull.txt', $lines);
    //file_put_contents(dirname($in) . '/SpeciesShort.txt', $short);
}

/**
 * function destroy session
 *
 * Destroys a session as well as delete the project directory
 */
function destroy_session(){
    if(isset($_SESSION['project']['directory'])) passthru("rm -R {$_SESSION['project']['directory']}");
    session_destroy();
    header('Location: index.php');
    exit();
}


/**
 * function goto_home
 *
 * Redirect to home with a message
 *
 * @param string $msg   Message to be shown to the user
 * @param bool $destroy Destroy project data?
 */
function goto_home($msg = null, $destroy = false){
    if($destroy && isset($_SESSION['project'])) unset($_SESSION['project']);
    if($msg !== null) array_push($_SESSION['warnings'], $msg);
    header('Location: index.php');
    exit();
}