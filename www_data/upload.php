<?php

$PATH_TO_CONVERTER = "..";

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
        //readfile($_FILES['shapefile']['tmp_name']);

        CreateInputDir();
        CreateConfigFile();
        RunConverter();
    }
    echo "</div>";
}

function RunConverter(){
    chdir(__DIR__);
    global $PATH_TO_CONVERTER;
    $configPath = realpath("input\\config.json");
    $cmd = "python ..\\main.py --config '$configPath'";

    exec($cmd . ' 2>&1', $output, $return_var);
    echo "<br>cli command<code>$cmd</code>";
    echo "Output:\n";
    print_r($output);

    //debug python cmd
    // echo "<br>cli command<code>$cmd</code>";
    // echo "Return code: $return_var\n";
    // echo "Output:\n";
    // print_r($output);

    if(file_exists(realpath("input\\configMap.json"))){
        echo '<h3>output.json erfolgreich erstellt</h3>';
        echo '<a class="button" href="input/configMap.json">download</a>';
    } else {
        echo '<h3>Sorry</h3>';
        echo 'output.json konnte nicht erstellt werden <br><br>';
    }
}

function CreateInputDir()
{
    if (!is_dir('input'))
        mkdir('input');

    $old_files = glob('input/*');
    // Deleting all the files in the list
    foreach($old_files as $file)
        if(is_file($file)) unlink($file);

    if(!move_uploaded_file($_FILES['shapefile']['tmp_name'], 'input/uploaded_file.shp'))
        return;
}

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
        "file" => realpath("input/uploaded_file.shp")
    ];

    $config = fopen("input/config.json", "w") or die("Unable to open file!");
    fwrite($config, json_encode($config_array));
    fclose($config);
}

Main();