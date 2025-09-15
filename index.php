<?php
$db = new SQLite3(__DIR__ . '/data.db');

function renderArticles($db, $category, $limit = 3) {
    $limit = (int)$limit;
    $sql = "
        SELECT id, title, excerpt, image, date
        FROM articles
        WHERE TRIM(category) = :category
        ORDER BY date DESC
        LIMIT $limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $res = $stmt->execute();

    echo '<div class="grid">';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $img = trim($row['image'] ?? '') !== '' ? $row['image'] : 'assets/fallback.jpg';

        $excerpt = strip_tags($row['excerpt']);
        $excerpt = htmlspecialchars($excerpt);
        if (mb_strlen($excerpt) > 80) {
            $excerpt = mb_strimwidth($excerpt, 0, 80, "…", "UTF-8");
        }

        echo '<div class="card">';
        echo '<img src="'.htmlspecialchars($img).'" alt="" class="article-cover" loading="lazy">';
        echo '<h3 class="article-title"><a href="article.php?id='.$row['id'].'">'.htmlspecialchars($row['title']).'</a></h3>';
        echo '<p class="badge">'.htmlspecialchars($row['date']).'</p>';
        echo '<p>'.$excerpt.'</p>';
        echo '</div>';
    }
    echo '</div>';
}

$latestThematic = $db->querySingle("SELECT * FROM articles WHERE category='Tematisk läsning' ORDER BY date DESC LIMIT 1", true);
?>

<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>د سولې او روحانیت کتابتون – د مولانا وحیدالدین خان لیکنې</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'nav.php'; ?>

<main class="container">

  <?php if ($latestThematic): ?>
  <section class="featured-thematic">
    <img src="<?php echo htmlspecialchars($latestThematic['image'] ?: 'assets/fallback.jpg'); ?>" alt="">
    <div class="info">
      <h2><a href="thematicarticles.php?id=<?php echo $latestThematic['id']; ?>">🌟 <?php echo htmlspecialchars($latestThematic['title']); ?></a></h2>
      <p class="date"><?php echo htmlspecialchars($latestThematic['date']); ?></p>
      <p>
        <?php 
          $excerpt = strip_tags($latestThematic['excerpt']);
          echo htmlspecialchars(mb_strimwidth($excerpt, 0, 120, "…", "UTF-8")); 
        ?>
      </p>
    </div>
  </section>
  <?php endif; ?>

  <section>
    <a href="daily.php"><h2>📖 تازه مطالب</h2></a>
    <?php renderArticles($db, 'Dagens läsning', 6); ?>
  </section>

  <section>
    <h2>📚 ځانګړي مطالب</h2>
    <?php renderArticles($db, 'Veckans läsning', 6); ?>
  </section>

  <section>
    <h2>الهامي ویناوې</h2>
    <?php renderArticles($db, 'Dagens inspiration', 6); ?>
  </section>

</main>


<?php include 'footer.php'; renderFooter(); ?>

</body>
</html>
