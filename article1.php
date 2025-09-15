<?php
$db = new SQLite3(__DIR__ . '/data.db');
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM articles WHERE id=:id");
$stmt->bindValue(':id',$id,SQLITE3_INTEGER);
$res = $stmt->execute();
$article = $res->fetchArray(SQLITE3_ASSOC);

if (!$article) {
    echo "❌ Artikel hittades inte.";
    exit;
}

// Funktion för att hämta slumpade artiklar
function getRandomArticles($db, $category, $excludeId, $limit = 3) {
    $stmt = $db->prepare("SELECT id, title, excerpt, image, date 
                          FROM articles 
                          WHERE category=:cat AND id != :id 
                          ORDER BY RANDOM() 
                          LIMIT :limit");
    $stmt->bindValue(':cat', $category, SQLITE3_TEXT);
    $stmt->bindValue(':id', $excludeId, SQLITE3_INTEGER);
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    return $stmt->execute();
}
?>

<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($article['title']); ?></title>
<link rel="stylesheet" href="styles.css">
<style>
  .article-layout {
    display: flex;
    flex-direction: row;
    gap: 2rem;
    align-items: flex-start;
    margin-top: 2rem;
  }
  .article-text { flex: 2; }
  .article-image { flex: 1; }
  .article-image img { max-width: 100%; border-radius: 10px; }

  @media (max-width: 768px) {
    .article-layout { flex-direction: column; }
    .article-image { margin-top: 1rem; }
  }

  /* Relaterade artiklar */
  .related-section { margin-top: 3rem; }
  .related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(250px,1fr));
    gap: 1rem;
  }
  .related-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 0.75rem;
    background: #fafafa;
  }
  .related-card img {
    width: 100%;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
  }
  .related-card h4 {
    margin: .5rem 0 .25rem;
    font-size: 1.1rem;
  }
  .related-card p {
    font-size: .9rem;
    color: #444;
  }
</style>
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">

  <h1><?php echo htmlspecialchars($article['title']); ?></h1>
  <p class="badge"><?php echo htmlspecialchars($article['date']); ?> 



</p>

  <div class="article-layout">
    <div class="article-text">
      <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
    </div>
    <?php if ($article['image']): ?>
    <div class="article-image">
      <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="">
    </div>
    <?php endif; ?>
  </div>

  <p style="text-align:center;margin-top:1rem">
    <a class="btn" href="index.php">⬅️ بېرته کور ته</a>
  </p>

  <!-- Relaterade artiklar -->
  <div class="related-section">
    <h2>📖  نورې لنډې مطالب  </h2>
    <div class="related-grid">
      <?php
      $res = getRandomArticles($db, "Dagens läsning", $article['id'], 3);
      while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
          $img = $row['image'] ?: "assets/fallback.jpg";
          echo "<div class='related-card'>";
          echo "<img src='".htmlspecialchars($img)."' alt=''>";
          echo "<h4><a href='article.php?id=".$row['id']."' style='text-decoration:none;color:inherit'>".htmlspecialchars($row['title'])."</a></h4>";
          echo "<p class='badge'>".$row['date']."</p>";
          echo "<p>".htmlspecialchars($row['excerpt'])."</p>";
          echo "</div>";
      }
      ?>
    </div>
  </div>

  <div class="related-section">
    <h2>📚 نورې ځانګړي مطالب</h2>
    <div class="related-grid">
      <?php
      $res = getRandomArticles($db, "Veckans läsning", $article['id'], 3);
      while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
          $img = $row['image'] ?: "assets/fallback.jpg";
          echo "<div class='related-card'>";
          echo "<img src='".htmlspecialchars($img)."' alt=''>";
          echo "<h4><a href='article.php?id=".$row['id']."' style='text-decoration:none;color:inherit'>".htmlspecialchars($row['title'])."</a></h4>";
          echo "<p class='badge'>".$row['date']."</p>";
          echo "<p>".htmlspecialchars($row['excerpt'])."</p>";
          echo "</div>";
      }
      ?>
    </div>
  </div>

</main>

<b><footer>په دې ویب پاڼه کې مقالې د مولانا وحیدالدین خان لخوا لیکل شوي اوپه آزاده توګه د خپریدو اجازه لري.<p> د نورو معلوماتو لپاره مهرباني وکړئ www.cpsglobal.org ته مراجعه وکړئ.</footer></b>
</body>
</html>
