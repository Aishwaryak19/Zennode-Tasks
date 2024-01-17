<?php

class ShoppingCart {
    public $products = [
        'Product A' => 20,
        'Product B' => 40,
        'Product C' => 50
    ];

    private $quantities = [];
    private $discounts;
    private $giftWrapFee = 1;
    private $shippingFee = 5;
    private $unitsPerPackage = 10;

    public function __construct() {
        $this->discounts = [
            'flat_10_discount' => function($total) {
                return $total > 200 ? 10 : 0;
            },
            'bulk_5_discount' => function($quantity, $price) {
                return $quantity > 10 ? 0.05 * $quantity * $price : 0;
            },
            'bulk_10_discount' => function($totalQuantity, $totalPrice) {
                return $totalQuantity > 20 ? 0.10 * $totalPrice : 0;
            },
            'tiered_50_discount' => function($totalQuantity, $quantity, $price) {
                return ($totalQuantity > 30 && $quantity > 15) ? 0.50 * $price * ($quantity - 15) : 0;
            },
        ];
    }

    public function getProducts() {
        return $this->products;
    }

    public function calculateDiscount($totalQuantity, $quantity, $price) {
        $applicableDiscounts = [];
        $totalPrice = $quantity * $price;

        foreach ($this->discounts as $discountName => $discountFunc) {
            $applicableDiscounts[$discountName] = $discountFunc($totalQuantity, $quantity, $price);
        }

        $maxDiscountName = array_search(max($applicableDiscounts), $applicableDiscounts);
        $maxDiscountAmount = max($applicableDiscounts);

        return [$maxDiscountName, $maxDiscountAmount];
    }

    public function calculateCost($product, $quantity, $isGiftWrapped) {
        $totalQuantity = array_sum($this->quantities);
        $this->quantities[$product] = $quantity;

        $price = $this->products[$product];
        list($discountName, $discountAmount) = $this->calculateDiscount($totalQuantity, $quantity, $price);

        $subtotal = $quantity * $price;
        $totalDiscount = $discountAmount;
        $shippingFee = ceil(($totalQuantity + $quantity) / $this->unitsPerPackage) * $this->shippingFee;
        $giftWrapFee = $isGiftWrapped ? $quantity * $this->giftWrapFee : 0;
        $total = $subtotal - $totalDiscount + $shippingFee + $giftWrapFee;

        return [
            "Product" => "$product: $quantity x $$price = $$subtotal",
            "Subtotal" => $subtotal,
            "Discount" => "Discount Applied: $discountName - $$totalDiscount",
            "Shipping Fee" => "Shipping Fee: $$shippingFee",
            "Gift Wrap Fee" => "Gift Wrap Fee: $$giftWrapFee",
            "Total" => "Total: $$total",
        ];
    }
}

$cart = new ShoppingCart();

foreach ($cart->getProducts() as $product => $price) {
    $quantity = intval(readline("Enter quantity of $product: "));
    $isGiftWrapped = strtolower(readline("Is $product wrapped as a gift? (yes/no): ")) === 'yes';
    $result = $cart->calculateCost($product, $quantity, $isGiftWrapped);
    
    echo implode("\n", $result) . "\n";
    echo str_repeat("=", 50) . "\n";
}
?>
