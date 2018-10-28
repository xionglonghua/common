<?php

namespace xionglonghua\common\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\base\Application;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\helpers\UnsetArrayValue;
use koenigseggposche\yii2mns\Mns;

//todo 支持配置uuid
class ULogBehavior extends Behavior
{
    const TYPE_CREATE = 0x1;
    const TYPE_READ = 0x2;
    const TYPE_UPDATE = 0x4;
    const TYPE_DELETE = 0x8;
    private const MNS_QUEUE = 'uLog';

    public $mask = 0xFFFF & ~self::TYPE_READ;
    /**
     * @var array attributes the only attributes to be in the log, ignore `$excepts`.
     *            AttrBehavior attributes or other getter attr can be in here.
     */
    public $attributes = [];
    /**
     * @var array excepts when attributes is empty, these attributes would not be in the log
     */
    public $excepts = [
        'updateTime',
        'dt_modtime',
    ];

    /**
     * @var string mns component key
     */
    public $mns = 'mns';
    /**
     * @var Mns mns instance
     */
    private static $_mns;

    /**
     * @var array _store
     */
    private static $_store = [];

    public function init()
    {
        parent::init();
        self::firstBind($this->mns);
    }

    public function events()
    {
        //todo AFTER_EVENT 来代替before
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'storeAfterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'storeAfterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'storeBeforeUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'storeBeforeDelete',
        ];
    }

    public static function onAfterAction($event): void
    {
        foreach (self::$_store as $model) {
            if ($model->canSend() && $model->buildDisplay()) {
                try {
                    self::$_mns->{self::MNS_QUEUE}->send($model);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }

    public function storeAfterFind($event): void
    {
        if ($model = $this->store($event->sender)) {
            $model->type |= self::TYPE_READ;
            $model->old = $this->filteredAttributes($event->sender);
        }
    }

    public function storeBeforeUpdate($event): void
    {
        if ($model = $this->store($event->sender)) {
            $model->type |= self::TYPE_UPDATE;
            $dirties = array_keys($event->sender->dirtyAttributes);
            $model->new = $this->filteredAttributes($event->sender);
            $this->filterDirty($model->new, $model->old);
        }
    }

    public function storeAfterInsert($event): void
    {
        if ($model = $this->store($event->sender)) {
            $model->type |= self::TYPE_CREATE;
            $dirties = array_keys($event->sender->dirtyAttributes);
            $model->old = [];
            $model->new = $this->filteredAttributes($event->sender);
        }
    }

    public function storeBeforeDelete($event): void
    {
        if ($model = $this->store($event->sender)) {
            $model->type |= self::TYPE_DELETE;
        }
    }

    private function filteredAttributes(ActiveRecord $sender): array
    {
        if (!empty($this->attributes)) {
            if ($allAttributes = ArrayHelper::removeValue($this->attributes, '*')) {
                $this->attributes = array_unique(array_merge($this->attributes, array_keys($this->owner->attributes)));
            }
            foreach ($this->attributes as $attr) {
                $attributes[$attr] = $sender->$attr;
            }
        } elseif (!empty($this->excepts)) {
            $attributes = ArrayHelper::merge(
                $sender->attributes,
                array_combine(
                    $this->excepts, array_pad([], count($this->excepts), new UnsetArrayValue())
                )
            );
        }
        return $attributes;
    }

    private function filterDirty(array &$old, array &$new): void
    {
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old)) {
                continue;
            }
            if ($value === $old[$key]) {
                unset($old[$key]);
                unset($new[$key]);
            }
        }
    }

    private function store(ActiveRecord $sender): ?_ULOG
    {
        if (!$this->owner instanceof ActiveRecord) {
            throw new InvalidConfigException('本行为仅能由AR使用');
        }
        $dsn = $sender->db->dsn;
        preg_match('/dbname=([^;]*)/', $dsn, $match);
        $db = $match[1];
        $tableName = $sender->tableName();
        $token = "$db|$tableName";
        $primaryKeys = $sender->getPrimaryKey(true);
        $modelId = implode('|', $primaryKeys);
        $pks = implode('|', array_keys($primaryKeys));
        $hash = md5("$db|$tableName|$pks|$modelId");
        if (!isset(self::$_store[$hash])) {
            self::$_store[$hash] = new _ULOG([
                'userId' => Yii::$app->user->id ?? 0,
                'db' => $db,
                'tableName' => $tableName,
                'model' => get_class($sender),
                'modelId' => $modelId,
                'pks' => $pks,
                'mask' => $this->mask,
                'labels' => $sender->attributeLabels(),
            ]);
        }
        return self::$_store[$hash];
    }

    private static function firstBind(string $mns): void
    {
        if (!self::$_mns) {
            $mns = Yii::$app->{$mns} ?? null;
            if (!$mns || !$mns instanceof Mns) {
                throw new InvalidConfigException('使用通用日志，必须设置消息队列进行日志收集。');
            } else {
                self::$_mns = $mns;
            }
            Yii::$app->on(Application::EVENT_AFTER_ACTION, [self::class, 'onAfterAction']);
        }
    }
}

class _ULOG extends \yii\base\BaseObject
{
    public $userId;
    public $db;
    public $tableName;
    public $model;
    public $modelId;
    public $pks;
    public $type = 0;
    public $mask = 0;

    public $labels = [];
    public $display = [];
    public $old = [];
    public $new = [];

    public function canSend()
    {
        return $this->type & $this->mask;
    }

    public function buildDisplay()
    {
        if (!$this->model || !class_exists($this->model)) {
            return false;
        }
        $keys = array_unique(array_merge(array_keys($this->old), array_keys($this->new)));
        foreach ($keys as $key) {
            $label = ArrayHelper::getValue($this->labels, $key, $key);
            $old = ArrayHelper::getValue($this->old, $key, null);
            $new = ArrayHelper::getValue($this->new, $key, null);
            $this->display[] = "$label [$old] => [$new]";
        }
        return !empty($this->display);
    }

    public function __toString()
    {
        unset($this->labels);
        return json_encode($this, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
    }
}
