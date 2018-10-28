<?php

namespace xionglonghua\common\helpers;

use yii\db\Query;
use yii\data\BaseDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SheetHelper
{
    private const FIRST_ROW = 1;
    private const LIMIT_ROW = 1000;
    private static $_col_counter = 'A';
    private static $_row_counter = 1;

    public function export(Query $query, $columns, $sheetTitle = '')
    {
        $spreadSheet = new Spreadsheet();
        self::resetCounter();
        $sheet = $spreadSheet->getActiveSheet();
        if (mb_strlen($sheetTitle, 'UTF-8') > 31) {
            $sheetTitle = mb_substr($sheetTitle, 0, 31, 'UTF-8');
        }
        $sheetTitle && $sheet->setTitle($sheetTitle);

        $total = $offset = 0;
        $limit = self::FIRST_ROW;
        do {
            $models = $query->offset($offset)->limit($limit)->all();
            if (!$models) {
                break;
            }
            if ($limit == self::FIRST_ROW) {
                $rows = [];
                foreach ($columns as $title => $column) {
                    if (is_numeric($title)) {
                        $rows[] = $models[0]->getAttributeLabel($column);
                    } else {
                        $rows[] = $title;
                    }
                }
                self::parseRow($sheet, $rows);
                $limit = self::LIMIT_ROW;
            }
            foreach ($models as $model) {
                $rows = [];
                foreach ($columns as $column) {
                    $contents = ArrayHelper::getValue($model, $column);
                    $contents = str_replace("\n", '&#13;', $contents);
                    $rows[] = $contents;
                }
                self::parseRow($sheet, $rows);
            }
            $total = count($models);
            $offset += $total;
        } while ($total != 0);

        $writer = new Xlsx($spreadSheet);
        $path = tempnam('/tmp', 'xlsx_');
        $writer->save($path);
        return file_get_contents($path);
    }

    public function exportDp(BaseDataProvider $dataProvider, $columns, $sheetTitle = '')
    {
        $spreadSheet = new Spreadsheet();
        self::resetCounter();
        $sheet = $spreadSheet->getActiveSheet();
        if (mb_strlen($sheetTitle, 'UTF-8') > 31) {
            $sheetTitle = mb_substr($sheetTitle, 0, 31, 'UTF-8');
        }
        $sheetTitle && $sheet->setTitle($sheetTitle);

        $total = $offset = 0;
        $models = $dataProvider->getModels();

        if (count($models) > 0) {
            $rows = [];
            foreach ($columns as $title => $column) {
                if (is_int($title)) {
                    if ($models[0] instanceof \yii\base\Model) {
                        $rows[] = $models[0]->getAttributeLabel($column);
                    } else {
                        $rows[] = $column;
                    }
                } else {
                    $rows[] = $title;
                }
            }
            self::parseRow($sheet, $rows);
        }
        do {
            foreach ($models as $model) {
                $rows = [];
                foreach ($columns as $column) {
                    $rows[] = ArrayHelper::getValue($model, $column);
                }
                self::parseRow($sheet, $rows);
            }
            $dataProvider->pagination->page++;
            $dataProvider->prepare(true);
        } while ($models = $dataProvider->getModels());

        $writer = new Xlsx($spreadSheet);
        $path = tempnam('/tmp', 'xlsx_');
        $writer->save($path);
        return file_get_contents($path);
    }

    private static function resetCounter(): void
    {
        self::$_col_counter = 'A';
        self::$_row_counter = 1;
    }

    private static function parseRow(Worksheet $sheet, array $rows): void
    {
        foreach ($rows as $cell) {
            $sheet->setCellValue(self::$_col_counter.(string) self::$_row_counter, $cell);
            ++self::$_col_counter;
        }
        self::$_col_counter = 'A';
        ++self::$_row_counter;
    }
}
