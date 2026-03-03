<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                $product->id,
                $product->name,
                $product->slug,
                $product->price,
                $product->category?->name ?? 'N/A',
                $product->description,
                $product->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Slug',
            'Price',
            'Category',
            'Description',
            'Created At'
        ];
    }
}
