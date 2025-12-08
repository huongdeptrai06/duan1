<?php

require_once BASE_PATH . '/src/helpers/database.php';

class GuideController
{
    // List guides, optional filter by group (noidia / quocte)
    public function index(): void
    {
        requireAdmin();

        $group = $_GET['group'] ?? null;
        $db = getDB();

        if ($group && in_array($group, ['noidia', 'quocte'])) {
            $stmt = $db->prepare('SELECT * FROM guides WHERE `group` = :group ORDER BY id DESC');
            $stmt->execute([':group' => $group]);
        } else {
            $stmt = $db->query('SELECT * FROM guides ORDER BY id DESC');
        }

        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin.guides.index', [
            'title' => 'Danh sách hướng dẫn viên',
            'pageTitle' => 'Danh sách hướng dẫn viên',
            'guides' => $guides,
        ]);
    }

    // Show create form
    public function create(): void
    {
        requireAdmin();

        view('admin.guides.create', [
            'title' => 'Thêm hướng dẫn viên',
            'pageTitle' => 'Thêm hướng dẫn viên',
        ]);
    }

    // Store new guide
    public function store(): void
    {
        requireAdmin();

        $db = getDB();

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'dob' => $_POST['dob'] ?? null,
            'contact' => $_POST['contact'] ?? '',
            'certificates' => $_POST['certificates'] ?? '',
            'languages' => $_POST['languages'] ?? '',
            'experience' => $_POST['experience'] ?? '',
            'tour_history' => $_POST['tour_history'] ?? '',
            'rating' => $_POST['rating'] ?? null,
            'health_status' => $_POST['health_status'] ?? '',
            'group' => in_array($_POST['group'] ?? 'noidia', ['noidia','quocte']) ? $_POST['group'] : 'noidia',
            'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1,
        ];

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadsDir = BASE_PATH . '/public/uploads/guides';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('guide_') . '.' . $ext;
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoPath = 'uploads/guides/' . $fileName;
            }
        }

        $stmt = $db->prepare('INSERT INTO guides (full_name,dob,photo,contact,certificates,languages,experience,tour_history,rating,health_status,`group`,status,created_at,updated_at) VALUES (:full_name,:dob,:photo,:contact,:certificates,:languages,:experience,:tour_history,:rating,:health_status,:group,:status, NOW(), NOW())');
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':dob' => $data['dob'],
            ':photo' => $photoPath,
            ':contact' => $data['contact'],
            ':certificates' => $data['certificates'],
            ':languages' => $data['languages'],
            ':experience' => $data['experience'],
            ':tour_history' => $data['tour_history'],
            ':rating' => $data['rating'],
            ':health_status' => $data['health_status'],
            ':group' => $data['group'],
            ':status' => $data['status'],
        ]);

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }

    // Show edit form
    public function edit(): void
    {
        requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $guide = $stmt->fetch(PDO::FETCH_ASSOC);

        view('admin.guides.edit', [
            'title' => 'Chỉnh sửa hướng dẫn viên',
            'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
            'guide' => $guide,
        ]);
    }

    // Update guide and write audit log
    public function update(): void
    {
        requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'dob' => $_POST['dob'] ?? null,
            'contact' => $_POST['contact'] ?? '',
            'certificates' => $_POST['certificates'] ?? '',
            'languages' => $_POST['languages'] ?? '',
            'experience' => $_POST['experience'] ?? '',
            'tour_history' => $_POST['tour_history'] ?? '',
            'rating' => $_POST['rating'] ?? null,
            'health_status' => $_POST['health_status'] ?? '',
            'group' => in_array($_POST['group'] ?? 'noidia', ['noidia','quocte']) ? $_POST['group'] : 'noidia',
            'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1,
        ];

        // Handle photo upload
        $photoPath = $old['photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadsDir = BASE_PATH . '/public/uploads/guides';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('guide_') . '.' . $ext;
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoPath = 'uploads/guides/' . $fileName;
            }
        }

        $stmt = $db->prepare('UPDATE guides SET full_name = :full_name, dob = :dob, photo = :photo, contact = :contact, certificates = :certificates, languages = :languages, experience = :experience, tour_history = :tour_history, rating = :rating, health_status = :health_status, `group` = :group, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':dob' => $data['dob'],
            ':photo' => $photoPath,
            ':contact' => $data['contact'],
            ':certificates' => $data['certificates'],
            ':languages' => $data['languages'],
            ':experience' => $data['experience'],
            ':tour_history' => $data['tour_history'],
            ':rating' => $data['rating'],
            ':health_status' => $data['health_status'],
            ':group' => $data['group'],
            ':status' => $data['status'],
            ':id' => $id,
        ]);

        // Write audit log: record changed fields
        $changed = [];
        $fields = ['full_name','dob','photo','contact','certificates','languages','experience','tour_history','rating','health_status','group','status'];
        foreach ($fields as $f) {
            $oldVal = $old[$f] ?? null;
            $newVal = ($f === 'photo') ? $photoPath : ($data[$f] ?? $oldVal);
            if ($oldVal != $newVal) {
                $changed[$f] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        if (!empty($changed)) {
            $user = getCurrentUser();
            $logStmt = $db->prepare('INSERT INTO guide_logs (guide_id, changed_by, change_data, created_at) VALUES (:guide_id, :changed_by, :change_data, NOW())');
            $logStmt->execute([
                ':guide_id' => $id,
                ':changed_by' => $user ? $user->id : null,
                ':change_data' => json_encode($changed, JSON_UNESCAPED_UNICODE),
            ]);
        }

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }

    // Show guide detail (including change log)
    public function show(): void
    {
        requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $guide = $stmt->fetch(PDO::FETCH_ASSOC);

        $logStmt = $db->prepare('SELECT * FROM guide_logs WHERE guide_id = :id ORDER BY id DESC');
        $logStmt->execute([':id' => $id]);
        $logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin.guides.show', [
            'title' => 'Chi tiết hướng dẫn viên',
            'pageTitle' => 'Chi tiết hướng dẫn viên',
            'guide' => $guide,
            'logs' => $logs,
        ]);
    }

    // Delete guide
    public function delete(): void
    {
        requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('DELETE FROM guides WHERE id = :id');
        $stmt->execute([':id' => $id]);

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }
}
