<?php

declare(strict_types=1);

namespace App\Services\Export;

use RuntimeException;
use ZipArchive;

/**
 * Minimal XLSX writer.
 *
 * An XLSX file is just a ZIP archive containing a handful of XML files.
 * Rather than pulling in phpoffice/phpspreadsheet (≈10 MB of dependencies)
 * just to export a flat table, this builder writes the bare-minimum set
 * of OOXML parts directly using PHP's built-in ZipArchive.
 *
 * What it supports:
 *   - one sheet (default name "Sheet1")
 *   - inline string cells (no shared strings table)
 *   - automatic XML escaping of string values
 *
 * What it does NOT support (intentionally):
 *   - styling, fonts, column widths, merged cells, formulas, multiple sheets
 *   - cached number-vs-string detection — every value is stored as text
 *
 * If we later need formatting, swap this for phpoffice/phpspreadsheet.
 * The controller-level API (writeBinary) won't change.
 */
final class SimpleXlsxBuilder
{
    /** @var array<int, string> */
    private array $headers = [];

    /** @var iterable<int, array<int, scalar|null>> */
    private iterable $rows = [];

    private string $sheetName = 'Sheet1';

    public function sheetName(string $name): self
    {
        // Excel sheet names: max 31 chars, no \/?*[]:
        $name = preg_replace('/[\\\\\/?*\[\]:]+/', ' ', $name) ?? 'Sheet1';
        $this->sheetName = mb_substr($name, 0, 31);

        return $this;
    }

    /** @param array<int, string> $headers */
    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /** @param iterable<int, array<int, scalar|null>> $rows */
    public function rows(iterable $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Build the XLSX file in a tmp path and return its absolute filesystem path.
     * Caller is responsible for streaming / deleting the file.
     */
    public function writeToTempFile(): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'lions_xlsx_');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temporary file for XLSX export.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to open ZIP archive for XLSX export.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());
        $zip->close();

        return $tmp;
    }

    // ---- XML parts ----------------------------------------------------------

    private function contentTypesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function rootRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        $name = htmlspecialchars($this->sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="{$name}" sheetId="1" r:id="rId1"/></sheets>
</workbook>
XML;
    }

    private function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    private function sheetXml(): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
            '<sheetData>';

        $rowIndex = 1;

        if ($this->headers !== []) {
            $out .= $this->renderRow($rowIndex, $this->headers);
            $rowIndex++;
        }

        foreach ($this->rows as $row) {
            $out .= $this->renderRow($rowIndex, $row);
            $rowIndex++;
        }

        $out .= '</sheetData></worksheet>';

        return $out;
    }

    /**
     * @param array<int, scalar|null> $values
     */
    private function renderRow(int $rowIndex, array $values): string
    {
        $cells = '';
        $colIndex = 0;
        foreach ($values as $value) {
            $cellRef = self::columnLetter($colIndex).$rowIndex;
            $text = $value === null ? '' : (string) $value;
            $escaped = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            // Always inline string — Excel auto-formats numeric-looking values when opened.
            $cells .= '<c r="'.$cellRef.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';
            $colIndex++;
        }

        return '<row r="'.$rowIndex.'">'.$cells.'</row>';
    }

    /**
     * 0 → A, 25 → Z, 26 → AA, etc.
     */
    private static function columnLetter(int $index): string
    {
        $letter = '';
        $i = $index;
        while ($i >= 0) {
            $letter = chr(($i % 26) + 65).$letter;
            $i = intdiv($i, 26) - 1;
        }

        return $letter;
    }
}
