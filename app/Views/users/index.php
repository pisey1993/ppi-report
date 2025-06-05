<!DOCTYPE html>
<html>
<head>
    <title>Users List</title>
</head>
<body>
<h1>Users</h1>
<a href="<?= site_url('users/create') ?>">Create New User</a>
<br><br>

<?php if(session()->getFlashdata('success')): ?>
    <p style="color: green"><?= session()->getFlashdata('success') ?></p>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th>
    </tr>
    <?php if (!empty($users) && is_array($users)) : ?>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= esc($user['id']) ?></td>
                <td><?= esc($user['name']) ?></td>
                <td><?= esc($user['username']) ?></td>
                <td><?= esc($user['email']) ?></td>
                <td><?= esc($user['status']) ?></td>
                <td>
                    <a href="<?= site_url('users/edit/'.$user['id']) ?>">Edit</a> |
                    <a href="<?= site_url('users/delete/'.$user['id']) ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6">No users found.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>
