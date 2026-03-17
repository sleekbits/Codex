<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([user()['id']]);
$me = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $designation = trim($_POST['designation']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $profileImage = $me['profile_image'] ?? null;
    if (!empty($_POST['remove_image'])) {
        if ($profileImage && file_exists(__DIR__ . '/../' . $profileImage)) {
            @unlink(__DIR__ . '/../' . $profileImage);
        }
        $profileImage = null;
    }

    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            $dir = __DIR__ . '/../uploads/profiles';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $filename = 'uploads/profiles/u' . user()['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__ . '/../' . $filename);
            if ($profileImage && file_exists(__DIR__ . '/../' . $profileImage)) {
                @unlink(__DIR__ . '/../' . $profileImage);
            }
            $profileImage = $filename;
        } else {
            $_SESSION['flash_error'] = 'Invalid image type. Use JPG/PNG/WEBP only.';
            header('Location: profile.php'); exit;
        }
    }

    $stmt = $pdo->prepare('UPDATE users SET full_name=?, designation=?, email=?, phone=?, profile_image=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([$name, $designation ?: null, $email, $phone ?: null, $profileImage, user()['id']]);

    $_SESSION['user']['full_name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['designation'] = $designation;
    $_SESSION['user']['phone'] = $phone;

    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters.';
            header('Location: profile.php'); exit;
        }
        $stmt = $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), user()['id']]);
    }

    $_SESSION['flash_success'] = 'Profile updated';
    header('Location: profile.php'); exit;
}
?>
<h3>My Profile</h3>
<div class="card p-4">
  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-3 text-center">
      <?php if (!empty($me['profile_image'])): ?>
        <img src="/Codex/<?= e($me['profile_image']) ?>" class="img-thumbnail rounded-circle" style="width:140px;height:140px;object-fit:cover;">
      <?php else: ?>
        <div class="rounded-circle border d-flex align-items-center justify-content-center mx-auto" style="width:140px;height:140px;"><i class="bi bi-person fs-1"></i></div>
      <?php endif; ?>
      <input type="file" name="profile_image" class="form-control mt-2">
      <?php if (!empty($me['profile_image'])): ?><label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_image" value="1"> Remove image</label><?php endif; ?>
    </div>
    <div class="col-md-9">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="full_name" value="<?= e($me['full_name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" name="designation" value="<?= e($me['designation']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e($me['email']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($me['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Change Password</label><input type="password" class="form-control" name="password" placeholder="Minimum 8 characters"></div>
      </div>
    </div>
    <div class="col-12"><button class="btn btn-primary">Save Profile</button></div>
  </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
