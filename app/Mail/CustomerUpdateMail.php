<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;


class CustomerUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    private $excelData;

    public function __construct(
        private $filePath, 
        private $fileName, 
        private $pharmacy_cip13, 
        private $emailSubject, 
        private $emailText, 
        private $emailText_2, 
        private $data = null,
        private $order_reference = null
    ) {
        // Procesar los datos para la tabla en el email
        if ($data) {
            $this->processExcelData($data);
        }
    }

    /**
     * Procesa los datos para mostrarlos en la tabla del email
     */
    private function processExcelData($data)
    {
        $this->excelData = [
            'header' => [],
            'address' => [],
            'oldAddress' => [],
            'bank' => [],
            'bankCodes' => ''
        ];

        // Campos de encabezado
        $headerFields = [
            'CODE CLIENT SAP',
            'DENOMINATION SOCIALE',
            'DENOMINATION COMMERCIAL',
            'CODE CIP',
            'SIREN',
            'SIRET',
            'Titulaire'
        ];

        // Campos de dirección
        $addressFields = [
            'ADRESSE',
            'COMPLEMENT ADRESSE 1',
            'COMPLEMENT ADRESSE 2',
            'COMPLEMENT ADRESSE 3',
            'CODE POSTAL',
            'VILLE'
        ];

        // Campos bancarios
        $bankFields = [
            'Compte',
            'Clé Rib',
            'Domiciliation (nom de la banque)',
            'IBAN OBLIGATOIRE'
        ];

        // Procesar campos de encabezado
        foreach ($headerFields as $field) {
            if (isset($data[$field])) {
                $this->excelData['header'][$field] = $data[$field];
            }
        }

        // Procesar campos de dirección
        foreach ($addressFields as $field) {
            if (isset($data[$field])) {
                $this->excelData['address'][$field] = $data[$field];
            }
        }

        // Procesar campos de dirección antigua
        $oldAddressData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, '_old_address') !== false) {
                $displayKey = str_replace('_old_address', '', $key);
                if (!in_array($displayKey, ['CODE CIP', 'DENOMINATION SOCIALE', 'DENOMINATION COMMERCIAL', 'id'])) {
                    $this->excelData['oldAddress'][$displayKey] = $value;
                }
            }
        }

        // Procesar códigos bancarios
        $bankCode = isset($data['BANK CODE']) ? $data['BANK CODE'] : '';
        $guichetCode = isset($data['GUICHET CODE']) ? $data['GUICHET CODE'] : '';
        
        if (!empty($bankCode) || !empty($guichetCode)) {
            $this->excelData['bankCodes'] = $bankCode;
            if (!empty($guichetCode)) {
                if (!empty($bankCode)) {
                    $this->excelData['bankCodes'] .= ' / ';
                }
                $this->excelData['bankCodes'] .= $guichetCode;
            }
        }

        foreach ($bankFields as $field) {
            if (isset($data[$field])) {
                $this->excelData['bank'][$field] = $data[$field];
            }
        }
    }

    public function envelope()
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content()
    {
        return new Content(
            view: 'email.customerUpdate',
            with: [
                'emailText' => $this->emailText, 
                'emailText_2' => $this->emailText_2,
                'excelData' => $this->excelData ?? null,
                'order_reference' => $this->order_reference ?? null
            ],
        );
    }

    public function attachments(): array
    {
        /*
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ];
        */
        return [];
    }
/*
    public function build()
    {
        return $this->attach(
                        $this->filePath,
                        [
                            'as' =>  $this->fileName,
                            'mime' =>  'application/xlsx',
                        ]
                    );
    }
*/  
}
