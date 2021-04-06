<!DOCTYPE html>
<html lang="ja">

<?php include __DIR__ . "/../common/_head.php"; ?>

<body>
    <?php include __DIR__ . "/../common/_header.php"; ?>

    <div class="wrapper">
        <?php include __DIR__ . "/_post.php"; ?>

        <form action="create.php" method="post" enctype="multipart/form-data">
            <?php include __DIR__ . "/_form.php"; ?>
            <div class="form-group">
                <input type="submit" class="btn" value="更新">
            </div>
        </form>
    </div>

    <?php include __DIR__ . "/../common/_footer.php"; ?>
</body>

</html>