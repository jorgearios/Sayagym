<?php
/**
 * Archivo: ticket.php
 * Descripción: Módulo de impresión térmica de tickets/recibos de pagos usando JS/PHP.
 * Parte del sistema integral de gestión Sayagym.
 */

// ticket.php — Genera e imprime el ticket de venta en PDF
include 'config.php';
if (!esAdministrador()) { header("Location: login.php"); exit(); }

if (!isset($_GET['id'])) { header("Location: ventas.php"); exit(); }
$id_venta = (int)$_GET['id'];

// Datos de la venta
$venta = $conexion->query("
    SELECT v.*, u.nombre_completo as cajero,
           CONCAT(s.nombre,' ',s.apellido) as cliente
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    LEFT JOIN socios   s ON v.id_socio   = s.id_socio
    WHERE v.id_venta = $id_venta
")->fetch_assoc();

if (!$venta) { die("Venta no encontrada."); }

// Detalle de la venta
$detalle = $conexion->query("
    SELECT vd.*, p.nombre
    FROM venta_detalle vd
    JOIN productos p ON vd.id_producto = p.id_producto
    WHERE vd.id_venta = $id_venta
    ORDER BY vd.id_detalle ASC
");

// ── GENERAR PDF CON FPDF ──────────────────────────────────
require_once 'fpdf.php';

class TicketPDF extends FPDF {
    function Header() {
        // Header vacío — lo llenamos manualmente
    }
    function Footer() {
        $this->SetY(-18);
        $this->SetFont('Arial','I',7);
        $this->SetTextColor(150,150,150);
        $this->Cell(0,5,'Gracias por su compra — Sayagym Fitness Club',0,1,'C');
        $this->Cell(0,5,'Este ticket es su comprobante de compra',0,0,'C');
    }
}

// Ticket angosto estilo POS (58mm → 80 unidades aprox, usamos 80mm)
$pdf = new TicketPDF('P','mm',array(80,200));
$pdf->AddPage();
$pdf->SetMargins(4, 4, 4);
$pdf->SetAutoPageBreak(true, 18);

$ancho = 72; // ancho útil en mm

// ── ENCABEZADO ────────────────────────────────────────────
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(127,0,0);
$pdf->Cell($ancho,7,'SAYAGYM',0,1,'C');
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(80,80,80);
$pdf->Cell($ancho,4,'Fitness Club',0,1,'C');
$pdf->Cell($ancho,4,'Sistema de Gestion Integral',0,1,'C');
$pdf->Ln(2);

// Línea divisoria
$pdf->SetDrawColor(200,200,200);
$pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());
$pdf->Ln(2);

// Datos de la venta
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(60,60,60);
$pdf->Cell(28,5,'Ticket #:',0,0,'L');
$pdf->SetFont('Arial','B',8);
$pdf->Cell($ancho-28,5, str_pad($venta['id_venta'],6,'0',STR_PAD_LEFT) ,0,1,'L');

$pdf->SetFont('Arial','',8);
$pdf->Cell(28,5,'Fecha:',0,0,'L');
$pdf->Cell($ancho-28,5, date('d/m/Y H:i', strtotime($venta['fecha'])), 0,1,'L');

$pdf->Cell(28,5,'Cajero:',0,0,'L');
$pdf->Cell($ancho-28,5, $venta['cajero'] ?? 'Admin', 0,1,'L');

if (!empty($venta['cliente'])) {
    $pdf->Cell(28,5,'Cliente:',0,0,'L');
    $pdf->Cell($ancho-28,5, $venta['cliente'], 0,1,'L');
}

$pdf->Cell(28,5,'Metodo pago:',0,0,'L');
$pdf->Cell($ancho-28,5, $venta['metodo_pago'], 0,1,'L');

$pdf->Ln(2);
$pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());
$pdf->Ln(2);

// ── ENCABEZADO TABLA ──────────────────────────────────────
$pdf->SetFillColor(245,245,245);
$pdf->SetFont('Arial','B',8);
$pdf->SetTextColor(40,40,40);
$pdf->Cell(34,5,'Producto',0,0,'L',true);
$pdf->Cell(10,5,'Cant',0,0,'C',true);
$pdf->Cell(14,5,'P.Unit',0,0,'R',true);
$pdf->Cell(14,5,'Subtotal',0,1,'R',true);
$pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());

// ── ÍTEMS ─────────────────────────────────────────────────
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(40,40,40);
while ($item = $detalle->fetch_assoc()) {
    $nombre = strlen($item['nombre']) > 22 ? substr($item['nombre'],0,19).'...' : $item['nombre'];
    $pdf->Cell(34,5, $nombre ,0,0,'L');
    $pdf->Cell(10,5, $item['cantidad'],0,0,'C');
    $pdf->Cell(14,5,'$'.number_format($item['precio_unit'],2),0,0,'R');
    $pdf->Cell(14,5,'$'.number_format($item['subtotal'],2),0,1,'R');
}

$pdf->Ln(1);
$pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());
$pdf->Ln(2);

// ── TOTALES ───────────────────────────────────────────────
$pdf->SetFont('Arial','',8);
$pdf->Cell(50,5,'Subtotal:',0,0,'R');
$pdf->Cell(22,5,'$'.number_format($venta['subtotal'],2),0,1,'R');

if ($venta['descuento'] > 0) {
    $pdf->SetTextColor(180,0,0);
    $pdf->Cell(50,5,'Descuento:',0,0,'R');
    $pdf->Cell(22,5,'-$'.number_format($venta['descuento'],2),0,1,'R');
    $pdf->SetTextColor(40,40,40);
}

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(127,0,0);
$pdf->Cell(50,7,'TOTAL:',0,0,'R');
$pdf->Cell(22,7,'$'.number_format($venta['total'],2),0,1,'R');
$pdf->SetTextColor(40,40,40);

if (!empty($venta['nota'])) {
    $pdf->Ln(2);
    $pdf->SetFont('Arial','I',7);
    $pdf->SetTextColor(100,100,100);
    $pdf->MultiCell($ancho,4,'Nota: '.$venta['nota'],0,'L');
}

$pdf->Ln(3);
$pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());
$pdf->Ln(3);

// QR / código de ticket
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(40,40,40);
$pdf->Cell($ancho,5,'*** CONSERVE ESTE TICKET ***',0,1,'C');

// Salida del PDF
$pdf->Output('I','ticket_'.str_pad($id_venta,6,'0',STR_PAD_LEFT).'.pdf');
exit;
