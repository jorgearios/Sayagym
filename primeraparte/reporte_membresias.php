<?php
/**
 * Archivo: reporte_membresias.php
 * Descripción: Reporte detallado sobre membresías activas, vencidas y canceladas.
 * Parte del sistema integral de gestión Sayagym.
 */

// reporte_membresias.php — PDF de membresías activas o vencidas
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }

$tipo = $_GET['tipo'] ?? 'activas';
$hoy  = date('Y-m-d');

if ($tipo === 'activas') {
    $titulo   = 'MEMBRESÍAS ACTIVAS';
    $color_r  = 21; $color_g = 128; $color_b = 61;
    $socios   = $conexion->query("
        SELECT s.nombre, s.apellido, s.correo, s.telefono,
               s.fecha_vencimiento, m.nombre as plan,
               CONCAT(e.nombre) as entrenador,
               TIMESTAMPDIFF(DAY, '$hoy', s.fecha_vencimiento) as dias_restantes
        FROM socios s
        LEFT JOIN membresias  m ON s.id_membresia  = m.id_membresia
        LEFT JOIN entrenadores e ON s.id_entrenador = e.id_entrenador
        WHERE s.fecha_vencimiento >= '$hoy' AND s.estado = 'activo'
        ORDER BY s.fecha_vencimiento ASC
    ");
} else {
    $titulo   = 'MEMBRESÍAS VENCIDAS';
    $color_r  = 183; $color_g = 28; $color_b = 28;
    $socios   = $conexion->query("
        SELECT s.nombre, s.apellido, s.correo, s.telefono,
               s.fecha_vencimiento, m.nombre as plan,
               CONCAT(COALESCE(e.nombre,'—')) as entrenador,
               TIMESTAMPDIFF(DAY, s.fecha_vencimiento, '$hoy') as dias_vencida
        FROM socios s
        LEFT JOIN membresias   m ON s.id_membresia  = m.id_membresia
        LEFT JOIN entrenadores e ON s.id_entrenador = e.id_entrenador
        WHERE s.fecha_vencimiento < '$hoy' OR s.estado = 'inactivo'
        ORDER BY s.fecha_vencimiento DESC
    ");
}

require_once 'fpdf.php';

class ReporteMembresiaspdf extends FPDF {
    public $titulo_rep = '';
    public $cr = 127; public $cg = 0; public $cb = 0;

    function Header() {
        $this->SetFillColor($this->cr, $this->cg, $this->cb);
        $this->Rect(0,0,297,22,'F');
        $this->SetTextColor(255,255,255);
        $this->SetFont('Arial','B',16);
        $this->SetY(5);
        $this->Cell(0,7,'SAYAGYM — '.$this->titulo_rep,0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Sistema de Gestion Integral | Generado: '.date('d/m/Y H:i'),0,1,'C');
        $this->SetTextColor(0,0,0);
        $this->Ln(4);
    }
    function Footer() {
        $this->SetY(-14);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(150,150,150);
        $this->Cell(0,5,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new ReporteMembresiaspdf('L','mm','A4');
$pdf->titulo_rep = $titulo;
$pdf->cr = $color_r; $pdf->cg = $color_g; $pdf->cb = $color_b;
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10,28,10);

$total_socios = $socios->num_rows;
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Total de registros: '.$total_socios.' socios',0,1,'C');
$pdf->Ln(4);

// Cabecera tabla
$pdf->SetFillColor($color_r, $color_g, $color_b);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',8);

if ($tipo === 'activas') {
    $pdf->Cell(55,8,'Nombre completo',1,0,'C',true);
    $pdf->Cell(60,8,'Correo',1,0,'C',true);
    $pdf->Cell(28,8,'Teléfono',1,0,'C',true);
    $pdf->Cell(40,8,'Plan',1,0,'C',true);
    $pdf->Cell(40,8,'Entrenador',1,0,'C',true);
    $pdf->Cell(32,8,'Vence',1,0,'C',true);
    $pdf->Cell(22,8,'Días rest.',1,1,'C',true);
} else {
    $pdf->Cell(55,8,'Nombre completo',1,0,'C',true);
    $pdf->Cell(60,8,'Correo',1,0,'C',true);
    $pdf->Cell(28,8,'Teléfono',1,0,'C',true);
    $pdf->Cell(40,8,'Plan',1,0,'C',true);
    $pdf->Cell(40,8,'Entrenador',1,0,'C',true);
    $pdf->Cell(32,8,'Venció el',1,0,'C',true);
    $pdf->Cell(22,8,'Días venc.',1,1,'C',true);
}

$pdf->SetTextColor(40,40,40);
$pdf->SetFont('Arial','',8);
$fill = false;

while ($s = $socios->fetch_assoc()) {
    $fcolor = $fill ? 248 : 255;
    $pdf->SetFillColor($fcolor,$fcolor,$fcolor);
    $nombre = htmlspecialchars($s['nombre'].' '.$s['apellido']);
    if (strlen($nombre) > 28) $nombre = substr($nombre,0,25).'...';
    $correo = $s['correo'] ? $s['correo'] : '—';
    if (strlen($correo) > 30) $correo = substr($correo,0,27).'...';
    $plan = $s['plan'] ?: '—';
    $ent  = $s['entrenador'] ?: '—';
    $fecha = date('d/m/Y', strtotime($s['fecha_vencimiento']));

    if ($tipo === 'activas') {
        $dias = (int)$s['dias_restantes'];
        if ($dias <= 7) { $pdf->SetTextColor(183,28,28); }
        elseif ($dias <= 15) { $pdf->SetTextColor(180,100,0); }
        else { $pdf->SetTextColor(40,40,40); }
    } else {
        $dias = (int)$s['dias_vencida'];
        $pdf->SetTextColor(183,28,28);
    }

    $pdf->Cell(55,5,' '.$nombre,1,0,'L',$fill);
    $pdf->SetTextColor(40,40,40);
    $pdf->Cell(60,5,' '.$correo,1,0,'L',$fill);
    $pdf->Cell(28,5,' '.($s['telefono']??'—'),1,0,'L',$fill);
    $pdf->Cell(40,5,' '.$plan,1,0,'L',$fill);
    $pdf->Cell(40,5,' '.$ent,1,0,'L',$fill);
    $pdf->Cell(32,5,$fecha,1,0,'C',$fill);
    $pdf->Cell(22,5,$dias.' días',1,1,'C',$fill);
    $fill = !$fill;
}

// Fila de total
$pdf->SetFillColor($color_r, $color_g, $color_b);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(277,7,'  Total: '.$total_socios.' socios',1,1,'L',true);

$nombre_archivo = 'reporte_membresias_'.$tipo.'_'.date('Ymd').'.pdf';
$pdf->Output('I', $nombre_archivo);
exit;
