<?php

namespace App\Services;

class ReporteGraficosService
{
    private $fontBold;
    private $fontRegular;

    public function __construct()
    {
        $this->fontBold = public_path('assets/fonts/Montserrat/static/Montserrat-Bold.ttf');
        $this->fontRegular = public_path('assets/fonts/Montserrat/static/Montserrat-Regular.ttf');

        // Fallback si no existe la ruta estática
        if (!file_exists($this->fontBold)) {
            $this->fontBold = public_path('build/assets/Omnes Bold-CieqamCH.ttf');
        }
        if (!file_exists($this->fontRegular)) {
            $this->fontRegular = $this->fontBold;
        }
    }

    /**
     * Dibuja texto usando TTF si está disponible, o imagestring como fallback
     */
    private function drawText($im, $size, $angle, $x, $y, $color, $text, $bold = false)
    {
        $font = $bold ? $this->fontBold : $this->fontRegular;
        if (function_exists('imagettftext') && file_exists($font)) {
            imagettftext($im, $size, $angle, $x, $y, $color, $font, $text);
        } else {
            // Fallback con fuentes GD internas (1 a 5)
            $gdFont = $bold ? 4 : 2;
            imagestring($im, $gdFont, $x, $y - 12, $text, $color);
        }
    }

    /**
     * Mide el ancho del texto en píxeles
     */
    private function getTextWidth($size, $text, $bold = false)
    {
        $font = $bold ? $this->fontBold : $this->fontRegular;
        if (function_exists('imagettfbbox') && file_exists($font)) {
            $bbox = imagettfbbox($size, 0, $font, $text);
            return abs($bbox[4] - $bbox[0]);
        }
        return strlen($text) * ($size * 0.7);
    }

    /**
     * Genera el gráfico del Dashboard General (Dona de participación + Barras horizontales por tipo)
     * Recrea el gráfico del Dashboard de la página 1 del informe oficial.
     */
    public function generarGraficoDashboard($totalHabilitados, $totalVotos, $distribucionTipos)
    {
        $w = 800;
        $h = 320;
        $im = imagecreatetruecolor($w, $h);
        if (function_exists('imageantialias')) {
            imageantialias($im, true);
        }

        // Colores temáticos oscuros estilo dashboard institucional
        $bgMain = imagecolorallocate($im, 45, 55, 72);       // #2d3748
        $cardBg = imagecolorallocate($im, 30, 41, 59);       // #1e293b
        $cardBorder = imagecolorallocate($im, 71, 85, 105);  // #475569
        $textWhite = imagecolorallocate($im, 255, 255, 255);
        $textMuted = imagecolorallocate($im, 203, 213, 225);
        $blueSlice = imagecolorallocate($im, 59, 130, 246);  // #3b82f6
        $graySlice = imagecolorallocate($im, 100, 116, 139); // #64748b
        $barColor  = imagecolorallocate($im, 59, 130, 246);  // #3b82f6 azul

        imagefill($im, 0, 0, $bgMain);

        // Sub-panel izquierdo: Nivel de votaciones (Dona)
        $panelLeftW = 340;
        $panelLeftH = 290;
        imagefilledrectangle($im, 15, 15, 15 + $panelLeftW, 15 + $panelLeftH, $cardBg);
        imagerectangle($im, 15, 15, 15 + $panelLeftW, 15 + $panelLeftH, $cardBorder);

        $this->drawText($im, 11, 0, 95, 45, $textWhite, "Nivel de votaciones", true);
        $subH = "Total Votantes habilitados - " . number_format($totalHabilitados, 0, ',', '.');
        $this->drawText($im, 8.5, 0, 60, 65, $textMuted, $subH, false);

        // Dibujar Dona
        $centerX = 185;
        $centerY = 175;
        $outerR = 170;
        $innerR = 105;

        $pct = $totalHabilitados > 0 ? ($totalVotos / $totalHabilitados) : 0;
        $angle = round($pct * 360);

        // Fondo de la dona (gris)
        imagefilledarc($im, $centerX, $centerY, $outerR, $outerR, 0, 360, $graySlice, IMG_ARC_PIE);
        // Parte votada (azul)
        if ($angle > 0) {
            imagefilledarc($im, $centerX, $centerY, $outerR, $outerR, 270, (270 + $angle) % 360, $blueSlice, IMG_ARC_PIE);
        }
        // Centro hueco
        imagefilledarc($im, $centerX, $centerY, $innerR, $innerR, 0, 360, $cardBg, IMG_ARC_PIE);

        // Texto en el centro de la dona
        $strVotos = (string)$totalVotos;
        $wVotos = $this->getTextWidth(20, $strVotos, true);
        $this->drawText($im, 20, 0, $centerX - ($wVotos / 2), $centerY + 8, $textWhite, $strVotos, true);

        // Sub-panel derecho: Total votos según tipo (Barras Horizontales)
        $panelRightX = 370;
        $panelRightW = 415;
        $panelRightH = 290;
        imagefilledrectangle($im, $panelRightX, 15, $panelRightX + $panelRightW, 15 + $panelRightH, $cardBg);
        imagerectangle($im, $panelRightX, 15, $panelRightX + $panelRightW, 15 + $panelRightH, $cardBorder);

        $this->drawText($im, 11, 0, $panelRightX + 110, 45, $textWhite, "Total votos según tipo", true);

        // Calcular máximo para escala de barras
        $maxVotos = 1;
        foreach ($distribucionTipos as $dt) {
            if ($dt['votos'] > $maxVotos) $maxVotos = $dt['votos'];
        }

        $startY = 85;
        $barMaxW = 230;
        $count = count($distribucionTipos);
        $spacing = $count > 0 ? min(55, floor(170 / $count)) : 50;

        foreach ($distribucionTipos as $i => $dt) {
            $y = $startY + ($i * $spacing);
            
            // Label del tipo (acortado si es muy largo)
            $label = $dt['tipo'];
            if (strlen($label) > 16) {
                $label = substr($label, 0, 15) . '..';
            }
            $this->drawText($im, 8.5, 0, $panelRightX + 15, $y + 14, $textMuted, $label, false);

            // Barra horizontal
            $barW = round(($dt['votos'] / $maxVotos) * $barMaxW);
            if ($barW < 2 && $dt['votos'] > 0) $barW = 2;
            $barX = $panelRightX + 125;

            // Fondo de la barra
            imagefilledrectangle($im, $barX, $y, $barX + $barMaxW, $y + 20, imagecolorallocate($im, 51, 65, 85));
            // Relleno de la barra
            if ($barW > 0) {
                imagefilledrectangle($im, $barX, $y, $barX + $barW, $y + 20, $barColor);
            }

            // Número dentro o fuera de la barra
            $valStr = (string)$dt['votos'];
            $textValColor = $barW > 30 ? $textWhite : imagecolorallocate($im, 147, 197, 253);
            $posX = $barW > 30 ? ($barX + ($barW / 2) - 8) : ($barX + $barW + 8);
            $this->drawText($im, 8.5, 0, $posX, $y + 14, $textValColor, $valStr, true);
        }

        return $this->salvarImagen($im, 'dashboard_general');
    }

    /**
     * Genera el gráfico para una categoría:
     * - Izquierda: Gráfico de barras de resultados de candidatos.
     * - Derecha: Gráfico de dona con votos realizados vs. abstención.
     * Recrea los gráficos de las páginas 2 y 3 del informe oficial.
     */
    public function generarGraficoCategoria($tipoNombre, $votosRealizados, $abstencion, $candidatos)
    {
        $w = 800;
        $h = 320;
        $im = imagecreatetruecolor($w, $h);
        if (function_exists('imageantialias')) {
            imageantialias($im, true);
        }

        // Colores temáticos limpios (fondo claro como en el reporte de categoría)
        $bgMain = imagecolorallocate($im, 255, 255, 255);
        $cardBg = imagecolorallocate($im, 248, 250, 252);     // #f8fafc
        $cardBorder = imagecolorallocate($im, 226, 232, 240); // #e2e8f0
        $textDark = imagecolorallocate($im, 15, 23, 42);      // #0f172a
        $textMuted = imagecolorallocate($im, 100, 116, 139);  // #64748b
        $redBar = imagecolorallocate($im, 226, 6, 19);        // #e20613 Rojo institucional
        $slateBar = imagecolorallocate($im, 148, 163, 184);   // #94a3b8 Voto en blanco
        $greenBar = imagecolorallocate($im, 16, 185, 129);    // #10b981 Ganador
        $blueSlice = imagecolorallocate($im, 0, 72, 132);     // #004884 Azul Pereira
        $graySlice = imagecolorallocate($im, 100, 116, 139);  // #64748b Abstención

        imagefill($im, 0, 0, $bgMain);

        // Panel Izquierdo: Resultado Votaciones (Barras de candidatos)
        $leftW = 430;
        $leftH = 290;
        imagefilledrectangle($im, 15, 15, 15 + $leftW, 15 + $leftH, $cardBg);
        imagerectangle($im, 15, 15, 15 + $leftW, 15 + $leftH, $cardBorder);

        $this->drawText($im, 11, 0, 130, 42, $textDark, "Resultado Votaciones", true);

        // Escala y dibujo de barras de candidatos
        $maxVotos = 1;
        foreach ($candidatos as $c) {
            if ($c['votos'] > $maxVotos) $maxVotos = $c['votos'];
        }

        $numCand = min(count($candidatos), 6);
        $candSlice = array_slice($candidatos, 0, 6);

        if ($numCand > 0) {
            $chartBottomY = 240;
            $chartTopY = 75;
            $chartH = $chartBottomY - $chartTopY;
            $slotW = floor(($leftW - 50) / $numCand);
            $barW = min(36, floor($slotW * 0.65));

            // Eje horizontal base
            imageline($im, 30, $chartBottomY, 15 + $leftW - 20, $chartBottomY, $cardBorder);

            foreach ($candSlice as $idx => $cand) {
                $slotCenterX = 45 + ($idx * $slotW) + ($slotW / 2);
                $barX1 = $slotCenterX - ($barW / 2);
                $barX2 = $barX1 + $barW;

                $barH = round(($cand['votos'] / $maxVotos) * $chartH);
                $barY1 = $chartBottomY - $barH;
                $barY2 = $chartBottomY;

                // Color de barra
                $color = $redBar;
                if (!empty($cand['es_ganador'])) {
                    $color = $greenBar;
                } elseif (!empty($cand['es_blanco'])) {
                    $color = $slateBar;
                }

                if ($barH > 0) {
                    imagefilledrectangle($im, $barX1, $barY1, $barX2, $barY2, $color);
                }

                // Número de votos sobre la barra
                $vStr = (string)$cand['votos'];
                $vw = $this->getTextWidth(8, $vStr, true);
                $this->drawText($im, 8, 0, $slotCenterX - ($vw / 2), max($chartTopY + 10, $barY1 - 5), $textDark, $vStr, true);

                // Nombre debajo de la barra (inclinado o abreviado)
                $nParts = explode(' ', trim($cand['nombre']));
                $shortName = $nParts[0];
                if (count($nParts) > 1) {
                    $shortName .= ' ' . substr($nParts[1], 0, 1) . '.';
                }
                if ($cand['es_blanco']) {
                    $shortName = 'Blanco';
                }
                $nw = $this->getTextWidth(7, $shortName, false);
                $this->drawText($im, 7, 0, $slotCenterX - ($nw / 2), $chartBottomY + 16, $textMuted, $shortName, false);
            }
        } else {
            $this->drawText($im, 9, 0, 120, 150, $textMuted, "Sin candidatos registrados", false);
        }

        // Panel Derecho: Nivel de votaciones (Dona)
        $rightX = 460;
        $rightW = 325;
        $rightH = 290;
        imagefilledrectangle($im, $rightX, 15, $rightX + $rightW, 15 + $rightH, $cardBg);
        imagerectangle($im, $rightX, 15, $rightX + $rightW, 15 + $rightH, $cardBorder);

        $this->drawText($im, 11, 0, $rightX + 90, 42, $textDark, "Nivel de votaciones", true);

        // Leyenda
        $leyendaY = 65;
        imagefilledrectangle($im, $rightX + 35, $leyendaY - 8, $rightX + 45, $leyendaY + 2, $blueSlice);
        $this->drawText($im, 7.5, 0, $rightX + 50, $leyendaY, $textMuted, "Votos: " . $votosRealizados, false);

        imagefilledrectangle($im, $rightX + 175, $leyendaY - 8, $rightX + 185, $leyendaY + 2, $graySlice);
        $this->drawText($im, 7.5, 0, $rightX + 190, $leyendaY, $textMuted, "Abstención: " . $abstencion, false);

        // Dona de la categoría
        $totalCat = $votosRealizados + $abstencion;
        $pctCat = $totalCat > 0 ? ($votosRealizados / $totalCat) : 0;
        $angleCat = round($pctCat * 360);

        $dCenterX = $rightX + ($rightW / 2);
        $dCenterY = 180;
        $dOuterR = 170;
        $dInnerR = 105;

        imagefilledarc($im, $dCenterX, $dCenterY, $dOuterR, $dOuterR, 0, 360, $graySlice, IMG_ARC_PIE);
        if ($angleCat > 0) {
            imagefilledarc($im, $dCenterX, $dCenterY, $dOuterR, $dOuterR, 270, (270 + $angleCat) % 360, $blueSlice, IMG_ARC_PIE);
        }
        imagefilledarc($im, $dCenterX, $dCenterY, $dInnerR, $dInnerR, 0, 360, $cardBg, IMG_ARC_PIE);

        // Centro con votos
        $vStrCat = (string)$votosRealizados;
        $wVC = $this->getTextWidth(18, $vStrCat, true);
        $this->drawText($im, 18, 0, $dCenterX - ($wVC / 2), $dCenterY + 7, $textDark, $vStrCat, true);

        $slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($tipoNombre));
        return $this->salvarImagen($im, 'cat_' . $slug);
    }

    /**
     * Guarda la imagen en disco temporal y retorna ruta física y base64 URI
     */
    private function salvarImagen($im, $prefix)
    {
        $dir = storage_path('app/public/reportes_temp');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $prefix . '_' . uniqid() . '.png';
        $fullPath = $dir . '/' . $filename;
        imagepng($im, $fullPath);

        // Generar base64
        ob_start();
        imagepng($im);
        $raw = ob_get_clean();
        imagedestroy($im);

        $base64 = 'data:image/png;base64,' . base64_encode($raw);

        return [
            'path' => $fullPath,
            'base64' => $base64,
        ];
    }
}
