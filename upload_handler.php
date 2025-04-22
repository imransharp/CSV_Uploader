<?php
set_time_limit(0); 
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = $_POST['target_table'];
    $file = $_FILES['csv_file'];

    // Check file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload error");
    }

    $filename = basename($file['name']);
    $target_path = "uploads/" . $filename;

    // Move to uploads folder
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        die("Failed to move uploaded file");
    }

    
    // Backup the table
    $backup_table = $table . "_backup_" . date("Ymd_His");
    $conn->query("CREATE TABLE $backup_table AS SELECT * FROM $table");

    // 🧹 Clear the existing data
    $conn->query("TRUNCATE TABLE $table");
     
    // Start DB transaction
    $conn->begin_transaction();

    try 
    {
        $handle = fopen($target_path, "r");
        if (!$handle) throw new Exception("Cannot open CSV");

        fgetcsv($handle); // Skip header

        $batchSize = 1000;
        $rowCount = 0;
        $batch = [];

        while (($row = fgetcsv($handle)) !== FALSE) {
            $escaped = array_map(fn($val) => "'" . $conn->real_escape_string($val) . "'", $row);
            $batch[] = "(" . implode(",", $escaped) . ")";
            $rowCount++;

            if ($rowCount % $batchSize === 0) {
                $sql = "INSERT INTO $table VALUES " . implode(",", $batch);
                $conn->query($sql);
                $batch = []; // reset
            }
        }

        // Insert remaining rows
        if (count($batch) > 0) {
            $sql = "INSERT INTO $table VALUES " . implode(",", $batch);
            $conn->query($sql);
        }

        fclose($handle);
        $conn->commit();

        echo "✅ Upload complete: $rowCount rows inserted.";
            
    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ Upload failed: " . $e->getMessage();
    }       

    $conn->close();
} else {
    echo "Invalid request";
}
?>
