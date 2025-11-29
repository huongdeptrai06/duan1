<?php

class CategoryController
{
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        $errors = [];
        $categories = [];
        $successMessage = $_GET['success'] ?? null;

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                $stmt = $pdo->query('SELECT id, name, description, status, created_at, updated_at FROM categories ORDER BY created_at DESC');
                $categories = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Categories index failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách danh mục.';
            }
        }

        view('admin.categories.index', [
            'title' => 'Quản lý danh mục',
            'categories' => $categories,
            'errors' => $errors,
            'successMessage' => $successMessage,
        ]);
    }

    public function create(): void
    {
        requireAdmin();

        view('admin.categories.create', [
            'title' => 'Thêm danh mục',
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tên danh mục không được để trống.';
        }

        if (strlen($name) > 255) {
            $errors[] = 'Tên danh mục không được vượt quá 255 ký tự.';
        }

        $formData = [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ];

        $pdo = getDB();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        }

        if (!empty($errors)) {
            view('admin.categories.create', [
                'title' => 'Thêm danh mục',
                'errors' => $errors,
                'formData' => $formData,
            ]);
            return;
        }

        try {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('INSERT INTO categories (name, description, status, created_at, updated_at) VALUES (:name, :description, :status, :created_at, :updated_at)');
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (PDOException $e) {
            error_log('Create category failed: ' . $e->getMessage());
            $errors[] = 'Không thể tạo danh mục. Vui lòng thử lại.';
            view('admin.categories.create', [
                'title' => 'Thêm danh mục',
                'errors' => $errors,
                'formData' => $formData,
            ]);
            return;
        }

        header('Location: ' . BASE_URL . 'admin/categories?success=' . urlencode('Đã thêm danh mục mới.'));
        exit;
    }

    public function edit(): void
    {
        requireAdmin();

        $category = $this->getCategoryFromRequest();
        if (!$category) {
            view('not_found', ['title' => 'Danh mục không tồn tại']);
            return;
        }

        view('admin.categories.edit', [
            'title' => 'Cập nhật danh mục',
            'category' => $category,
        ]);
    }

    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tên danh mục không được để trống.';
        }

        if (strlen($name) > 255) {
            $errors[] = 'Tên danh mục không được vượt quá 255 ký tự.';
        }

        $pdo = getDB();
        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        }

        if (!empty($errors)) {
            view('admin.categories.edit', [
                'title' => 'Cập nhật danh mục',
                'errors' => $errors,
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'status' => $status,
                ],
            ]);
            return;
        }

        try {
            $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description, status = :status, updated_at = :updated_at WHERE id = :id');
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Update category failed: ' . $e->getMessage());
            $errors[] = 'Không thể cập nhật danh mục. Vui lòng thử lại.';
            view('admin.categories.edit', [
                'title' => 'Cập nhật danh mục',
                'errors' => $errors,
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'status' => $status,
                ],
            ]);
            return;
        }

        header('Location: ' . BASE_URL . 'admin/categories?success=' . urlencode('Đã cập nhật danh mục.'));
        exit;
    }

    public function delete(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        $pdo = getDB();
        if ($pdo !== null && $id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
                $stmt->execute(['id' => $id]);
            } catch (PDOException $e) {
                error_log('Delete category failed: ' . $e->getMessage());
                header('Location: ' . BASE_URL . 'admin/categories?success=' . urlencode('Không thể xóa danh mục.'));
                exit;
            }
        }

        header('Location: ' . BASE_URL . 'admin/categories?success=' . urlencode('Đã xóa danh mục.'));
        exit;
    }

    public function show(): void
    {
        requireAdmin();

        $category = $this->getCategoryFromRequest();
        if (!$category) {
            view('not_found', ['title' => 'Danh mục không tồn tại']);
            return;
        }

        view('admin.categories.show', [
            'title' => 'Chi tiết danh mục',
            'category' => $category,
        ]);
    }

    private function getCategoryFromRequest(): ?array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, name, description, status, created_at, updated_at FROM categories WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch();
            return $category ?: null;
        } catch (PDOException $e) {
            error_log('Fetch category failed: ' . $e->getMessage());
            return null;
        }
    }
}








