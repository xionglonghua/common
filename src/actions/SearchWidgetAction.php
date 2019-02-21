<?php

namespace xionglonghua\common\actions;

use xionglonghua\common\dataproviders\WebDataProvider;
use lspbupt\curl\CurlHttp;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use Closure;

class SearchWidgetAction extends Action
{
    public $limit = 5;
    public $searchUrl;
    public $searchInstance;
    public $markFields;
    public $searchView;
    public $searchDetailView;
    // 不用default的search 自定义processQuery函数
    public $processQuery;
    public $userId = 0;
    public $multiQuery = null;
    public $query;
    public $formulaName;

    public function init()
    {
        parent::init();
        // 自定义process
        if ($this->processQuery) {
            if (!($this->processQuery instanceof Closure)) {
                throw new InvalidConfigException('查询必须是closure');
            }
        } else {
            // 默认process
            if (empty($this->searchInstance) || empty($this->searchUrl)) {
                throw new InvalidConfigException('请配置对应的SearchInstance和SearchUrl');
            }
            if (!$this->searchInstance instanceof CurlHttp) {
                throw new InvalidConfigException('searchInstance必须是CurlHttp');
            }
        }
        $this->filterMarkFields();
    }

    private function filterMarkFields()
    {
        $markFields = '';
        if ($this->markFields) {
            is_string($this->markFields) && $this->markFields = [$this->markFields];
            $markFieldsArr = array_filter($this->markFields);
            $markFieldsArr && $markFields = implode(',', array_unique($markFieldsArr));
        }
        $this->markFields = $markFields;
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        $query = $this->query;
        empty($query) && $query = Yii::$app->request->get('_searchWidgetQuery', '');

        if ($this->processQuery) {
            $provider = call_user_func($this->processQuery, $query);
        } else {
            $params = ['query' => $query, 'markFields' => $this->markFields, 'userId' => $this->userId, 'formulaName' => $this->formulaName];
            $this->multiQuery && $params['multiQuery'] = $this->multiQuery;
            $provider = new WebDataProvider([
                'instance' => $this->searchInstance,
                'url' => $this->searchUrl,
                'params' => $params,
                'pagination' => ['pageSize' => $this->limit],
            ]);
        }

        return $this->controller->render($this->searchView, [
            'dataProvider' => $provider,
            'searchDetailView' => $this->searchDetailView,
        ]);
    }
}
