<?php
session_start();

// ======= Ange ditt admin-konto här ========
$ADMIN_USER = "akmalsafi";
$ADMIN_PASS = "Meena_2011";
// ==========================================

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "❌ Fel användarnamn eller lösenord";
    }
}
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container" style="max-width:400px;margin:5rem auto;text-align:center">
    <h2>🔐 Logga in</h2>
    <?php if ($error): ?><p style="color:red"><?php echo $error; ?></p><?php endif; ?>
    <form method="post" style="display:flex;flex-direction:column;gap:1rem;text-align:left">
      <label for="username">Användarnamn</label>
      <input type="text" id="username" name="username" required style="padding:.75rem;font-size:1rem">

      <label for="password">Lösenord</label>
      <input type="password" id="password" name="password" required style="padding:.75rem;font-size:1rem">

      <button type="submit" class="btn" style="padding:1rem;font-size:1.1rem;width:100%">Logga in</button>
    </form>
  </div>
</body>
</html>
