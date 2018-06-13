<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Drawing;

abstract class ImageUtils
{
    const RESIZE_FILL = 0;
    const RESIZE_FIT = 1;
    const RESIZE_STRETCH = 2;

    private function __construct() { }


    /**
     * resize
     * @author Raven
     * @param resource $source
     * @param int $width
     * @param int $height
     * @param int $mode
     * @return resource
     */
    public static function resize($source, $width, $height, $mode = self::RESIZE_FILL)
    {
        $image = imagecreatetruecolor($width, $height);
        $bgc = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgc);

        $src_w = imagesx($source);
        $src_h = imagesy($source);
        $dst_w = $width;
        $dst_h = $height;
        $src_x = 0;
        $src_y = 0;
        $dst_x = 0;
        $dst_y = 0;
        $ratio_w = $src_w / $dst_w;
        $ratio_h = $src_h / $dst_h;

        if (strval($ratio_w) !== strval($ratio_h)) {
            $flag = $ratio_w > $ratio_h;
            switch ($mode) {
                case self::RESIZE_FILL:
                    if ($flag) {
                        $src_w = $dst_w * $ratio_h;
                        $src_h = $dst_h * $ratio_h;
                        $src_x = abs(imagesx($source) - $src_w) / 2;
                    } else {
                        $src_w = $dst_w * $ratio_w;
                        $src_h = $dst_h * $ratio_w;
                        $src_y = abs(imagesy($source) - $src_h) / 2;
                    }
                    break;
                case self::RESIZE_FIT:
                    if ($flag) {
                        $dst_w = $src_w / $ratio_w;
                        $dst_h = $src_h / $ratio_w;
                        $dst_y = abs($height - $dst_h) / 2;
                    } else {
                        $dst_w = $src_w / $ratio_h;
                        $dst_h = $src_h / $ratio_h;
                        $dst_x = abs($width - $dst_w) / 2;
                    }
                    break;
            }
        }

        imagecopyresampled($image, $source, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        return $image;
    }


    /**
     * resizeToJpeg
     * @author Raven
     * @param string $sourceFile
     * @param string $destFile
     * @param int $width
     * @param int $height
     * @param int $mode
     * @param int $quality
     * @return bool
     */
    public static function resizeToJpeg($sourceFile, $destFile, $width, $height, $mode = self::RESIZE_FILL, $quality = 75)
    {
        $source = self::createImageFromFile($sourceFile);
        $image = self::resize($source, $width, $height, $mode);
        $result = imagejpeg($image, $destFile, $quality);
        imagedestroy($source);
        imagedestroy($image);
        return $result;
    }


    /**
     * createImageFromFile
     * @author Raven
     * @param string $filename
     * @return resource | false
     */
    public static function createImageFromFile($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = array(
            'jpg' => 'jpeg'
        );
        if (isset($map[$ext])) {
            $ext = $map[$ext];
        }
        $func = 'imagecreatefrom' . $ext;
        if (!function_exists($func)) {
            throw new \LogicException(sprintf('GD function (%s) not exists.', $func));
        }
        return call_user_func($func, $filename);
    }

}