<?php

namespace xionglonghua\common\dataproviders;

use xionglonghua\common\forms\WebDPFieldForm;
use xionglonghua\curl\CurlHttp;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;
use yii\data\Sort;

/**
 * WebDataProvider implements a data provider based on a data web interface which supports limit & offset
 * NOTE 要求接口返回的格式类似, 也可以自己定义
 * [
 *   code =>
 *   data => [
 *      'total' => (int),
 *      'items' => []
 *  ]
 * ]
 * ```php
 *     $searchArr = ['query' => '大',];
 *      $provider = new WebDataProvider([
 *          'instance' => Yii::$app->upsearch,
 *          'url' => '/up/searchonline',
 *          'params' => $searchArr,
 *          'sort' => [
 *              'attributes' => ['id'],
 *          ],
 *          'pagination' => [
 *              'pageSize' => 2,
 *          ],
 *          'fieldMap' => ['code' => 'code', 'total' => 'data.total', 'items' => 'data.items']
 *       ]);
 * $rows = $provider->getModels();
 * ```
 */
class WebDataProvider extends BaseDataProvider
{
    /**
     * @var CurlHttp
     */
    public $instance;
    public $url;
    public $method = CurlHttp::METHOD_GET;
    public $fieldMap = '';
    public $totalCountCallback;
    /* @var WebDPFieldForm */
    protected $webDPField = null;
    /**
     * @var \Closure 对接口返回的models数据进行处理
     *               可以自定义转换一下接口的原始models数据, 比如实现根据返回的ids 查出AR以及关联的with Relations来简化操作
     *               demo:
     *               $dataProvider->afterWebQuery = function ($models) use ($sqlSort) {
     *               $ids = ArrayHelper::getColumn($models, ['id']);
     *               return self::find()->where(['id' => $ids])->orderBy($sqlSort)->with('up', 'source', 'claimedUser', 'baseLeads', 'tags')->all();
     *               };
     */
    public $afterWebQuery = null;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string|callable the column that is used as the key of the data models.
     *                      This can be either a column name, or a callable that returns the key value of a given data model.
     *                      If this is not set, the index of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;

    private static $_defaultFieldMap = ['code' => 'code', 'total' => 'data.total', 'items' => 'data.items'];

    public function init()
    {
        if (!$this->loadFieldMap()) {
            throw new InvalidConfigException('The "fieldMap" property must be an array which defines the format of result');
        }
        if (!is_null($this->afterWebQuery) && !($this->afterWebQuery instanceof \Closure)) {
            throw new InvalidConfigException('如果使用afterWebQuery必须是closure');
        }
        parent::init();
    }

    private function loadFieldMap()
    {
        $result = false;
        !$this->fieldMap && $this->fieldMap = self::$_defaultFieldMap;
        $this->webDPField = new WebDPFieldForm();
        if (is_array($this->fieldMap)) {
            $result = $this->webDPField->load($this->fieldMap, '') && $this->webDPField->validate();
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        $models = [];
        if (!$this->instance instanceof CurlHttp) {
            throw new InvalidConfigException('The "instance" property must be an instance of CurlHttp');
        }

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();

            if ($pagination->totalCount === 0) {
                return [];
            }
            $params = $this->params;
            $params['limit'] = $pagination->getLimit();
            $params['offset'] = $pagination->getOffset();

            if (($sort = $this->getSort()) !== false) {
                $sortParams = $this->genSortParams($sort);
                $sortParams && $params['sort'] = $sortParams;
            }

            $ret = $this->instance->setMethod($this->method)->httpExec($this->url, $params);
            if (!empty($ret) && is_array($ret)) {
                if ($ret[$this->webDPField->code] == 0) {
                    $models = ArrayHelper::getValue($ret, $this->webDPField->items, []);
                }
            }
        }
        return is_null($this->afterWebQuery) ? $models : call_user_func($this->afterWebQuery, $models);
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        $totalCount = 0;
        if (!$this->instance instanceof CurlHttp) {
            throw new InvalidConfigException('The "container" property must be an instance of CurlHttp');
        }
        if ($this->totalCountCallback instanceof \Closure) {
            return call_user_func($this->totalCountCallback, $this);
        }
        $ret = $this->instance->setMethod($this->method)->httpExec($this->url, $this->params);

        if (!empty($ret) && is_array($ret)) {
            if ($ret[$this->webDPField->code] == 0) {
                $totalCount = ArrayHelper::getValue($ret, $this->webDPField->total, 0);
            }
        }
        return (int) $totalCount;
    }

    /**
     * generate sort params in url (such as '-id,projectname')
     *
     * @param Sort $sort the sort definition
     *
     * @return array the sorted data models
     */
    protected function genSortParams($sort)
    {
        $orders = $sort->getOrders();
        $attributes = array_keys($orders);
        $sortParams = '';
        $sortParamsArr = [];
        if ($attributes) {
            foreach ($attributes  as $attribute) {
                $sortParamsArr[] = $sort->createSortParam($attribute);
            }
            $sortParams = implode($sortParamsArr, ',');
        }
        return $sortParams;
    }
}
