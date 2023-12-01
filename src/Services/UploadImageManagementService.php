<?php

namespace Megaads\UploadImageManagement\Services;

use Illuminate\Http\UploadedFile;
use Imagick;

class ManagementService {

    public function __construct() {
    }

    public function compressImage($source, $destination, $quality) {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        }

        imagejpeg($image, $destination, $quality);

        // Giải phóng bộ nhớ
        imagedestroy($image);

        return $destination;
    }

    public function createLogUpload(UploadedFile $fileUpload) {
        // create file log: logs/{year}/{month}/log-{day}.txt
        dd(123, $fileUpload);
    }

    public function updateRecordInLogFile() {

    }

    public function getPathsUse() {

    }

    public function getPathsNotUse() {
        
    }
    

}