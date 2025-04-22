<?php
// Static table list — later can be fetched from DB
$tables = ['accts_sub', 'accts_chg', 'accts_dis', 'accts_dor', 'accts_gcr','accts_pay','accts_rev','mtd'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>CSV Uploader</title>
</head>
<body>
  <h2>Upload CSV</h2>
  <form action="upload_handler.php" method="POST" enctype="multipart/form-data">
    <label>Select Table:</label>
    <select name="target_table" required>
      <?php foreach ($tables as $table): ?>
        <option value="<?= $table ?>"><?= $table ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Choose CSV File:</label>
    <input type="file" name="csv_file" accept=".csv" required><br><br>

    <button type="submit">Upload</button>
  </form>
</body>
</html>
