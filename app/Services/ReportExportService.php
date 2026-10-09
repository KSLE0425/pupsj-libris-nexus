<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export raw report data to CSV, Excel-compatible format, or JSON.
     */
    public static function export(string $reportName, array $headers, array $rows, string $format = 'csv', array $metadata = []): mixed
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($reportName)) . '_' . now()->format('Y-m-d_His');

        return match (strtolower($format)) {
            'json' => self::exportJson($filename, $reportName, $headers, $rows, $metadata),
            'xlsx', 'excel' => self::exportExcel($filename, $reportName, $headers, $rows, $metadata),
            default => self::exportCsv($filename, $headers, $rows),
        };
    }

    /**
     * Generate CSV streamed response with UTF-8 BOM.
     */
    protected static function exportCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            if (!empty($headers)) {
                fputcsv($handle, $headers);
            }

            foreach ($rows as $row) {
                fputcsv($handle, array_values((array) $row));
            }

            fclose($handle);
        }, "{$filename}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Generate Excel XML Spreadsheet format (.xlsx / .xml) that opens seamlessly in Microsoft Excel.
     */
    protected static function exportExcel(string $filename, string $reportTitle, array $headers, array $rows, array $metadata = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($reportTitle, $headers, $rows, $metadata) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // Write Excel 2003 XML format which Excel opens natively without warnings
            fwrite($handle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
            fwrite($handle, "<?mso-application progid=\"Excel.Sheet\"?>\n");
            fwrite($handle, "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n");
            fwrite($handle, " xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n");
            fwrite($handle, " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n");
            fwrite($handle, " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n");
            fwrite($handle, " <Styles>\n");
            fwrite($handle, "  <Style ss:ID=\"Default\" ss:Name=\"Normal\"><Font ss:FontName=\"Segoe UI\" ss:Size=\"10\"/></Style>\n");
            fwrite($handle, "  <Style ss:ID=\"Header\"><Font ss:FontName=\"Segoe UI\" ss:Size=\"11\" ss:Bold=\"1\" ss:Color=\"#FFFFFF\"/><Interior ss:Color=\"#800000\" ss:Pattern=\"Solid\"/></Style>\n");
            fwrite($handle, "  <Style ss:ID=\"Title\"><Font ss:FontName=\"Segoe UI\" ss:Size=\"14\" ss:Bold=\"1\" ss:Color=\"#800000\"/></Style>\n");
            fwrite($handle, "  <Style ss:ID=\"Meta\"><Font ss:FontName=\"Segoe UI\" ss:Size=\"9\" ss:Color=\"#666666\"/></Style>\n");
            fwrite($handle, " </Styles>\n");
            fwrite($handle, " <Worksheet ss:Name=\"Report\">\n");
            fwrite($handle, "  <Table>\n");

            // Title Row
            fwrite($handle, "   <Row ss:StyleID=\"Title\"><Cell><Data ss:Type=\"String\">" . htmlspecialchars($reportTitle) . "</Data></Cell></Row>\n");
            // Metadata Row
            $metaStr = "Generated on " . now('Asia/Manila')->format('Y-m-d H:i:s') . (!empty($metadata['period']) ? " | Period: " . $metadata['period'] : '');
            fwrite($handle, "   <Row ss:StyleID=\"Meta\"><Cell><Data ss:Type=\"String\">" . htmlspecialchars($metaStr) . "</Data></Cell></Row>\n");
            fwrite($handle, "   <Row></Row>\n");

            // Header Row
            if (!empty($headers)) {
                fwrite($handle, "   <Row ss:StyleID=\"Header\">\n");
                foreach ($headers as $h) {
                    fwrite($handle, "    <Cell><Data ss:Type=\"String\">" . htmlspecialchars((string) $h) . "</Data></Cell>\n");
                }
                fwrite($handle, "   </Row>\n");
            }

            // Data Rows
            foreach ($rows as $row) {
                fwrite($handle, "   <Row>\n");
                foreach ((array) $row as $val) {
                    $isNum = is_numeric($val) && !str_starts_with((string)$val, '0') && strlen((string)$val) < 12;
                    $type = $isNum ? 'Number' : 'String';
                    $valStr = htmlspecialchars((string) ($val ?? ''));
                    fwrite($handle, "    <Cell><Data ss:Type=\"{$type}\">{$valStr}</Data></Cell>\n");
                }
                fwrite($handle, "   </Row>\n");
            }

            fwrite($handle, "  </Table>\n");
            fwrite($handle, " </Worksheet>\n");
            fwrite($handle, "</Workbook>\n");

            fclose($handle);
        }, "{$filename}.xls", [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * Generate JSON download response.
     */
    protected static function exportJson(string $filename, string $reportName, array $headers, array $rows, array $metadata = []): StreamedResponse
    {
        $payload = [
            'report_name'  => $reportName,
            'generated_at' => now('Asia/Manila')->toIso8601String(),
            'period'       => $metadata['period'] ?? 'All Time',
            'total_rows'   => count($rows),
            'headers'      => $headers,
            'data'         => $rows,
        ];

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, "{$filename}.json", [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }
}
