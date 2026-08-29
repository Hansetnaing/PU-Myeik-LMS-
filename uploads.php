<?php
function upload_document(array $file, string $directory): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'xlsx'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true) || $file['size'] > 10 * 1024 * 1024) {
        return null;
    }

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return null;
    }

    $name = bin2hex(random_bytes(16)) . '.' . $extension;
    $path = rtrim($directory, '/\\') . '/' . $name;
    return move_uploaded_file($file['tmp_name'], $path) ? $path : null;
}
