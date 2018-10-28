<?php

namespace xionglonghua\common\widgets;

use xionglonghua\common\assets\SearchWidgetAsset;
use yii\web\View;
use yii\base\Widget;
use Yii;

class SearchWidget extends Widget
{
    public $dataProvider;
    public $searchUrl;
    public $suggestUrl;
    public $searchDetailView;
    public $viewSourcePath;
    public $searchTitle = '搜索';

    // 如果js有依赖关系就放到这里 such as: frontend\assets\InspiniaAsset::class
    public $jsDependAsset = '';

    /* @var $view View */
    public $view;

    public function init()
    {
        $this->viewSourcePath = __DIR__. '/src/views';
        parent::init();
    }

    public function run()
    {
        $this->registerCss();
        $this->registerJs();

        return $this->view->renderFile($this->viewSourcePath . '/' . 'template.php',
        [
            'dataProvider' => $this->dataProvider,
            'searchUrl' => $this->searchUrl,
            'searchDetailView' => $this->searchDetailView,
            'viewSourcePath' => $this->viewSourcePath,
            'title' => $this->searchTitle,
            'query' => Yii::$app->request->get('_searchWidgetQuery', ''),
        ]);
    }

    public function registerJs()
    {
        SearchWidgetAsset::register($this->view);
        if ($this->jsDependAsset) {
            $this->view->registerJsFile('//g.alicdn.com/opensearch/opensearch-console/0.16.0/scripts/jquery-ui-1.10.2.js', ['depends' => $this->jsDependAsset]);
        }

        $this->view->registerJs('var searchWidgetSuggestUrl = "' . $this->suggestUrl . '";', View::POS_END);
        $this->view->registerJs(
<<<JS
        $(function() {
          var hit = 10; // 下拉提示列表长度，可修改
          var searchBox = $('#searchform-query');

          var ajaxTimer = null;
          var delay = 0; // 单位ms

          window.setTimeout(function() {
            searchBox.autocomplete({
              source: function(request, response) {
                // 输入结束才发送请求
                if (ajaxTimer) {
                  window.clearTimeout(ajaxTimer);
                }

                ajaxTimer = window.setTimeout(function () {
                  // 只有input有值才发送请求
                  searchBox.val() !== '' && $.ajax({
                    /* url: '/searchdemo/suggestdemo' 输入你的serverUrl*/
                    url:searchWidgetSuggestUrl,
                    xhrFields: {
                      withCredentials: true
                    },
                    dataType: 'json',
                    data: {
                      _suggestWidgetQuery: searchBox.val()
                    },

                    success: function(data) {
                        suggests = data.data;
                        if(suggests.length >= hit) {
                            response(suggests.slice(0, hit));
                        } else {
                            response(suggests);
                        }
                    }
                  });
                }, delay);
              }
            }).bind('input.autocomplete', function () {
              // 修复Firefox不支持中文
              searchBox.autocomplete('search', searchBox.val());
            }).focus();
          }, 0);
        });
JS
    , View::POS_END);
    }

    public function registerCss()
    {
        $this->view->registerCssFile('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
        $this->view->registerCss(
            <<<CSS
            em {color:red; font-style: normal}
            p.abstract {font-size : 14px; margin-top: 5px}
            p.tiny {color: grey; font-size : 11px}
            h3.search-title {
                font-size : 18px;
                margin-bottom: 0;
                color: #1E0FBE;
            }
            #searchform-query:hover {border-color: #abbcd3;}
            #searchform-query:focus {border-color: #6cb6ff; outline: 0 !important;}
            .hr-line-dashed {
                border-top: 1px dashed #e7eaec;
                color: #ffffff;
                background-color: #ffffff;
                height: 1px;
                margin: 20px 0;
            }
CSS
        );
    }
}
