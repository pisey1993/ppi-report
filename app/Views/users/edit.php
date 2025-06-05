<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
<h1>Edit User</h1>
<a href="<?= site_url('users') ?>">Back to list</a>

<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (!empty($errors)) : ?>
    <ul style="color:red;">
        <?php foreach ($errors as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="<?= site_url('users/update/'.$user['id']) ?>">
    <?= csrf_field() ?>
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= esc(old('name', $user['name'])) ?>"><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" value="<?= esc(old('username', $user['username'])) ?>"><br><br>

    <label>Code:</label><br>
    <input type="text" name="code" value="<?= esc(old('code', $user['code'])) ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= esc(old('email', $user['email'])) ?>"><br><br>

    <label>Password (leave blank if not changing):</label><br>
    <input type="password" name="password"><br><br>

    <label>Status:</label><br>
    <input type="text" name="status" value="<?= esc(old('status', $user['status'])) ?>"><br><br>

    <button type="submit">Update User</button>
</form>
</body>
</html>
