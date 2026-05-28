<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../src/auth/login.php');
    exit();
}

require_once '../../config.php';

$result = $conn->query("SELECT * FROM books ORDER BY title");

// Get distinct categories for filter dropdown
$cat_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
$categories = [];
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        if ($row['category']) $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library Management System</title>
    <style>
        :root {
            --primary: #a749ff;
            --primary-dark: #8a3ad9;
            --accent: #ff8a3d;
            --text: #1f1230;
            --muted: #6b5c80;
            --border: #e9def8;
            --shadow: 0 2px 8px rgba(10,8,24,0.18);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(167, 73, 255, 0.30), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255, 138, 61, 0.28), transparent 30%),
                linear-gradient(180deg, #1a1030 0%, #130e28 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text);
        }

        .navbar {
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand { font-size: 1.5em; font-weight: bold; color: var(--primary); }

        .navbar-menu { display: flex; gap: 20px; align-items: center; }

        .navbar-menu a {
            text-decoration: none;
            color: var(--text);
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .navbar-menu a:hover { background: #f0f0f0; color: var(--primary); }

        .logout-btn { background: var(--primary) !important; color: white !important; }
        .logout-btn:hover { background: var(--primary-dark) !important; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        .page-title {
            background: rgba(255,255,255,0.96);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .page-title h1 { font-size: 2em; margin-bottom: 8px; }
        .page-title p { color: var(--muted); }

        /* ── Search & Filter Bar ── */
        .search-bar {
            background: rgba(255,255,255,0.96);
            padding: 20px 24px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .search-bar .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 160px;
        }

        .search-bar .field-group.search-input-wrap { flex: 2; min-width: 220px; }

        .search-bar label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .search-bar input[type="text"],
        .search-bar select {
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text);
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            width: 100%;
        }

        .search-bar input[type="text"]:focus,
        .search-bar select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(167,73,255,0.12);
        }

        .search-bar .clear-btn {
            padding: 10px 18px;
            background: #f4f0fa;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .search-bar .clear-btn:hover { background: var(--border); color: var(--text); }

        .results-info {
            margin-bottom: 16px;
            color: rgba(255,255,255,0.75);
            font-size: 0.93em;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .book-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .book-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.15); }

        .book-title { font-size: 1.2em; font-weight: bold; margin-bottom: 10px; color: var(--primary); }

        .book-info { margin: 8px 0; color: var(--muted); font-size: 0.95em; }
        .book-info strong { color: var(--text); }

        .availability {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: bold;
            margin-top: 12px;
            text-align: center;
        }
        .available { background: #d4edda; color: #155724; }
        .unavailable { background: #f8d7da; color: #721c24; }

        .back-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .back-btn:hover { background: var(--primary-dark); transform: translateX(-3px); }

        .no-books, .no-results {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            color: var(--muted);
            grid-column: 1 / -1;
        }
        .no-results { display: none; }
        .no-results h3 { margin-bottom: 8px; color: var(--text); }

        @media (max-width: 640px) {
            .search-bar { flex-direction: column; }
            .search-bar .field-group, .search-bar .field-group.search-input-wrap { min-width: 100%; flex: none; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">📚 Library Management System</div>
        <div class="navbar-menu">
            <a href="../../index.php">Back to Dashboard</a>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="../../src/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <a href="../../index.php" class="back-btn">← Back to Dashboard</a>

        <div class="page-title">
            <h1>📖 Browse Library Books</h1>
            <p>Explore all books available in our library</p>
        </div>

        <!-- Search & Filter Bar -->
        <div class="search-bar">
            <div class="field-group search-input-wrap">
                <label for="searchInput">Search</label>
                <input type="text" id="searchInput" placeholder="Type to search books…" autocomplete="off">
            </div>

            <div class="field-group">
                <label for="filterField">Search by</label>
                <select id="filterField">
                    <option value="all">All Fields</option>
                    <option value="title">Title</option>
                    <option value="author">Author</option>
                    <option value="category">Category</option>
                </select>
            </div>

            <div class="field-group">
                <label for="filterCategory">Category</label>
                <select id="filterCategory">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars(strtolower($cat)) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <label for="filterAvail">Availability</label>
                <select id="filterAvail">
                    <option value="all">All</option>
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>
            </div>

            <button class="clear-btn" onclick="clearFilters()">✕ Clear</button>
        </div>

        <div class="results-info" id="resultsInfo"></div>

        <?php if ($result && $result->num_rows > 0): ?>
        <div class="books-grid" id="booksGrid">
            <?php while ($book = $result->fetch_assoc()): ?>
            <div class="book-card"
                 data-title="<?= htmlspecialchars(strtolower($book['title'])) ?>"
                 data-author="<?= htmlspecialchars(strtolower($book['author'])) ?>"
                 data-category="<?= htmlspecialchars(strtolower($book['category'])) ?>"
                 data-available="<?= $book['available'] ? '1' : '0' ?>">
                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                <div class="book-info"><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></div>
                <div class="book-info"><strong>Category:</strong> <?= htmlspecialchars($book['category']) ?></div>
                <div class="availability <?= $book['available'] ? 'available' : 'unavailable' ?>">
                    <?= $book['available'] ? '✓ Available' : '✗ Not Available' ?>
                </div>
            </div>
            <?php endwhile; ?>

            <div class="no-results" id="noResults">
                <h3>No books found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="no-books">
            <h2>No books found</h2>
            <p>There are currently no books in the library.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const cards = Array.from(document.querySelectorAll('.book-card:not(#noResults)'));
        const noResults = document.getElementById('noResults');
        const resultsInfo = document.getElementById('resultsInfo');

        function filterBooks() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const field = document.getElementById('filterField').value;
            const category = document.getElementById('filterCategory').value;
            const avail = document.getElementById('filterAvail').value;

            let visible = 0;

            cards.forEach(card => {
                const title = card.dataset.title;
                const author = card.dataset.author;
                const cardCategory = card.dataset.category;
                const cardAvail = card.dataset.available;

                // Text match
                let matchText = true;
                if (query) {
                    if (field === 'all') {
                        matchText = title.includes(query) || author.includes(query) || cardCategory.includes(query);
                    } else {
                        matchText = card.dataset[field].includes(query);
                    }
                }

                // Category match
                const matchCategory = category === 'all' || cardCategory === category;

                // Availability match
                const matchAvail = avail === 'all' || cardAvail === avail;

                const show = matchText && matchCategory && matchAvail;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';

            const total = cards.length;
            if (query || category !== 'all' || avail !== 'all') {
                resultsInfo.textContent = `Showing ${visible} of ${total} book${total !== 1 ? 's' : ''}`;
            } else {
                resultsInfo.textContent = `${total} book${total !== 1 ? 's' : ''} in the library`;
            }
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterField').value = 'all';
            document.getElementById('filterCategory').value = 'all';
            document.getElementById('filterAvail').value = 'all';
            filterBooks();
        }

        document.getElementById('searchInput').addEventListener('input', filterBooks);
        document.getElementById('filterField').addEventListener('change', filterBooks);
        document.getElementById('filterCategory').addEventListener('change', filterBooks);
        document.getElementById('filterAvail').addEventListener('change', filterBooks);

        // Initialize count
        filterBooks();
    </script>
</body>
</html>
<?php $conn->close(); ?>
