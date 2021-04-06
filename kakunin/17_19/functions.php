<?php

// 接続処理を行う関数
function connectDb()
{
    try {
        return new PDO(DSN, USER, PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        echo 'システムエラーが発生しました';
        error_log($e->getMessage());
        exit;
    }
}

// エスケープ処理を行う関数
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function createPager($page, $total_count, $category_id)
{
    $html = "";
    if ($total_count > Post::PER_PAGE) {
        if ($category_id) {
            $c_href = "&category_id={$category_id}";
        } else {
            $c_href = "";
        }

        $max_page = ceil($total_count / Post::PER_PAGE);
        $prev = $page - 1;
        $next = min($page + 1, $max_page);

        $html = "<nav class=\"pager\">\n";
        $html .= "<div class=\"pagination\">\n";

        // PREVのリンク
        if ($page > 1) {
            $html .= "<a href=\"index.php?page=" . $prev . $c_href . "\" class=\"page-prev\">&lt; PREV</a>\n";
        }

        // 1のリンク
        if ($page > 2) {
            $html .= "<a href=\"index.php?page=1" . $c_href . "\">1</a>\n";
        }

        if ($page > 3) {
            $html .= "<span>...</span>\n";
        }

        // 1つ前のリンク
        if ($page > 1) {
            $html .= "<a href=\"index.php?page=" . $prev . $c_href . "\">" . $prev . "</a>\n";
        }

        // 現在のページのリンク
        $html .= "<a href=\"index.php?page=" . $page . $c_href . "\" class=\"current-page\">" . $page . "</a>\n";

        // 次のページのリンク
        if ($page < $max_page) {
            $html .= "<a href=\"index.php?page=" . $next . $c_href . "\">" . $next . "</a>\n";
        }

        if ($max_page - $page > 2) {
            $html .= "<span>...</span>\n";
        }

        // 最終ページのリンク
        if ($max_page - $page > 1) {
            $html .= "<a href=\"index.php?page=" . $max_page . $c_href . "\">" . $max_page . "</a>\n";
        }

        // NEXTのリンク
        if ($page < $max_page) {
            $html .= "<a href=\"index.php?page=" . $next . $c_href . "\" class=\"page-next\">NEXT &gt;</a>\n";
        }

        $html .= "</div>\n";
        $html .= "</nav>\n";
    }
    return $html;
}

function generateToken()
{
    if (empty($_SESSION['token'])) {
        $token = bin2hex(random_bytes(24));
        $_SESSION['token'] = $token;
    } else {
        $token = $_SESSION['token'];
    }
    return $token;
}

function redirectAlert($url, $alert, $post_data = [], $errors = [])
{
    $_SESSION['alert'] = $alert;
    $_SESSION['post_data'] = $post_data;
    $_SESSION['errors'] = $errors;
    header("Location: {$url}");
    exit;
}

function redirectNotice($url, $notice)
{
    $_SESSION['notice'] = $notice;
    header("Location: {$url}");
    exit;
}

function getAlert()
{
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
    return $alert;
}

function getNotice()
{
    $notice = $_SESSION['notice'];
    unset($_SESSION['notice']);
    return $notice;
}

function getErrors()
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;
}

function getPostData()
{
    $post_data = $_SESSION['post_data'];
    unset($_SESSION['post_data']);
    return $post_data;
}

function getCurrentUser()
{
    if (is_array($_SESSION['current_user'])) {
        return $_SESSION['current_user'];
    } else {
        return [];
    }
}

function createErrMsg($errors)
{
    $err_msg = "<div class=\"error-message-area\">\n";
    $err_msg .= "<ul>\n";

    foreach ((array)$errors as $error) {
        $err_msg .= "<li>" . h($error) . "</li>\n";
    }

    $err_msg .= "</ul>\n";
    $err_msg .= "</div>\n";

    return $err_msg;
}