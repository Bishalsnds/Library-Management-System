<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Fines & Warnings</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Fine & Warning Management</h1>
        <p>Complete library management system with edit, update, delete, and status management functions connected to your LMS database.</p>
      </div>
    </header>

    <section class="hero">
      <div class="section-title">Core Modules</div>
      <div class="card-grid">
        <article class="card">
          <h2>Fine Management</h2>
          <p>Add, edit, update, and delete fine records. Mark fines as paid or cancel/waive them. Track all fine transactions with DKK currency.</p>
          <a class="btn-primary" href="fines-complete.php">Open Fine Module</a>
        </article>
        <article class="card">
          <h2>Warning System</h2>
          <p>Record, edit, and manage student warnings. Track warning levels and mark warnings as resolved or closed. Monitor student behavior history.</p>
          <a class="btn-primary" href="warnings-complete.php">Open Warning Module</a>
        </article>
        <article class="card">
          <h2>Book Management</h2>
          <p>Manage your library book inventory. Add new books, edit details, track availability, and delete outdated records from the system.</p>
          <a class="btn-primary" href="books-manage.php">Manage Books</a>
        </article>
        <article class="card">
          <h2>💳 Payment System</h2>
          <p>Accept fine payments through Google Pay and Mobile Pay. Track payment history, view transaction details, and monitor completed payments.</p>
          <a class="btn-primary" href="payment-history.php">View Payments</a>
        </article>
      </div>
    </section>

    <section class="section">
      <div class="card">
        <h2>Database Connected</h2>
        <p><strong>Database:</strong> LMS fines and warning</p>
        <p><strong>Connection:</strong> XAMPP MySQL (localhost)</p>
        <p><strong>Features:</strong> Full CRUD operations (Create, Read, Update, Delete) for all modules. All data persists permanently in your database.</p>
      </div>
    </section>
  </main>
</body>
</html>
