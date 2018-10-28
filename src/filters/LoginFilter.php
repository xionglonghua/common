<?php

namespace xionglonghua\common\filters;

use Yii;
use yii\web\Response;
use yii\base\ActionFilter;
use xionglonghua\common\helpers\SysMsg;

class LoginFilter extends ActionFilter
{
    /**
     * Actions that login filter applys
     *
     * @var array
     */
    public $actions = [];

    /**
     * login token name
     *
     * @var string|array
     */
    public $tokenName = ['token', 'access_token'];

    /**
     * login app name
     * posible values: appname
     * posible values: [appname, _key]
     *
     * @var string|array
     */
    public $appnameName = 'appname';

    /**
     * token position: header, get, post, cookie
     * posible values: header
     * posible values: [header, post]
     *
     * @var string|array
     */
    public $tokenPosition = ['get', 'header', 'post'];

    /**
     * loginType has three options
     * forceLogin: user must login
     * tryLogin: user try login first, if failed, can also access
     * noLogin: user not neccessary to login, no invoke of login process
     *
     * @var string
     */
    public $loginType = 'forceLogin';

    /**
     * login processor: passport, sso, callable
     *
     * @var string
     */
    public $processor = 'passport';

    /**
     * component name in passport mode
     *
     * @var string
     */
    public $passportName = 'passport';

    /**
     * component name in sso mode
     *
     * @var string
     */
    public $ssoName = 'sso';

    /**
     * output format when failed
     *
     * @var string
     */
    public $failFormat = Response::FORMAT_JSON;

    /**
     * login url
     *
     * @var string
     */
    public $loginUrl;

    public function beforeAction($action)
    {
        if (!in_array($action->id, $this->actions)) {
            return true;
        }
        if (!in_array($this->loginType, ['tryLogin', 'forceLogin'])) {
            return true;
        }

        // Token
        $token = $appname = '';
        foreach ((array) $this->tokenPosition as $position) {
            foreach ((array) $this->tokenName as $tokenName) {
                $ret = $this->getValueByPosition($tokenName, $position);
                $token = $ret ?: $token;
            }
            foreach ((array) $this->appnameName as $appnameName) {
                $ret = $this->getValueByPosition($appnameName, $position);
                $appname = $ret ?: $appname;
            }
        }

        // 登录
        $user = null;
        if ($this->processor == 'passport') {
            Yii::$app->{$this->passportName}->getUserFromCache($token, $user);
            $user && Yii::$app->user->login($user);
        } elseif ($this->processor == 'sso') {
            Yii::$app->{$this->ssoName}->getUserFromCache($token, $user);
            $user && Yii::$app->user->login($user);
        } elseif (is_callable($this->processor)) {
            call_user_func_array($this->processor, [$appname, $token, &$user]);
        }

        if (!$user && $this->loginType == 'forceLogin') {
            if ($this->failFormat == \yii\web\Response::FORMAT_JSON) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Yii::$app->response->data = SysMsg::getErrData('F_OUTFILTER_USER_NOTLOGIN');
                Yii::$app->response->send();
            } else {
                $loginUrl = $this->loginUrl ?: Yii::$app->user->loginUrl;
                Yii::$app->response->redirect($loginUrl);
                Yii::$app->response->send();
            }
            return false;
        }
        return true;
    }

    protected function getValueByPosition($name, $position)
    {
        if ($position == 'get') {
            return Yii::$app->request->get($name, '');
        } elseif ($position == 'post') {
            return Yii::$app->request->post($name, '');
        } elseif ($position == 'header') {
            return Yii::$app->request->getHeaders()->get($name, '', true);
        } elseif ($position == 'cookie') {
            return Yii::$app->request->getCookies()->getValue($name, '');
        } else {
            return null;
        }
    }
}

SysMsg::register('F_OUTFILTER_USER_NOTLOGIN', '当前用户未登录', 10);
