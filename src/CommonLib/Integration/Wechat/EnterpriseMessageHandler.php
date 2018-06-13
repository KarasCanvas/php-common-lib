<?php
namespace CommonLib\Integration\Wechat;

/**
 * @summary 微信企业号消息处理程序 <http://dwz.cn/Rjrpd>
 * @author Raven <karascanvas@qq.com>
 */
abstract class EnterpriseMessageHandler extends MessageHandler
{
    protected static $eventHandlers = array(
        'subscribe'          => 'onSubscribeEvent',
        'unsubscribe'        => 'onUnSubscribeEvent',
        'scan'               => 'onScanEvent',
        'location'           => 'onLocationEvent',
        'click'              => 'onClickEvent',
        'view'               => 'onViewEvent',
        'scancode_push'      => 'onScanCodePushEvent',
        'scancode_waitmsg'   => 'onScanCodeWaitMsgEvent',
        'pic_sysphoto'       => 'onSysPhotoPicEvent',
        'pic_photo_or_album' => 'onPhotoOrAlbumPicEvent',
        'pic_weixin'         => 'onWeixinPicEvent',
        'location_select'    => 'onLocationSelectEvent',
        'enter_agent'        => 'onEnterAgentEvent',
        'batch_job_result'   => 'onBatchJobResultEvent',
    );


    public function handle($xml, $output = true)
    {
        $msg = XmlUtils::deserialize($xml);
        $msg = $this->decrypt($msg);
        $response = $this->handleMessage($msg);
        if ($response === null && $this->defaultEnabled) {
            $response = $this->defaultResponse($msg);
        }
        $response = $this->encrypt($response);
        if ($output) {
            $this->respond($response);
        }
        return $response;
    }


    protected function encrypt(array $message)
    {
        // todo impl EnterpriseMessageHandler.encrypt
        return $message;
    }


    protected function decrypt(array $message)
    {
        // todo impl EnterpriseMessageHandler.decrypt
        return $message;
    }


    protected function onScanCodePushEvent($message) { }

    protected function onScanCodeWaitMsgEvent($message) { }

    protected function onSysPhotoPicEvent($message) { }

    protected function onPhotoOrAlbumPicEvent($message) { }

    protected function onWeixinPicEvent($message) { }

    protected function onLocationSelectEvent($message) { }

    protected function onEnterAgentEvent($message) { }

    protected function onBatchJobResultEvent($message) { }

}
