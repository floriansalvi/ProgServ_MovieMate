<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MovieMate - <?=$title?></title>
        <link rel="stylesheet" href="./assets/css/style.css" />
        <script src="https://kit.fontawesome.com/4123c44153.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php
        include_once 'header.php'; 
        echo $content;
        include_once 'footer.php'; ?>
    </body>
</html>