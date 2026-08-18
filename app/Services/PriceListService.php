<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Articulo;

class PriceListService
{
    public function getEffectivePrice($productId, $context = 'sales', $defaultPrice = null)
    {
        $product = Articulo::find($productId);
        if (!$product) {
            return $defaultPrice;
        }

        $priceLists = PriceList::where('context', $context)
            ->where('active', true)
            ->orderBy('id')
            ->get();

        // ✅ usar siempre el defaultPrice como base
        $effectivePrice = $defaultPrice ?? ($context === 'sales' ? $product->pventa_con_iva : $product->pcompra_con_iva);

        // si no hay listas activas, devolvemos directamente el precio base
        if ($priceLists->isEmpty()) {
            return round($effectivePrice, 2);
        }

        foreach ($priceLists as $priceList) {
            $effectivePrice = $this->calculatePriceWithList($effectivePrice, $priceList, $productId, $context);
        }

        return round($effectivePrice, 2);
    }

    public function calculatePriceWithList($basePrice, $priceList, $productId, $context)
    {
        switch ($priceList->type) {
            case 'amount':
                return $this->calculateAmountPrice($basePrice, $priceList, $productId, $context);
            case 'product_percentage':
                return $this->calculateProductPercentagePrice($basePrice, $priceList, $productId, $context);
            case 'general_percentage':
                return $this->calculateGeneralPercentagePrice($basePrice, $priceList, $productId, $context);
            default:
                return $basePrice;
        }
    }

    private function calculateAmountPrice($basePrice, $priceList, $productId, $context)
    {
        $item = PriceListItem::where('price_list_id', $priceList->id)
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return $basePrice;
        }

        if ($context === 'sales' && $item->amount_price !== null) {
            return (float) $item->amount_price;
        }

        if ($context === 'purchase' && $item->purchase_price !== null) {
            return (float) $item->purchase_price;
        }

        return $basePrice;
    }

    private function calculateProductPercentagePrice($basePrice, $priceList, $productId, $context)
    {
        $item = PriceListItem::where('price_list_id', $priceList->id)
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return $basePrice;
        }

        $percentage = $context === 'sales' ? $item->percentage : $item->purchase_percentage;
        $valueType = $context === 'sales' ? $item->value_type : $item->purchase_value_type;

        if ($percentage === null || $valueType === null) {
            return $basePrice;
        }

        $delta = ($basePrice * (float) $percentage) / 100;
        
        return $valueType === 'discount' ? ($basePrice - $delta) : ($basePrice + $delta);
    }

    private function calculateGeneralPercentagePrice($basePrice, $priceList, $productId, $context)
    {
        // ✅ Verificamos si el producto está en la lista antes de aplicar el porcentaje general
        $item = PriceListItem::where('price_list_id', $priceList->id)
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return $basePrice;
        }

        if ($priceList->percentage === null || $priceList->value_type === null) {
            return $basePrice;
        }

        $delta = ($basePrice * (float) $priceList->percentage) / 100;
        
        return $priceList->value_type === 'discount'
            ? ($basePrice - $delta)
            : ($basePrice + $delta);
    }

    public function getAllEffectivePrices($productId)
    {
        $product = Articulo::find($productId);
        if (!$product) {
            return null;
        }

        $result = [
            'product' => $product,
            'sales_prices' => [],
            'purchase_prices' => []
        ];

        $salesLists = PriceList::sales()->active()->get();
        foreach ($salesLists as $list) {
            $effectivePrice = $this->calculatePriceWithList($product->pventa_con_iva, $list, $productId, 'sales');
            $result['sales_prices'][] = [
                'list_id' => $list->id,
                'list_name' => $list->name,
                'base_price' => $product->pventa_con_iva,
                'effective_price' => $effectivePrice,
                'difference' => round($effectivePrice - $product->pventa_con_iva, 2)
            ];
        }

        $purchaseLists = PriceList::purchase()->active()->get();
        foreach ($purchaseLists as $list) {
            $effectivePrice = $this->calculatePriceWithList($product->pcompra_con_iva, $list, $productId, 'purchase');
            $result['purchase_prices'][] = [
                'list_id' => $list->id,
                'list_name' => $list->name,
                'base_price' => $product->pcompra_con_iva,
                'effective_price' => $effectivePrice,
                'difference' => round($effectivePrice - $product->pcompra_con_iva, 2)
            ];
        }

        return $result;
    }

    public function applyPriceListToTransaction($priceListId, $items)
    {
        $priceList = PriceList::find($priceListId);
        if (!$priceList || !$priceList->active) {
            return $items;
        }

        $context = $priceList->context;
        
        foreach ($items as &$item) {
            if (isset($item['product_id'])) {
                $effectivePrice = $this->getEffectivePrice($item['product_id'], $context, $item['price'] ?? null);
                $item['effective_price'] = $effectivePrice;
                $item['price_list_applied'] = $priceList->name;
            }
        }

        return $items;
    }

    /**
     * Simplified: always return effective sale price for product base,
     * fallback to basePrice if result is 0 or null
     */
    public function getEffectiveSalePrice($productId, $basePrice)
    {
        $effective = $this->getEffectivePrice($productId, 'sales', $basePrice);

        if ($effective === null || $effective == 0.0) {
            return round($basePrice, 2);
        }

        return $effective;
    }
}