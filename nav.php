<?php
// nav.php - banner + meny + quote
$db = new SQLite3(__DIR__ . '/data.db');
$quoteRow = $db->querySingle("SELECT title, excerpt FROM articles WHERE category='Quotes' ORDER BY RANDOM() LIMIT 1", true);
$quoteSource = trim($quoteRow['title'] ?? '');
?>

<!-- Banner -->
<div class="banner" style="position:relative; margin-bottom:0.5rem; min-height:230px;">

  <!-- Meny (låst separat, påverkas inte av citatet) -->
  <div class="menu"
       style="position:absolute; left:2rem; top:1rem; width:680px;
              border-radius:12px; font-size:1.1rem; display:flex; gap:2rem; align-items:center;
              background:rgba(250,250,250,.2); padding:.6rem 1.2rem; box-shadow:var(--shadow); font-weight:bold;">
    <a href="index.php">کورپاڼه</a>
    <a href="weekly.php">ځانګړي مطالب</a>
    <a href="daily.php">لنډ مطالب</a>
    <a href="questions.php">سوال او ځواب</a>
    <a href="thematic.php">تماتیک لیکنې</a>
    <a href="admin.php">اداره</a>
  </div>

  <!-- Khan-bild + citat (låst block under menyn) -->
  <?php if ($quoteSource): ?>
    <div class="quote-box"
         style="
           position:absolute; left:2rem; top:4.5rem;
           display:flex; align-items:center; gap:10px;
           color:var(--ink); background:rgba(250,250,250,.2);
           border-radius:8px; padding:.6rem 1rem;
           box-shadow:0 6px 18px rgba(0,0,0,.2);
           width:680px;  /* 👈 låst bredd */
           max-width:100%;
	color: #99330C; line-height:1.6; flex:1; text-align:right; word-wrap:break-word;
         ">
      <a href="index.php" title="Home" style="flex-shrink:0;">
        <img src="images/icons/khan6.png" alt="Khan Logo"
             style="max-width:140px; height:auto; border-radius:6px; box-shadow:var(--shadow);">
      </a>
      <div style="font-size:1.1rem; line-height:1.6; flex:1; text-align:right; word-wrap:break-word;">
        <b>"<?php echo nl2br(htmlspecialchars($quoteSource)); ?>"</b>
      </div>
    </div>
  <?php endif; ?>

  <!-- Circle-loggan (låst till höger) -->
  <a href="index.php" title="Home" aria-hidden="true" style="pointer-events:none;">
    <img class="circle-logo" src="images/icons/circle-logo16.svg" alt="Circle Logo"
         style="
           position:absolute;
           right:-65px; top:-60px;
           width:350px; z-index:1; opacity:0.95;
         ">
  </a>
</div>
