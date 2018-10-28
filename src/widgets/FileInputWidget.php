<?php

namespace xionglonghua\common\widgets;

use kartik\file\FileInput;

class FileInputWidget
{
    public static function imgInput($fieldname, $maxFileCount = 1, $viewstr = '', $type = '', $name = 'file', $id = 'fileinput', $dest = '')
    {
        $multiple = $maxFileCount != 1;
        $preview = [];
        if (!empty($viewstr)) {
            $arr = explode(',', $viewstr);
            foreach ($arr as $var) {
                if (!empty($var)) {
                    $preview[] = '<img src="'.$var.'" class="file-preview-image"></img>';
                }
            }
        }
        return FileInput::widget([
            'name' => $name,
            'language' => 'zh-cn',
            'options' => [
                'multiple' => true,
                'id' => $id,
            ],
            'pluginOptions' => [
                'previewFileType' => 'image',
                'initialPreview' => $preview,
                'uploadUrl' => '/file/upload?type='.$type.'&filekey='.$name.'&desc='.$dest,
                'allowedFileExtensions' => ['jpg', 'png', 'jpeg', 'gif'],
                'maxFileCount' => $maxFileCount,
                'showRemove' => false,
                'showUpload' => false,
            ],
            'pluginEvents' => [
                //在成功上传后将文件的url写入到model字段
                'fileuploaded' => 'function(event, files, extra) {
                    var text = '. ($multiple ? ('$("#'.$fieldname.'").val()') : '""') .';
                    if(text == "") {
                        text += files.response.body.imgurl;
                    } else {
                        text += "," + files.response.body.imgurl;
                    }
                    $("#'.$fieldname.'").val(text);
                }',
                //在文件remove时，将certimg中去掉
                'filecleared' => 'function(event, key) {
                    $("#'.$fieldname.'").val("");
                }',
                'filebatchselected' => 'function(event, files){
                    $("#'.$id.'").fileinput("upload");
                }',
            ],
        ]);
    }
}
