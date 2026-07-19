<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SecureImage implements Rule
{
    protected $maxSize = 5120; // 5MB in KB
    protected $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    protected $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    public function passes($attribute, $value)
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        // Check file size
        if ($value->getSize() > $this->maxSize * 1024) {
            return false;
        }

        // Check MIME type
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimes)) {
            return false;
        }

        // Check extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            return false;
        }

        // Verify it's a real image
        $imageInfo = getimagesize($value->getRealPath());
        if ($imageInfo === false) {
            return false;
        }

        // Check for malicious content (basic)
        $content = file_get_contents($value->getRealPath());
        if ($this->containsMaliciousContent($content)) {
            return false;
        }

        // Check for EXIF data injection (optional)
        if ($this->hasExifData($content)) {
            // You may want to strip EXIF data or reject
            // return false;
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must be a valid, secure image file (JPG, PNG, GIF, WEBP, BMP) and not exceed 5MB.';
    }

    protected function containsMaliciousContent($content)
    {
        $patterns = [
            '/<\?php/i',
            '/<\?=.*?php/i',
            '/eval\(/i',
            '/base64_decode\(/i',
            '/system\(/i',
            '/exec\(/i',
            '/shell_exec\(/i',
            '/passthru\(/i',
            '/popen\(/i',
            '/proc_open\(/i',
            '/assert\(/i',
            '/create_function\(/i',
            '/GLOBALS/i',
            '/_SERVER/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    protected function hasExifData($content)
    {
        // Check for EXIF data that might contain malicious code
        return strpos($content, 'exif') !== false;
    }
}