<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class XmlHelper
{
    /**
     * Parse CEGEDIM Order XML to array
     *
     * @param string $xmlString
     * @return array
     * @throws \Exception
     */
    public static function parseCegedimOrderXml(string $xmlString): array
    {
        try {
            // Cargar el XML
            $xml = new \SimpleXMLElement($xmlString);

            // Registrar el namespace
            $xml->registerXPathNamespace('ns0', 'http://schemas.noName.com/PET/FR/v1.0');

            $orders = [];

            // Iterar sobre cada orden usando el namespace correcto
            foreach ($xml->Order as $orderXml) {
                $order = [
                    'header' => self::parseHeader($orderXml->Header),
                    'lines' => self::parseLines($orderXml->Line)
                ];

                $orders[] = $order;
            }

            return [
                'success' => true,
                'orders' => $orders
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse Header section
     *
     * @param \SimpleXMLElement $header
     * @return array
     */
    private static function parseHeader(\SimpleXMLElement $header): array
    {
        $headerData = [
            'sales_org' => (string)$header->SalesOrg,
            'sold_to' => (string)$header->SoldTo,
            'ship_to' => (string)$header->ShipTo,
            'customer_po' => (string)$header->CustomerPO,
            'po_type' => (string)$header->POType,
            'order_block_code' => (string)$header->OrderBlockCode,
            'shipment_method' => (string)$header->ShipmentMethod,
            'delivery_priority' => (string)$header->DeliveryPriority,
            'po_date' => (string)$header->PODate,
            'requested_delivery_date' => (string)$header->RequestedDeliveryDate,
            'header_texts' => []
        ];

        // Procesar HeaderText si existe
        if (isset($header->HeaderText)) {
            foreach ($header->HeaderText as $headerText) {
                $headerData['header_texts'][] = [
                    'text_type' => (string)$headerText->TextType,
                    'free_text' => (string)$headerText->FreeHeaderText
                ];
            }
        }

        return $headerData;
    }

    /**
     * Parse Line items
     *
     * @param \SimpleXMLElement $lines
     * @return array
     */
    private static function parseLines(\SimpleXMLElement $lines): array
    {
        $linesData = [];

        foreach ($lines as $line) {
            $lineData = [
                'product_no' => (string)$line->ProductNo,
                'product_qualifier_code' => (string)$line->ProductQualiferCode,
                'qty' => (int)$line->Qty,
                'item_category' => (string)$line->ItemCategory,
                'line_texts' => []
            ];

            // Procesar Discount si existe
            if (isset($line->Discount)) {
                $lineData['discount'] = [
                    'type' => (string)$line->Discount->Type,
                    'value' => (float)$line->Discount->Value
                ];
            }

            // Procesar LineText si existe
            if (isset($line->LineText)) {
                foreach ($line->LineText as $lineText) {
                    $lineData['line_texts'][] = [
                        'text_type' => (string)$lineText->TextType,
                        'free_text' => (string)$lineText->FreeLineText
                    ];
                }
            }

            $linesData[] = $lineData;
        }

        return $linesData;
    }
}
