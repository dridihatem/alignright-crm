<?php

if (!function_exists('google_drive_image_url')) {
    /**
     * Convert a Google Drive share URL to direct image URL.
     *
     * @param string|null $driveUrl
     * @return string|null
     */
    function google_drive_image_url(?string $driveUrl): ?string
    {
        if (!$driveUrl) return null;

        if (preg_match('/\/file\/d\/(.*?)\//', $driveUrl, $matches)) {
            $fileId = $matches[1];
            return "https://drive.google.com/uc?export=view&id={$fileId}";
        }

        return null;
    }

    function google_drive_download_url(?string $driveUrl): ?string
{
    if (!$driveUrl) return null;

    if (preg_match('/\/file\/d\/(.*?)\//', $driveUrl, $matches)) {
        $fileId = $matches[1];
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }

    return null;
}

 function google_drive_file_public_url(?string $driveUrl): ?string
{
    if (!$driveUrl) return null;

    if (preg_match('/\/file\/d\/(.*?)\//', $driveUrl, $matches)) {
        $fileId = $matches[1];
        return "https://drive.google.com/file/d/{$fileId}/view";
    }
}

if (!function_exists('ensure_https_url')) {
    /**
     * Ensure a URL has the proper protocol (https://) if it's missing
     *
     * @param string|null $url
     * @return string|null
     */
    function ensure_https_url(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        // If it's already a valid URL with protocol, return as is
        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }

        // If it already has http:// or https://, return as is
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        // Check if it looks like a domain (contains a dot and valid characters)
        if (preg_match('/^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}/', $url)) {
            return 'https://' . $url;
        }

        // If it doesn't look like a URL, return as is (might be a relative path or other type of link)
        return $url;
    }
}
}