<?php
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

try {
    $connector = new WindowsPrintConnector("POS58");
    $printer = new Printer($connector);
    $printer->text("Hello, this is a test!\n");
    $printer->cut();
    $printer->close();
    echo "Sent to printer.";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}