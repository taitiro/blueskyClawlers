<?php
require_once __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
if(!isset($_GET['q'])) {
  exit(1);
}
$query = trim(preg_quote($_GET['q']));
$regexStr = '^(?=.*' . preg_replace(['/ /', '/　/'], ')(?=.*', $query) . ')';
if(isset($_GET['n']) && is_numeric($_GET['n'])) {
  $num = intval($_GET['n']);
} else {
  $num = 0;
}
$numStr = strval($num + 1);
$resultHtml = '';
$i = 0;

$apiVersion = new ServerApi(ServerApi::V1);

try {
  $client = new MongoDB\Client($_ENV['DB_URI'], [], ['serverApi' => $apiVersion]);
  $collection = $client->db0->post;
  $cursor = $collection->find(
    ['text' => new MongoDB\BSON\Regex($regexStr, 'i')],
    [
        'limit' => 21,
        'sort' => ['date' => -1],
        'skip' => $num * 20,
    ]
  );
  $resultHtml = "";
  foreach ($cursor as $document) {
    $i++;
    if ($i > 20){
      break;
    }
    $href = preg_replace('~at://(.+?)/app.bsky.feed.post/(.+?)~',
    'https://bsky.app/profile/${1}/post/${2}',
    $document['uri']);
    $resultHtml .= '        <a href="' . 
      $href . 
      '" class="list-group-item list-group-item-action d-flex gap-3 py-3">' . 
      $document['text'] .
      '<span class="d-block small opacity-50">' .
      date('Y/m/d H:m:d', $document['date']) .
      '</span></a></p>';
 }
} catch (Exception $e) {
  //NOP
}
$last = '<div class="alert alert-dark" role="alert">検索結果は以上です</div>';
if($i > 20){
  $last = '<input type="submit" value="次へ" class="btn btn-primary btn-md px-4 gap-3">';
}
echo <<<EOT
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>検索結果</title>
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <article>
      <h1 class="display-5 my-5 fw-bold text-body-emphasis text-center">検索結果</h1>
      <form action="search.php" method="GET">
        <input type="hidden" name="q" value ="{$query}">
        <input type="hidden" name="n" value ="{$numStr}">
        <div class="list-group">

EOT;
echo $resultHtml;
echo <<<EOT
        </div>
        {$last}
      </form>
    </article>
    <footer class="px-4 my-5 text-center">
    <p><a href="./">TOPに戻る</a></p>
    <p>運営者：<a href="https://www.darakeru.com/amamako.html">amamako</a>
      （<a href="https://bsky.app/profile/darakeru.com">Blueskyアカウント</a>）</p>
    </footer>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
EOT;