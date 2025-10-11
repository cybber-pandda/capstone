<?php

namespace App\Exports;

use App\Models\ManualEmailOrder;
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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesSummaryManualExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithColumnFormatting
{
    protected $startDate;
    protected $endDate;
    protected $company;
    protected $reportTitle;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->company   = CompanySetting::find(1);
        $start = Carbon::parse($startDate)->format('M d, Y');
        $end = Carbon::parse($endDate)->format('M d, Y');
        $this->reportTitle = "Sales Summary Report (Manual Orders) from $start to $end";
    }

    public function collection()
    {
        return ManualEmailOrder::whereDate('order_date', '>=', $this->startDate)
            ->whereDate('order_date', '<=', $this->endDate)
            ->where('status', 'approve')
            ->get();
    }

    public function map($pr): array
    {
        $rows = [];

        // Check if purchase_request is a string that needs decoding, or is already an array/object
        $items = is_string($pr->purchase_request)
            ? (json_decode($pr->purchase_request, true) ?? [])
            : ($pr->purchase_request ?? []);

        $subtotal = 0; // VAT Exclusive Sales (total of items)
        
        foreach ($items as $item) {
            $qty   = (float)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $subtotal += $qty * $price;
        }

        // --- Sales Calculations ---
        $vatRate = 0.12;
        $salesVAT = $subtotal * $vatRate;
        $totalInclusive = $subtotal + $salesVAT; // Sales (VAT Inclusive)

        // --- Delivery Calculations ---
        $deliveryFeeInclusive = (float)($pr->delivery_fee ?? 0);
        
        // Assuming delivery fee is VAT inclusive based on common practice for manual orders, 
        // unlike the example code which might treat it as VAT exclusive or use a fixed fee.
        // Let's recalculate based on the common Philippine VAT-inclusive delivery model (delivery fee = exclusive + 12% VAT).
        $deliveryFeeExclusive = $deliveryFeeInclusive / (1 + $vatRate);
        $deliveryVAT = $deliveryFeeInclusive - $deliveryFeeExclusive;
        
        $totalVAT = $salesVAT + $deliveryVAT;
        $grandTotal = $totalInclusive + $deliveryFeeInclusive;

        $orderDate = $pr->order_date ? Carbon::parse($pr->order_date)->format('Y-m-d') : '';

        foreach ($items as $index => $item) {
            // Fetch product name safely
            $productName = DB::table('products')->where('id', $item['product_id'])->value('name') ?? 'Unknown Product';
            $itemTotal = (float)(($item['qty'] ?? 0) * ($item['price'] ?? 0));

            $rows[] = [
                // Order Details (Show only on the first item row)
                $index === 0 ? $orderDate : '',
                $index === 0 ? $pr->id : '',
                $index === 0 ? $pr->customer_name : '',
                $index === 0 ? $pr->customer_type : '',
                $index === 0 ? $pr->customer_address : '',
                $index === 0 ? $pr->customer_phone_number : '',

                // Item Details
                $productName,
                (float)($item['qty'] ?? 0),
                (float)($item['price'] ?? 0),
                $itemTotal,

                // Order Totals (Show only on the last item row)
                // K: VAT Exclusive Sales (Subtotal of all items)
                $index === count($items) - 1 ? round($subtotal, 2) : '',
                // L: Sales VAT (VAT on Subtotal)
                $index === count($items) - 1 ? round($salesVAT, 2) : '',
                // M: Sales (VAT Inclusive) (Subtotal + Sales VAT)
                $index === count($items) - 1 ? round($totalInclusive, 2) : '',

                // N: Delivery Fee (VAT Exclusive)
                $index === count($items) - 1 ? round($deliveryFeeExclusive, 2) : '',
                // O: Delivery VAT
                $index === count($items) - 1 ? round($deliveryVAT, 2) : '',
                // P: Delivery Fee (VAT Inclusive)
                $index === count($items) - 1 ? round($deliveryFeeInclusive, 2) : '',

                // Q: Total VAT (Sales VAT + Delivery VAT)
                $index === count($items) - 1 ? round($totalVAT, 2) : '',
                // R: Grand Total (Total Inclusive + Delivery Inclusive)
                $index === count($items) - 1 ? round($grandTotal, 2) : '',
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Order Date',
            'ID',
            'Customer Name',
            'Customer Type',
            'Customer Address',
            'Customer Phone',
            'Product Name',
            'Quantity',
            'Unit Price',
            'Subtotal Total (Item)',
            'VAT Exclusive Sales (Order)',
            'Sales VAT (Order)',           // L itooooooooooooooooooooooo
            'Sales (VAT Inclusive) (Order)', // M
            'Delivery Fee (VAT Exclusive)',// N
            'Delivery VAT',                // O
            'Delivery Fee (VAT Inclusive)',// P
            'Total VAT',                   // Q
            'Grand Total',                 // R
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 25, 'C' => 15, 'D' => 35, 'E' => 20,
            'F' => 18, 'G' => 30, 'H' => 12, 'I' => 15, 'J' => 18,
            'K' => 20, 'L' => 18, 'M' => 25, 'N' => 25, 'O' => 18,
            'P' => 25, 'Q' => 18, 'R' => 20,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD, 
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER_00, 
            'J' => NumberFormat::FORMAT_NUMBER_00, 
            'K' => NumberFormat::FORMAT_NUMBER_00, 
            'L' => NumberFormat::FORMAT_NUMBER_00, 
            'M' => NumberFormat::FORMAT_NUMBER_00, 
            'N' => NumberFormat::FORMAT_NUMBER_00, 
            'O' => NumberFormat::FORMAT_NUMBER_00, 
            'P' => NumberFormat::FORMAT_NUMBER_00, 
            'R' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        $lastCol = 'R'; // Last column is R (18 columns total)

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
                $sheet->getStyle("M" . ($headingRow + 1) . ":M{$highestRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6FFE6'); // Light Green

                // Highlight Grand Total (R)
                $sheet->getStyle("R" . ($headingRow + 1) . ":R{$highestRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFE6F7FF'); // Light Blue
            }
        ];
    }
}