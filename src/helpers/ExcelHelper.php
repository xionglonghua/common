<?php

// 数据导出组件

namespace xionglonghua\common\helpers;

/**
 * 导出 XML格式的 Excel 数据
 */
class ExcelHelper
{
    /**
     * 文档头标签
     *
     * @var string
     */
    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<ss:Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\"><Styles><Style ss:ID=\"s16\"><NumberFormat ss:Format=\"0.00_ \"/></Style></Styles>";

    /**
     * 文档尾标签
     *
     * @var string
     */
    private $footer = '</ss:Workbook>';

    /**
     * 内容编码
     *
     * @var string
     */
    private $sEncoding;

    /**
     * 是否转换特定字段值的类型
     *
     * @var bool
     */
    private $bConvertTypes;

    /**
     * 生成的Excel内工作簿的个数
     *
     * @var int
     */
    private $dWorksheetCount = 0;

    /**
     * 构造函数
     *
     * 使用类型转换时要确保:页码和邮编号以'0'开头
     *
     * @param string $sEncoding     内容编码
     * @param bool   $bConvertTypes 是否转换特定字段值的类型
     */
    public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false)
    {
        $this->bConvertTypes = $bConvertTypes;
        $this->sEncoding = $sEncoding;
    }

    /**
     * 返回工作簿标题,最大 字符数为 31
     *
     * @param string $title 工作簿标题
     *
     * @return string
     */
    public function getWorksheetTitle($title = 'Table1')
    {
        $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", '', empty($title) ? 'Table' . ($this->dWorksheetCount + 1) : $title);
        return mb_substr($title, 0, 31, 'utf-8');
    }

    /**
     * 向客户端发送Excel头信息
     *
     * @param string $filename 文件名称,不能是中文
     */
    public function generateXMLHeader($filename)
    {
        $filename = urlencode($filename);

        // 中文名称使用urlencode编码后在IE中打开能保存成中文名称的文件,但是在FF上却是乱码
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header("Content-Type: application/vnd.ms-excel; charset={$this->sEncoding}");
        header('Content-Transfer-Encoding: binary');
        header("Content-Disposition: attachment; filename={$filename}.xls");

        return $this->getXMLHeader();
    }

    /**
     * 获取头部信息
     */
    public function getXMLHeader()
    {
        return stripslashes(sprintf($this->header, $this->sEncoding));
    }

    /**
     * 向客户端发送Excel结束标签
     *
     * @param string $filename 文件名称,不能是中文
     */
    public function generateXMLFoot()
    {
        return $this->footer;
    }

    /**
     * 开启工作簿
     *
     * @param string $title
     */
    public function worksheetStart($title)
    {
        $this->dWorksheetCount++;
        return "\n<ss:Worksheet ss:Name=\"" . $this->getWorksheetTitle($title) . "\">\n<ss:Table>\n";
    }

    /**
     * 结束工作簿
     */
    public function worksheetEnd()
    {
        return "</ss:Table>\n</ss:Worksheet>\n";
    }

    /**
     * 设置表头信息
     *
     * @param array $header
     */
    public function setTableHeader(array $header)
    {
        return $this->_parseRow($header);
    }

    public function parseRow(array $row)
    {
        return $this->_parseRow($row);
    }

    /**
     * 设置表内行记录数据
     *
     * @param array $rows 多行记录
     */
    public function setTableRows(array $rows)
    {
        $table = '';
        foreach ($rows as $row) {
            $table .= $this->_parseRow($row);
        }
        return $table;
    }

    /**
     * 将传人的单行记录数组转换成 xml 标签形式
     *
     * @param array $array 单行记录数组
     */
    private function _parseRow(array $row, array $calculableCols = array())
    {
        $cells = '';
        foreach ($row as $k => $v) {
            $type = 'String';
            if ($this->bConvertTypes === true && is_numeric($v) && !StringHelper::isScien($v)) {
                $type = 'Number';
                $v = $v + 0;
            }
            $v = strip_tags($v);
            $cells .= in_array($k, $calculableCols) ? '<ss:Cell ss:StyleID="s16">' : '<ss:Cell>';
            $cells .= "<ss:Data ss:Type=\"$type\">" . $v . "</ss:Data></ss:Cell>\n";
        }
        return "<ss:Row>\n" . $cells . "</ss:Row>\n";
    }

    // 以下三个函数，用于分段输出xls
    public function outputXlsHeader($filename, $cols)
    {
        if ($filename == '') {
            $filename = date('Y_m_d_H_i_s');
        }
        echo $this->generateXMLHeader($filename);
        echo $this->worksheetStart('Table1');
        echo $this->setTableHeader($cols);
    }

    public function outputXlsBody($row)
    {
        echo $this->_parseRow($row);
    }

    public function outputXlsFooter()
    {
        echo $this->worksheetEnd();
        echo $this->generateXMLFoot();
    }

    public function getXls($filename, $cols, $data)
    {
        if ($filename == '') {
            $filename = date('Y_m_d_H_i_s');
        }
        echo $this->generateXMLHeader($filename);
        echo $this->worksheetStart('Table1');
        echo $this->setTableHeader($cols);
        foreach ($data as $row) {
            echo $this->_parseRow($row);
        }
        echo $this->worksheetEnd();
        echo $this->generateXMLFoot();
    }

    public function saveXls($filename, $cols, $data, $calculableCols = array())
    {
        if ($filename == '') {
            $filename = date('Y_m_d_H_i_s');
        }
        $saveRoot = '/tmp/';
        $filepath = $saveRoot . $filename . '.xls';
        if (file_exists($filepath)) {
            $name = date('Y_m_d_H_i_s') . $filename . '.xls';
            $filepath = $saveRoot . $name;
        }
        $f = fopen($filepath, 'w');

        // 生成xml文件
        $file = '';
        $file .= $this->getXMLHeader();
        $file .= $this->worksheetStart('Table1');
        $file .= $this->setTableHeader($cols);
        $s = fwrite($f, $file);
        foreach ($data as $row) {
            $file = '';
            $file = $this->_parseRow($row, $calculableCols);
            $s = fwrite($f, $file);
            unset($file);
        }
        $file = '';
        $file .= $this->worksheetEnd();
        $file .= $this->generateXMLFoot();
        $s = fwrite($f, $file);

        fclose($f);
        if ($s) {
            return $filepath;
        }
    }

    public function saveMultiSheetXls($filename, $sheets, $columns, $datas)
    {
        if ($filename == '') {
            $filename = date('Y_m_d_H_i_s');
        }
        $saveRoot = '/tmp/';
        $filepath = $saveRoot . $filename . '.xls';
        if (file_exists($filepath)) {
            $name = date('Y_m_d_H_i_s') . $filename . '.xls';
            $filepath = $saveRoot . $name;
        }
        $f = fopen($filepath, 'w');

        // 生成xml文件
        $file = '';

        $file .= $this->getXMLHeader();
        foreach ($sheets as $key => $sheetname) {
            $file .= $this->worksheetStart($sheetname);
            $file .= $this->setTableHeader($columns[$key]);
            $s = fwrite($f, $file);
            foreach ($datas[$key] as $row) {
                $file = '';
                $file = $this->_parseRow($row);
                $s = fwrite($f, $file);
                unset($file);
            }
            $file = '';
            $file .= $this->worksheetEnd();
        }
        $file .= $this->generateXMLFoot();
        $s = fwrite($f, $file);
        fclose($f);
        if ($s) {
            return $filepath;
        }
    }

    public function sendXls($filename, $cols, $data, $sendto, $title, $msg)
    {
        $from = ControllerAction::$loginUser->login . '@meituan.com';
        if ($filename == '') {
            $filename = date('Y_m_d_H_i_s');
        }
        if ($sendto == '') {
            $sendto == $from;
        }
        if ($title == '') {
            $title = $filename;
        }
        $filename .= '.xls';
        $title = '=?utf-8?B?'.base64_encode($title).'?=';
        $filename = '=?utf-8?B?'.base64_encode($filename).'?=';
        $msg = stripslashes($msg);
        $sendto = $sendto . ',' . $from;

        // 生成xml文件
        $file = '';
        $file .= $this->getXMLHeader();
        $file .= $this->worksheetStart('Table1');
        $file .= $this->setTableHeader($cols);
        $file .= $this->setTableRows($data);
        $file .= $this->worksheetEnd();
        $file .= $this->generateXMLFoot();

        $boundary = uniqid('');
        $header = "From: $from\r\n" .
            "Reply-To: $from\r\n" .
            'X-Mailer: PHP/' . phpversion() . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-type: multipart/mixed; boundary= $boundary\r\n";
        $mimetype = 'application/msexcel';
        // 我们用base64方法把它编码
        $read = base64_encode($file);
        // 把这个长字符串切成由每行76个字符组成的小块
        $read = chunk_split($read);
        // 现在我们可以建立邮件的主体
        $body = "--$boundary\r\n"
            . "content-type: text/plain; charset=UTF-8\r\n"
            . "content-transfer-encoding: 8bit\r\n"
            . "\r\n$msg\r\n\r\n"
            . "--$boundary\r\n"
            . "content-type: $mimetype; name=$filename\r\n"
            . "content-disposition: attachment; filename=$filename\r\n"
            . "content-transfer-encoding: base64\r\n\r\n"
            . "$read\r\n"
            . "--$boundary--";

        $ret = mail($sendto, $title, $body, $header, '-fsankuai@meituan.com');
        if ($ret) {
            return '文件发送至' . $sendto;
        } else {
            return '文件发送失败';
        }
    }
}
