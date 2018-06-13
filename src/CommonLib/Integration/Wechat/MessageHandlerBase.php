<?php
namespace CommonLib\Integration\Wechat;

/**
 * @summary 提供微信消息处理程序对象模型
 * @author Raven <karascanvas@qq.com>
 */
abstract class MessageHandlerBase
{
    const MSG_TEXT = 'text';
    const MSG_IMAGE = 'image';
    const MSG_VOICE = 'voice';
    const MSG_VIDEO = 'video';
    const MSG_MUSIC = 'music';
    const MSG_NEWS = 'news';
    const MSG_LINK = 'link';
    const MSG_LOCATION = 'location';
    const MSG_SHORT_VIDEO = 'shortvideo';
    const MSG_EVENT = 'event';


    protected static $handlers = array(
        self::MSG_EVENT => 'handleEvent',
    );

    protected static $eventHandlers = array();

    protected $defaultEnabled = true;


    public function handle($xml, $output = true)
    {
        $msg = XmlUtils::deserialize($xml);
        $response = $this->handleMessage($msg);
        if ($response === null && $this->defaultEnabled) {
            $response = $this->defaultResponse($msg);
        }
        if ($output) {
            $this->respond($response);
        }
        return $response;
    }


    protected function handleMessage(array $message)
    {
        if (isset($message['MsgType'])) {
            $type = strtolower($message['MsgType']);
            if (isset(static::$handlers[$type])) {
                $func = static::$handlers[$type];
                if (is_string($func) && method_exists($this, $func)) {
                    return call_user_func(array($this, $func), $message);
                } else if (is_callable($func)) {
                    return call_user_func($func, $message);
                }
            }
        }
        return null;
    }


    protected function handleEvent(array $message)
    {
        if (isset($message['Event'])) {
            $type = strtolower($message['Event']);
            if (isset(static::$eventHandlers[$type])) {
                $func = static::$eventHandlers[$type];
                if (is_string($func) && method_exists($this, $func)) {
                    return call_user_func(array($this, $func), $message);
                } else if (is_callable($func)) {
                    return call_user_func($func, $message);
                }
            }
        }
        return null;
    }


    protected function respond($message)
    {
        header('Content-Type:text/xml; charset=utf-8');
        if ($message instanceof \SimpleXMLElement) {
            $message = $message->asXML();
        } elseif (is_array($message)) {
            $message = XmlUtils::serialize($message);
        }
        exit(strval($message));
    }


    protected function defaultResponse(array $message)
    {
        return $this->createTextResponse($message, 'Hello!');
    }


    protected function createResponse(array $request, $type)
    {
        return array(
            'ToUserName'   => $request['FromUserName'],
            'FromUserName' => $request['ToUserName'],
            'CreateTime'   => time(),
            'MsgType'      => $type,
        );
    }

    protected function createTextResponse(array $request, $content)
    {
        $response = $this->createResponse($request, self::MSG_TEXT);
        $response['Content'] = $content;
        return $response;
    }

    protected function createImageResponse(array $request, $mediaId)
    {
        $response = $this->createResponse($request, self::MSG_IMAGE);
        $response['Image'] = array('MediaId' => $mediaId);
        return $response;
    }

    protected function createVoiceResponse(array $request, $mediaId)
    {
        $response = $this->createResponse($request, self::MSG_VOICE);
        $response['Voice'] = array('MediaId' => $mediaId);
        return $response;
    }

    protected function createVideoResponse(array $request, $mediaId, $title = null, $description = null)
    {
        $response = $this->createResponse($request, self::MSG_VIDEO);
        $response['Video'] = array(
            'MediaId'     => $mediaId,
            'Title'       => $title,
            'Description' => $description
        );
        return $response;
    }

    protected function createMusicResponse(array $request, $thumbMediaId, $musicUrl = null, $hqMusicUrl = null, $title = null, $description = null)
    {
        $response = $this->createResponse($request, self::MSG_MUSIC);
        $response['Music'] = array(
            'Title'        => $title,
            'Description'  => $description,
            'MusicURL'     => $musicUrl,
            'HQMusicUrl'   => $hqMusicUrl,
            'ThumbMediaId' => $thumbMediaId,
        );
        return $response;
    }

    protected function createNewsResponse(array $request, array $articles)
    {
        $response = $this->createResponse($request, self::MSG_NEWS);
        $response['ArticleCount'] = count($articles);
        $response['Articles'] = $articles;
        return $response;
    }

    protected function createSingleNewsResponse(array $request, $title = null, $description = null, $picUrl = null, $url = null)
    {
        $response = $this->createResponse($request, self::MSG_NEWS);
        $response['ArticleCount'] = 1;
        $response['Articles'] = array(
            array('Title' => $title, 'Description' => $description, 'PicUrl' => $picUrl, 'Url' => $url)
        );
        return $response;
    }

}