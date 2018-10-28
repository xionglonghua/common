<?php
use yii\widgets\ListView;

$this->title = $title;
?>
<div class="common-search">
    <p></p>
    <?php
        echo $this->renderFile($viewSourcePath . '/' . '_search.php', ['searchUrl' => $searchUrl, 'query' => $query]);
    ?>
</div>

<div class="panel panel">
    <div class="panel panel-body">
        <div class="common-search">
            <?= ListView::widget([
                'options' => [
                    'tag' => 'div',
                    'class' => 'list-wrapper',
                    'id' => 'list-wrapper',
                ],
                'dataProvider' => $dataProvider,
                'itemView' => function ($model, $key, $index, $widget) use ($searchDetailView) {
                    $itemContent = $this->render($searchDetailView, ['model' => $model]);
                    return $itemContent;
                },
                'itemOptions' => [
                    'tag' => false,
                ],

                'layout' => "{summary}\n{items}\n{pager}",
                'pager' => [
                    'firstPageLabel' => 'First',
                    'lastPageLabel' => 'Last',
                    'maxButtonCount' => 10,
                    'options' => [
                        'class' => 'pagination',
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>

