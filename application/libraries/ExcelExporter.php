<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

// Include PhpSpreadsheet's namespace
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;

class ExcelExporter {

    /* private */
    private $spreadsheet;
    private $sheet;

    public function __construct() 
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

   /**
     * Export a multidimensional array to an Excel file.
     * 
     * @param array $data The input data, where each row is an associative array.
     * @param string $filename The filename for the downloaded Excel file.
     */
    public function exportToExcel(array $data, $filename = 'export.xlsx') {
        if (empty($data)) {
            throw new Exception('No data provided for export.');
        }

        // Write the header row (keys from the first data row)
        $header = array_keys(reset($data));
        foreach ($header as $colIndex => $colName) {
            $this->sheet->setCellValue([$colIndex + 1, 1], ucfirst($colName));
        }

        // Write the data rows
        foreach ($data as $rowIndex => $row) {
            foreach ($header as $colIndex => $colName) {
                // Use the header keys to match the data
                $cellValue = isset($row[$colName]) ? $row[$colName] : null;
                $this->sheet->setCellValue([$colIndex + 1, $rowIndex + 2], $cellValue);
            }
        }

        // Set headers for the file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        // Write the file to output
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportToExcelAI(array $data, $filename = 'export.xlsx') {
        if (empty($data)) {
            throw new Exception('No data provided for export.');
        }

        // load the advanced value binder
        $this->spreadsheet->setValueBinder(new AdvancedValueBinder());

        // Write the header row (keys from the first data row)
        $header = array_keys(reset($data));
        foreach ($header as $colIndex => $colName) {
            $this->sheet->setCellValue([$colIndex + 1, 1], ucfirst($colName));
        }

        // Write the data rows
        foreach ($data as $rowIndex => $row) {
            foreach ($header as $colIndex => $colName) {
                // Use the header keys to match the data
                $cellValue = isset($row[$colName]) ? $row[$colName] : null;
                $this->sheet->setCellValue([$colIndex + 1, $rowIndex + 2], $cellValue);
            }
        }

        // Set headers for the file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        // Write the file to output
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
