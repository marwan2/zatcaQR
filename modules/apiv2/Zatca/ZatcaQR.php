<?php

namespace API\Zatca;

use API\Helpers\Helper;
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

class ZatcaQR{
    private $seller_name, $tax_number, $date, $total_amount, $tax_amount;

    public function __construct(string $seller_name, string $tax_number, string $date, $total_amount, $tax_amount)
    {
        $this->seller_name = $seller_name;
        $this->tax_number = $tax_number;
        $this->date = date(DATE_ISO8601, strtotime($date));
        $this->total_amount = $total_amount;
        $this->tax_amount = $tax_amount;
    }

    private function generateQRCode()
    {
        $QRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($this->seller_name), // seller name        
            new TaxNumber($this->tax_number), // seller tax number
            new InvoiceDate($this->date), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount($this->total_amount), // invoice total amount
            new InvoiceTaxAmount($this->tax_amount) // invoice tax amount
        ])->render();

        return $QRCodeAsBase64;
    }

    public function getQRCode()
    {
        $generatedQRCode = $this->generateQRCode();
        return $generatedQRCode;
    }

    public function renderQRCodeImg()
    {
        $srcQRCode = $this->getQRCode();
        echo "<img src='$srcQRCode' alt=''/>";
    }

   public function getZatcaCodeAPI()
   {
        $data = Helper::getPostData();
        $this->seller_name = $data['seller_name'];
        $this->tax_number = $data['tax_number'];
        $this->date = date(DATE_ISO8601, strtotime($data['date']));
        $this->total_amount = $data['total_amount'];
        $this->tax_amount = $data['tax_amount'];

        return Helper::api_success_response(['code' => $this->generateQRCode()], 'Generated QR Code');
    }
}
