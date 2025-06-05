<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PPI Report System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
    html,
    body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
        font-family: Arial, sans-serif;
        background: #f8f9fa;
    }

    main {
        flex: 1;
        padding: 20px;
    }

    .navbar-custom {
        background-color: #4da5a5;
        color: white;
    }

    .navbar-custom .navbar-nav .nav-link,
    .navbar-custom .navbar-brand {
        color: white;
    }

    .navbar-custom .dropdown-menu {
        background-color: white;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .navbar-custom .dropdown-item:hover {
        background-color: #f1f1f1;
    }

    .navbar-custom .logout-btn {
        border: 1px solid white;
        color: white;
        padding: 5px 10px;
        background: transparent;
        border-radius: 5px;
        text-decoration: none;
    }

    .navbar-custom .logout-btn:hover {
        background-color: #ffffff22;
    }

    .insured-name {
        color: white;
        margin-right: 10px;
        font-size: 0.9rem;
    }

    .insured-name strong {
        font-weight: bold;
    }

    .navbar-nav .nav-link i {
        margin-right: 5px;
    }

    footer {
        background-color: #006666;
        color: white;
        text-align: center;
        padding: 1rem;
    }
    </style>
</head>

<body>

    <?= view('layout/nav') ?>

    <!-- Main Content -->
    <main class="container">
   
   

