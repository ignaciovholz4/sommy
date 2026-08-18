# Multiple Price Lists System Guide

## Overview

This system allows you to create and manage multiple price lists for both sales and purchases. Each price list can have different pricing strategies and can be applied to products to calculate effective prices.

## Features

- **Dual Context Support**: Separate price lists for sales and purchases
- **Multiple Pricing Types**: 
  - Amount-based pricing (fixed prices)
  - Product-specific percentage adjustments
  - General percentage adjustments for all products
- **Flexible Value Types**: Support for both discounts and price increases
- **Product Management**: Add/remove products from price lists
- **API Integration**: Easy integration with sales and purchase systems

## Database Structure

### Price Lists Table
- `id`: Unique identifier
- `name`: Name of the price list
- `type`: Pricing strategy (amount, product_percentage, general_percentage)
- `context`: Either 'sales' or 'purchase'
- `value_type`: 'discount' or 'increase'
- `percentage`: Percentage value for adjustments
- `description`: Optional description
- `active`: Whether the list is active

### Price List Items Table
- `id`: Unique identifier
- `price_list_id`: Reference to price list
- `product_id`: Reference to product
- `amount_price`: Fixed price for amount-based lists
- `value_type`: Product-specific value type
- `percentage`: Product-specific percentage
- `purchase_price`: Fixed purchase price
- `purchase_value_type`: Purchase-specific value type
- `purchase_percentage`: Purchase-specific percentage

## Usage Examples

### 1. Creating a Sales Price List

```php
use App\Models\PriceList;

$salesList = PriceList::create([
    'name' => 'Wholesale Prices',
    'type' => 'general_percentage',
    'context' => 'sales',
    'value_type' => 'discount',
    'percentage' => 15.0,
    'description' => '15% discount for wholesale customers'
]);
```

### 2. Creating a Purchase Price List

```php
$purchaseList = PriceList::create([
    'name' => 'Bulk Purchase Discount',
    'type' => 'product_percentage',
    'context' => 'purchase',
    'value_type' => 'discount',
    'percentage' => 10.0,
    'description' => '10% discount for bulk purchases'
]);
```

### 3. Adding Products to Price Lists

```php
use App\Models\PriceListItem;

PriceListItem::create([
    'price_list_id' => $purchaseList->id,
    'product_id' => $productId,
    'purchase_percentage' => 10.0,
    'purchase_value_type' => 'discount'
]);
```

### 4. Using the Price List Service

```php
use App\Services\PriceListService;

$priceService = new PriceListService();

// Get effective sales price
$effectivePrice = $priceService->getEffectivePrice($productId, 'sales');

// Get effective purchase price
$effectivePurchasePrice = $priceService->getEffectivePrice($productId, 'purchase');

// Get all effective prices for a product
$allPrices = $priceService->getAllEffectivePrices($productId);
```

## Integration with Sales System

### In Sales Controller

```php
use App\Services\PriceListService;

public function store(Request $request)
{
    $priceService = new PriceListService();
    
    // Get items with price list applied
    $items = $request->input('items');
    if ($request->has('price_list_id')) {
        $items = $priceService->applyPriceListToTransaction(
            $request->price_list_id, 
            $items
        );
    }
    
    // Process sale with adjusted prices
    // ...
}
```

### In Sales View

```blade
<x-price-list-selector 
    context="sales" 
    name="sales_price_list_id" 
    label="Lista de precios de venta"
    :selected="$sale->price_list_id ?? null"
/>
```

## Integration with Purchase System

### In Purchase Controller

```php
use App\Services\PriceListService;

public function store(Request $request)
{
    $priceService = new PriceListService();
    
    // Get items with purchase price list applied
    $items = $request->input('items');
    if ($request->has('purchase_price_list_id')) {
        $items = $priceService->applyPriceListToTransaction(
            $request->purchase_price_list_id, 
            $items
        );
    }
    
    // Process purchase with adjusted prices
    // ...
}
```

### In Purchase View

```blade
<x-price-list-selector 
    context="purchase" 
    name="purchase_price_list_id" 
    label="Lista de precios de compra"
    :selected="$purchase->price_list_id ?? null"
/>
```

## API Endpoints

### Get Sales Price Lists
```
GET /price-lists/sales
```

### Get Purchase Price Lists
```
GET /price-lists/purchase
```

### Get Price List Items
```
GET /price-lists/{id}/items
```

### Bulk Attach Products
```
POST /price-lists/bulk-attach
{
    "price_list_id": 1,
    "product_ids": [1, 2, 3],
    "percentage": 10.0,
    "purchase_percentage": 5.0
}
```

### Remove Item
```
DELETE /price-lists/items/{id}
```

## Price Calculation Logic

### Amount-Based Lists
- If a product has a fixed price in the list, use that price
- Otherwise, use the base product price

### Product-Specific Percentage Lists
- Calculate: `base_price ± (base_price × percentage / 100)`
- Apply discount or increase based on `value_type`

### General Percentage Lists
- Apply the same percentage to all products in the list
- Calculate: `base_price ± (base_price × percentage / 100)`

### Multiple Lists
- Process lists in order (by ID)
- Each list modifies the price from the previous calculation
- Final result is the effective price

## Best Practices

1. **Naming Convention**: Use descriptive names for price lists
2. **Context Separation**: Keep sales and purchase lists separate
3. **Testing**: Test price calculations with various scenarios
4. **Documentation**: Document special pricing rules
5. **Monitoring**: Monitor price list usage and effectiveness

## Migration Notes

If you're upgrading from the previous system:

1. Run the migration: `php artisan migrate`
2. Existing price lists will default to 'sales' context
3. Update your controllers to use the new service
4. Test price calculations thoroughly

## Troubleshooting

### Common Issues

1. **Prices not updating**: Check if price lists are active
2. **Wrong context**: Ensure you're using the correct context ('sales' or 'purchase')
3. **Calculation errors**: Verify percentage values and value types
4. **Missing products**: Check if products are properly added to price lists

### Debug Tips

1. Use `PriceListService::getAllEffectivePrices()` to see all calculations
2. Check database for proper price list assignments
3. Verify context values in price lists
4. Test with simple percentage calculations first

## Support

For additional support or questions about the multiple price lists system, refer to the main application documentation or contact the development team. 