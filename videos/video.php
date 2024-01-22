<?
if ($_REQUEST["file"] == "videos/oqituvchilar_kuni_full.mp4")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video iframe</title>
    <link rel="stylesheet" href="../modules/plyr/plyr.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

<?php
    echo '<video id="video_player" playsinline controls data-poster="/path/to/poster.jpg">
                <source src="../'.$_REQUEST["file"].'" type="video/mp4" />
                </video>';
?>

<script src="../modules/plyr/plyr.js"></script>
<script>
    var video_player = new Plyr('#video_player');
</script>
</body>
</html>