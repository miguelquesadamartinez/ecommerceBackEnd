<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Mail\OrderReportMail;
use App\Mail\CustomerUpdateMail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GenerateHelper {

    public static function generateExcelNewCustomerOrChange($data, $filePath, $fileName, $pharmacy_cip13, $emailSubject, $emailText, $emailText_2, $order_reference)
    {
        $headerFields = [
            'CODE CLIENT SAP',
            'DENOMINATION SOCIALE',
            'DENOMINATION COMMERCIAL',
            'CODE CIP',
            'SIREN',
            'SIRET',
            'Titulaire'
        ];

        $addressFields = [
            'ADRESSE',
            'COMPLEMENT ADRESSE 1',
            'COMPLEMENT ADRESSE 2',
            'COMPLEMENT ADRESSE 3',
            'CODE POSTAL',
            'VILLE'
        ];

        $bankFields = [
            'Compte',
            'Clé Rib',
            'Domiciliation (nom de la banque)',
            'IBAN OBLIGATOIRE'
        ];

        // Buscar campos de dirección antigua
        $oldAddressData = [];
        $hasOldAddress = false;

        foreach ($data as $key => $value) {
            if (strpos($key, '_old_address') !== false) {
                $hasOldAddress = true;
                $oldAddressData[$key] = $value;
            }
        }

        // Si hay dirección antigua, añadir título y campos

            unset($oldAddressData['CODE CIP_old_address']);
            unset($oldAddressData['DENOMINATION SOCIALE_old_address']);
            unset($oldAddressData['DENOMINATION COMMERCIAL_old_address']);
            unset($oldAddressData['id_old_address']);
            unset($oldAddressData['id']);
            unset($oldAddressData['order_reference_old_address']);
            unset($oldAddressData['order_reference']);
            unset($oldAddressData['CODE CIP']);
            unset($oldAddressData['DENOMINATION SOCIALE']);
            unset($oldAddressData['DENOMINATION COMMERCIAL']);

        $hasBankCode = isset($data['BANK CODE']);
        $hasGuichetCode = isset($data['GUICHET CODE']);

        if ($hasBankCode || $hasGuichetCode) {
            $combinedValue = '';
            if ($hasBankCode) {
                $combinedValue = $data['BANK CODE'];
            }
            if ($hasGuichetCode) {
                if (!empty($combinedValue)) {
                    $combinedValue .= ' / ';
                }
                $combinedValue .= $data['GUICHET CODE'];
            }

            $data['bankCodes'] = $combinedValue;
        } else {
            $data['bankCodes'] = 'No data';
        }

        unset($bankFields['BANK CODE']);
        unset($bankFields['GUICHET CODE']);

        // Añadir cualquier campo restante que no esté en las categorías anteriores
        $otherFields = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $headerFields) &&
                !in_array($key, $addressFields) &&
                !in_array($key, $bankFields) &&
                $key !== 'BANK CODE' &&
                $key !== 'GUICHET CODE' &&
                strpos($key, '_old_address') === false) {
                $otherFields[$key] = $value;
            }
        }

        Mail::to(env('EMAIL_FOR_APP_CUSTOMER'))
/*
            ->cc([
                'g.naze@callmedicall.com',
                'malika.bouallel@noName.com',
                'alina.velicu@noName.com',
                'valerie.gattelet-un@noName.com',
                'sylvie.tiber@noName.com',
                'pascal.dury@noName.com',
                'silvere.chapin@noName.com',
                'adel.boukraa@noName.com',
                'Marilyn.Gayffier@noName.com',
                'Sylvain.BERGERON@noName.com'
            ])*/
            ->send(new CustomerUpdateMail($filePath, $fileName, $pharmacy_cip13, $emailSubject, $emailText, $emailText_2, $data, $order_reference));

        return $filePath;
    }

    public static function generateExcelQuarterlyActivityReporting($data, $filename)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data[0] as $colIndex => $headerText) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $headerText);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                $sheet->setCellValueExplicit(
                    $cellCoordinate,
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                if ( $rowIndex > 0 && ($rowIndex) % 2 == 0) {
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cellCoordinate)->getFill()->getStartColor()->setARGB('FFD3D3D3');
                }
            }
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        // Auto-size columns
        //$sheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        //$filePath = App::basePath() . env('OUT_FOLDER') . NomaneHelper::insertCurrentDateBeforeExtension($filename);
        $timestamp = date('Y-m-d_His');
        $tempFileName = 'Rapport_des_comandes_trimestriel_' . $timestamp . '.xlsx';
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);
        $writer->save($filePath);

        $subject_2 = 'Rapport des commandes trimestriel - ' . date('d/m/Y');
        $text2 = 'Veuillez trouver ci-joint le rapport des commandes trimestriel généré le ' . date('d/m/Y') . '.';

        $count = count($data) ? count($data) : 0;

        Mail::to(env('EMAIL_FOR_INFO'))
/*
            ->cc([
                'malika.bouallel@noName.com',
                'alina.velicu@noName.com',
                'valerie.gattelet-un@noName.com',
                'sylvie.tiber@noName.com',
                'pascal.dury@noName.com',
                'silvere.chapin@noName.com'
            ])*/
            ->send(new OrderReportMail($filePath, $tempFileName, $count, $subject_2, $text2));

        return $filePath;
    }

    public static function generateExcelTwoWeeklyActivityReporting($data, $filename)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data[0] as $colIndex => $headerText) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $headerText);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);

                $sheet->setCellValueExplicit(
                    $cellCoordinate,
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                if ( $rowIndex > 0 && ($rowIndex) % 2 == 0) {
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cellCoordinate)->getFill()->getStartColor()->setARGB('FFD3D3D3');
                }
            }
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        // Auto-size columns
        //$sheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        //$filePath = App::basePath() . env('OUT_FOLDER') . NomaneHelper::insertCurrentDateBeforeExtension($filename);
        $timestamp = date('Y-m-d_His');
        $tempFileName = 'Rapport_des_comandes_deux_trimestriel_' . $timestamp . '.xlsx';
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);
        $writer->save($filePath);

        $subject_2 = 'Rapport des commandes deux trimestriel - ' . Carbon::now()->subMonths(3)->format('d-m-Y');
        $text2 = 'Veuillez trouver ci-joint le rapport des commandes trimestriel généré le ' . date('d/m/Y') . '.';

        $count = count($data) ? count($data) : 0;

        Mail::to(env('EMAIL_FOR_INFO'))
/*
            ->cc([
                'malika.bouallel@noName.com',
                'alina.velicu@noName.com',
                'valerie.gattelet-un@noName.com',
                'sylvie.tiber@noName.com',
                'pascal.dury@noName.com',
                'silvere.chapin@noName.com',
                'adel.boukraa@noName.com',
                'Marilyn.Gayffier@noName.com',
                'Sylvain.BERGERON@noName.com'
            ])*/
            ->send(new OrderReportMail($filePath, $tempFileName, $count, $subject_2, $text2));

        return $filePath;
    }

    public static function generateExcelWeeklyActivityReporting($data, $filename)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data[0] as $colIndex => $headerText) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $headerText);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);

                $sheet->setCellValueExplicit(
                    $cellCoordinate,
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                if ( $rowIndex > 0 && ($rowIndex) % 2 == 0) {
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cellCoordinate)->getFill()->getStartColor()->setARGB('FFD3D3D3');
                }
            }
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        // Auto-size columns
        //$sheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        //$filePath = App::basePath() . env('OUT_FOLDER') . NomaneHelper::insertCurrentDateBeforeExtension($filename);
        $timestamp = date('Y-m-d_His');
        $tempFileName = 'Rapport_des_comandes_hebdomadaire_' . $timestamp . '.xlsx';
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);
        $writer->save($filePath);

        $subject_2 = 'Rapport des commandes hebdomadaire - ' . date('d/m/Y');
        $text2 = 'Veuillez trouver ci-joint le rapport des commandes hebdomadaire généré le ' . date('d/m/Y') . '.';

        $count = count($data) ? count($data) : 0;

        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new OrderReportMail($filePath, $tempFileName, $count, $subject_2, $text2));

        return $filePath;
    }

    public static function generateExcelWeeklyActivityForBlockedOrders($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data[0] as $colIndex => $headerText) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $headerText);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);

                $sheet->setCellValueExplicit(
                    $cellCoordinate,
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                if ( $rowIndex > 0 && ($rowIndex) % 2 == 0) {
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cellCoordinate)->getFill()->getStartColor()->setARGB('FFD3D3D3');
                }
            }
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        // Auto-size columns
        //$sheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        //$filePath = App::basePath() . env('OUT_FOLDER') . NomaneHelper::insertCurrentDateBeforeExtension($filename);
        $timestamp = date('Y-m-d_His');
        $tempFileName = 'Rapport_des_comandes_daily _' . $timestamp . '.xlsx';
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);
        $writer->save($filePath);

        return $filePath;
    }

    public static function generateExcelMonthlyActivityReporting($data, $filename)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data[0] as $colIndex => $headerText) {
            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $headerText);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);

                $sheet->setCellValueExplicit(
                    $cellCoordinate,
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                if ( $rowIndex > 0 && ($rowIndex) % 2 == 0) {
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle($cellCoordinate)->getFill()->getStartColor()->setARGB('FFD3D3D3');
                }
            }
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        // Auto-size columns
        //$sheet->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        //$filePath = App::basePath() . env('OUT_FOLDER') . NomaneHelper::insertCurrentDateBeforeExtension($filename);
        $timestamp = date('Y-m-d_His');
        $tempFileName = 'Rapport_des_comandes_mensuelle_' . $timestamp . '.xlsx';
        $filePath = Storage::disk('nomane_temp_folder')->path($tempFileName);
        $writer->save($filePath);

        $subject_2 = 'Rapport des commandes mensuelle - ' . date('d/m/Y');
        $text2 = 'Veuillez trouver ci-joint le rapport des commandes mensuelle généré le ' . date('d/m/Y') . '.';

        $count = count($data) ? count($data) : 0;

        Mail::to(env('EMAIL_FOR_INFO'))
            ->send(new OrderReportMail($filePath, $tempFileName, $count, $subject_2, $text2));

        return $filePath;
    }
}
