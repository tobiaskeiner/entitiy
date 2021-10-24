<?php

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
        echo "Datei ". $_FILES['shapefile']['name'] ." erfolgreich upgeloadet.\n";

        $PATH_TO_SHAPEFILE = $_FILES['shapefile']['tmp_name'];
        //CreateInputDir();
        CreateConfigFile();
        RunConverter();
    }
    echo "</div>";
}

function RunConverter(){
    global $PATH_TO_CONFIG;
    $cmd = 'python ../main.py --config "'.$PATH_TO_CONFIG.'"';

    exec($cmd . ' 2>&1', $output, $return_var);
    echo "<br>cli command<code>$cmd</code>";
    echo "Output:\n<pre>";
    print_r($output);
    echo "</pre>";

    $mapPath = realpath(dirname($_FILES['shapefile']['tmp_name']).DIRECTORY_SEPARATOR.'configMap.json');
    if(file_exists($mapPath)){
        echo '<h3>output.json erfolgreich erstellt</h3>';
        echo '<a class="button" href="input/configMap.json">download</a>';
    } else {
        echo '<h3>Sorry</h3>';
        echo 'output.json konnte nicht erstellt werden <br><br>';
    }
}

// function CreateInputDir()
// {
//     if (!is_dir('input'))
//         mkdir('input');

//     $old_files = glob('input/*');
//     // Deleting all the files in the list
//     foreach($old_files as $file)
//         if(is_file($file)) unlink($file);

//     if(!move_uploaded_file($_FILES['shapefile']['tmp_name'], 'input/uploaded_file.shp'))
//         return;
// }

function CreateConfigFile()
{
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
        "file" =>  $_FILES['shapefile']['tmp_name'] //realpath("input/uploaded_file.shp")
    ];

    // echo "Pfad: ".getcwd();
    // echo "File: ".basename(getcwd()."/input/config.json");

    global $PATH_TO_CONFIG;
    $PATH_TO_CONFIG = dirname($_FILES['shapefile']['tmp_name']).'/config.json';
    $config = fopen($PATH_TO_CONFIG, "w") or die("Unable to open file!");
    fwrite($config, json_encode($config_array));
    fclose($config);
}

Main();