// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

function ensureHttpServer() {
    if (window.location.protocol === 'file:') {
        const resultDiv = document.getElementById('results');
        if (resultDiv) {
            resultDiv.innerHTML = "<p style='color:red; text-align:center;'>Please open this page through a PHP-enabled local web server, e.g. <strong>http://localhost/library%20mgt/index.html</strong>. VS Code Live Server or python http.server will not execute PHP.</p>";
        }
        console.error('Page loaded from file://; PHP requests cannot execute.');
        return false;
    }
    return true;
}

function getApiUrl() {
    return new URL('api.php', window.location.href).href;
}

function fetchApi(path, context, init = {}) {
    const url = getApiUrl() + path;
    return fetch(url, init)
        .then(response => {
            if (!response.ok) {
                throw new Error(context + ' returned HTTP ' + response.status + ' ' + response.statusText + ' for ' + url);
            }
            return response.text();
        })
        .then(text => parseApiJson(text, context));
}

function parseApiJson(text, context) {
    const raw = text.trim();
    const problemPatterns = [
        /^<\?php/i,
        /^<!DOCTYPE html/i,
        /^<html/i,
        /Fatal error/i,
        /Parse error/i,
        /Warning:/i,
        /Notice:/i,
        /Invalid action"\]\);/i,
        /function handleSearch\(/i,
        /api\.php/i
    ];

    if (problemPatterns.some(rx => rx.test(raw))) {
        throw new Error(context + ' returned raw server output or PHP source. Open through a local web server and ensure PHP is enabled. Response starts with: ' + raw.slice(0, 250));
    }

    try {
        return JSON.parse(raw);
    } catch (err) {
        throw new Error(context + ' returned invalid JSON. Response starts with: ' + raw.slice(0, 250));
    }
}

// Load categories from database
function loadCategories() {
    console.log('Loading categories...');
    if (!ensureHttpServer()) {
        console.log('HTTP server check failed');
        return;
    }

    // First check database status
    console.log('Checking database status...');
    fetchApi('?action=checkDb', 'Database check API')
        .then(dbInfo => {
            console.log('Database status:', dbInfo);
            
            if (!dbInfo.table_exists) {
                const resultDiv = document.getElementById('results');
                if (resultDiv) {
                    resultDiv.innerHTML = "<div style='color:red; text-align:center; padding:20px; border:2px solid red; margin:20px;'><h3>Database Not Set Up</h3><p>Please run <code>setup.sql</code> in phpMyAdmin or MySQL to create the database and insert sample data.</p><p><strong>Steps:</strong></p><ol><li>Open <code>http://localhost/phpmyadmin</code></li><li>Select your database</li><li>Go to SQL tab</li><li>Copy and paste the contents of <code>setup.sql</code></li><li>Click Go</li></ol></div>";
                }
                return;
            }
            
            if (dbInfo.book_count === 0) {
                const resultDiv = document.getElementById('results');
                if (resultDiv) {
                    resultDiv.innerHTML = "<div style='color:orange; text-align:center; padding:20px; border:2px solid orange; margin:20px;'><h3>No Books Found</h3><p>The database table exists but contains no books. Please insert sample data from <code>setup.sql</code>.</p></div>";
                }
                return;
            }
            
            console.log('Database OK, loading categories...');
            // Database is OK, now load categories
            return fetchApi('?action=getCategories', 'Categories API');
        })
        .then(categories => {
            if (!categories) {
                console.log('No categories returned');
                return; // Skip if database check failed
            }
            
            console.log('Categories loaded:', categories);
            const select = document.getElementById('categoryFilter');
            // Clear existing options except "All Categories"
            select.innerHTML = '<option value="">All Categories</option>';
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                select.appendChild(option);
            });
            
            console.log('Categories populated in dropdown');
            
            // Auto-load all books on page load
            console.log('Auto-loading all books...');
            searchBooks();
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            const resultDiv = document.getElementById('results');
            if (resultDiv) {
                resultDiv.innerHTML = "<div style='color:red; text-align:center; padding:20px; border:2px solid red; margin:20px;'><h3>Connection Error</h3><p>Unable to connect to the server. Please ensure:</p><ul><li>You opened this page through <code>http://localhost/library%20mgt/index.html</code></li><li>XAMPP Apache is running</li><li>PHP is enabled</li></ul><p>Error: " + error.message + "</p></div>";
            }
        });
}

// Search books
function searchBooks() {
    console.log('Searching books...');
    const input = document.getElementById("searchInput").value;
    const category = document.getElementById("categoryFilter").value;
    const resultDiv = document.getElementById("results");
    
    console.log('Search input:', input);
    console.log('Selected category:', category);

    resultDiv.innerHTML = "<p>Loading...</p>";

    // Build query parameters
    let params = new URLSearchParams();
    params.append('action', 'search');
    if (input) params.append('search', input);
    if (category) params.append('category', category);
    
    console.log('Query parameters:', params.toString());

    if (!ensureHttpServer()) {
        console.log('HTTP server check failed');
        return;
    }

    // Fetch from PHP backend API
    fetchApi('?' + params.toString(), 'Search API')
        .then(books => {
            console.log('Search results received:', books);
            resultDiv.innerHTML = "";

            if (books.error) {
                console.log('API returned error:', books.error);
                resultDiv.innerHTML = "<p>Error: " + books.error + "</p>";
                return;
            }

            if (books.length === 0) {
                console.log('No books found');
                resultDiv.innerHTML = "<p style='text-align: center; color: #7f8c8d; margin-top: 20px;'>No books found</p>";
                return;
            }

            console.log('Displaying', books.length, 'books');
            let html = '<table class="books-table"><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Status</th></tr></thead><tbody>';
            books.forEach(book => {
                const availableStatus = book.available === '1' || book.available === 1 || book.available === true;
                const statusClass = availableStatus ? 'status-available' : 'status-not-available';
                const statusText = availableStatus ? 'Available' : 'Not Available';

                html += `
                    <tr>
                        <td>${book.id}</td>
                        <td>${book.title}</td>
                        <td>${book.author}</td>
                        <td>${book.category}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
            resultDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error fetching books:', error);
            resultDiv.innerHTML = "<p>Error fetching books: " + error.message + "</p>";
            console.error('Error:', error);
        });
}

// Clear search
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('results').innerHTML = '';
}