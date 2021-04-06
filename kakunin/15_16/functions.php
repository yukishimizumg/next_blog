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
        $prev = max($page - 1, 1);
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