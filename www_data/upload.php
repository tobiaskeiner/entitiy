<?php

//temp working path in php temp folder
$PATH_TMP_WORKING_DIR = "";

//contains the dir path to the converter script (pyton)
$PATH_TO_CONVERTER = "..";
//contains the file path to the config file
$PATH_TO_CONFIG = null;

function Main(){
    if (!isset($_FILES["shapefile"])) {
        return;
    }

    echo "<div class=\"section\">";
    echo "<h3>Upload results</h3>";

    if (!is_uploaded_file($_FILES['shapefile']['tmp_name'])) {
        return;
    } else {
        // echo '<pre>';
        // echo $_FILES['shapefile']['tmp_name']. '<br>';
        // echo $_FILES['attributes']['tmp_name']. '<br>';
        // echo $_FILES['indexes']['tmp_name']. '<br>';
        // echo '</pre>';

        $PATH_TO_SHAPEFILE = $_FILES['shapefile']['tmp_name'];
        CreateInputDir();
        CreateConfigFile();
        RunConverter();
    }
    echo "</div>";
}

function RunConverter(){
    global $PATH_TO_CONFIG;
    global $PATH_TMP_WORKING_DIR;
    $cmd = 'python ../main.py --config "'.$PATH_TO_CONFIG.'"';

    exec($cmd . ' 2>&1', $output, $return_var);
    if (count($output) > 0)
    {
        echo "<br>cli command<code>$cmd</code>" . count($output);
        echo "Output:\n<pre>";
        print_r($output);
        echo "</pre>";
    }


    $mapPath = realpath($PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'configMap.json');
    if(file_exists($mapPath)){
        echo '<h3>configMap.json erfolgreich erstellt</h3>';
        echo '<a class="button" href="configMap.json">download</a>';
    } else {
        echo '<h3>Sorry</h3>';
        echo 'output.json konnte nicht erstellt werden <br><br>';
    }
}

function CreateInputDir()
{
    global $PATH_TMP_WORKING_DIR;
    $PATH_TMP_WORKING_DIR = dirname($_FILES['indexes']['tmp_name']).DIRECTORY_SEPARATOR.'input';

    if (!is_dir($PATH_TMP_WORKING_DIR))
        mkdir($PATH_TMP_WORKING_DIR);

    $old_files = glob($PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'*');
    // Deleting all the files in the list
    foreach($old_files as $file)
        if(is_file($file)) unlink($file);

    echo '<ul>';
    if(!move_uploaded_file($_FILES['shapefile']['tmp_name'], $PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'uploaded_file.shp'))
    return;
    echo '<li>'.$_FILES['shapefile']['name'].' erfolgreich hochgeladen und Datei kopiert</li>';
    if(!move_uploaded_file($_FILES['attributes']['tmp_name'], $PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'uploaded_file.dbf'))
    return;
    echo '<li>'.$_FILES['attributes']['name'].' erfolgreich hochgeladen und Datei kopiert</li>';
    if(!move_uploaded_file($_FILES['indexes']['tmp_name'], $PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'uploaded_file.shx'))
    return;
    echo '<li>'.$_FILES['indexes']['name'].' erfolgreich hochgeladen und Datei kopiert</li>';
    echo '</ul>';
}

function CreateConfigFile()
{
    global $PATH_TMP_WORKING_DIR;
    $config_array = [
        "xMult"     => floatval($_POST['xMult']),
        "yMult"     => floatval($_POST['yMult']),
        "xOffset"   => intval($_POST['xOffset']),
        "yOffset"   => intval($_POST['yOffset']),
        "xLimit"    => intval($_POST['xLimit']),
        "yLimit"    => intval($_POST['yLimit']),
        "wallHeight" => intval($_POST['wallHeight']),
        "isWall"    => boolval($_POST['isWall']),
        "printMatrix" => boolval($_POST['printMatrix']),
        "file" =>  $PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'uploaded_file.shp' //realpath("input/uploaded_file.shp")
    ];

    global $PATH_TO_CONFIG;
    $PATH_TO_CONFIG = $PATH_TMP_WORKING_DIR.DIRECTORY_SEPARATOR.'config.json';
    $config = fopen($PATH_TO_CONFIG, "w") or die("Unable to open file!");
    fwrite($config, json_encode($config_array));
    fclose($config);
}

Main();