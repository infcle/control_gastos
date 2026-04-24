<!doctype html>
<html lang="en" dir="ltr">

<head>
    <?php
    $headFile = __DIR__ . '/partials/head.php';
    if (file_exists($headFile)) {
        include $headFile;
    } else {
        trigger_error("Head file not found: $headFile", E_USER_WARNING);
    }
    ?>
</head>

<body class="  ">
    <!-- loader Start -->
    <?php
    $loaderFile = __DIR__ . '/partials/loader-start.php';
    if (file_exists($loaderFile)) {
        include $loaderFile;
    } else {
        trigger_error("Loader file not found: $loaderFile", E_USER_WARNING);
    }
    ?>
    <!-- loader END -->

    <?php
    $asideFile = __DIR__ . '/partials/aside.php';
    if (file_exists($asideFile)) {
        include $asideFile;
    } else {
        trigger_error("Aside file not found: $asideFile", E_USER_WARNING);
    }

    ?>
    <main class="main-content">
        <?php
        $navBarFile = __DIR__ . '/partials/nav-bar.php';
        if (file_exists($navBarFile)) {
            include $navBarFile;
        } else {
            trigger_error("Navigation bar file not found: $navBarFile", E_USER_WARNING);
        }
        ?>
        <div class="conatiner-fluid content-inner mt-n5 py-0">
            <div class="row">
                <?php
                if (isset($content))
                    require_once($content);
                ?>
            </div>
        </div>

        <!-- Footer Section Start -->
        <?php
        $footerFile = __DIR__ . '/partials/footer.php';
        if (file_exists($footerFile)) {
            include $footerFile;
        } else {
            trigger_error("Footer file not found: $footerFile", E_USER_WARNING);
        }
        ?>
        <!-- Footer Section End -->
    </main>

    <?php
    $overlayFile = __DIR__ . '/partials/overlay.php';
    if (file_exists($overlayFile)) {
        include $overlayFile;
    } else {
        trigger_error("Overlay file not found: $overlayFile", E_USER_WARNING);
    }
    ?>

    <?php
    $scriptsFile = __DIR__ . '/partials/scripts.php';
    if (file_exists($scriptsFile)) {
        include $scriptsFile;
    } else {
        trigger_error("Scripts file not found: $headFile", E_USER_WARNING);
    }
    ?>

</body>

</html>