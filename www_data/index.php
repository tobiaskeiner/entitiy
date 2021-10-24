<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
  <!-- CSS Reset -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.css">
  <!-- Milligram CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.css">
  <title>Fan Adventure</title>

  <style>
    html, body { height: 100%;
        width: 100%;
        overflow: hidden;
      }

    .bgimage  {
        background-image: url("stadion.png");
        position: absolute;
        background-position: top;
        -webkit-background-size: auto;
        -moz-background-size: auto;
        -o-background-size: auto;
        background-size: auto;
        background-repeat: no-repeat;
        width: 100%;
        height: 100%;
      }
    form { background-color: #fafafa89; padding: 1em; }
  </style>
</head>
<body>
  <div class="bgimage">
    <div class="container">
      <div class="section">
        <h1>Fan Adventure</h1>

        <p>
          Convert your Shapefiles into GeoJSON for WorkAdventure
        </p>
      </div>

    <?php include_once("upload.php") ?>

    <h3>
      Upload your Shapefile:
    </h3>

      <form  enctype="multipart/form-data"
        action="index.php"
        method="post">
          <fieldset>

          <div class="row">
            <div class="column">
              <label for="shapefile">Dein Shapefile (*.shp)</label>
              <input type="file" name="shapefile">
            </div>
            <div class="column">
              <label for="shapefile">Deine Datei Attributdatei bzw Sachdatendatei (*.dbf)</label>
              <input type="file" name="attributes">
            </div>
            <div class="column">
              <label for="shapefile">Deine Datei mit der Index der Geometrie (*.shx)</label>
              <input type="file" name="indexes">
            </div>
          </div>
          <div class="row">
            <div class="column">
              <label for="xMult">Multiplikator x-Achse</label>
              <input type="text" name="xMult" value="0.6">
              <label for="yMult">Multiplikator y-Achse</label>
              <input type="text" name="yMult" value="0.3">
            </div>
            <div class="column">
              <label for="xOffset">Offset x-Achse</label>
              <input type="text" name="xOffset" value="90">
              <label for="yOffset">Offset y-Achse</label>
              <input type="text" name="yOffset" value="80">
            </div>
            <div class="column">
              <label for="xLimit">Limit x-Achse</label>
              <input type="text" name="xLimit" value="180">
              <label for="yLimit">Limit y-Achse</label>
              <input type="text" name="yLimit" value="150">
            </div>
          </div>
          <div class="row">
            <div class="column">
              <label for="wallHeight">Wandhöhe</label>
              <input type="text" name="wallHeight" value="2">
            </div>
            <div class="column">
              <label for="isWall">Projektion (0/1)</label>
              <input type="text" name="isWall" value="0">
            </div>
            <div class="column">
              <label for="printMatrix">Matrix (0/1)</label>
              <input type="text" name="printMatrix" value="0">
            </div>
          </div>
            <div class="column">
              <label for="submit">Hochladen</label>
              <input type="submit" name="submit" value="Submit">
            </div>
        </fieldset>
      </form>
    </div>
  </div>
</body>
</html>