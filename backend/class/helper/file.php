<?php

namespace codename\core\helper;

use codename\core\app;
use codename\core\exception;

/**
 * [file description]
 */
class file
{
    /**
     * [downloadToClient description]
     * @param string $filepath [local filepath]
     * @param string $filename [target filename]
     * @param array $option [array of options, provide ['inline' => true] to perform inline pushing]
     * @return void [type]           [description]
     * @throws exception
     */
    public static function downloadToClient(string $filepath, string $filename, array $option = []): void
    {
        if (!file_exists($filepath)) {
            throw new exception('HELPER_FILE_DOWNLOADER_DOES_NOT_EXIST', exception::$ERRORLEVEL_ERROR, $filepath);
        }

        if (array_key_exists('inline', $option) === true && $option['inline'] === true) {
            // Determine Mime Type by extension. I know it's bad.
            $path_parts = pathinfo($filepath);
            $ext = strtolower($path_parts["extension"]);

            // Determine Content Type (only for inlining)
            $ctype = match ($ext) {
                "pdf" => "application/pdf",
                "gif" => "image/gif",
                "png" => "image/png",
                "jpeg", "jpg" => "image/jpg",
                default => "application/force-download",
            };

            app::getResponse()->setHeader('Content-Type: ' . $ctype);
            app::getResponse()->setHeader("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            app::getResponse()->setHeader("Cache-Control: post-check=0, pre-check=0");
            app::getResponse()->setHeader("Pragma: no-cache");
            app::getResponse()->setHeader('Content-Disposition: inline; filename="' . $filename . '"');
            app::getResponse()->setHeader('Content-Length: ' . filesize($filepath));
            app::getResponse()->setHeader('Content-Transfer-Encoding: binary');
        } else {
            app::getResponse()->setHeader('Content-Description: File Transfer');
            app::getResponse()->setHeader('Content-Type: application/octet-stream');
            app::getResponse()->setHeader('Content-Transfer-Encoding: binary');
            app::getResponse()->setHeader('Pragma: public');
            app::getResponse()->setHeader('Content-Length: ' . filesize($filepath));
            app::getResponse()->setHeader('Content-Disposition: attachment; filename="' . $filename . '"');

            // add needed headers for CORS compat
            app::getResponse()->setHeader('access-control-expose-headers: content-disposition, content-type');
        }
        if (ob_get_contents()) {
            ob_clean();
        }
        flush();
        readfile($filepath);
        unlink($filepath);
        exit(0);
    }
}
