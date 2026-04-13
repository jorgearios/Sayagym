<?php
/**
 * Archivo: reporte_ingresos.php
 * Descripción: Generación de reportes tabulares y gráficos de los ingresos del gimnasio.
 * Parte del sistema integral de gestión Sayagym.
 */

// reporte_ingresos.php — PDF de ingresos por rango de fechas
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }

$desde = $conexion->real_escape_string($_GET['desde'] ?? date('Y-m-01'));
$hasta = $conexion->real_escape_string($_GET['hasta'] ?? date('Y-m-d'));

// Ventas del período
$ventas = $conexion->query("
    SELECT v.id_venta, v.fecha, v.total, v.subtotal, v.descuento, v.metodo_pago,
           CONCAT(COALESCE(s.nombre,''),' ',COALESCE(s.apellido,'')) as cliente,
           u.nombre_completo as cajero
    FROM ventas v
    LEFT JOIN socios   s ON v.id_socio   = s.id_socio
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE DATE(v.fecha) BETWEEN '$desde' AND '$hasta'
    ORDER BY v.fecha ASC
");

$resumen = $conexion->query("SELECT COUNT(*) as n,
    COALESCE(SUM(total),0) as total,
    COALESCE(SUM(subtotal),0) as subtotal,
    COALESCE(SUM(descuento),0) as descuentos
    FROM ventas WHERE DATE(fecha) BETWEEN '$desde' AND '$hasta'")->fetch_assoc();

// Por método de pago
$por_metodo = $conexion->query("SELECT metodo_pago, COUNT(*) as n, SUM(total) as t
    FROM ventas WHERE DATE(fecha) BETWEEN '$desde' AND '$hasta'
    GROUP BY metodo_pago ORDER BY t DESC");

// Productos más vendidos
$top_prod = $conexion->query("SELECT p.nombre, SUM(vd.cantidad) as total_qty, SUM(vd.subtotal) as total_monto
    FROM venta_detalle vd
    JOIN ventas   v ON vd.id_venta    = v.id_venta
    JOIN productos p ON vd.id_producto = p.id_producto
    WHERE DATE(v.fecha) BETWEEN '$desde' AND '$hasta'
    GROUP BY p.id_producto ORDER BY total_qty DESC LIMIT 8");

require_once 'fpdf.php';

class ReporteIngresospdf extends FPDF {
    function Header() {
        $this->SetFillColor(127,0,0);
        $this->Rect(0,0,210,22,'F');
        $this->SetTextColor(255,255,255);
        $this->SetFont('Arial','B',16);
        $this->SetY(5);
        $this->Cell(0,7,'SAYAGYM - REPORTE DE INGRESOS',0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Sistema de Gestion Integral',0,1,'C');
        $this->SetTextColor(0,0,0);
        $this->Ln(4);
    }
    function Footer() {
        $this->SetY(-14);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(150,150,150);
        $this->Cell(0,5,'Generado el '.date('d/m/Y H:i').' | Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
    function SectionTitle($title) {
        $this->SetFillColor(245,245,245);
        $this->SetFont('Arial','B',10);
        $this->SetTextColor(127,0,0);
        $this->Cell(0,7,' '.$title,0,1,'L',true);
        $this->SetTextColor(0,0,0);
        $this->Ln(1);
    }
}

$pdf = new ReporteIngresospdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15,28,15);
$pdf->SetFont('Arial','',10);

// ── Período ───────────────────────────────────────────────
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(80,80,80);
$pdf->Cell(0,6,'Período: '.date('d/m/Y',strtotime($desde)).' al '.date('d/m/Y',strtotime($hasta)),0,1,'C');
$pdf->Ln(4);

// ── Resumen ───────────────────────────────────────────────
$pdf->SectionTitle('Resumen del Período');
$pdf->SetFont('Arial','',10);

$col = 57;
$pdf->SetFillColor(220,252,231);
$pdf->SetFont('Arial','B',12);
$pdf->Cell($col,12,'  Ventas',1,0,'L',true);
$pdf->Cell($col,12,'  Ingresos brutos',1,0,'L',true);
$pdf->Cell($col,12,'  Descuentos',1,1,'L',true);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(21,128,61);
$pdf->Cell($col,10,'  '.$resumen['n'],1,0,'L');
$pdf->SetTextColor(0,0,0);
$pdf->Cell($col,10,'  $'.number_format($resumen['total'],2),1,0,'L');
$pdf->SetTextColor(183,28,28);
$pdf->Cell($col,10,'  $'.number_format($resumen['descuentos'],2),1,1,'L');
$pdf->SetTextColor(0,0,0);
$pdf->Ln(6);

// ── Por método de pago ────────────────────────────────────
$pdf->SectionTitle('Ingresos por Método de Pago');
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(250,250,250);
$pdf->Cell(90,7,'Método',1,0,'C',true);
$pdf->Cell(45,7,'N° Ventas',1,0,'C',true);
$pdf->Cell(45,7,'Total',1,1,'C',true);
$pdf->SetFont('Arial','',9);
$fill = false;
while ($m = $por_metodo->fetch_assoc()) {
    $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
    $pdf->Cell(90,6,' '.$m['metodo_pago'],1,0,'L',$fill);
    $pdf->Cell(45,6,$m['n'],1,0,'C',$fill);
    $pdf->Cell(45,6,'$'.number_format($m['t'],2),1,1,'R',$fill);
    $fill = !$fill;
}
$pdf->Ln(6);

// ── Productos más vendidos ────────────────────────────────
$pdf->SectionTitle('Productos Más Vendidos');
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(250,250,250);
$pdf->Cell(90,7,'Producto',1,0,'C',true);
$pdf->Cell(45,7,'Unidades',1,0,'C',true);
$pdf->Cell(45,7,'Monto',1,1,'C',true);
$pdf->SetFont('Arial','',9);
$fill = false;
while ($pr = $top_prod->fetch_assoc()) {
    $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
    $nombre = strlen($pr['nombre']) > 42 ? substr($pr['nombre'],0,39).'...' : $pr['nombre'];
    $pdf->Cell(90,6,' '.$nombre,1,0,'L',$fill);
    $pdf->Cell(45,6,$pr['total_qty'],1,0,'C',$fill);
    $pdf->Cell(45,6,'$'.number_format($pr['total_monto'],2),1,1,'R',$fill);
    $fill = !$fill;
}
$pdf->Ln(6);

// ── Detalle de ventas ────────────────────────────────────
$pdf->SectionTitle('Detalle de Ventas');
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(250,250,250);
$pdf->Cell(18,7,'#Venta',1,0,'C',true);
$pdf->Cell(30,7,'Fecha',1,0,'C',true);
$pdf->Cell(55,7,'Cliente',1,0,'C',true);
$pdf->Cell(30,7,'Método',1,0,'C',true);
$pdf->Cell(27,7,'Total',1,1,'C',true);

$pdf->SetFont('Arial','',8);
$fill = false;
while ($v = $ventas->fetch_assoc()) {
    $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
    $cliente = trim($v['cliente']) ?: 'Ocasional';
    if (strlen($cliente) > 26) $cliente = substr($cliente,0,23).'...';
    $pdf->Cell(18,5,' #'.str_pad($v['id_venta'],4,'0',STR_PAD_LEFT),1,0,'L',$fill);
    $pdf->Cell(30,5,date('d/m/y H:i',strtotime($v['fecha'])),1,0,'C',$fill);
    $pdf->Cell(55,5,' '.$cliente,1,0,'L',$fill);
    $pdf->Cell(30,5,$v['metodo_pago'],1,0,'C',$fill);
    $pdf->Cell(27,5,'$'.number_format($v['total'],2),1,1,'R',$fill);
    $fill = !$fill;
}

// Total final
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(220,252,231);
$pdf->Cell(133,7,'TOTAL DEL PERÍODO',1,0,'R',true);
$pdf->Cell(27,7,'$'.number_format($resumen['total'],2),1,1,'R',true);

$pdf->Output('I','reporte_ingresos_'.date('Ymd').'.pdf');
exit;
