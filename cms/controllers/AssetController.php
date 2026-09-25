<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Asset.php';

class AssetController extends Controller
{
    private Asset $model;

    public function __construct()
    {
        $this->model = new Asset();
    }

    /** GET /assets */
    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';

        $assets = $this->model->getFilteredAssets($search, $category, $sort);
        $stats = $this->model->getStats();

        $this->success([
            'assets' => $assets,
            'stats'  => $stats,
        ]);
    }

    /** GET /assets/{id} */
    public function show(int $id): void
    {
        $asset = $this->model->find($id);
        if (!$asset) {
            $this->error('Asset not found', 404);
        }
        $this->success($asset);
    }

    /** POST /assets */
    public function store(): void
    {
        $title = trim($_POST['title'] ?? '');
        $altText = trim($_POST['alt_text'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $imageUrl = trim($_POST['image_url'] ?? '');

        // If file is uploaded
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

            if (!in_array($ext, $allowed, true)) {
                $this->error('Format file tidak didukung. Harap unggah gambar (JPG, PNG, WEBP, GIF, SVG).', 422);
            }

            $uploadDir = __DIR__ . '/../../assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFilename = 'asset_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $newFilename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $filePath = 'assets/images/' . $newFilename;
                $fileSize = filesize($targetPath) ?: 0;
                $mimeType = $file['type'] ?: 'image/' . $ext;

                $dimensions = null;
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    $info = @getimagesize($targetPath);
                    if ($info && isset($info[0], $info[1])) {
                        $dimensions = $info[0] . 'x' . $info[1];
                    }
                }

                $assetTitle = $title !== '' ? $title : ucwords(str_replace(['-', '_'], ' ', pathinfo($file['name'], PATHINFO_FILENAME)));
                $assetAlt = $altText !== '' ? $altText : $assetTitle;

                $assetId = $this->model->create([
                    'title'      => $assetTitle,
                    'filename'   => $newFilename,
                    'file_path'  => $filePath,
                    'file_size'  => $fileSize,
                    'mime_type'  => $mimeType,
                    'dimensions' => $dimensions,
                    'alt_text'   => $assetAlt,
                    'category'   => $category,
                ]);

                $newAsset = $this->model->find($assetId);
                $this->success($newAsset, 201);
            } else {
                $this->error('Gagal menyimpan file ke server.', 500);
            }
        } elseif ($imageUrl !== '') {
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH) ?: 'external_image');
            $assetTitle = $title !== '' ? $title : ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

            $assetId = $this->model->create([
                'title'      => $assetTitle,
                'filename'   => $filename,
                'file_path'  => $imageUrl,
                'file_size'  => 0,
                'mime_type'  => 'image/external',
                'dimensions' => null,
                'alt_text'   => $altText !== '' ? $altText : $assetTitle,
                'category'   => $category,
            ]);

            $newAsset = $this->model->find($assetId);
            $this->success($newAsset, 201);
        } else {
            $this->error('File gambar atau URL gambar wajib diisi.', 422);
        }
    }

    /** PUT/PATCH /assets/{id} */
    public function update(int $id): void
    {
        $asset = $this->model->find($id);
        if (!$asset) {
            $this->error('Asset not found', 404);
        }

        $input = $this->getInput();
        $data = [];

        if (isset($input['title'])) {
            $data['title'] = trim($input['title']);
        }
        if (isset($input['alt_text'])) {
            $data['alt_text'] = trim($input['alt_text']);
        }
        if (isset($input['category'])) {
            $data['category'] = trim($input['category']);
        }

        if (!empty($data)) {
            $this->model->update($id, $data);
        }

        $updated = $this->model->find($id);
        $this->success($updated);
    }

    /** DELETE /assets/{id} */
    public function destroy(int $id): void
    {
        $asset = $this->model->find($id);
        if (!$asset) {
            $this->error('Asset not found', 404);
        }

        // Unlink local physical file if applicable
        if (str_starts_with($asset['file_path'], 'assets/images/')) {
            $fullPath = __DIR__ . '/../../' . $asset['file_path'];
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $this->model->delete($id);
        $this->success(['message' => 'Asset berhasil dihapus.']);
    }
}
