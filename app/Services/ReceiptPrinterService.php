<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Exception;

class ReceiptPrinterService
{
    protected string $printerName;




    public function __construct(string $printerName = 'POS58')
    {
        $this->printerName = $printerName;
    }

    /**
     * Returns a connected Printer instance.
     *
     * @throws Exception if the printer cannot be opened.
     */
    protected function getPrinter(): Printer
    {
        if (!extension_loaded('intl')) {
            throw new Exception("The PHP 'intl' extension is required for printing. Please enable 'extension=intl' in your php.ini and restart the server.");
        }

        $connector = new WindowsPrintConnector($this->printerName); // 'POS58'
        return new Printer($connector);
    }

    /**
     * Safely execute a print job and always close the printer.
     *
     * @param callable $callback receives Printer instance
     * @throws \Throwable
     */
    protected function printSafely(callable $callback): void
    {
        $printer = null;
        try {
            $printer = $this->getPrinter();
            $callback($printer);
        } finally {
            if ($printer !== null) {
                $printer->close();
            }
        }
    }
public function testPlainText(): void
{
    $this->printSafely(function (Printer $printer) {
        $printer->text("Hello from web!\n");
        $printer->cut();
    });
}
    public function printBookLabel(string $barcode, string $title, string $author): void
    {
        $this->printSafely(function (Printer $printer) use ($barcode, $title, $author) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 2);
            $printer->text("PUPSJ Libris\n");

            $printer->setTextSize(1, 1);
            $printer->text("-------------------------------\n");
            $printer->text("{$title}\n");
            $printer->text("By: {$author}\n\n");

            // Print QR code containing the barcode value
            $printer->qrCode($barcode, Printer::QR_ECLEVEL_L, 8);
            
            // $printer->setBarcodeHeight(60);
            // $printer->setBarcodeWidth(3);
            // $printer->barcode("{B" . $barcode, Printer::BARCODE_CODE128);
            // $printer->text("\n{$barcode}\n\n");

            $printer->text("-------------------------------\n");
            $printer->text(date('Y-m-d H:i') . "\n");
            $printer->cut();
        });
    }

    public function printStudentCard(string $studentNumber, string $name, string $course, string $barcode): void
    {
        $this->printSafely(function (Printer $printer) use ($studentNumber, $name, $course, $barcode) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 2);
            $printer->text("PUPSJ Libris\n");
            $printer->text("Student ID Card\n\n");

            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Name: {$name}\n");
            $printer->text("S/N:  {$studentNumber}\n");
            $printer->text("Course: {$course}\n\n");

            $printer->setBarcodeHeight(50);
            $printer->setBarcodeWidth(2);
            $printer->barcode($barcode, Printer::BARCODE_CODE128);
           
            $printer->cut();
        });
    }

    public function printTransactionReceipt(string $studentName, string $bookTitle, string $action, string $date): void
    {
        $this->printSafely(function (Printer $printer) use ($studentName, $bookTitle, $action, $date) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 2);
            $printer->text("PUPSJ Libris\n");
            $printer->text("Transaction Receipt\n\n");

            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("-------------------------------\n");
            $printer->text("Action:   " . strtoupper($action) . "\n");
            $printer->text("Student:  {$studentName}\n");
            $printer->text("Book:     {$bookTitle}\n");
            $printer->text("Date:     {$date}\n");
            $printer->text("-------------------------------\n");
            $printer->cut();
        });
    }
}