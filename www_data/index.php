<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Fan Adventure</title>
</head>
<body>
    <h1>Fan Adventure</h1>

    <p>
      Convert your Shapefiles into GeoJSON for WorkAdventure
    </p>


    <?php include_once("upload.php") ?>


    <h3>
      Upload your Shapefile:
    </h3>
    <form enctype="multipart/form-data"
          action="index.php"
          method="post">
      <input type="file" name="shapefile">

      <input type="text" name="xMult" value="0.6">
      <input type="text" name="yMult" value="0.3">
      <input type="text" name="xOffset" value="90">
      <input type="text" name="yOffset" value="80">
      <input type="text" name="xLimit" value="180">
      <input type="text" name="yLimit" value="150">
      <input type="text" name="isWall" value="0">
      <input type="text" name="wallHeight" value="2">
      <input type="text" name="printMatrix" value="0">

      <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>