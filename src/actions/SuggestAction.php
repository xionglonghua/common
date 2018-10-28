<?php

namespace xionglonghua\common\actions;

use xionglonghua\common\helpers\ArrayHelper;
use xionglonghua\common\helpers\SysMsg;
use xionglonghua\curl\CurlHttp;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;

class SuggestAction extends Action
{
    public $limit = 10;
    public $suggestUrl;
    public $suggestInstance;
    public $queryKey = '';

    public function init()
    {
        parent::init();
        if (empty($this->suggestInstance) || empty($this->suggestUrl)) {
            throw new InvalidConfigException('请配置对应的suggestInstance和SuggestUrl');
        }
        if (!$this->suggestInstance instanceof CurlHttp) {
            throw new InvalidConfigException('suggestInstance必须是CurlHttp');
        }
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Yii::$app->request->get('_suggestWidgetQuery', '');
        $retArr = [];
        $params = ['query' => $query, 'limit' => $this->limit];
        $this->queryKey && $params['queryKey'] = $this->queryKey;
        $ret = $this->suggestInstance->setMethod(CurlHttp::METHOD_GET)->httpExec($this->suggestUrl, $params);

        if (!empty($ret) && is_array($ret)) {
            if ($ret['code'] == 0) {
                $retArr = ArrayHelper::getRightValue($ret, 'data', []);
            }
        }
        return SysMsg::getOkData($retArr);
    }
}
