<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$files = [
    'lembar_saldo' => __DIR__ . '/docs/LEMBAR SALDO 2025 FIX.xlsx',
    'laba_rugi' => __DIR__ . '/docs/LABA RUGI 2025 FIX.xlsx',
    'buku_besar' => __DIR__ . '/docs/BUKU BESAR 2025 FIX.xlsx'
];

$output = [];

foreach ($files as $key => $path) {
    if (!file_exists($path)) {
        $output[$key] = ['error' => 'File not found'];
        continue;
    }
    try {
        $spreadsheet = IOFactory::load($path);
        $sheetNames = $spreadsheet->getSheetNames();
        $activeSheet = $spreadsheet->getActiveSheet()->getTitle();

        $sheetsInfo = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $title = $sheet->getTitle();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            
            // sample first 20 rows
            $data = $sheet->rangeToArray('A1:' . $highestColumn . min(20, $highestRow), null, true, true, true);
            
            $sheetsInfo[] = [
                'name' => $title,
                'rows' => $highestRow,
                'cols' => $highestColumn,
                'sample' => $data
            ];
        }

        $output[$key] = [
            'sheet_names' => $sheetNames,
            'active_sheet' => $activeSheet,
            'sheets_info' => $sheetsInfo
        ];
    } catch (\Exception $e) {
        $output[$key] = ['error' => $e->getMessage()];
    }
}

file_put_contents(__DIR__ . '/excel_structure.json', json_encode($output, JSON_PRETTY_PRINT));
echo "Saved to excel_structure.json\n";
