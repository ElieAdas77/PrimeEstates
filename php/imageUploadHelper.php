<?php



define("MAX_IMAGE_UPLOAD_BYTES", 10 * 1024 * 1024); // 10MB per image

/**
 * @param string      $tmpPath
 * @param int         $uploadErrorCode
 * @param string      $uploadDir
 * @param string      $filenamePrefix
 * @param string|null &$errorReason
 */
function saveValidatedImage($tmpPath, $uploadErrorCode, $uploadDir, $filenamePrefix, &$errorReason = null) {
    $errorReason = null;

    if ($uploadErrorCode === UPLOAD_ERR_INI_SIZE || $uploadErrorCode === UPLOAD_ERR_FORM_SIZE) {
        $errorReason = "exceeds the server's upload size limit (check upload_max_filesize / post_max_size in php.ini)";
        return null;
    }

    if ($uploadErrorCode !== UPLOAD_ERR_OK) {
        $errorReason = "failed to upload (error code $uploadErrorCode)";
        return null;
    }

    if (!is_file($tmpPath)) {
        $errorReason = "could not be read after upload";
        return null;
    }

    if (filesize($tmpPath) > MAX_IMAGE_UPLOAD_BYTES) {
        $errorReason = "is larger than " . (MAX_IMAGE_UPLOAD_BYTES / 1024 / 1024) . "MB";
        return null;
    }

    $allowedMimeToExt = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
        "image/gif"  => "gif",
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    if (!isset($allowedMimeToExt[$mimeType])) {
        $errorReason = "is not a supported image type (jpg, png, webp, gif only)";
        return null; // not a real, allowed image type
    }


    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        $errorReason = "is not a valid, readable image";
        return null;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }


    $extension = $allowedMimeToExt[$mimeType];
    $safeName = $filenamePrefix . "_" . bin2hex(random_bytes(8)) . "." . $extension;

    if (move_uploaded_file($tmpPath, $uploadDir . $safeName)) {
        return $safeName;
    }

    $errorReason = "could not be saved to disk";
    return null;
}