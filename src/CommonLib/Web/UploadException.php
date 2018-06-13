<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Web;

class UploadException extends \Exception
{
    const ERR_UNSPECIFIED = 100;
    const ERR_NO_FILE = 101;
    const ERR_SIZE_TOO_SMALL = 102;
    const ERR_SIZE_TOO_LARGE = 103;
    const ERR_INVALID_EXTENSION = 104;
    const ERR_CANT_MOVE_FILE = 105;
}