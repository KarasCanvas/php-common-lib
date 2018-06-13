<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Web;

use CommonLib\Drawing\ImageUtils;

class ImageUploadHandler extends UploadHandler
{
    protected $thumbnails = array();
    protected $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');


    public function setThumbnail($name, $prefix, $suffix, $width, $height, $mode = ImageUtils::RESIZE_FILL)
    {
        $this->thumbnails[strval($name)] = array(
            'prefix' => strval($prefix), 'suffix' => strval($suffix), 'width' => intval($width),
            'height' => intval($height), 'mode' => intval($mode)
        );
    }


    protected function generateThumbnail($srcpath, $config)
    {
        $dir = pathinfo($srcpath, PATHINFO_DIRNAME);
        $name = pathinfo($srcpath, PATHINFO_FILENAME);
        $filename = $config['prefix'] . $name . $config['suffix'] . '.jpg';
        $destpath = $dir . DIRECTORY_SEPARATOR . $filename;
        ImageUtils::resizeToJpeg($srcpath, $destpath, $config['width'], $config['height'], $config['mode']);
        return array(
            'filename' => $filename, 'realpath' => $destpath
        );
    }


    public function save($name = 'file')
    {
        $result = parent::save($name);
        if (!empty($this->thumbnails)) {
            $path = pathinfo($result['path'], PATHINFO_DIRNAME);
            $list = array();
            foreach ($this->thumbnails as $key => $value) {
                $item = $this->generateThumbnail($result['realpath'], $value);
                $item['path'] = $path . '/' . $item['filename'];
                $list[$key] = $item;
            }
            $result['thumbnails'] = $list;
        }
        return $result;
    }

}