<?php

namespace xionglonghua\common\filters;

use Yii;
use xionglonghua\common\helpers\OuterHelper;
use xionglonghua\common\helpers\SysMsg;
use yii\base\ActionFilter;

//外部接口行为
class OuterFilter extends ActionFilter
{
    public $actions = [];
    //允许的状态，allow优先
    public $allowStatus = [0, 5, 10, 1000, -1];

    public $checkSign = true;

    public $verifyUrl = '/verify/bindphone';

    public $checkStatus = [4];

    public function beforeAction($action)
    {
        $actionId = $action->id;
        if (in_array($actionId, $this->actions)) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $access_token = Yii::$app->request->post('access_token', '');
            if (empty($access_token)) {
                $access_token = Yii::$app->request->getHeaders()->get('access_token', '');
            }
            if ($this->checkSign) {
                $dataArr = [];
                $mobile = isset(Yii::$app->mobile) ? Yii::$app->mobile : null;
                if ($mobile && ($mobile->isH5() || $mobile->isSmallApp())) {
                    $authKey = Yii::$app->request->getHeaders()->get('X-token-1', '');
                    $signature = Yii::$app->request->getHeaders()->get('X-token-2', '');
                    if (empty($authKey) || empty($signature)) {
                        Yii::info(array_merge(Yii::$app->request->getHeaders()->toArray(), $_POST), 'sign.h5error');
                        $ret = 'F_OUTFILTER_USER_NOTLOGIN';
                    } else {
                        $ret = OuterHelper::checkWebAjaxSign($authKey, $signature, $dataArr);
                    }
                } else {
                    $ret = OuterHelper::checkSign($access_token, $dataArr);
                }
                //当解密不成功时，返回数据
                if ($ret) {
                    if ($mobile && ($mobile->isIOS() || $mobile->isAndroid()) && $ret == OuterHelper::EMPTY_TOKEN_RET) {
                        \Yii::$app->response->data = SysMsg::getOkData('');
                    } else {
                        \Yii::$app->response->data = SysMsg::getErrData($ret);
                    }
                    \Yii::$app->response->send();
                    return false;
                }
                //检查签名
                // 根据获取的token来获取用户
                $token = $dataArr['token'];
            } else {
                $token = $access_token;
            }
            $user = null;
            $ret = Yii::$app->sso->getUserFromCache($token, $user);
            //用户未登录
            if ($ret) {
                \Yii::$app->response->data = SysMsg::getErrData('F_OUTFILTER_USER_NOTLOGIN');
                \Yii::$app->response->send();
                return false;
            }
            if (in_array($user->status, $this->checkStatus)) {
                /**
                 * 临时对小程序的绑定跳转hack，跳转绑定页的行为返回未登录。
                 * 发版之后下掉。
                 */
                $mobile = isset(Yii::$app->mobile) ? Yii::$app->mobile : null;
                if ($mobile && $mobile->isSmallApp()) {
                    \Yii::$app->response->data = SysMsg::getErrData('F_OUTFILTER_USER_NOTLOGIN');
                    \Yii::$app->response->send();
                    return false;
                }
                Yii::$app->sso->setAction($this->verifyUrl);
                $verifyUrl = Yii::$app->sso->getUrl().'?appname='.Yii::$app->sso->appkey;
                \Yii::$app->response->data = ['code' => 15, 'message' => '', 'data' => ['verifyUrl' => $verifyUrl]];
                \Yii::$app->response->send();
                return false;
            }
            //状态不正确，因此不能登录
            if (!in_array($user->status, $this->allowStatus)) {
                \Yii::$app->response->data = SysMsg::getErrData('F_OUTFILTER_USER_STATUSERR');
                \Yii::$app->response->send();
                return false;
            }
            Yii::$app->user->login($user);
        }
        return true;
    }
}

SysMsg::register('F_OUTFILTER_USER_NOTLOGIN', '当前用户未登录', 10);
SysMsg::register('F_OUTFILTER_USER_STATUSERR', '抱歉，您的账号已被封禁，如有疑问可通过邮件vc@ethercap.com或微信juliayitai 反馈', 1020);
