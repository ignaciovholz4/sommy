# Sales Price List Implementation Guide

## Overview

This document describes the implementation of price list functionality for sales, allowing users to apply different pricing strategies to their sales transactions. When a price list is selected, it affects the item prices in the cart and calculates the final sale amount.

## Features Implemented

### 1. **Price List Selection in Sales Form**
- Added a price list dropdown in the sales creation form
- Shows only active sales price lists
- Allows users to select from available pricing strategies

### 2. **Real-time Price Application**
- Price lists are applied immediately when selected
- Cart items are updated with new effective prices
- Original prices are preserved for reference
- Total calculations are updated automatically

### 3. **Visual Price Display**
- Shows both original and effective prices in the cart
- Highlights when price lists are applied
- Displays total savings from applied price lists
- Color-coded pricing information

### 4. **Database Integration**
- Price list information is stored with sales records
- Both main sales table and detail tables track price list usage
- Temporary cart table supports price list calculations

## Technical Implementation

### Database Changes

#### 1. **Ventas Table**
```sql
ALTER TABLE ventas ADD COLUMN price_list_id BIGINT UNSIGNED NULL;
ALTER TABLE ventas ADD COLUMN price_list_name VARCHAR(255) NULL;
ALTER TABLE ventas ADD FOREIGN KEY (price_list_id) REFERENCES price_lists(id);
```

#### 2. **Detalle Ventas Table**
```sql
ALTER TABLE detalle_ventas ADD COLUMN price_list_id BIGINT UNSIGNED NULL;
ALTER TABLE detalle_ventas ADD COLUMN original_price DECIMAL(11,2) NULL;
ALTER TABLE detalle_ventas ADD COLUMN effective_price DECIMAL(11,2) NULL;
ALTER TABLE detalle_ventas ADD COLUMN price_list_name VARCHAR(255) NULL;
```

#### 3. **Detalle Venta Temp Table**
```sql
ALTER TABLE detalle_venta_temp ADD COLUMN price_list_id BIGINT UNSIGNED NULL;
ALTER TABLE detalle_venta_temp ADD COLUMN original_price DECIMAL(11,2) NULL;
ALTER TABLE detalle_venta_temp ADD COLUMN effective_price DECIMAL(11,2) NULL;
ALTER TABLE detalle_venta_temp ADD COLUMN price_list_name VARCHAR(255) NULL;
```

### Model Updates

#### 1. **Venta_producto Model**
- Added `price_list_id` and `price_list_name` to fillable array
- Added relationship to `PriceList` model
- Added relationship to `Detalle_venta_producto` model

#### 2. **Detalle_venta_producto Model**
- Added price list fields to fillable array
- Added relationship to `PriceList` model
- Added relationship to `Venta_producto` model

### Controller Updates

#### 1. **VentaController**
- Injected `PriceListService` for price calculations
- Added `getSalesPriceLists()` method to fetch available price lists
- Added `applyPriceListToCart()` method to apply price lists to temporary cart
- Updated `store()` method to save price list information with sales

#### 2. **New Methods**
```php
public function getSalesPriceLists()
// Returns active sales price lists for the form

public function applyPriceListToCart(Request $request)
// Applies selected price list to all items in temporary cart
// Updates prices and recalculates totals
```

### Routes Added

```php
// Get sales price lists
Route::get('ventas/price-lists', 'Venta\VentaController@getSalesPriceLists')
    ->name('ventas.price-lists')
    ->middleware(['auth','verified']);

// Apply price list to cart
Route::post('ventas/apply-price-list', 'Venta\VentaController@applyPriceListToCart')
    ->name('ventas.apply-price-list')
    ->middleware(['auth','verified']);
```

### Frontend Updates

#### 1. **Sales Form (create.blade.php)**
- Added price list selector dropdown
- Added price list information display
- Updated table headers to show original and effective prices
- Added visual indicators for applied price lists

#### 2. **JavaScript (venta.js)**
- Added `loadSalesPriceLists()` function to populate dropdown
- Added `handlePriceListChange()` function to handle price list selection
- Updated `pintar_tabla_venta()` function to display both prices
- Added event listeners for price list changes

#### 3. **Price Display Logic**
```javascript
// Shows both original and effective prices when price list is applied
if (item.original_price && item.effective_price && item.original_price !== item.effective_price) {
    // Display both prices with visual indicators
} else {
    // Display single price
}
```

## User Experience Flow

### 1. **Creating a Sale**
1. User opens sales creation form
2. Price list dropdown is automatically populated with available sales price lists
3. User can select a price list or leave it as "Sin lista de precios"

### 2. **Adding Products to Cart**
1. User searches and adds products to cart
2. Products are added with their base prices
3. If a price list is selected, prices are automatically adjusted

### 3. **Applying Price List**
1. User selects a price list from dropdown
2. System immediately applies price list to all cart items
3. Cart display updates to show:
   - Original prices (in muted text)
   - Effective prices (in green text)
   - Total savings calculation
   - Visual indicator of applied price list

### 4. **Completing the Sale**
1. Price list information is saved with the sale
2. Both original and effective prices are recorded
3. Sale history shows which price list was applied

## Price Calculation Logic

### 1. **Amount-Based Lists**
- If product has fixed price in list: use that price
- Otherwise: use base product price

### 2. **Percentage-Based Lists**
- Calculate: `base_price ± (base_price × percentage / 100)`
- Apply discount or increase based on `value_type`

### 3. **Multiple Lists**
- Process lists in order (by ID)
- Each list modifies price from previous calculation
- Final result is the effective price

## Benefits

### 1. **Flexible Pricing**
- Support for multiple pricing strategies
- Easy application of discounts or price increases
- Product-specific or general pricing rules

### 2. **Transparency**
- Users see both original and adjusted prices
- Clear indication of applied price lists
- Total savings calculation

### 3. **Audit Trail**
- Price list usage is tracked in sales records
- Historical pricing information is preserved
- Easy reporting on price list effectiveness

### 4. **User Experience**
- Real-time price updates
- Visual feedback on price changes
- Seamless integration with existing sales workflow

## Future Enhancements

### 1. **Customer-Specific Price Lists**
- Assign price lists to specific customers
- Automatic application based on customer selection

### 2. **Bulk Price List Application**
- Apply price lists to multiple sales at once
- Batch processing for large operations

### 3. **Price List Analytics**
- Track price list usage and effectiveness
- Report on savings and revenue impact

### 4. **Advanced Pricing Rules**
- Time-based pricing (seasonal discounts)
- Quantity-based pricing (bulk discounts)
- Conditional pricing rules

## Testing

### 1. **Unit Tests**
- Test price calculation logic
- Test model relationships
- Test controller methods

### 2. **Integration Tests**
- Test complete sales flow with price lists
- Test price list application to cart
- Test database persistence

### 3. **User Acceptance Tests**
- Test price list selection workflow
- Test visual price display
- Test total calculations

## Deployment Notes

### 1. **Database Migration**
- Run all new migrations in order
- Ensure foreign key constraints are properly set
- Test with existing data

### 2. **Configuration**
- Verify price list permissions are set correctly
- Check that sales users can access price list functionality
- Test price list loading in sales form

### 3. **Monitoring**
- Monitor price list application performance
- Track any errors in price calculations
- Verify data integrity in sales records

## Support and Maintenance

### 1. **Common Issues**
- Price lists not loading: Check permissions and active status
- Prices not updating: Verify price list is active and properly configured
- Calculation errors: Check percentage values and value types

### 2. **Debugging**
- Use browser console to check JavaScript errors
- Verify API endpoints are accessible
- Check database for proper price list assignments

### 3. **Performance**
- Monitor query performance for price list calculations
- Consider caching frequently used price lists
- Optimize price calculation algorithms if needed

This implementation provides a robust foundation for applying price lists to sales, with clear visual feedback and comprehensive tracking of pricing changes throughout the sales process. 