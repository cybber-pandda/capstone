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
use Carbon\Carbon;

class SalesSummaryExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithColumnFormatting
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
            ->whereIn('status', ['delivered', 'invoice_sent'])
            ->get();
    }


    public function map($pr): array
{
    $rows = [];

    // Calculate totals per PR
    $itemsSubtotal = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
    $vatRate = $pr->vat ?? 0;
    $vatAmount = $itemsSubtotal * ($vatRate / 100);
    $total = $itemsSubtotal + $vatAmount;

    $fullAddress = $pr->address->full_address ?? 'No provided address';
    $customer    = ($pr->detail->business_name ?? 'No Company Name') . '/' . (optional($pr->customer)->name ?? '-');
    $tin         = $pr->detail->tin_number ?? 'No provided tin number';
    $transactionDate = $pr->created_at ? Carbon::parse($pr->created_at)->format('F d, Y h:i A') : '';
    $invoiceNo = 'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT);

    foreach ($pr->items as $index => $item) {
        $rows[] = [
            // Show transaction, invoice, customer, tin, address only in first row
            $index === 0 ? $transactionDate : '',
            $index === 0 ? $invoiceNo : '',
            $index === 0 ? $customer : '',
            $index === 0 ? $tin : '',
            $index === 0 ? $fullAddress : '',

            $item->product->name ?? 'No Product Name',
            $item->quantity,
            (float) ($item->product->price ?? 0),
            (float) round($item->quantity * ($item->product->price ?? 0), 2),

            // ✅ Show totals only in LAST row of items
            $index === count($pr->items) - 1 ? round($vatAmount, 2) : '',
            $index === count($pr->items) - 1 ? round($itemsSubtotal, 2) : '',
            $index === count($pr->items) - 1 ? round($total, 2) : '',
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
        'Product Name',      // ✅ moved up
        'Quantity',
        'Unit Price',
        'Subtotal',
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
            'F' => 25,  // ✅ Product Name
            'G' => 12,  // ✅ Quantity
            'H' => 15,
            'I' => 18,
            'J' => 18,
            'K' => 22,
            'L' => 18,
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

    public function columnFormats(): array
    {
        // F = Quantity (integer), G-K = numeric with 2 decimals
        return [
            'G' => NumberFormat::FORMAT_NUMBER,     // ✅ Quantity (integer)
            'H' => NumberFormat::FORMAT_NUMBER_00,  // Unit Price
            'I' => NumberFormat::FORMAT_NUMBER_00,  // Total Sales
            'J' => NumberFormat::FORMAT_NUMBER_00,  // VAT Amount
            'K' => NumberFormat::FORMAT_NUMBER_00,  // VAT Exclusive Sales
        ];

    }
}
