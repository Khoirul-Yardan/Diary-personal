<?php
// Menyambungkan ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diary_db";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$feedback = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update'])) {
    $entry = $_POST['diaryEntry'];

    // Hitung jumlah kata "senang", "sedih", dan lainnya
    $happy_count = substr_count(strtolower($entry), "senang");
    $sad_count = substr_count(strtolower($entry), "sedih");
    $stress_count = substr_count(strtolower($entry), "stres");
    $anxious_count = substr_count(strtolower($entry), "cemas");

    // Proses feedback berdasarkan jumlah kata
    if ($sad_count > $happy_count || $stress_count > $anxious_count) {
        $feedback = "Sepertinya kamu merasa tertekan. Ingat, waktu buruk tidak bertahan selamanya. Ambil napas dalam-dalam dan percayalah semuanya akan membaik.";
    } else if ($happy_count > $sad_count || $anxious_count == 0) {
        $feedback = "Senang mendengar perasaan positif! Terus nikmati momen-momen ini dan sebarkan energi positif.";
    } else {
        $feedback = "Terima kasih telah berbagi! Terus lakukan yang terbaik.";
    }

    // Menyimpan entri dan feedback ke dalam database
    $stmt = $conn->prepare("INSERT INTO diary_entries (entry, feedback) VALUES (?, ?)");
    $stmt->bind_param("ss", $entry, $feedback);
    $stmt->execute();
    $stmt->close();
}

// Mengambil semua entri dari database
$result = $conn->query("SELECT * FROM diary_entries ORDER BY created_at DESC");

// Menghapus entri jika ada
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM diary_entries WHERE id = $delete_id");
    header("Location: index.php"); // Refresh halaman setelah menghapus
}

// Mengedit entri
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result_edit = $conn->query("SELECT * FROM diary_entries WHERE id = $edit_id");
    $edit_entry = $result_edit->fetch_assoc();
}

if (isset($_POST['update'])) {
    $update_id = $_POST['update_id'];
    $updated_entry = $_POST['updated_diaryEntry'];
    
    // Hitung jumlah kata "senang", "sedih", "stres", "cemas" setelah entri diperbarui
    $happy_count = substr_count(strtolower($updated_entry), "senang");
    $sad_count = substr_count(strtolower($updated_entry), "sedih");
    $stress_count = substr_count(strtolower($updated_entry), "stres");
    $anxious_count = substr_count(strtolower($updated_entry), "cemas");

    // Tentukan umpan balik berdasarkan jumlah kata
    if ($sad_count > $happy_count || $stress_count > $anxious_count) {
        $feedback = "Sepertinya kamu merasa tertekan. Ingat, waktu buruk tidak bertahan selamanya. Ambil napas dalam-dalam dan percayalah semuanya akan membaik.";
    } else if ($happy_count > $sad_count || $anxious_count == 0) {
        $feedback = "Senang mendengar perasaan positif! Terus nikmati momen-momen ini dan sebarkan energi positif.";
    } else {
        $feedback = "Terima kasih telah berbagi! Terus lakukan yang terbaik.";
    }

    // Perbarui entri dan umpan balik di database
    $stmt = $conn->prepare("UPDATE diary_entries SET entry = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("ssi", $updated_entry, $feedback, $update_id);
    
    if ($stmt->execute()) {
        header("Location: index.php"); // Refresh halaman setelah memperbarui
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diary Interaktif</title>
    <style>
           body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            color: #333;
        }
        header {
            background: #4CAF50;
            color: white;
            text-align: center;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .entry-section {
    flex: 1;
    padding-left: 2rem;
    margin-bottom: 2rem;
    padding-right: 2rem; /* Menambahkan padding kanan untuk ruang lebih */
    padding-top: 1.5rem; /* Menambahkan padding atas agar tidak terlalu mepet */
    padding-bottom: 1.5rem; /* Menambahkan padding bawah agar lebih rapi */
}

textarea {
    width: 100%;
    height: 150px;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 1rem;
    font-size: 1rem;
    resize: none;
    margin-top: 1rem; /* Menambahkan margin atas agar tidak mepet dengan judul */
}

        .history-section {
            flex: 1;
            background: #e0f7fa;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 500px;
            overflow-y: auto;
            margin-right: 2rem;
        }
        .history-item {
            background: #fff;
            border-left: 4px solid #00acc1;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .history-item strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        textarea {
            width: 100%;
            height: 150px;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 1rem;
            font-size: 1rem;
            resize: none;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background: #45a049;
        }
        .feedback {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #e0f7fa;
            border-left: 4px solid #00acc1;
            border-radius: 4px;
        }
        .feedback strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        .action-buttons {
            margin-top: 1rem;
        }
        .action-buttons a {
            text-decoration: none;
            color: white;
            background: #f44336;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-right: 1rem;
        }
        .action-buttons a.edit {
            background: #FF9800;
        }
        .close-edit-btn {
            background: #ccc;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            cursor: pointer;
        }
        .close-edit-btn:hover {
            background: #999;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 1rem;
            }
            .entry-section {
                padding-left: 0;
                margin-bottom: 2rem;
            }
            .history-section {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Diary Interaktif</h1>
        <p>Tulis pikiranmu dan terima umpan balik yang dipersonalisasi.</p>
    </header>
    <div class="container">
        <!-- Sejarah -->
        <div class="history-section">
            <h3>Sejarah Entri</h3>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="history-item">
                        <strong>Tanggal: <?= date("d F Y", strtotime($row['created_at'])); ?></strong>
                        <p><strong>Entri:</strong> <?= nl2br(htmlspecialchars($row['entry'])); ?></p>
                        <p><strong>Umpan Balik:</strong> <?= nl2br(htmlspecialchars($row['feedback'])); ?></p>
                        <div class="action-buttons">
                            <a href="index.php?delete=<?= $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus entri ini?');">Hapus</a>
                            <a href="index.php?edit=<?= $row['id']; ?>" class="edit">Edit</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Belum ada entri sebelumnya.</p>
            <?php endif; ?>
        </div>

        <!-- Entri Form -->
        <div class="entry-section">
            <h2>Entri Hari Ini</h2>
            <form action="index.php" method="POST">
                <textarea name="diaryEntry" id="diaryEntry" placeholder="Tulis tentang hari Anda..." required></textarea>
                <button type="submit">Kirim</button>
            </form>
            <?php if ($feedback): ?>
                <div class="feedback">
                    <strong>Umpan Balik:</strong>
                    <p><?= $feedback; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($edit_entry)): ?>
        <!-- Form Edit Entri -->
        <div class="container">
            <div class="entry-section">
                <h2>Edit Entri</h2>
                <form action="index.php" method="POST">
                    <textarea name="updated_diaryEntry" required><?= htmlspecialchars($edit_entry['entry']); ?></textarea>
                    <input type="hidden" name="update_id" value="<?= $edit_entry['id']; ?>">
                    <button type="submit" name="update">Perbarui Entri</button>
                </form>
                <button class="close-edit-btn" onclick="window.location.href='index.php'">Tutup Edit</button>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
