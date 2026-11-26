<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Simple_pdf
 * Generator PDF 1.4 Native Pure-PHP (Zero External Dependency, Kompatibel PHP 7.x s/d PHP 8.4+).
 * Menghasilkan PDF resmi presisi vektor dengan:
 * - Dukungan A5 Portrait, A4 Portrait, & A4 Landscape
 * - Tabel metrik lebar karakter Base-14 Helvetica (Regular, Bold, Italic) untuk alignment & word-wrap akurat
 * - Penyematan logo PNG/JPG melalui konversi RGB JPEG DCTDecode
 * - Cross-reference (xref) & offset byte PDF 1.4 standar
 */
class Simple_pdf {

    const PT_PER_MM = 2.8346456692913;

    private $pages = [];
    private $current_page = -1;
    private $page_w_pt = 419.53; // Default A5 Portrait (148mm)
    private $page_h_pt = 595.28; // Default A5 Portrait (210mm)

    private $font_family = 'F1'; // F1=Helvetica, F2=Helvetica-Bold, F3=Helvetica-Oblique
    private $font_size = 9.0;
    private $text_color_rgb = [0, 0, 0];
    private $fill_color_rgb = [1, 1, 1];
    private $draw_color_rgb = [0, 0, 0];
    private $line_width_pt = 0.5;

    private $images = [];
    private $char_widths = [];

    public function __construct() {
        $this->_init_font_widths();
    }

    /**
     * Memulai halaman baru (ukuran dalam mm atau preset 'A5' / 'A4')
     */
    public function add_page($paper = 'A5', $orientation = 'portrait') {
        $sizes = [
            'A5' => [148.0, 210.0],
            'A4' => [210.0, 297.0]
        ];
        $dim = isset($sizes[strtoupper($paper)]) ? $sizes[strtoupper($paper)] : $sizes['A5'];
        $w_mm = $dim[0];
        $h_mm = $dim[1];
        if (strtolower($orientation) === 'landscape') {
            $tmp = $w_mm;
            $w_mm = $h_mm;
            $h_mm = $tmp;
        }

        $w_pt = round($w_mm * self::PT_PER_MM, 2);
        $h_pt = round($h_mm * self::PT_PER_MM, 2);

        $this->page_w_pt = $w_pt;
        $this->page_h_pt = $h_pt;
        $this->current_page = count($this->pages);
        $this->pages[$this->current_page] = [
            'w_pt'   => $w_pt,
            'h_pt'   => $h_pt,
            'stream' => "2 J\n" // Cap square line end
        ];
    }

    public function get_page_width_mm() {
        return $this->page_w_pt / self::PT_PER_MM;
    }

    public function get_page_height_mm() {
        return $this->page_h_pt / self::PT_PER_MM;
    }

    /**
     * Set font aktif: style '' (Regular), 'B' (Bold), 'I' (Oblique/Italic)
     */
    public function set_font($style = '', $size_pt = 9.0) {
        $style = strtoupper(trim((string)$style));
        if ($style === 'B') {
            $this->font_family = 'F2';
        } elseif ($style === 'I') {
            $this->font_family = 'F3';
        } else {
            $this->font_family = 'F1';
        }
        $this->font_size = (float)$size_pt;
    }

    public function set_text_color($r, $g, $b) {
        $this->text_color_rgb = [round($r / 255, 3), round($g / 255, 3), round($b / 255, 3)];
    }

    public function set_fill_color($r, $g, $b) {
        $this->fill_color_rgb = [round($r / 255, 3), round($g / 255, 3), round($b / 255, 3)];
    }

    public function set_draw_color($r, $g, $b) {
        $this->draw_color_rgb = [round($r / 255, 3), round($g / 255, 3), round($b / 255, 3)];
    }

    public function set_line_width_pt($pt) {
        $this->line_width_pt = max(0.1, (float)$pt);
    }

    /**
     * Gambar garis lurus (koordinat top-down mm)
     */
    public function line($x1_mm, $y1_mm, $x2_mm, $y2_mm, $width_pt = null) {
        if ($this->current_page < 0) return;
        $lw = $width_pt !== null ? (float)$width_pt : $this->line_width_pt;
        $x1 = $x1_mm * self::PT_PER_MM;
        $y1 = $this->page_h_pt - ($y1_mm * self::PT_PER_MM);
        $x2 = $x2_mm * self::PT_PER_MM;
        $y2 = $this->page_h_pt - ($y2_mm * self::PT_PER_MM);

        $dc = $this->draw_color_rgb;
        $cmd = sprintf(
            "%.3f %.3f %.3f RG %.2f w %.2f %.2f m %.2f %.2f l S\n",
            $dc[0], $dc[1], $dc[2], $lw, $x1, $y1, $x2, $y2
        );
        $this->pages[$this->current_page]['stream'] .= $cmd;
    }

    /**
     * Gambar persegi panjang (border / fill / keduanya, koordinat top-down mm)
     * $mode: 'D' (border saja), 'F' (fill saja), 'DF' (fill + border)
     */
    public function rect($x_mm, $y_mm, $w_mm, $h_mm, $mode = 'D', $width_pt = null) {
        if ($this->current_page < 0) return;
        $lw = $width_pt !== null ? (float)$width_pt : $this->line_width_pt;
        $x = $x_mm * self::PT_PER_MM;
        $w = $w_mm * self::PT_PER_MM;
        $h = $h_mm * self::PT_PER_MM;
        $y_bottom = $this->page_h_pt - (($y_mm + $h_mm) * self::PT_PER_MM);

        $fc = $this->fill_color_rgb;
        $dc = $this->draw_color_rgb;

        $op = 'S';
        if ($mode === 'F') $op = 'f';
        elseif ($mode === 'DF' || $mode === 'FD') $op = 'B';

        $cmd = sprintf(
            "%.3f %.3f %.3f rg %.3f %.3f %.3f RG %.2f w %.2f %.2f %.2f %.2f re %s\n",
            $fc[0], $fc[1], $fc[2], $dc[0], $dc[1], $dc[2], $lw, $x, $y_bottom, $w, $h, $op
        );
        $this->pages[$this->current_page]['stream'] .= $cmd;
    }

    /**
     * Menghitung lebar string dalam satuan mm sesuai font aktif
     */
    public function get_string_width_mm($text) {
        $cp1252 = $this->_to_cp1252($text);
        $table = $this->char_widths[$this->font_family];
        $len = strlen($cp1252);
        $w_units = 0;
        for ($i = 0; $i < $len; $i++) {
            $ord = ord($cp1252[$i]);
            $w_units += isset($table[$ord]) ? $table[$ord] : 556;
        }
        $w_pt = $w_units * ($this->font_size / 1000.0);
        return $w_pt / self::PT_PER_MM;
    }

    /**
     * Memecah teks menjadi beberapa baris sesuai max lebar (mm)
     */
    public function wrap_lines($text, $max_width_mm) {
        $text = str_replace(["\r\n", "\r"], "\n", (string)$text);
        $paragraphs = explode("\n", $text);
        $lines = [];

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') {
                $lines[] = '';
                continue;
            }
            $words = preg_split('/\s+/', $para);
            $current = '';
            foreach ($words as $word) {
                $candidate = ($current === '') ? $word : ($current . ' ' . $word);
                if ($this->get_string_width_mm($candidate) <= $max_width_mm) {
                    $current = $candidate;
                } else {
                    if ($current !== '') {
                        $lines[] = $current;
                        $current = '';
                    }
                    // Jika satu kata lebih lebar dari lebar kolom, pecah per karakter
                    if ($this->get_string_width_mm($word) > $max_width_mm) {
                        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
                        $chunk = '';
                        foreach ($chars as $ch) {
                            if ($this->get_string_width_mm($chunk . $ch) <= $max_width_mm) {
                                $chunk .= $ch;
                            } else {
                                if ($chunk !== '') $lines[] = $chunk;
                                $chunk = $ch;
                            }
                        }
                        $current = $chunk;
                    } else {
                        $current = $word;
                    }
                }
            }
            if ($current !== '') {
                $lines[] = $current;
            }
        }
        return empty($lines) ? [''] : $lines;
    }

    /**
     * Tulis teks satu baris dalam kotak (koordinat top-down mm)
     * $align: 'L', 'C', 'R'; $underline: true/false
     */
    public function cell($x_mm, $y_mm, $w_mm, $h_mm, $text, $align = 'L', $pad_x_mm = 1.2, $underline = false) {
        if ($this->current_page < 0) return;

        $txt_w = $this->get_string_width_mm($text);
        if ($align === 'R') {
            $tx_mm = $x_mm + $w_mm - $pad_x_mm - $txt_w;
        } elseif ($align === 'C') {
            $tx_mm = $x_mm + max(0, ($w_mm - $txt_w) / 2.0);
        } else {
            $tx_mm = $x_mm + $pad_x_mm;
        }

        // Baseline berada sedikit di bawah titik tengah vertikal kotak
        $y_center_pt = ($y_mm + ($h_mm / 2.0)) * self::PT_PER_MM;
        $baseline_y_pt = $this->page_h_pt - ($y_center_pt + ($this->font_size * 0.34));
        $tx_pt = $tx_mm * self::PT_PER_MM;

        $tc = $this->text_color_rgb;
        $escaped = $this->_pdf_escape($this->_to_cp1252($text));

        $cmd = sprintf(
            "BT /%s %.2f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET\n",
            $this->font_family, $this->font_size, $tc[0], $tc[1], $tc[2], $tx_pt, $baseline_y_pt, $escaped
        );
        $this->pages[$this->current_page]['stream'] .= $cmd;

        if ($underline && $txt_w > 0) {
            $uy_mm = ($this->page_h_pt - $baseline_y_pt) / self::PT_PER_MM + 0.65;
            $this->line($tx_mm, $uy_mm, $tx_mm + $txt_w, $uy_mm, 0.55);
        }
    }

    /**
     * Tulis teks multi-baris di dalam area (koordinat top-down mm)
     */
    public function multi_text($x_mm, $y_mm, $w_mm, $line_h_mm, $text, $align = 'L', $pad_x_mm = 1.2) {
        $usable_w = max(4, $w_mm - ($pad_x_mm * 2));
        $lines = $this->wrap_lines($text, $usable_w);
        $cur_y = $y_mm;
        foreach ($lines as $ln) {
            $this->cell($x_mm, $cur_y, $w_mm, $line_h_mm, $ln, $align, $pad_x_mm);
            $cur_y += $line_h_mm;
        }
        return count($lines);
    }

    /**
     * Menyematkan gambar PNG/JPEG ke dalam halaman PDF melalui buffer JPEG (/DCTDecode)
     */
    public function add_image_file($filepath, $x_mm, $y_mm, $w_mm, $h_mm) {
        if ($this->current_page < 0 || !file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }

        $key = md5($filepath);
        if (!isset($this->images[$key])) {
            $raw = @file_get_contents($filepath);
            if ($raw === false) return false;
            $src = @imagecreatefromstring($raw);
            if (!$src) return false;

            $px_w = imagesx($src);
            $px_h = imagesy($src);
            $canvas = imagecreatetruecolor($px_w, $px_h);
            imagealphablending($canvas, true);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            imagecopy($canvas, $src, 0, 0, 0, 0, $px_w, $px_h);
            imagedestroy($src);

            ob_start();
            imagejpeg($canvas, null, 92);
            $jpg_bin = ob_get_clean();
            imagedestroy($canvas);

            if (empty($jpg_bin)) return false;

            $img_name = 'Im' . (count($this->images) + 1);
            $this->images[$key] = [
                'name' => $img_name,
                'w_px' => $px_w,
                'h_px' => $px_h,
                'data' => $jpg_bin
            ];
        }

        $img = $this->images[$key];
        $x_pt = $x_mm * self::PT_PER_MM;
        $w_pt = $w_mm * self::PT_PER_MM;
        $h_pt = $h_mm * self::PT_PER_MM;
        $y_bottom_pt = $this->page_h_pt - (($y_mm + $h_mm) * self::PT_PER_MM);

        $cmd = sprintf(
            "q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q\n",
            $w_pt, $h_pt, $x_pt, $y_bottom_pt, $img['name']
        );
        $this->pages[$this->current_page]['stream'] .= $cmd;
        return true;
    }

    /**
     * Merakit binary lengkap dokumen PDF 1.4
     */
    public function output($title = 'Dokumen SDMIS') {
        if (empty($this->pages)) {
            $this->add_page('A5', 'portrait');
        }

        $objects = [];
        $obj_id = 1;

        // 1: Catalog, 2: Pages (diisi akhir)
        $catalog_id = $obj_id++;
        $pages_id = $obj_id++;

        // 3, 4, 5: Font Base-14
        $font1_id = $obj_id++;
        $font2_id = $obj_id++;
        $font3_id = $obj_id++;

        $objects[$font1_id] = "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n/Encoding /WinAnsiEncoding\n>>";
        $objects[$font2_id] = "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica-Bold\n/Encoding /WinAnsiEncoding\n>>";
        $objects[$font3_id] = "<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica-Oblique\n/Encoding /WinAnsiEncoding\n>>";

        // Image XObjects
        $xobj_dict_parts = [];
        foreach ($this->images as $img) {
            $img_id = $obj_id++;
            $len = strlen($img['data']);
            $objects[$img_id] = "<<\n/Type /XObject\n/Subtype /Image\n/Width {$img['w_px']}\n/Height {$img['h_px']}\n/ColorSpace /DeviceRGB\n/BitsPerComponent 8\n/Filter /DCTDecode\n/Length {$len}\n>>\nstream\n{$img['data']}\nendstream";
            $xobj_dict_parts[] = "/{$img['name']} {$img_id} 0 R";
        }

        $xobj_res = !empty($xobj_dict_parts) ? "/XObject << " . implode(' ', $xobj_dict_parts) . " >>\n" : "";

        // Page Objects + Content Stream Objects
        $page_kids = [];
        foreach ($this->pages as $pg) {
            $content_id = $obj_id++;
            $page_id = $obj_id++;
            $stream = $pg['stream'];
            $slen = strlen($stream);

            $objects[$content_id] = "<<\n/Length {$slen}\n>>\nstream\n{$stream}\nendstream";
            $objects[$page_id] = "<<\n/Type /Page\n/Parent {$pages_id} 0 R\n/MediaBox [0 0 " . sprintf('%.2f %.2f', $pg['w_pt'], $pg['h_pt']) . "]\n/Resources <<\n/Font << /F1 {$font1_id} 0 R /F2 {$font2_id} 0 R /F3 {$font3_id} 0 R >>\n{$xobj_res}>>\n/Contents {$content_id} 0 R\n>>";
            $page_kids[] = "{$page_id} 0 R";
        }

        // Info Object
        $info_id = $obj_id++;
        $safe_title = $this->_pdf_escape($this->_to_cp1252($title));
        $objects[$info_id] = "<<\n/Title ({$safe_title})\n/Producer (SDMIS Payroll System)\n>>";

        $page_count = count($page_kids);
        $kids_str = implode(' ', $page_kids);
        $objects[$pages_id] = "<<\n/Type /Pages\n/Kids [{$kids_str}]\n/Count {$page_count}\n>>";
        $objects[$catalog_id] = "<<\n/Type /Catalog\n/Pages {$pages_id} 0 R\n>>";

        ksort($objects);

        // Build final PDF binary & accurate xref table
        $pdf = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $offsets = [0 => 0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xref_offset = strlen($pdf);
        $total_objs = count($objects) + 1;
        $pdf .= "xref\n0 {$total_objs}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $total_objs; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<<\n/Size {$total_objs}\n/Root {$catalog_id} 0 R\n/Info {$info_id} 0 R\n>>\n";
        $pdf .= "startxref\n{$xref_offset}\n%%EOF\n";

        return $pdf;
    }

    /**
     * Stream langsung PDF sebagai attachment download ke browser
     */
    public function download($filename, $title = 'Dokumen SDMIS') {
        $binary = $this->output($title);

        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($binary));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $binary;
        exit();
    }

    private function _to_cp1252($str) {
        $s = (string)$str;
        $s = str_replace(
            ['—', '–', '•', '≤', '≥', '“', '”', '‘', '’', '&bull;', '&amp;', '&le;', '&ge;', '&mdash;'],
            ['-', '-', '-', '<=', '>=', '"', '"', "'", "'", '-', '&', '<=', '>=', '-'],
            $s
        );
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
            if ($conv !== false) {
                return $conv;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            $conv = @mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
            if ($conv !== false) {
                return $conv;
            }
        }
        return preg_replace('/[^\x20-\x7E]/', '?', $s);
    }

    private function _pdf_escape($str) {
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], (string)$str);
    }

    /**
     * Tabel lebar karakter standar Adobe Helvetica Regular, Bold, Oblique (1/1000 em)
     */
    private function _init_font_widths() {
        $reg = [
            32=>278,33=>278,34=>355,35=>556,36=>556,37=>889,38=>667,39=>191,40=>333,41=>333,
            42=>389,43=>584,44=>278,45=>333,46=>278,47=>278,48=>556,49=>556,50=>556,51=>556,
            52=>556,53=>556,54=>556,55=>556,56=>556,57=>556,58=>278,59=>278,60=>584,61=>584,
            62=>584,63=>556,64=>1015,65=>667,66=>667,67=>722,68=>722,69=>667,70=>611,71=>778,
            72=>722,73=>278,74=>500,75=>667,76=>556,77=>833,78=>722,79=>778,80=>667,81=>778,
            82=>722,83=>667,84=>611,85=>722,86=>667,87=>944,88=>667,89=>667,90=>611,91=>278,
            92=>278,93=>278,94=>469,95=>556,96=>333,97=>556,98=>556,99=>500,100=>556,101=>556,
            102=>278,103=>556,104=>556,105=>222,106=>222,107=>500,108=>222,109=>833,110=>556,
            111=>556,112=>556,113=>556,114=>333,115=>500,116=>278,117=>556,118=>500,119=>722,
            120=>500,121=>500,122=>500,123=>334,124=>260,125=>334,126=>584
        ];

        $bold = [
            32=>278,33=>333,34=>474,35=>556,36=>556,37=>889,38=>722,39=>238,40=>333,41=>333,
            42=>389,43=>584,44=>278,45=>333,46=>278,47=>278,48=>556,49=>556,50=>556,51=>556,
            52=>556,53=>556,54=>556,55=>556,56=>556,57=>556,58=>333,59=>333,60=>584,61=>584,
            62=>584,63=>611,64=>975,65=>722,66=>722,67=>722,68=>722,69=>667,70=>611,71=>778,
            72=>722,73=>278,74=>556,75=>722,76=>611,77=>833,78=>722,79=>778,80=>667,81=>778,
            82=>722,83=>667,84=>611,85=>722,86=>667,87=>944,88=>667,89=>667,90=>611,91=>333,
            92=>278,93=>333,94=>584,95=>556,96=>333,97=>556,98=>611,99=>556,100=>611,101=>556,
            102=>333,103=>611,104=>611,105=>278,106=>278,107=>556,108=>278,109=>889,110=>611,
            111=>611,112=>611,113=>611,114=>389,115=>556,116=>333,117=>611,118=>556,119=>778,
            120=>556,121=>556,122=>500,123=>389,124=>280,125=>389,126=>584
        ];

        $this->char_widths = [
            'F1' => $reg,
            'F2' => $bold,
            'F3' => $reg
        ];
    }
}
