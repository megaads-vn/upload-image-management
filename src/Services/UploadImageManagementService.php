<?php

namespace Megaads\UploadImageManagement\Services;

class ManagementService {

    public function __construct() {
        $config = \Config::get('config.compression_ratio', 50);
    }

    public function compressImage(string $targetDir = 'uploads'): array
    {
        $error = null;
        $image = null;
        $message = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $error = $this->createFolder($targetDir);

            $targetDir = "$targetDir/"; // Thư mục lưu trữ ảnh tải lên
            $target_file = $targetDir . time() . basename($_FILES["upload"]["name"]); // Đường dẫn tới file ảnh tải lên
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Loại file ảnh
            // Kiểm tra xem file có phải là ảnh không
            $check = getimagesize($_FILES["upload"]["tmp_name"]);
            if ($check !== false && !$error) {
                // File là ảnh
                switch ($imageFileType) {
                    case 'png':
                        $image = imagecreatefrompng($_FILES["upload"]["tmp_name"]); // Đối với PNG
                        $quality = \Config::get('config.compression_ratio', 50); // Compression quality (0 - 100)
                        imagepng($image, $target_file, $quality); // Lưu ảnh đã nén với đường dẫn và chất lượng nén
                        // Giải phóng bộ nhớ
                        imagedestroy($image);
                        $message = 'Compression image upload successful.';
                        break;

                    case 'jpeg':
                    case 'jpg':
                        $image = imagecreatefromjpeg($_FILES["upload"]["tmp_name"]); // Đối với JPEG
                        $quality = \Config::get('config.compression_ratio', 50); // Compression quality (0 - 100)
                        imagejpeg($image, $target_file, $quality); // Lưu ảnh đã nén với đường dẫn và chất lượng nén
                        // Giải phóng bộ nhớ
                        imagedestroy($image);
                        $message = 'Compression image upload successful.';
                        break;

                    case 'gif':
                        $image = imagecreatefromgif($_FILES["upload"]["tmp_name"]); // Đối với GIF
                        $quality = \Config::get('config.compression_ratio', 50); // Compression quality (0 - 100)
                        imagegif($image, $target_file, $quality); // Lưu ảnh đã nén với đường dẫn và chất lượng nén
                        // Giải phóng bộ nhớ
                        imagedestroy($image);
                        $message = 'Compression image upload successful.';
                        break;

                    case 'wbmp':
                        $image = imagecreatefromwbmp($_FILES["upload"]["tmp_name"]); // Đối với WBMP
                        $quality = \Config::get('config.compression_ratio', 50); // Compression quality (0 - 100)
                        imagewbmp($image, $target_file, $quality); // Lưu ảnh đã nén với đường dẫn và chất lượng nén
                        // Giải phóng bộ nhớ
                        imagedestroy($image);
                        $message = 'Compression image upload successful.';
                        break;
                        
                    case 'webp':
                        $image = imagecreatefromwebp($_FILES["upload"]["tmp_name"]); // Đối với WEBP
                        $quality = \Config::get('config.compression_ratio', 50); // Compression quality (0 - 100)
                        imagewebp($image, $target_file, $quality); // Lưu ảnh đã nén với đường dẫn và chất lượng nén
                        // Giải phóng bộ nhớ
                        imagedestroy($image);
                        $message = 'Compression image upload successful.';
                        break;
                    
                    default:
                        $error = 'Only image files in JPG, JPEG, PNG, GIF, WBMP, WEBP formats are allowed to be uploaded.';
                        break;
                }
            } else {
                
                $error = "File is not an image.";
            }
        }

        $retVal = [
            'status' => $error ? 'failed' : 'successful',
            'message' => $error ?? $message
        ];

        if ($image) {
            $retVal['compression_file_path'] = 'uploads/'.basename($target_file);
        }

        return $retVal;
    }

    public function createLogUploadImage(string $url): array
    {
        $targetDir = 'logs/';
        $error = $this->createFolder($targetDir);
        if (!$error){
            $error = $this->createFolder($targetDir . date('Y') . '/');
        }

        if (!$error){
            $error = $this->createFolder($targetDir . date('Y') . '/' . date('m') . '/');
        }
        
        if (!$error) {
            $logPath = $targetDir . date('Y') . '/' . date('m') . '/';

            // Tạo cấu trúc thư mục nếu không tồn tại
            if (!file_exists($logPath)) {
                mkdir($logPath, 0777, true);
            }

            $filePath = $targetDir . date('Y') . '/' . date('m') . '/log-' . date('Y-m-d') . '.json';
            if (!file_exists($filePath)) {
                // Tên file cần đặt cho file JSON (ví dụ: log-2023-11-28.json)
                $fileName = date('Y-m-d') . '.json';
                $filePath = $logPath . $fileName;
                $jsonData = json_encode([$url]);
            } else {
                $jsonData = file_get_contents($filePath);

                // Chuyển đổi JSON thành mảng dữ liệu PHP
                $data = json_decode($jsonData, true);

                // Thêm URL mới vào mảng dữ liệu
                $data[] = $url;

                // Chuyển đổi mảng dữ liệu đã được cập nhật thành JSON
                $jsonData = json_encode($data);
            }

            if (file_put_contents($filePath, $jsonData)) {
                $retVal = [
                    'status' => 'successful',
                    'result' => [
                        'message' => 'JSON file created successfully.'
                    ]
                ];
            } else {
                $retVal = [
                    'status' => 'failed',
                    'result' => [
                        'message' => 'Error creating JSON file.'
                    ]
                ];
            }

        } else {
            $retVal = [
                'status' => 'failed',
                'result' => [
                    'message' => 'Error creating folder.'
                ]
            ];
        }
        

        return $retVal;
    }

    private function createFolder(string $path)
    {
        $error = null;
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                $error = "Can't create folder {$path}";
            }
        }

        return $error;
    }

    public function removeUrlUsedInLog(string $url): array
    {
        // Path to your JSON file
        $filePath = public_path('logs/' . date('Y') . '/' . date('m') . '/log-' . date('Y-m-d') . '.json');

        // Read the content of the JSON file
        $jsonData = file_get_contents($filePath);
        
        // Parse JSON content into a PHP array
        $data = json_decode($jsonData, true);
        
        // Find and remove the URL from the array
        $key = array_search($url, $data);
        if ($key !== false) {
            unset($data[$key]);
        }

        // Encode the updated array back to JSON
        $newJsonData = json_encode(array_values($data));

        // Write the modified JSON data back to the file
        if (file_put_contents($filePath, $newJsonData)) {
            $retVal = [
                'status' => 'successful',
                'result' => [
                    'message' => 'URL removed from JSON file successfully.'
                ]
            ];
        } else {
            $retVal = [
                'status' => 'failed',
                'result' => [
                    'message' => 'Error removing URL from JSON file.'
                ]
            ];
        }

        return $retVal;
    }

    public function removeImageOnLocal(string $path): array
    {
        if (file_exists($path)) {
            unlink($path);
            $retVal = [
                'status' => 'successful',
                'result' => [
                    'message' => 'File '.$path.' has been deleted'
                ]
            ];
        } else {
            $retVal = [
                'status' => 'successful',
                'result' => [
                    'message' => 'Could not delete '.$path.', file does not exist'
                ]
            ];
        }

        return $retVal;
    }
}