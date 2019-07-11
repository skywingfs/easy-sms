<?php
/**
 * Author: skywing
 * Date: 2018/11/29
 * Time: 上午11:21
 * Describe:
 */

namespace Overtrue\EasySms\Gateways;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * Class MengwangShortGateway.
 *
 * @see http://con.monyun.cn:9960/developer_Center/index.html?htmlURL1=API&htmlURL2=APIone&iden=1350605183813637187
 */
class MengwangShortGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'http://%s/sms/%s/std/%s';

    const ENDPOINT_VERSION = 'v2';

    const ENDPOINT_ACTION = 'single_send';

    const ENDPOINT_FORMAT = 'json';

    /**
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface $message
     * @param \Overtrue\EasySms\Support\Config $config
     * @return array
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException ;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $data = $message->getData();
        $action = array_key_exists('action', $data) ? $data['action'] : self::ENDPOINT_ACTION;
        $host = $config->get('host');
        $endpoint = $this->buildEndpoint($host, $action);
        $userid = $config->get('userid');
        $pwd = $config->get('pwd');
        $timestamp = date('mdHis', time());
        $result = $this->request('post', $endpoint, [
            'json' => [
                'userid' => $userid,
                'pwd' => $this->buildPwd($userid, $pwd, $timestamp),
                'mobile' => $to->getNumber(),
                'content' => urlencode(iconv('UTF-8', 'GBK', $message->getContent($this))),
                'timestamp' => $timestamp,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json;charset=utf-8',
            ],
        ]);
//        if (0 != $result['result']) {
//            throw new GatewayErrorException($result['result'], $result['result'], $result);
//        }

        return $result;
    }

    /**
     * Build endpoint url.
     * @param string $action
     * @return string
     */
    protected function buildEndpoint($host, $action)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $host, self::ENDPOINT_VERSION, $action);
    }

    /**
     * Build password.
     * @param $userid
     * @param $pwd
     * @param $timestamp
     * @return string
     */
    protected function buildPwd($userid, $pwd, $timestamp)
    {
        $beforeMd5 = $userid . '00000000' . $pwd . $timestamp;
        return md5($beforeMd5);
    }

}
