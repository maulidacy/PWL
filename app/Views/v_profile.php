<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h2>Profil Pengguna</h2>

<div style="margin-top: 20px;">
    <div style="display: flex; align-items: center; margin-bottom: 5px;">
        <div style="width: 10px; height: 10px; background-color: #333; border-radius: 50%; margin-right: 10px;"></div>
        <div><strong>Username</strong>: <?= esc($username) ?></div>
    </div>
    <div style="display: flex; align-items: center; margin-bottom: 5px;">
        <div style="width: 10px; height: 10px; background-color: #333; border-radius: 50%; margin-right: 10px;"></div>
        <div><strong>Role</strong>: <?= esc($role) ?></div>
    </div>
    <div style="display: flex; align-items: center; margin-bottom: 5px;">
        <div style="width: 10px; height: 10px; background-color: #333; border-radius: 50%; margin-right: 10px;"></div>
        <div><strong>Email</strong>: <?= esc($email) ?></div>
    </div>
    <div style="display: flex; align-items: center; margin-bottom: 5px;">
        <div style="width: 10px; height: 10px; background-color: #333; border-radius: 50%; margin-right: 10px;"></div>
        <div><strong>Waktu Login</strong>: <?= esc($login_time) ?></div>
    </div>
    <div style="display: flex; align-items: center; margin-bottom: 5px;">
        <div style="width: 10px; height: 10px; background-color: #333; border-radius: 50%; margin-right: 10px;"></div>
        <div><strong>Status Login</strong>: <?= $isLoggedIn ? 'Sudah Login' : 'Logged Out' ?></div>
    </div>
</div>

<?= $this->endSection() ?>