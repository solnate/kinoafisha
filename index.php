<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php' ?>
<body>
    <h1>Тестовое задание для Junior PHP Developer</h1>
    <?php
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/core/data/auth')) {
        include $_SERVER['DOCUMENT_ROOT'] . '/core/include/getData.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/templates/blocks/main.phtml';
    } else {
        include $_SERVER['DOCUMENT_ROOT'] . '/templates/blocks/registration.phtml';
    }
    ?>
</body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php' ?>