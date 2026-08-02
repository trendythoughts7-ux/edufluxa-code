<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read int $quantity
 * @property-read string $status
 * @property-read int $created_at
 * @property-read \App\User $buyer
 * @property-read \App\User $seller
 * @property-read \App\Models\Sale $sale
 * @property-read \App\Models\Product $product
 */
class ProductOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'buyer' => $this->buyer ? [
                'id' => $this->buyer->id,
                'full_name' => $this->buyer->full_name,
                'email' => $this->buyer->email,
                'avator' => $this->buyer->getAvatar(),
            ] : null,
            'seller' => $this->seller ? [
                'id' => $this->seller->id,
                'full_name' => $this->seller->full_name,
                'email' => $this->seller->email,
                'avator' => $this->seller->getAvatar(),
            ] : null,
            'price' => (float)convertPriceToUserCurrency($this->sale->amount),
            'discount' => (float)convertPriceToUserCurrency($this->sale->discount),
            'total_amount' => (float)$this->sale->total_amount,
            'income' => (float)convertPriceToUserCurrency($this->sale->getIncomeItem()),
            'tax' => convertPriceToUserCurrency($this->sale->tax) ?? 0,
            'product_delivery_fee' => convertPriceToUserCurrency($this->sale->product_delivery_fee) ?? 0,
            'product_type' => $this->product->type,
            'status' => $this->status,
            'created_st' => $this->created_at
        ];
    }
}
