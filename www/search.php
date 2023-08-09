<?php
if(isset($_GET['q'])) {
  $query = $_GET['q'];
} else {
  exit(1);
}
if(isset($_GET['n']) && is_numeric($_GET['n'])) {
  $num = intval($_GET['n']);
} else {
  $num = 0;
}
$numStr = strval($num + 1);

$db = new SQLite3('../db.sqlite');
$stmt = $db->prepare('SELECT * FROM post WHERE text LIKE :query ORDER BY date DESC LIMIT :start, 21');
$stmt->bindValue('query', '%' . $query . '%', SQLITE3_TEXT);
$offset = $num * 20;
$stmt->bindValue('start', $offset, SQLITE3_INTEGER);
$results = $stmt->execute();
$resultHtml = "";
$i = 0;
while($row = $results->fetchArray()){
  $i++;
  if ($i > 20){
    break;
  }
  $href = preg_replace('~at://(.+?)/app.bsky.feed.post/(.+?)~',
    'https://bsky.app/profile/${1}/post/${2}',
    $row["uri"]);
  $resultHtml .= '        <a href="' . 
    $href . 
    '" class="list-group-item list-group-item-action d-flex gap-3 py-3">' . 
    $row["text"] .
    '<span class="d-block small opacity-50">' .
    date('Y/m/d H:m:d', $row["date"]) .
    '</span></a></p>';
}
$stmt->close();
$db->close();
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