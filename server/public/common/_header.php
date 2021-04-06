<header class="page-header header-wrapper">
    <h1 class="logo"><a href="/">NR BLOG</a></h1>
    <nav>
        <ul class="main-nav">
                <?php if ($current_user['id']) : ?>
                    <li class="nav-item">
                        <a href="/users/edit.php" class="nav-link">アカウント編集</a>
                    </li>
                    <li class="nav-item">
                        <a href="/users/log_out.php" class="nav-link">ログアウト</a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a href="/users/new.php" class="nav-link">アカウント登録</a>
                    </li>
                    <li class="nav-item">
                        <a href="/users/log_in.php" class="nav-link">ログイン</a>
                    </li>
                <?php endif; ?>
        </ul>
    </nav>
</header>
<hr class="header-under-line">