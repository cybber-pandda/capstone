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

class SalesSummaryExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
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
        return PurchaseRequest::with(['customer', 'address', 'detail', 'items.product'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->get();
    }

    public function map($pr): array
    {
        $subtotal = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
        $deliveryFee = $pr->delivery_fee ?? 0;
        
        $subtotal += $deliveryFee;

        $vatRate   = $pr->vat ?? 0;
        $vatAmount = $subtotal * ($vatRate / 100);
        $vatExclusive = $subtotal;

        $total = $subtotal + $vatAmount;

        $fullAddress = $pr->address->full_address ?? 'No provided address';
        $customer    = ($pr->detail->business_name ?? 'No Company Name') . '/' . (optional($pr->customer)->name ?? '-');
        $tin         = $pr->detail->tin_number ?? 'No provided tin number';

        return [
            $pr->created_at->format('F d, Y h:i A'),
            'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT),
            $customer,
            $tin,
            $fullAddress,
            $pr->items->sum('quantity'),
            number_format($pr->items->avg(fn($item) => $item->product->price ?? 0), 2),
            number_format($subtotal, 2),
            number_format($vatAmount, 2),
            number_format($vatExclusive, 2),
            number_format($total, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'Transaction Date',
            'Invoice No.',
            'Customer Name/Company',
            'TIN',
            'Customer Address',
            'Quantity',
            'Unit Price',
            'Total Sales',
            'VAT Amount',
            'VAT Exclusive Sales',
            'Total Amount',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 30,
            'D' => 15,
            'E' => 50,
            'F' => 12,
            'G' => 15,
            'H' => 18,
            'I' => 18,
            'J' => 22,
            'K' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert rows BEFORE headings (pushes headings + data down)
                $sheet->insertNewRowBefore(1, 6);

                // Company name
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'Tantuco Construction and Trading Corporation');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Address
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue('A2', $this->company->company_address ?? '');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Tel / Telefax
                $sheet->mergeCells('A3:K3');
                $sheet->setCellValue('A3', "Tel: {$this->company->company_tel} | Telefax: {$this->company->company_telefax}");
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Email / Phone
                $sheet->mergeCells('A4:K4');
                $sheet->setCellValue('A4', "Email: {$this->company->company_email} | Phone: {$this->company->company_phone}");
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // VAT Reg
                $sheet->mergeCells('A5:K5');
                $sheet->setCellValue('A5', "VAT Reg TIN: {$this->company->company_vat_reg}");
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}
