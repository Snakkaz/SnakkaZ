<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Response.php';

// Verify authentication
$auth = new Auth();
$user = $auth->authenticateRequest();

if (!$user) {
    Response::unauthorized('Authentication required');
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    Response::error('No file uploaded', 400);
}

$file = $_FILES['file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    Response::error('File upload error: ' . $file['error'], 400);
}

// Validate file size (max 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxSize) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

// Validate file type
$allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/webm',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    Response::error('File type not allowed: ' . $mimeType, 400);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('upload_', true) . '.' . $extension;
$uploadDir = __DIR__ . '/../../uploads/';
$filePath = $uploadDir . $filename;

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    Response::error('Failed to save file', 500);
}

// Generate thumbnail for images
$thumbnailPath = null;
if (strpos($mimeType, 'image/') === 0) {
    $thumbnailPath = generateThumbnail($filePath, $uploadDir, $filename);
}

// Save to database
try {
    $db = Database::getInstance();
    
    $uploadId = $db->insert('uploads', [
        'user_id' => $user['user_id'],
        'filename' => $filename,
        'original_filename' => $file['name'],
        'file_type' => $mimeType,
        'file_size' => $file['size'],
        'file_path' => '/uploads/' . $filename,
        'thumbnail_path' => $thumbnailPath
    ]);
    
    Response::success([
        'upload_id' => $uploadId,
        'filename' => $filename,
        'original_filename' => $file['name'],
        'file_type' => $mimeType,
        'file_size' => $file['size'],
        'file_url' => '/uploads/' . $filename,
        'thumbnail_url' => $thumbnailPath
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded file on database error
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    Response::error('Database error: ' . $e->getMessage(), 500);
}

// Generate thumbnail for image
function generateThumbnail($sourcePath, $uploadDir, $filename) {
    try {
        $thumbWidth = 200;
        $thumbHeight = 200;
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return null;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Create image from source
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return null;
        }
        
        if (!$source) {
            return null;
        }
        
        // Calculate thumbnail dimensions (maintain aspect ratio)
        $ratio = min($thumbWidth / $width, $thumbHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG/GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save thumbnail
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $uploadDir . $thumbFilename;
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $thumbPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $thumbPath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumb, $thumbPath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return '/uploads/' . $thumbFilename;
        
    } catch (Exception $e) {
        error_log('Thumbnail generation failed: ' . $e->getMessage());
        return null;
    }
}
