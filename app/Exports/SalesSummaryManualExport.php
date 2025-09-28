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
use Illuminate\Support\Facades\DB;

class SalesSummaryManualExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $company;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->company   = CompanySetting::find(1);
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

        // Decode items JSON
        $items = json_decode($pr->purchase_request, true) ?? [];

        $totalQty = 0;
        $subtotal = 0;

        foreach ($items as $item) {
            $qty   = (float)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $totalQty += $qty;
            $subtotal += $qty * $price;
        }

        // Default rules for manual orders
        $vatRate      = 12; // 12%
        $vatAmount    = $subtotal * ($vatRate / 100);
        $vatExclusive = $subtotal;
        $grandTotal   = $vatExclusive + $vatAmount;

        foreach ($items as $index => $item) {
            $rows[] = [
                // Show order/customer info only in the first row
                $index === 0 ? $pr->id : '',
                $index === 0 ? $pr->customer_name : '',
                $index === 0 ? $pr->customer_type : '',
                $index === 0 ? $pr->customer_address : '',
                $index === 0 ? $pr->customer_phone_number : '',
                $index === 0 ? $pr->order_date : '',

                 // ✅ Get product name
                $productName = DB::table('products')
                    ->where('id', $item['product_id'])
                    ->value('name') ?? 'Unknown Product',   // ✅ Product Name
                (float)($item['qty'] ?? 0),              // ✅ Quantity
                (float)($item['price'] ?? 0),            // Unit Price
                (float)(($item['qty'] ?? 0) * ($item['price'] ?? 0)), // Line Total

                // Correct (keeps numbers as numeric values)
                $index === count($items) - 1 ? round($vatAmount, 2) : '',
                $index === count($items) - 1 ? round($vatExclusive, 2) : '',
                $index === count($items) - 1 ? round($grandTotal, 2) : '',

            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Customer Type',
            'Customer Address',
            'Customer Phone',
            'Order Date',
            'Product Name',          // ✅ added before quantity
            'Quantity',
            'Unit Price',
            'Subtotal',
            'VAT Amount',
            'VAT Exclusive Sales',
            'Grand Total',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 15,
            'D' => 30,
            'E' => 40,
            'F' => 18,
            'G' => 30,  // Product Name
            'H' => 12,  // Quantity
            'I' => 15,  // Unit Price
            'J' => 18,  // Total Sales
            'K' => 18,  // VAT Amount
            'L' => 22,  // VAT Exclusive Sales
            'M' => 18,  // Grand Total
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert rows BEFORE headings
                $sheet->insertNewRowBefore(1, 6);

                // Company name
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'Tantuco Construction and Trading Corporation');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Address
                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', $this->company->company_address ?? '');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Tel / Telefax
                $sheet->mergeCells('A3:M3');
                $sheet->setCellValue('A3', "Tel: {$this->company->company_tel} | Telefax: {$this->company->company_telefax}");
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Email / Phone
                $sheet->mergeCells('A4:M4');
                $sheet->setCellValue('A4', "Email: {$this->company->company_email} | Phone: {$this->company->company_phone}");
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // VAT Reg
                $sheet->mergeCells('A5:M5');
                $sheet->setCellValue('A5', "VAT Reg TIN: {$this->company->company_vat_reg}");
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}
