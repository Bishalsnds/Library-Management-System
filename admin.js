// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

function ensureHttpServer() {
    if (window.location.protocol === 'file:') {
        const searchDiv = document.getElementById('searchResults');
        const manageDiv = document.getElementById('manageBooks');
        if (searchDiv) {
            searchDiv.innerHTML = "<p style='color:red; text-align:center;'>Please open this page through a PHP-enabled local web server, e.g. <strong>http://localhost/library%20mgt/admin.html</strong>. VS Code Live Server or python http.server will not execute PHP.</p>";
        }
        if (manageDiv) {
            manageDiv.innerHTML = "<p style='color:red; text-align:center;'>Please open this page through a PHP-enabled local web server, e.g. <strong>http://localhost/library%20mgt/admin.html</strong>. VS Code Live Server or python http.server will not execute PHP.</p>";
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

// Tab switching
function switchTab(tabName, event) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));

    // Remove active class from buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Show selected tab
    document.getElementById(tabName).classList.add('active');

    // Add active class to clicked button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    // Automatically load the dashboard or manage view when switching tabs
    if (tabName === 'manage') {
        loadAllBooks();
    } else if (tabName === 'dashboard') {
        loadDashboard();
    }
}

// Load categories from database
function loadCategories() {
    if (!ensureHttpServer()) {
        return;
    }

    // First check database status
    fetchApi('?action=checkDb', 'Database check API')
        .then(dbInfo => {
            console.log('Database status:', dbInfo);
            
            if (!dbInfo.table_exists) {
                const searchDiv = document.getElementById('searchResults');
                const manageDiv = document.getElementById('manageBooks');
                const message = "<p style='color:red; text-align:center;'>Database not set up. Please run setup.sql in phpMyAdmin or MySQL to create the database and insert sample data.</p>";
                if (searchDiv) searchDiv.innerHTML = message;
                if (manageDiv) manageDiv.innerHTML = message;
                return;
            }
            
            if (dbInfo.book_count === 0) {
                const searchDiv = document.getElementById('searchResults');
                const manageDiv = document.getElementById('manageBooks');
                const message = "<p style='color:orange; text-align:center;'>Database table exists but no books found. Please insert sample data from setup.sql.</p>";
                if (searchDiv) searchDiv.innerHTML = message;
                if (manageDiv) manageDiv.innerHTML = message;
                return;
            }
            
            // Database is OK, now load categories
            return fetchApi('?action=getCategories', 'Categories API');
        })
        .then(categories => {
            if (!categories) return; // Skip if database check failed
            
            const select = document.getElementById('categoryFilter');
            // Clear existing options except "All Categories"
            select.innerHTML = '<option value="">All Categories</option>';
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading categories:', error));
}

function loadDashboard() {
    if (!ensureHttpServer()) {
        return;
    }

    const dashboardDiv = document.getElementById('dashboardStats');
    dashboardDiv.innerHTML = '<p>Loading dashboard...</p>';

    fetchApi('?action=checkDb', 'Dashboard API')
        .then(info => {
            if (!info || !info.table_exists) {
                dashboardDiv.innerHTML = '<p style="color:#c0392b;">Database not set up. Please run setup.sql to create the books table.</p>';
                return;
            }

            const bookCount = info.book_count || 0;
            const categoryCount = info.categories ? info.categories.length : 0;
            const availableCount = info.available_count || 0;
            const unavailableCount = info.unavailable_count || 0;
            const tableStatus = info.table_exists ? 'Ready' : 'Not Created';

            dashboardDiv.innerHTML = `
                <div class="dashboard-cards">
                    <div class="dashboard-card card-total">
                        <h3>Total Books</h3>
                        <p>${bookCount}</p>
                    </div>
                    <div class="dashboard-card card-categories">
                        <h3>Total Categories</h3>
                        <p>${categoryCount}</p>
                    </div>
                    <div class="dashboard-card card-available">
                        <h3>Available Books</h3>
                        <p>${availableCount}</p>
                    </div>
                    <div class="dashboard-card card-unavailable">
                        <h3>Unavailable Books</h3>
                        <p>${unavailableCount}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            dashboardDiv.innerHTML = '<div class="alert alert-error">Error loading dashboard: ' + error.message + '</div>';
        });
}

// ============ SEARCH FUNCTIONALITY ============
function searchBooks() {
    if (!ensureHttpServer()) {
        return;
    }

    const input = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const resultsDiv = document.getElementById('searchResults');

    resultsDiv.innerHTML = '<p>Loading...</p>';

    let params = new URLSearchParams();
    params.append('action', 'search');
    if (input) params.append('search', input);
    if (category) params.append('category', category);

    fetchApi('?' + params.toString(), 'Admin search API')
        .then(books => {
            resultsDiv.innerHTML = '';

            if (books.error) {
                resultsDiv.innerHTML = '<div class="alert alert-error">Error: ' + books.error + '</div>';
                return;
            }

            if (books.length === 0) {
                resultsDiv.innerHTML = '<p style="text-align: center; color: #7f8c8d; margin-top: 20px;">No books found</p>';
                return;
            }

            let html = '<table class="books-table"><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
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
                        <td>
                            <button class="btn-edit" onclick="openEditForm(${book.id})">Edit</button>
                            <button class="btn-remove" onclick="deleteBook(${book.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            resultsDiv.innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
        });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

// ============ ADD BOOK FUNCTIONALITY ============
function addBook() {
    const title = document.getElementById('addTitle').value.trim();
    const author = document.getElementById('addAuthor').value.trim();
    const category = document.getElementById('addCategory').value.trim();
    const available = document.getElementById('addAvailable').checked ? 1 : 0;
    const alertDiv = document.getElementById('addAlert');

    // Validation
    if (!title) {
        showAlert(alertDiv, 'Title is required', 'error');
        return;
    }
    if (!author) {
        showAlert(alertDiv, 'Author is required', 'error');
        return;
    }
    if (!category) {
        showAlert(alertDiv, 'Category is required', 'error');
        return;
    }

    const data = {
        title: title,
        author: author,
        category: category,
        available: available
    };

    fetchApi('?action=add', 'Add book API', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(result => {
            if (result.success) {
                showAlert(alertDiv, 'Book added successfully! ID: ' + result.id, 'success');
                clearAddForm();
                loadCategories();
                if (document.getElementById('manage').classList.contains('active')) {
                    loadAllBooks();
                }
            } else {
                showAlert(alertDiv, result.error || 'Failed to add book', 'error');
            }
        })
        .catch(error => {
            showAlert(alertDiv, 'Error: ' + error.message, 'error');
        });
}

function clearAddForm() {
    document.getElementById('addTitle').value = '';
    document.getElementById('addAuthor').value = '';
    document.getElementById('addCategory').value = '';
    document.getElementById('addAvailable').checked = true;
    document.getElementById('addAlert').innerHTML = '';
}

// ============ MANAGE BOOKS FUNCTIONALITY ============
function loadAllBooks() {
    if (!ensureHttpServer()) {
        return;
    }

    const manageDiv = document.getElementById('manageBooks');
    manageDiv.innerHTML = '<p>Loading...</p>';

    fetchApi('?action=getAll', 'Get all books API')
        .then(books => {
            manageDiv.innerHTML = '';

            if (books.error) {
                manageDiv.innerHTML = '<div class="alert alert-error">Error: ' + books.error + '</div>';
                return;
            }

            if (books.length === 0) {
                manageDiv.innerHTML = '<p style="text-align: center; color: #7f8c8d; margin-top: 20px;">No books in database</p>';
                return;
            }

            let html = '<table class="books-table"><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
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
                        <td>
                            <button class="btn-edit" onclick="openEditForm(${book.id})">Edit</button>
                            <button class="btn-remove" onclick="deleteBook(${book.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table>';
            manageDiv.innerHTML = html;
        })
        .catch(error => {
            manageDiv.innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
        });
}

function openEditForm(bookId) {
    // For now, show a simple modal-like form
    const newTitle = prompt('Enter new title:');
    if (newTitle !== null) {
        const newAuthor = prompt('Enter new author:');
        if (newAuthor !== null) {
            const newCategory = prompt('Enter new category:');
            if (newCategory !== null) {
                const available = confirm('Is this book available?');
                updateBook(bookId, newTitle, newAuthor, newCategory, available ? 1 : 0);
            }
        }
    }
}

function updateBook(id, title, author, category, available) {
    const data = {
        id: id,
        title: title,
        author: author,
        category: category,
        available: available
    };

    fetchApi('?action=update', 'Update book API', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(result => {
            if (result.success) {
                alert('Book updated successfully!');
                loadAllBooks();
            } else {
                alert('Error: ' + (result.error || 'Failed to update book'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function deleteBook(id) {
    if (confirm('Are you sure you want to delete this book?')) {
        const data = { id: id };

        fetchApi('?action=delete', 'Delete book API', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(result => {
                if (result.success) {
                    alert('Book deleted successfully!');
                    // Refresh the current tab
                    if (document.getElementById('manage').classList.contains('active')) {
                        loadAllBooks();
                    } else {
                        searchBooks();
                    }
                } else {
                    alert('Error: ' + (result.error || 'Failed to delete book'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    }
}

// ============ UTILITY FUNCTIONS ============
function showAlert(container, message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    container.innerHTML = '<div class="alert ' + alertClass + '">' + message + '</div>';
}