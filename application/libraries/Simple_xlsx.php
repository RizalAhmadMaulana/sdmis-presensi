<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Simple_xlsx
 * Zero-dependency OpenXML (.xlsx) spreadsheet generator untuk Modul Penggajian SDMIS.
 * Menghasilkan file Excel resmi (.xlsx) lengkap dengan:
 * - Garis border tabel penuh di setiap sel (header, isi, footer summary)
 * - Header tebal (bold) & background bersih seperti template Payroll November 2026
 * - Format Teks '@' pada nomor rekening, CUSTOMERREFF, NIP, & VALUEDATE agar angka panjang tidak berubah menjadi notasi ilmiah (6.90201E+14) atau kehilangan angka 0 di depan
 * - Lebar kolom otomatis agar tidak ada teks/email terpotong
 */
class Simple_xlsx {

    /**
     * Menghasilkan dan mengunduh file Excel (.xlsx) ke browser
     *
     * @param string $filename Nama file unduhan (.xlsx)
     * @param array  $columns  Definisi kolom: [['title'=>'...', 'type'=>'text|int|number', 'align'=>'left|center|right', 'min_width'=>8], ...]
     * @param array  $rows     Baris data biasa: [['val1', 'val2', ...], ...]
     * @param array  $footer_rows Opsional baris ringkasan: [ ['cells'=>[...], 'types'=>[...]], ... ]
     * @param string $sheet_name Nama sheet
     */
    public function download($filename, $columns, $rows, $footer_rows = [], $sheet_name = 'Payroll') {
        $binary = $this->build($columns, $rows, $footer_rows, $sheet_name);

        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($binary));
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        echo $binary;
        exit();
    }

    /**
     * Merakit paket binary file .xlsx
     */
    public function build($columns, $rows, $footer_rows = [], $sheet_name = 'Payroll') {
        $sheet_title = $this->_clean_sheet_name($sheet_name);

        $files = [
            '[Content_Types].xml'       => $this->_xml_content_types(),
            '_rels/.rels'               => $this->_xml_root_rels(),
            'xl/_rels/workbook.xml.rels'=> $this->_xml_workbook_rels(),
            'xl/workbook.xml'           => $this->_xml_workbook($sheet_title),
            'xl/styles.xml'             => $this->_xml_styles(),
            'xl/worksheets/sheet1.xml'  => $this->_xml_worksheet($columns, $rows, $footer_rows),
        ];

        // Gunakan ZipArchive jika tersedia, fallback ke Pure-PHP Zip Store Builder
        if (class_exists('ZipArchive')) {
            $tmp = @tempnam(sys_get_temp_dir(), 'sdmis_xlsx_');
            if ($tmp) {
                $zip = new ZipArchive();
                if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    foreach ($files as $path => $content) {
                        $zip->addFromString($path, $content);
                    }
                    $zip->close();
                    $data = @file_get_contents($tmp);
                    @unlink($tmp);
                    if ($data !== false && strlen($data) > 0) {
                        return $data;
                    }
                }
                @unlink($tmp);
            }
        }

        return $this->_build_zip_memory($files);
    }

    private function _col_letter($index) {
        $letter = '';
        $num = (int)$index;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $num = (int)(($num - $mod) / 26);
        }
        return $letter;
    }

    private function _escape_xml($val) {
        $str = (string)$val;
        // Bersihkan karakter kontrol XML yang tidak valid
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function _clean_sheet_name($name) {
        $clean = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', (string)$name);
        $clean = trim($clean);
        if ($clean === '') $clean = 'Payroll';
        return substr($clean, 0, 31);
    }

    private function _xml_content_types() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function _xml_root_rels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function _xml_workbook_rels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function _xml_workbook($sheet_title) {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->_escape_xml($sheet_title) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    /**
     * Style indices (cellXfs):
     * 0: Default
     * 1: Header (Bold, #D9E1F2 fill, thin black border, center/middle)
     * 2: Data Text Left (numFmt @, thin black border, left)
     * 3: Data Text Center (numFmt @, thin black border, center)
     * 4: Data Int Center (numFmt 0, thin black border, center)
     * 5: Data Currency Right (numFmt #,##0, thin black border, right)
     * 6: Footer Label Bold Left (#F2F2F2 fill, thin black border)
     * 7: Footer Empty/Spacer (#F2F2F2 fill, thin black border)
     * 8: Footer Currency/Count Bold Right (numFmt #,##0, #F2F2F2 fill, thin black border)
     * 9: Footer Text Bold Right (numFmt @, #F2F2F2 fill, thin black border)
     */
    private function _xml_styles() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
                . '<font><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>'
                . '<font><b/><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>'
            . '</fonts>'
            . '<fills count="4">'
                . '<fill><patternFill patternType="none"/></fill>'
                . '<fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFD9E1F2"/><bgColor indexed="64"/></patternFill></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFF2F2F2"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
                . '<border><left/><right/><top/><bottom/><diagonal/></border>'
                . '<border>'
                    . '<left style="thin"><color rgb="FF000000"/></left>'
                    . '<right style="thin"><color rgb="FF000000"/></right>'
                    . '<top style="thin"><color rgb="FF000000"/></top>'
                    . '<bottom style="thin"><color rgb="FF000000"/></bottom>'
                    . '<diagonal/>'
                . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1">'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '</cellStyleXfs>'
            . '<cellXfs count="10">'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
                . '<xf numFmtId="49" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>'
                . '<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="left" vertical="center"/></xf>'
                . '<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
                . '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
                . '<xf numFmtId="49" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="left" vertical="center"/></xf>'
                . '<xf numFmtId="49" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>'
                . '<xf numFmtId="3" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
                . '<xf numFmtId="49" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function _xml_worksheet($columns, $rows, $footer_rows) {
        $col_count = count($columns);
        $max_lens = [];

        foreach ($columns as $i => $col) {
            $max_lens[$i] = max(strlen((string)$col['title']) + 2, isset($col['min_width']) ? (int)$col['min_width'] : 8);
        }

        foreach ($rows as $row) {
            for ($i = 0; $i < $col_count; $i++) {
                $val = isset($row[$i]) ? (string)$row[$i] : '';
                $len = strlen($val);
                if (isset($columns[$i]['type']) && $columns[$i]['type'] === 'currency') {
                    $len += 4; // ruang pemisah ribuan
                }
                if ($len > $max_lens[$i]) {
                    $max_lens[$i] = $len;
                }
            }
        }

        foreach ($footer_rows as $fr) {
            $cells = isset($fr['cells']) ? $fr['cells'] : [];
            $merge_ranges = isset($fr['merges']) ? $fr['merges'] : [];
            $merged_starts = [];
            foreach ($merge_ranges as $mr) {
                $merged_starts[(int)$mr[0]] = true;
            }
            for ($i = 0; $i < $col_count; $i++) {
                // Lewati pelebaran satu kolom jika teks footer akan digabung melintasi beberapa kolom
                if (isset($merged_starts[$i])) {
                    continue;
                }
                $val = isset($cells[$i]) ? (string)$cells[$i] : '';
                $extra = (isset($fr['types'][$i]) && $fr['types'][$i] === 'currency') ? 4 : 2;
                if (strlen($val) + $extra > $max_lens[$i]) {
                    $max_lens[$i] = strlen($val) + $extra;
                }
            }
        }

        $cols_xml = '<cols>';
        for ($i = 0; $i < $col_count; $i++) {
            $w = min(50, max(6, $max_lens[$i] + 4));
            $idx = $i + 1;
            $cols_xml .= '<col min="' . $idx . '" max="' . $idx . '" width="' . $w . '" customWidth="1"/>';
        }
        $cols_xml .= '</cols>';

        $sheet_data = '<sheetData>';
        $merge_xml_items = [];
        $row_num = 1;

        // Baris 1: Header
        $sheet_data .= '<row r="' . $row_num . '" ht="24" customHeight="1">';
        foreach ($columns as $i => $col) {
            $ref = $this->_col_letter($i + 1) . $row_num;
            $sheet_data .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t xml:space="preserve">' . $this->_escape_xml($col['title']) . '</t></is></c>';
        }
        $sheet_data .= '</row>';
        $row_num++;

        // Baris Data
        foreach ($rows as $row) {
            $sheet_data .= '<row r="' . $row_num . '" ht="19" customHeight="1">';
            for ($i = 0; $i < $col_count; $i++) {
                $ref = $this->_col_letter($i + 1) . $row_num;
                $col = $columns[$i];
                $type = isset($col['type']) ? $col['type'] : 'text';
                $align = isset($col['align']) ? $col['align'] : 'left';
                $val = isset($row[$i]) ? $row[$i] : '';

                if ($type === 'int') {
                    $num = (int)$val;
                    $sheet_data .= '<c r="' . $ref . '" s="4" t="n"><v>' . $num . '</v></c>';
                } else if ($type === 'currency') {
                    $num = is_numeric($val) ? (float)$val : 0;
                    $sheet_data .= '<c r="' . $ref . '" s="5" t="n"><v>' . $num . '</v></c>';
                } else {
                    $style_id = ($align === 'center') ? 3 : 2;
                    $sheet_data .= '<c r="' . $ref . '" s="' . $style_id . '" t="inlineStr"><is><t xml:space="preserve">' . $this->_escape_xml($val) . '</t></is></c>';
                }
            }
            $sheet_data .= '</row>';
            $row_num++;
        }

        // Baris Footer / Trailer Kontrol Bank & Grand Total
        foreach ($footer_rows as $fr) {
            $cells = isset($fr['cells']) ? $fr['cells'] : [];
            $types = isset($fr['types']) ? $fr['types'] : [];
            $merges = isset($fr['merges']) ? $fr['merges'] : [];
            foreach ($merges as $m) {
                $c1 = $this->_col_letter((int)$m[0] + 1) . $row_num;
                $c2 = $this->_col_letter((int)$m[1] + 1) . $row_num;
                $merge_xml_items[] = '<mergeCell ref="' . $c1 . ':' . $c2 . '"/>';
            }

            $sheet_data .= '<row r="' . $row_num . '" ht="20" customHeight="1">';
            for ($i = 0; $i < $col_count; $i++) {
                $ref = $this->_col_letter($i + 1) . $row_num;
                $val = isset($cells[$i]) ? $cells[$i] : '';
                $t = isset($types[$i]) ? $types[$i] : ($i === 0 ? 'label' : ($val === '' ? 'empty' : 'text_right'));

                if ($t === 'currency' || $t === 'int') {
                    $num = is_numeric($val) ? (float)$val : 0;
                    $sheet_data .= '<c r="' . $ref . '" s="8" t="n"><v>' . $num . '</v></c>';
                } else if ($t === 'label') {
                    $sheet_data .= '<c r="' . $ref . '" s="6" t="inlineStr"><is><t xml:space="preserve">' . $this->_escape_xml($val) . '</t></is></c>';
                } else if ($t === 'text_right') {
                    $sheet_data .= '<c r="' . $ref . '" s="9" t="inlineStr"><is><t xml:space="preserve">' . $this->_escape_xml($val) . '</t></is></c>';
                } else {
                    $sheet_data .= '<c r="' . $ref . '" s="7" t="inlineStr"><is><t xml:space="preserve">' . $this->_escape_xml($val) . '</t></is></c>';
                }
            }
            $sheet_data .= '</row>';
            $row_num++;
        }

        $sheet_data .= '</sheetData>';

        $merge_section = '';
        if (!empty($merge_xml_items)) {
            $merge_section = '<mergeCells count="' . count($merge_xml_items) . '">' . implode('', $merge_xml_items) . '</mergeCells>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . $cols_xml
            . $sheet_data
            . $merge_section
            . '</worksheet>';
    }

    /**
     * Pure-PHP ZIP Store Builder (Method 0 - Uncompressed) sebagai fallback jika ekstensi zip tidak aktif
     */
    private function _build_zip_memory($files) {
        $data = '';
        $central = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $content) {
            $name_len = strlen($name);
            $len = strlen($content);
            $crc = crc32($content);

            // Local file header
            $local_hdr = "\x50\x4b\x03\x04"
                . "\x14\x00"        // version needed 2.0
                . "\x00\x00"        // general purpose bit flag
                . "\x00\x00"        // compression method (0 = store)
                . "\x00\x00\x00\x00"// mod time & date
                . pack('V', $crc)
                . pack('V', $len)
                . pack('V', $len)
                . pack('v', $name_len)
                . "\x00\x00";       // extra field len

            $data .= $local_hdr . $name . $content;

            // Central directory header
            $central .= "\x50\x4b\x01\x02"
                . "\x14\x00"        // version made by
                . "\x14\x00"        // version needed
                . "\x00\x00"        // flags
                . "\x00\x00"        // method store
                . "\x00\x00\x00\x00"// mod time & date
                . pack('V', $crc)
                . pack('V', $len)
                . pack('V', $len)
                . pack('v', $name_len)
                . "\x00\x00"        // extra len
                . "\x00\x00"        // comment len
                . "\x00\x00"        // disk start
                . "\x00\x00"        // internal attrs
                . "\x00\x00\x00\x00"// external attrs
                . pack('V', $offset)
                . $name;

            $offset += strlen($local_hdr) + $name_len + $len;
            $count++;
        }

        // End of central directory
        $eocd = "\x50\x4b\x05\x06"
            . "\x00\x00\x00\x00"
            . pack('v', $count)
            . pack('v', $count)
            . pack('V', strlen($central))
            . pack('V', strlen($data))
            . "\x00\x00";

        return $data . $central . $eocd;
    }
}
