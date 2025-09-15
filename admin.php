<?php
session_start();

// Skydda admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3(__DIR__ . '/data.db');

// Säkerställ att tabellen existerar
$db->exec("CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    excerpt TEXT,
    content TEXT,
    category TEXT,
    image TEXT,
    date TEXT DEFAULT CURRENT_TIMESTAMP
)");

// ---- LÄGG TILL ----
if (isset($_POST['add'])) {
    $title   = $_POST['title'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $category= $_POST['category'] ?? '';

    // Hantera bilduppladdning
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName   = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($_FILES['image']['name']));
        $targetFile = $targetDir . $fileName;
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $image = "uploads/" . $fileName;
        }
    }

    $stmt = $db->prepare("INSERT INTO articles (title, excerpt, content, category, image) 
                          VALUES (:title, :excerpt, :content, :category, :image)");
    $stmt->bindValue(':title',   $title,   SQLITE3_TEXT);
    $stmt->bindValue(':excerpt', $excerpt, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':category',$category,SQLITE3_TEXT);
    $stmt->bindValue(':image',   $image,   SQLITE3_TEXT);
    $stmt->execute();

    // Redirect för att undvika dubbla POST
    header("Location: admin.php");
    exit;
}

// ---- TA BORT ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Ta bort eventuell bildfil från disk (valfritt)
    $row = $db->querySingle("SELECT image FROM articles WHERE id=$id", true);
    if ($row && !empty($row['image']) && file_exists(__DIR__ . '/' . $row['image'])) {
        @unlink(__DIR__ . '/' . $row['image']);
    }

    $db->exec("DELETE FROM articles WHERE id=$id");
    header("Location: admin.php");
    exit;
}

// ---- UPPDATERA (REDIGERA) ----
if (isset($_POST['update'])) {
    $id      = (int)($_POST['id'] ?? 0);
    $title   = $_POST['title'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $category= $_POST['category'] ?? '';

    // Behåll befintlig bild om ingen ny laddas upp
    $image = $_POST['current_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName   = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($_FILES['image']['name']));
        $targetFile = $targetDir . $fileName;
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $image = "uploads/" . $fileName;
        }
    }

    $stmt = $db->prepare("UPDATE articles 
                          SET title=:title, excerpt=:excerpt, content=:content, category=:category, image=:image 
                          WHERE id=:id");
    $stmt->bindValue(':title',    $title,    SQLITE3_TEXT);
    $stmt->bindValue(':excerpt',  $excerpt,  SQLITE3_TEXT);
    $stmt->bindValue(':content',  $content,  SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':image',    $image,    SQLITE3_TEXT);
    $stmt->bindValue(':id',       $id,       SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>Adminpanel</title>
  <link rel="stylesheet" href="styles.css">

  <!-- TinyMCE (no-api-key för test: byt till din egen nyckel i produktion) -->
  <script src="https://cdn.tiny.cloud/1/autczrpyzfk1nlrgo7n3f4g3buxhojq9wwl77qpj7h5tgoym/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

  <script>
  // Initiera TinyMCE för både excerpt (sammanfattning) och content (artikeltext)
  // - excerpt får större, fetare stil i editorn
  // - content får mer utrymme och normal textstorlek
  document.addEventListener('DOMContentLoaded', function () {
    // Gemensamma inställningar
    const commonSettings = {
      menubar: false,
      branding: false,
      plugins: 'lists link image table code paste media',
      toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code',
      forced_root_block: 'p',
      paste_as_text: false,
      elementpath: false,
      relative_urls: false,
      remove_script_host: true,
      convert_urls: true,
      // säkerställ att innehållet skickas tillbaka till <textarea>
      setup: function (editor) {
        editor.on('init change input undo redo', function () {
          editor.save();
        });
      }
    };

    // Excerpt: fetare och något större editor (passar som intro)
    tinymce.init(Object.assign({}, commonSettings, {
      selector: 'textarea#excerpt',
      height: 220,
      content_style: "body { font-size: 16px; font-weight: 700; line-height:1.6; font-family: Noto Naskh Arabic, serif; }",
      toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat'
    }));

    // Content: full artikeltext, större arbetsyta
    tinymce.init(Object.assign({}, commonSettings, {
      selector: 'textarea#content',
      height: 420,
      content_style: "body { font-size: 16px; line-height:1.8; font-family: Noto Naskh Arabic, serif; }",
      toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | outdent indent | link image media | table | code | removeformat'
    }));

    // För säkerhets skull: på formulärsubmit, trigga save för alla editors
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form){
      form.addEventListener('submit', function(){
        if (typeof tinyMCE !== 'undefined') {
          tinyMCE.triggerSave();
        }
      }, {passive:true});
    });
  });
  </script>

  <style>
    /* Små läsbarhetsjusteringar för adminfälten */
    form.admin-form input[type="text"],
    form.admin-form select,
    form.admin-form textarea {
      font-size: 1rem;
    }
    .btn { cursor: pointer; }
  </style>
</head>
<body class="container">

<h1>Adminpanel</h1>

<?php
// ===== REDIGERINGS-LÄGE =====
if (isset($_GET['edit'])):
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM articles WHERE id=:id");
    $stmt->bindValue(':id', $editId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $article = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

    if ($article):
?>
  <h2>✏️ Redigera artikel</h2>
  <form class="admin-form" method="post" enctype="multipart/form-data" style="max-width:900px;margin:auto;display:flex;flex-direction:column;gap:1rem">
    <input type="hidden" name="id" value="<?php echo (int)$article['id']; ?>">
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($article['image'] ?? ''); ?>">

    <label for="title">Titel</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>"
           style="padding:.75rem;font-size:1.1rem;width:100%;border:1px solid #ccc;border-radius:8px">

    <label for="excerpt">Sammanfattning</label>
    <textarea id="excerpt" name="excerpt" rows="4" style="width:100%;"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>

    <label for="content">Artikeltext</label>
    <textarea id="content" name="content" rows="12" style="width:100%;"><?php echo htmlspecialchars($article['content']); ?></textarea>

    <label for="category">Kategori</label>
    <select id="category" name="category" style="padding:.6rem;border-radius:6px">
      <option value="Dagens läsning"    <?php if(($article['category']??'')==="Dagens läsning") echo "selected"; ?>>Dagens läsning</option>
      <option value="Veckans läsning"   <?php if(($article['category']??'')==="Veckans läsning") echo "selected"; ?>>Veckans läsning</option>
      <option value="Dagens inspiration"<?php if(($article['category']??'')==="Dagens inspiration") echo "selected"; ?>>Dagens inspiration</option>
      <option value="Tematisk läsning"  <?php if(($article['category']??'')==="Tematisk läsning") echo "selected"; ?>>Tematisk läsning</option>
      <option value="Q&A"               <?php if(($article['category']??'')==="Q&A") echo "selected"; ?>>Q&A</option>
      <option value="Quotes"            <?php if(($article['category']??'')==="Quotes") echo "selected"; ?>>Quotes</option>
    </select>

    <label for="image">Byt bild (valfritt)</label>
    <input type="file" id="image" name="image" accept="image/*" style="padding:.5rem">
    <?php if (!empty($article['image'])): ?>
      <p>Nuvarande bild:<br><img src="<?php echo htmlspecialchars($article['image']); ?>" style="max-width:240px;border-radius:6px;margin-top:5px"></p>
    <?php endif; ?>

    <button type="submit" name="update" class="btn" style="padding:1rem;font-size:1.05rem;width:100%">Uppdatera artikel</button>
  </form>

  <p style="margin-top:1rem"><a href="admin.php">⬅️ Tillbaka</a></p>

<?php
    else:
        echo "<p>❌ Artikel hittades inte.</p><p><a href='admin.php'>Tillbaka</a></p>";
    endif;

// ===== STANDARD-LÄGE (Lägg till + Lista) =====
else:
?>

<h2>➕ Lägg till ny artikel</h2>
<form class="admin-form" method="post" enctype="multipart/form-data" style="max-width:900px;margin:auto;display:flex;flex-direction:column;gap:1rem">
  <label for="title">Titel</label>
  <input type="text" id="title" name="title" style="padding:.75rem;font-size:1.1rem;width:100%;border:1px solid #ccc;border-radius:8px">

  <label for="excerpt">Sammanfattning</label>
  <textarea id="excerpt" name="excerpt" rows="4" style="width:100%;"></textarea>

  <label for="content">Artikeltext</label>
  <textarea id="content" name="content" rows="12" style="width:100%;"></textarea>

  <label for="category">Kategori</label>
  <select id="category" name="category" style="padding:.6rem;border-radius:6px">
    <option value="Dagens läsning">Dagens läsning</option>
    <option value="Veckans läsning">Veckans läsning</option>
    <option value="Dagens inspiration">Dagens inspiration</option>
    <option value="Tematisk läsning">Tematisk läsning</option>
    <option value="Q&A">Q&A</option>
    <option value="Quotes">Quotes</option>
  </select>

  <label for="image">Ladda upp bild</label>
  <input type="file" id="image" name="image" accept="image/*" style="padding:.5rem">

  <button type="submit" name="add" class="btn" style="padding:1rem;font-size:1.05rem;width:100%">Spara artikel</button>
</form>

<hr>

<h2>📑 Befintliga artiklar</h2>

<?php
// Pagination-inställningar
$limit = 50;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

// Sortering (valfritt)
$allowedSort = ['id','category','date'];
$sort = $_GET['sort'] ?? 'date';
if (!in_array($sort, $allowedSort)) $sort = 'date';
$dir = $_GET['dir'] ?? 'DESC';
$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
$nextDir = $dir === 'ASC' ? 'DESC' : 'ASC';

// Hämta artiklar med LIMIT/OFFSET
$results = $db->query("SELECT * FROM articles ORDER BY $sort $dir LIMIT $limit OFFSET $offset");
?>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%;margin-top:1rem;border-collapse:collapse">
  <tr>
    <th><a href="?sort=id&dir=<?php echo ($sort==='id' ? $nextDir : 'ASC'); ?>">ID</a></th>
    <th>Titel</th>
    <th><a href="?sort=category&dir=<?php echo ($sort==='category' ? $nextDir : 'ASC'); ?>">Kategori</a></th>
    <th><a href="?sort=date&dir=<?php echo ($sort==='date' ? $nextDir : 'ASC'); ?>">Datum</a></th>
    <th>Bild</th>
    <th>Åtgärder</th>
  </tr>
<?php
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>".(int)$row['id']."</td>";
    echo "<td>".htmlspecialchars(strip_tags($row['title']))."</td>";
    echo "<td>".htmlspecialchars($row['category'])."</td>";
    echo "<td>".htmlspecialchars($row['date'])."</td>";
    echo "<td>".(!empty($row['image']) ? "<img src='".htmlspecialchars($row['image'])."' width='80' style='border-radius:6px'>" : "-")."</td>";
    echo "<td>
            <a href='admin.php?edit=".$row['id']."'>✏️ Redigera</a> | 
            <a href='admin.php?delete=".$row['id']."' onclick=\"return confirm('Är du säker?')\">🗑️ Ta bort</a>
          </td>";
    echo "</tr>";
}
?>
</table>

<?php
// Pagination-länkar
$total = $db->querySingle("SELECT COUNT(*) FROM articles");
$pages = max(1, ceil($total / $limit));
?>
<div style="margin-top:1rem; text-align:center;">
  <?php if ($page > 1): ?>
    <a class="btn" href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>&dir=<?php echo $dir; ?>">⬅️ Föregående</a>
  <?php endif; ?>

  <span style="margin:0 1rem;">Sida <?php echo $page; ?> av <?php echo $pages; ?></span>

  <?php if ($page < $pages): ?>
    <a class="btn" href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>&dir=<?php echo $dir; ?>">Nästa ➡️</a>
  <?php endif; ?>
</div>

<p style="margin-top:1rem"><a href="logout.php">Logga ut</a></p>

<?php endif; // slut på redigerings-/standard-läge ?>
</body>
</html>
