class ShoppingCart {
    constructor() {
        this.products = { "Product A": 20, "Product B": 40, "Product C": 50 };
        this.quantities = {};
        this.discounts = {
            flat_10_discount: (total) => (total > 200 ? 10 : 0),
            bulk_5_discount: (quantity, price) => (quantity > 10 ? 0.05 * quantity * price : 0),
            bulk_10_discount: (totalQuantity, totalPrice) => (totalQuantity > 20 ? 0.10 * totalPrice : 0),
            tiered_50_discount: (totalQuantity, quantity, price) =>
                totalQuantity > 30 && quantity > 15 ? 0.50 * price * (quantity - 15) : 0,
        };
        this.giftWrapFee = 1;
        this.shippingFee = 5;
        this.unitsPerPackage = 10;
    }

    calculateDiscount(totalQuantity, quantity, price) {
        const applicableDiscounts = {};
        const totalPrice = quantity * price;

        for (const discountName in this.discounts) {
            const discountFunc = this.discounts[discountName];
            applicableDiscounts[discountName] = discountFunc(totalQuantity, quantity, price);
        }

        const maxDiscountName = Object.keys(applicableDiscounts).reduce(
            (a, b) => (applicableDiscounts[a] > applicableDiscounts[b] ? a : b)
        );
        const maxDiscountAmount = applicableDiscounts[maxDiscountName];

        return [maxDiscountName, maxDiscountAmount];
    }

    calculateCost(product, quantity, isGiftWrapped) {
        const totalQuantity = Object.values(this.quantities).reduce((a, b) => a + b, 0);
        this.quantities[product] = quantity;

        const price = this.products[product];
        const [discountName, discountAmount] = this.calculateDiscount(totalQuantity, quantity, price);

        const subtotal = quantity * price;
        const totalDiscount = discountAmount;
        const shippingFee = Math.ceil((totalQuantity + quantity) / this.unitsPerPackage) * this.shippingFee;
        const giftWrapFee = isGiftWrapped ? quantity * this.giftWrapFee : 0;
        const total = subtotal - totalDiscount + shippingFee + giftWrapFee;

        return {
            productDetails: `${product}: ${quantity} x $${price} = $${subtotal}`,
            subtotal: subtotal,
            discountApplied: `Discount Applied: ${discountName} - $${totalDiscount}`,
            shippingFee: `Shipping Fee: $${shippingFee}`,
            giftWrapFee: `Gift Wrap Fee: $${giftWrapFee}`,
            total: `Total: $${total}`,
        };
    }
}

const cart = new ShoppingCart();

for (const product in cart.products) {
    const quantity = parseInt(prompt(`Enter quantity of ${product}:`), 10);
    const isGiftWrapped = prompt(`Is ${product} wrapped as a gift? (yes/no):`).toLowerCase() === "yes";
    const result = cart.calculateCost(product, quantity, isGiftWrapped);
    console.log(Object.values(result).join("\n"));
    console.log("\n" + "=".repeat(50) + "\n");
}
