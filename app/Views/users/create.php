<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>
<h1>Create New User</h1>
<a href="<?= site_url('users') ?>">Back to list</a>

<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (!empty($errors)) : ?>
    <ul style="color:red;">
        <?php foreach ($errors as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= site_url('users/store') ?>">
    <?= csrf_field() ?>
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= old('name') ?>"><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" value="<?= old('username') ?>"><br><br>

    <label>Code:</label><br>
    <input type="text" name="code" value="<?= old('code') ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= old('email') ?>"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <label>Status:</label><br>
    <input type="text" name="status" value="<?= old('status') ?>"><br><br>

    <button type="submit">Create User</button>
</form>
</body>
</html>
