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

class SalesSummaryManualExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $company;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->company = CompanySetting::find(1);
    }

    public function collection()
    {
        return ManualEmailOrder::whereDate('order_date', '>=', $this->startDate)
            ->whereDate('order_date', '<=', $this->endDate)
            ->get();
    }

    public function map($pr): array
    {
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
        $deliveryFee  = 200;
        $vatRate      = 12; // 12%
        $vatAmount    = ($subtotal + $deliveryFee) * ($vatRate / 100);
        $vatExclusive = $subtotal + $deliveryFee;
        $grandTotal   = $vatExclusive + $vatAmount;

        return [
            $pr->id,
            $pr->customer_name,
            $pr->customer_type,
            $pr->customer_email,
            $pr->customer_address,
            $pr->customer_phone_number,
            $pr->order_date,
            $totalQty,
            number_format($subtotal, 2),
            number_format($deliveryFee, 2),
            number_format($vatAmount, 2),
            number_format($vatExclusive, 2),
            number_format($grandTotal, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Customer Type',
            'Customer Email',
            'Customer Address',
            'Customer Phone',
            'Order Date',
            'Total Quantity',
            'Subtotal',
            'Delivery Fee',
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
            'G' => 20,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 20,
            'M' => 20,
            'N' => 25,
            'O' => 15,
            'P' => 25,
            'Q' => 25,
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
                $sheet->mergeCells('A1:Q1');
                $sheet->setCellValue('A1', 'Tantuco Construction and Trading Corporation');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Address
                $sheet->mergeCells('A2:Q2');
                $sheet->setCellValue('A2', $this->company->company_address ?? '');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Tel / Telefax
                $sheet->mergeCells('A3:Q3');
                $sheet->setCellValue('A3', "Tel: {$this->company->company_tel} | Telefax: {$this->company->company_telefax}");
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Email / Phone
                $sheet->mergeCells('A4:Q4');
                $sheet->setCellValue('A4', "Email: {$this->company->company_email} | Phone: {$this->company->company_phone}");
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // VAT Reg
                $sheet->mergeCells('A5:Q5');
                $sheet->setCellValue('A5', "VAT Reg TIN: {$this->company->company_vat_reg}");
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}
