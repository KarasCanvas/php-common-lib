<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Web;

use CommonLib\Globalization\Language;

class UploadHandler
{
    const NAME_RANDOM = 1;
    const NAME_MD5 = 2;
    const NAME_UNIQID = 3;
    const NAME_RAW = 4;
    const NAME_FUNC = 5;

    protected $directory = './';
    protected $minFileSize = 1;
    protected $maxFileSize = 102400;
    protected $allowedExtensions = null;
    protected $pathProvider = null;
    protected $nameProvider = null;
    protected $filenameMode = self::NAME_RANDOM;


    public function __construct(array $options = array())
    {
        $keys = [
            'directory', 'minFileSize', 'maxFileSize', 'allowedExtensions', 'pathProvider', 'nameProvider',
            'filenameMode'
        ];
        foreach ($keys as $key) {
            if (isset($options[$key])) {
                $this->$key = $options[$key];
            }
        }
        if ($this->directory == null) {
            $this->directory = ('.' . DIRECTORY_SEPARATOR);
        }
    }


    protected function validateUploadError($error)
    {
        if ($error == UPLOAD_ERR_OK) {
            return;
        }
        $map = array(
            UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
            UPLOAD_ERR_FORM_SIZE  => 'UPLOAD_ERR_FORM_SIZE',
            UPLOAD_ERR_PARTIAL    => 'UPLOAD_ERR_PARTIAL',
            UPLOAD_ERR_NO_FILE    => 'UPLOAD_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
            UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
            UPLOAD_ERR_EXTENSION  => 'UPLOAD_ERR_EXTENSION',
        );
        if (isset($map[$error])) {
            throw new UploadException(Language::get($map[$error]), $error);
        }
        throw new UploadException(Language::get('UPLOAD_ERR_UNSPECIFIED'), UploadException::ERR_UNSPECIFIED);
    }


    protected function validateSize($size)
    {
        if ($size < $this->minFileSize) {
            throw new UploadException(Language::get('UPLOAD_ERR_SIZE_TOO_SMALL', $size, $this->minFileSize), UploadException::ERR_SIZE_TOO_SMALL);
        } elseif ($size > $this->maxFileSize) {
            throw new UploadException(Language::get('UPLOAD_ERR_SIZE_TOO_LARGE', $size, $this->maxFileSize), UploadException::ERR_SIZE_TOO_LARGE);
        }
    }


    protected function validateExtension($extension)
    {
        if (!is_array($this->allowedExtensions)) {
            return;
        }
        $extension = strtolower($extension);
        foreach ($this->allowedExtensions as $ext) {
            if (strtolower($ext) === $extension) {
                return;
            }
        }
        throw new UploadException(Language::get('UPLOAD_ERR_INVALID_EXTENSION', $extension), UploadException::ERR_INVALID_EXTENSION);
    }


    private function combinePath(array $array, $separator = DIRECTORY_SEPARATOR)
    {
        if (empty($array)) {
            return null;
        }
        $parts = array();
        foreach ($array as $item) {
            $item = strval($item);
            if (empty($item)) {
                continue;
            }
            if ($item[0] == '/' || $item[0] == '\\') {
                $item = trim($item, '/\\');
                $parts = empty($item) ? [] : [$item];
            } else {
                $parts[] = trim($item, '/\\');
            }
        }
        return implode($separator, $parts);
    }


    private function normalizePath($path, $separator = DIRECTORY_SEPARATOR)
    {
        $path = strval($path);
        $len = strlen($path);
        if ($len > 0) {
            $find = ($separator == '/') ? '\\' : '/';
            $path = str_replace($find, $separator, $path);
            if ($path[0] == $separator) {
                if ($len > 1) {
                    $path = substr($path, 1);
                    $len -= 1;
                } else {
                    return '';
                }
            }
            if ($path[$len - 1] == $separator) {
                if ($len > 1) {
                    $path = substr($path, 0, $len - 1);
                } else {
                    return '';
                }
            }
        }
        return $path;
    }


    protected function getStoragePath($file)
    {
        if (is_callable($this->pathProvider)) {
            return $this->normalizePath(call_user_func($this->pathProvider, $file), '/');
        }
        return date('Y/m/d');
    }


    protected function getRealStorageDirectory($path)
    {
        $dir = $this->combinePath([$this->directory, $path]);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return realpath($dir);
    }


    protected function getFileName($file)
    {
        switch ($this->filenameMode) {
            case self::NAME_RANDOM :
                return base_convert(md5(uniqid($file['tmp_name'], true)), 16, 36);
            case self::NAME_MD5 :
                return md5_file($file['tmp_name']);
            case self::NAME_UNIQID :
                return base_convert(uniqid(null, true), 16, 36);
            case self::NAME_RAW :
                return pathinfo($file['name'], PATHINFO_FILENAME);
            case self::NAME_FUNC :
                if (is_callable($this->nameProvider)) {
                    return call_user_func($this->nameProvider, $file);
                } else {
                    throw new \LogicException(Language::get('VAR_IS_NOT_CALLABLE', '$this->nameProvider'));
                }
        }
        throw new \LogicException(Language::get('INVALID_UPLOAD_FILENAME_MODE'));
    }


    protected function saveFile(array $file)
    {
        if (isset($file['error'])) {
            $this->validateUploadError($file['error']);
        }
        $this->validateSize($file['size']);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $this->validateExtension($extension);
        $path = $this->getStoragePath($file);
        $dir = $this->getRealStorageDirectory($path);
        $name_woe = $this->getFileName($file);
        $filename = empty($extension) ? $name_woe : $name_woe . '.' . $extension;
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new UploadException(Language::get('UPLOAD_ERR_CANT_MOVE_FILE'), UploadException::ERR_CANT_MOVE_FILE);
        }
        return array(
            'name'     => $name_woe, 'type' => $file['type'], 'size' => $file['size'], 'extension' => $extension,
            'raw_name' => $file['name'], 'path' => $this->combinePath([$path, $filename], '/'), 'realpath' => $filepath
        );
    }


    public function save($name = 'file')
    {
        if (!isset($_FILES[$name])) {
            throw new UploadException(Language::get('FILE_WAS_NOT_UPLOADED', $name), UploadException::ERR_NO_FILE);
        }
        return $this->saveFile($_FILES[$name]);
    }

}