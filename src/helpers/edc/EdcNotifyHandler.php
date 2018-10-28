<?php

namespace xionglonghua\common\helpers\edc;

use Yii;
use xionglonghua\common\forms\edc\EdcFinishNotifyForm;
use xionglonghua\common\forms\edc\EdcNotifyConfForm;
use xionglonghua\common\forms\edc\EdcRepeatNotifyForm;
use xionglonghua\common\helpers\ArrayHelper;
use yii\base\Component;
use yii\db\ActiveRecord;

/**
 * Class EdcNotifyHandler
 * EdcNotify 的处理类 后续的所有处理类可以继承这个类直接使用 来处理id的扭转和变更
 * NOTE: 注意一个类只对应一种主题队列, 比如只对应edcInvestment或者edcUp
 * NOTE: 推荐common config里面配置 专属log
 * [
 *      'class' => 'yii\log\FileTarget',
 *      'levels' => ['error', 'warning', 'info'],
 *      'logFile' => "@app/runtime/logs/edcNotifyHandler.log",
 *      'categories' => ["edcNotifyHandler.*"],
 *      'logVars' => [],
 *  ],
 */
class EdcNotifyHandler extends Component
{
    /**
     * @var array
     *            0 如果表中字段就是id/edcId 那么就只填class名就可以了
     *            1 className 对fromType进行处理的AR model类名
     *            2 id fromId在表中的字段名
     *            3 edcId edcId在表中的字段名
     *            fromType => [
     *            Model2::class,
     *            ['className' => Model1::class, 'id' => 'id', 'edcId' => 'businessId'],
     *            ]
     *            demo1:
     *            FromType::FROM_PERSION_BUSINESS => PersonBusinessForm::class,
     *            demo2:
     *            FromType::FROM_PERSION_BUSINESS => ['className' => PersonBusinessForm::class, 'id' => 'id', 'edcId' => 'upId'],
     *            demo3:
     *            FromType::FROM_PERSION_BUSINESS => [
     *            ['className' => PersonBusinessForm::class, 'id' => 'id', 'edcId' => 'upId'],
     *            ['className' => PersonBusinessForm::class, 'id' => 'id', 'edcId' => 'upId'],
     *            ...
     *            ],
     */
    public static $fromTypeModels = [
    ];

    const EVENT_TYPE_ON_FINISH = 'onFinish';
    const EVENT_TYPE_ON_REPEAT = 'onRepeat';
    const EVENT_TYPE_ON_DATA_FINISH = 'onDataFinish';
    const EVENT_TYPE_ON_DATA_UPDATE = 'onDataUpdate';
    const EVENT_TYPE_ON_DATA_REPEAT = self::EVENT_TYPE_ON_DATA_UPDATE;
    const EVENT_TYPE_ON_DATA_NEW = 'onDataNew';

    /*
     * update ** set edcId = edcId where id = fromId
     */
    public static function handleFinish($data = [])
    {
        return self::_handle(EdcFinishNotifyForm::class, $data);
    }

    /*
     * update ** set edcId = mainId where edcId = edcId
     */
    public static function handleRepeat($data = [])
    {
        if (static::$fromTypeModels) {
            foreach (static::$fromTypeModels as $fromType => $conf) {
                $data['fromType'] = $fromType;
                self::_handle(EdcRepeatNotifyForm::class, $data);
            }
        }
        return true;
    }

    /**
     * @param EdcFinishNotifyForm | EdcRepeatNotifyForm $class
     * @param array                                     $data
     *
     * @return bool
     */
    private static function _handle($class, $data = [])
    {
        $notifyDataForm = new $class($data);
        /** @var EdcFinishNotifyForm | EdcRepeatNotifyForm $notifyDataForm */
        if (!$notifyDataForm->validate()) {
            Yii::info(['data' => $data, 'errors' => $notifyDataForm->errors], 'edcNotifyHandler.data.validate.error');
            return false;
        }
        $confArrs = ArrayHelper::getValue(static::$fromTypeModels, $notifyDataForm->fromType, []);
        // 这种情况基本上是业务方不用处理此类id 扔回队列没有意义
        if (empty($confArrs)) {
            Yii::info(['data' => $data, 'confArrs' => $confArrs], 'edcNotifyHandler.config.empty');
            return true;
        }
        return self::handleNotifyConfs($confArrs, $notifyDataForm);
    }

    private static function handleNotifyConfs($confArrs, $notifyDataForm)
    {
        if (!is_array($confArrs) || !ArrayHelper::isIndexed($confArrs)) {
            return self::handleNotifyConf($confArrs, $notifyDataForm);
        } else {
            foreach ($confArrs as $confArr) {
                self::handleNotifyConf($confArr, $notifyDataForm);
            }
        }
        return true;
    }

    /**
     * @param array | ActiveRecord                      $confArr
     * @param EdcFinishNotifyForm | EdcRepeatNotifyForm $notifyDataForm
     */
    private static function handleNotifyConf($confArr, $notifyDataForm)
    {
        if (!is_array($confArr)) {
            $confArr = ['className' => $confArr, 'id' => 'id', 'edcId' => 'edcId'];
        }
        $notifyConfForm = new EdcNotifyConfForm($confArr);
        if (!$notifyConfForm->validate()) {
            Yii::info(['data' => $notifyDataForm->attributes, 'confArr' => $confArr, 'errors' => $notifyConfForm->errors], 'edcNotifyHandler.config.validate.error');
            return false;
        }
        $category = 'edcNotifyHandler.success.updateAll.' . $notifyDataForm->getNotifyType();
        if ($notifyDataForm->getNotifyType() == EdcNotifyHandler::EVENT_TYPE_ON_FINISH) {
            ($notifyConfForm->className)::updateAll([$notifyConfForm->edcId => $notifyDataForm->edcId], [
                'AND',
                [$notifyConfForm->id => $notifyDataForm->fromId],
            ]);
        } elseif ($notifyDataForm->getNotifyType() == EdcNotifyHandler::EVENT_TYPE_ON_REPEAT) {
            ($notifyConfForm->className)::updateAll([$notifyConfForm->edcId => $notifyDataForm->mainId], [
                'AND',
                [$notifyConfForm->edcId => $notifyDataForm->edcId],
            ]);
        }
        Yii::info(['data' => $notifyDataForm->attributes, 'confArr' => $confArr], $category);
        return true;
    }
}
