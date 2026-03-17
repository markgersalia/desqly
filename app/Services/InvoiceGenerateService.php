<?php

namespace App\Services;

use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Seller;
use LaravelDaily\Invoices\Invoice;

class InvoiceGenerateService{
     public function generateInvoice($data)
    { 

        $customer = self::generateCustomerData($data->customer);
        // Add items from invoice_items field
 
        $items = $data->items; 
        // dd($item);
        // $invoiceItem = InvoiceItem::make($item['name'])->pricePerUnit($item['price_per_unit']);

        // $item = InvoiceItem::make('Service 1')->pricePerUnit(2);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->seller($this->generateSellerData())
            ->name("Invoice for Booking #".$data->booking->booking_number)
            ->status($data->status) 
            ->filename("invoices/" . $data->invoice_number)
            ->currencyCode('PHP');

        $logoPath = public_path('images/logo.jpg');
        if (is_file($logoPath)) {
            $invoice->logo('images/logo.jpg');
        }

            if ($items) {
            foreach ($items as $item) {
                $invoiceItem = InvoiceItem::make($item['name'])
                    ->pricePerUnit($item['price_per_unit'])
                    ->quantity($item['quantity'] ?? 1);
                $invoice->addItem($invoiceItem);
            }
        }
 
        $disk = "public"; // Ensure 'public' disk is configured in config/filesystems.php
        $invoice->save($disk);

        return $invoice->url();
    }

    
    public function generateCustomerData($customer){
        return new Buyer([
            'name' => $customer->name,
            'custom_fields' => [
                'email' => $customer->email,
            ],
            'address' => $customer->address
        ]);
    }
    public function generateSellerData(){
        return new Seller([
            'name' => env('APP_NAME'),
            'custom_fields' => [
                'email' => env('APP_EMAIL') ?? "info@yourcompany.com",
            ],
        ]);
    }

}
