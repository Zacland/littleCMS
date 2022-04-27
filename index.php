<?php

require("config.php");
$action = isset($_GET['action']) ? $_GET['action'] : "";

switch ($action)
{
    case 'archive':
        archive();
        break;
    case 'viewArticle':
        viewArticle();
        break;
    default:
        homepage();
}


function archive()
{
    $article = new Article();
    $results = array();
    $data = $article->getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Archive | Widget News";
    require(TEMPLATE_PATH . "/archive.php");
}

function viewArticle()
{
    $article = new Article();
    if (!isset($_GET["articleId"]) || !$_GET["articleId"])
    {
        homepage();
        return;
    }

    $results = array();
    $results['article'] = $article->getById((int)$_GET["articleId"]);
    $results['pageTitle'] = $results['article']->title . " | Widget News";
    require(TEMPLATE_PATH . "/viewArticle.php");
}

function homepage()
{
    $article = new Article();
    $results = array();
    $data = $article->getList(HOMEPAGE_NUM_ARTICLES);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Widget News";
    require(TEMPLATE_PATH . "/homepage.php");
}
