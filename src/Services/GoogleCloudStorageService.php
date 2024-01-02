<?php

namespace Megaads\UploadImageManagement\Services;

use Google\Cloud\Storage\StorageClient;

class GoogleCloudStorageService {

    protected $ggCloudFormatUrl;
    protected $bucketName;
    protected $keyPath;
    public function __construct(string $ggCloudFormatUrl, string $keyPath) {
        $this->ggCloudFormatUrl = $ggCloudFormatUrl;
        $this->keyPath = $keyPath;
    }

    public function uploadSingleImage(string $bucketName, $file)
    {
        $retVal = false;
        $storage = new StorageClient([
            'keyFilePath' => $this->keyPath
        ]);
        $bucket = $storage->bucket($bucketName);
        $currentDate = date("Y/m/d");
        $file = fopen($file, 'r');
        $hashName = $this->getHashName($file);
        $objectName = "seller/$currentDate/$hashName";
        $bucket->upload($file, [
            'name' => $objectName,
            'predefinedAcl' => 'PUBLICREAD'
        ]);
        $retVal = $this->buildPublicUrl($bucketName, $objectName);
        return $retVal;
    }

    protected function buildPublicUrl($bucketName, $objectName)
    {
        $retVal = $this->ggCloudFormatUrl;
        $retVal = str_replace('#bucketName', $bucketName, $retVal);
        $retVal = str_replace('#objectName', $objectName, $retVal);
        return $retVal;
    }

    public function getHashName ($fileName) {
        $retval = "";
        $retval .= str_slug(preg_replace('/\.(.*)/', '', $fileName->getClientOriginalName()));
        $retval .= "-";
        $retval .= md5_file($fileName->getRealPath());
        $retval .= ".";
        $retval .= $fileName->getClientOriginalExtension();
        return $retval;
    }
}