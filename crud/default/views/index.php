<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator \mootensai\enhancedgii\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$tableSchema = $generator->getTableSchema();
$baseModelClass = StringHelper::basename($generator->modelClass);
$fk = $generator->generateFK($tableSchema);
echo "<?php\n";
?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\grid\\GridView;" : "yii\\widgets\\ListView;" ?>


$this->title = <?= ($generator->pluralize) ? $generator->generateString(Inflector::pluralize(Inflector::camel2words($baseModelClass))) : $generator->generateString(Inflector::camel2words($baseModelClass)) ?>;
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);
?>
<div class="<?= Inflector::camel2id($baseModelClass) ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if (!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Create ' . Inflector::camel2words($baseModelClass)) ?>, ['create'], ['class' => 'btn btn-success']) ?>
<?php if (!empty($generator->searchModelClass)): ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Advance Search')?>, '#', ['class' => 'btn btn-info search-button']) ?>
<?php endif; ?>
    </p>
<?php if (!empty($generator->searchModelClass)): ?>
    <div class="search-form" style="display:none">
        <?= "<?= " ?> $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <?php endif; ?>
<?php 
if ($generator->indexWidgetType === 'grid'): 
?>
<?= "<?php \n" ?>
    $gridColumn = [
        ['class' => 'yii\grid\SerialColumn'],
<?php
    if ($generator->expandable && !empty($fk)):
?>
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                return Yii::$app->controller->renderPartial('_expand', ['model' => $model]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true
        ],
<?php
    endif;
?>
<?php   
    if ($tableSchema === false) :
        foreach ($generator->getColumnNames() as $name) {
            if (++$count < 6) {
                echo "            '" . $name . "',\n";
            } else {
                echo "            // '" . $name . "',\n";
            }
        }
    else :
        foreach ($tableSchema->getColumnNames() as $attribute): 
            if (!in_array($attribute, $generator->skippedColumns)) :
?>
        <?= $generator->generateGridViewFieldIndex($attribute, $fk, $tableSchema)?>
<?php
            endif;
        endforeach; ?>
        [
            'class' => 'yii\grid\ActionColumn',
<?php if($generator->saveAsNew): ?>
            'template' => '{save-as-new} {view} {update} {delete}',
            'buttons' => [
                'save-as-new' => function ($url) {
                    return Html::a('<span class="glyphicon glyphicon-copy"></span>', $url, ['title' => 'Save As New']);
                },
            ],
<?php endif; ?>
        ],
    ]; 
<?php 
    endif; 
?>
    ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => \$gridColumn,\n" : "'columns' => \$gridColumn,\n"; ?>
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-<?= Inflector::camel2id(StringHelper::basename($generator->modelClass))?>']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
        ],
        <?php if(!$generator->pdf) : ?>
        'export' => [
            'label' => Yii::t('kvgrid','Page'),
        ],
        'exportConfig' =>[
            GridView::CSV => true,
            GridView::PDF => true,
            GridView::EXCEL => true,
        ],
<?php else : ?>
        'export' => false,
<?php endif; ?>
        // your toolbar can include the additional full export menu
        'toolbar' => [
            '{export}',
            ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'columns' => $gridColumn,
            'target' => ExportMenu::TARGET_BLANK,
            'fontAwesome' => true,
            'dropdownOptions' => [
                'label' => Yii::t('kvgrid','Full'),
                'class' => 'btn btn-default',
                'itemsBefore' => [
                    '<li class="dropdown-header">'.Yii::t('kvgrid', 'Export All Data').'</li>',
                    ],
                'title'=> Yii::t('kvgrid', 'Export All Data')
            ],
            'columnBatchToggleSettings'=>[
                'label'=> Yii::t('kvgrid', 'Select Columns')
            ],
            'columnSelectorOptions'=>[
                'title'=> Yii::t('kvgrid', 'Select Columns To export')
            ],
            'messages'=>[
                'confirmDownload'=>Yii::t('kvgrid','Ok to proceed ?'),
                'allowPopups'=>Yii::t('kvgrid','Disable any popup blockers in your browser to ensure proper download.'),
            ],
            <?php if(!$generator->pdf):?>
            'exportConfig' => [
                ExportMenu::FORMAT_HTML => false,
                ExportMenu::FORMAT_TEXT => false,
                ExportMenu::FORMAT_EXCEL => false,
                ExportMenu::FORMAT_CSV => [
                    'alertMsg' => Yii::t('kvgrid', 'The {fileType} export file will be generated for download.'
                    ,['fileType'=>'CSV']),
                ],
                ExportMenu::FORMAT_PDF => [
                    'alertMsg' => Yii::t('kvgrid', 'The {fileType} export file will be generated for download.'
                    ,['fileType'=>'PDF']),
                ],
                ExportMenu::FORMAT_EXCEL_X => [
                    'alertMsg' => Yii::t('kvgrid', 'The {fileType} export file will be generated for download.'
                    ,['fileType'=>'EXCEL 2007+ (xlsx)']),
                ],
            ]
        <?php else : ?>
            'exportConfig' => [
                ExportMenu::FORMAT_PDF => false
            ]
        <?php endif;?>
            ]) ,
        ],
        'krajeeDialogSettings' => [
            'options' => [
                'title' => Yii::t('common', 'Confirmation Message'),
                'btnCancelLabel' =>   '<i class="glyphicon glyphicon-ban-circle"></i> ' . Yii::t('common', 'No'),
                'btnOKLabel' => '<i class="glyphicon glyphicon-ok"></i> ' . Yii::t('common', 'Yes'),
            ]
        ],
    ]); ?>
<?php 
else: 
?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return $this->render('_index',['model' => $model, 'key' => $key, 'index' => $index, 'widget' => $widget, 'view' => $this]);
        },
    ]) ?>
<?php 
endif; 
?>

</div>
