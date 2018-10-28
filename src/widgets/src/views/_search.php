<?php
/* @var $searchUrl */
?>

<div class="common-search">
    <p></p>

    <div class="panel panel-default fund-search">
        <div class="panel-body">
            <form id="search-form" class="form-inline" action=<?php echo $searchUrl; ?> method="get"><div class="form-group col-sm-6 field-searchform-query" style="padding-top:10px">
                    <div><div class="row">
                            <div class="col-sm-3"><label class="" for="searchform-query">搜索词</label></div>
                            <div class="col-sm-9">
                                <input type="text" id="searchform-query" class="form-control ui-autocomplete-input" name="_searchWidgetQuery" autocomplete="off" value=<?php echo '"' . $query . '"'; ?>>
                                <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
                            </div>
                        </div></div>
                </div>

                <div class="form-group" style="padding-top:10px">
                    <button type="submit" class="btn btn-primary text-center">查询</button>
                    <button type="reset" class="btn btn-default text-center">清空</button>
                </div>
            </form>
        </div>
    </div>
</div>

