<?php include(__DIR__ . "/includes/header.php"); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Include sidebar -->
        <?php if ($_SESSION["activeUser"]["role"] >= 2) : ?>
            <?php include("includes/sidebar.php"); ?>
            <div id="page-content" class="col-auto col-md-7">
                <div class="container text-center mt-5">
                    <img src="imgs/dino.png" alt="" srcset="">
                    <h3>Nada por aqui, ainda...</h3>
                </div>
            </div>
        <?php else : ?>
            <?php include_once(__DIR__ . "/includes/access-error.php"); ?>
        <?php endif; ?>
    </div>
</div>
<?php include(__DIR__ . "/includes/footer.php"); ?>