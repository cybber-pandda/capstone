<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use App\Models\CompanySetting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class SalesSummaryExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithColumnFormatting
{
    protected $startDate;
    protected $endDate;
    protected $company;
    protected $reportTitle;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->company = CompanySetting::find(1);
        
        $start = Carbon::parse($startDate)->format('M d, Y');
        $end = Carbon::parse($endDate)->format('M d, Y');
        $this->reportTitle = "Sales Summary Report (Online Orders) from $start to $end";
    }

    public function collection()
    {
        return PurchaseRequest::with(['customer', 'address', 'detail', 'items.product'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->whereIn('status', ['delivered', 'invoice_sent'])
            ->get();
    }


public function map($pr): array
{
    $rows = [];

    // Calculate items subtotal considering discount
    $itemsSubtotal = $pr->items->sum(function($item) {
        $price = $item->product->price ?? 0;
        $discount = $item->product->discount ?? 0;
        $discountedPrice = $price - ($price * ($discount / 100));
        return $item->quantity * $discountedPrice;
    });

    $vatRate = $pr->vat ?? 0;
    $salesVAT = $itemsSubtotal * ($vatRate / 100);
    $salesInclusive = $itemsSubtotal + $salesVAT;

    $deliveryFee = $pr->delivery_fee ?? 0;
    $deliveryExclusive = $deliveryFee / 1.12;
    $deliveryVAT = $deliveryFee - $deliveryExclusive;
    $deliveryInclusive = $deliveryFee;

    $totalVAT = $salesVAT + $deliveryVAT;
    $grandTotal = $salesInclusive + $deliveryInclusive;

    $fullAddress = $pr->address->full_address ?? 'No provided address';
    $customer = ($pr->detail->business_name ?? 'No Company Name') . '/' . (optional($pr->customer)->name ?? '-');
    $tin = $pr->detail->tin_number ?? 'No provided tin number';
    $transactionDate = $pr->created_at ? Carbon::parse($pr->created_at)->format('Y-m-d H:i') : '';
    $invoiceNo = 'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT);

    foreach ($pr->items as $index => $item) {
        $price = $item->product->price ?? 0;
        $discount = $item->product->discount ?? 0;
        $discountedPrice = $price - ($price * ($discount / 100));

        $rows[] = [
            $index === 0 ? $transactionDate : '',
            $index === 0 ? $invoiceNo : '',
            $index === 0 ? $customer : '',
            $index === 0 ? $tin : '',
            $index === 0 ? $fullAddress : '',

            $item->product->name ?? 'No Product Name',
            $item->quantity,
            (float) $discountedPrice, // Apply discounted price here
            (float) round($item->quantity * $discountedPrice, 2), // Subtotal per item
            $index === count($pr->items) - 1 ? round($itemsSubtotal, 2) : '',
            $index === count($pr->items) - 1 ? round($salesVAT, 2) : '',
            $index === count($pr->items) - 1 ? round($salesInclusive, 2) : '',

            $index === count($pr->items) - 1 ? round($deliveryExclusive, 2) : '',
            $index === count($pr->items) - 1 ? round($deliveryVAT, 2) : '',
            $index === count($pr->items) - 1 ? round($deliveryInclusive, 2) : '',

            $index === count($pr->items) - 1 ? round($totalVAT, 2) : '',
            $index === count($pr->items) - 1 ? round($grandTotal, 2) : '',
        ];
    }

    return $rows;
}


    public function headings(): array
    {
        return [
            'Transaction Date',
            'Invoice No.',
            'Customer Name/Company',
            'TIN',
            'Customer Address',

            'Product Name',
            'Quantity',
            'Unit Price',
            'Subtotal (Item)',
            'VAT Exclusive Sales (Order)',
            'VAT Amount (Sales)',
            'Total (VAT Inclusive) (Sales)',

            'Delivery Fee (VAT Exclusive)',
            'Delivery VAT',
            'Delivery Fee (VAT Inclusive)',

            'Total VAT',
            'Grand Total',
        ];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 25, 'B' => 15, 'C' => 30, 'D' => 15, 'E' => 50,
            'F' => 25, 'G' => 12, 'H' => 15, 'I' => 18, 'J' => 18,
            'K' => 22, 'L' => 25, 'M' => 25, 'N' => 15, 'O' => 20,
            'P' => 18, 'Q' => 18,
        ];
    }

    public function registerEvents(): array
    {
        $lastCol = 'Q'; // Last column is Q (17 columns total)

        return [
            AfterSheet::class => function (AfterSheet $event) use ($lastCol) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert rows for company info and report title
                $sheet->insertNewRowBefore(1, 7);
                
                // --- Company Info Block ---
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Tantuco Construction and Trading Corporation');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', $this->company->company_address ?? '');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', "Tel: {$this->company->company_tel} | Telefax: {$this->company->company_telefax}");
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', "Email: {$this->company->company_email} | Phone: {$this->company->company_phone}");
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->setCellValue('A5', "VAT Reg TIN: {$this->company->company_vat_reg}");
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- Report Title ---
                $titleRow = 6;
                $sheet->mergeCells("A{$titleRow}:{$lastCol}{$titleRow}");
                $sheet->setCellValue("A{$titleRow}", $this->reportTitle);
                $sheet->getStyle("A{$titleRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A{$titleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$titleRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCDCDC'); // Light Gray background
                
                // --- Headings formatting ---
                $headingRow = 7;
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                      ->getFont()->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF3C8DBC'); // Dark Blue background
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                $highestRow = $sheet->getHighestRow();

                // --- Auto-size columns (overrides fixed width for better fit) ---
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // --- Conditional Highlighting ---
                
                // Highlight VAT Exclusive Sales (K) - Order Subtotal
                $sheet->getStyle("L" . ($headingRow + 1) . ":L{$highestRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6FFE6'); // Light Green

                // Highlight Grand Total (Q)
                $sheet->getStyle("Q" . ($headingRow + 1) . ":Q{$highestRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6F7FF'); // Light Blue
            }
        ];
    }


    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_00, 
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00, 
            'L' => NumberFormat::FORMAT_NUMBER_00, 
            'M' => NumberFormat::FORMAT_NUMBER_00,
            'N' => NumberFormat::FORMAT_NUMBER_00,
            'O' => NumberFormat::FORMAT_NUMBER_00, 
            'P' => NumberFormat::FORMAT_NUMBER_00,
            'Q' => NumberFormat::FORMAT_NUMBER_00,
            'A' => NumberFormat::FORMAT_DATE_YYYYMMDD,
        ];
    }
}