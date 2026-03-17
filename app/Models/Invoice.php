<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Invoice as InvoicesInvoice;

class Invoice extends Model
{
    //
    protected $fillable = ['invoice_number','customer_id','booking_id','amount','invoice_date','due_date','status','items','file_path'];

    protected $casts = ['items'=>'array'];


    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function booking(){
        return $this->belongsTo(Booking::class);
    }

    public function generateInvoice()
    { 


        dd($this->status);
        $customer = self::generateCustomerData($this->customer);
        $invoice = InvoicesInvoice::make()
            ->buyer($customer)
            ->name("Booking invoice")
            ->status($this->status) 
            ->logo('images/logo.png')
            ->filename("public/invoices/" . $this->invoice_number)
            ->currencyCode('PHP');

        // Add items from invoice_items field
        if ($this->invoice_items) {
            foreach ($this->invoice_items as $item) {
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


    public static function generateInvoiceNumber(){
          $latestInvoice = self::latest('id')->first();
        $nextNumber = $latestInvoice ? $latestInvoice->id + 1 : 1;

        return 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
