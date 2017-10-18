<?php 
include "products.php";

//starts session
session_start();

//updates form_data var with data retrived from current session
$form_data = $_SESSION["form-data"];

//if user goes directly to form.php without posting, the user is redicrected to index
if (!isset($form_data["product"])){
    header("Location: index.php");
    exit(); 
}

//calculates to total price of selected products
function calc_totalsum($form_data) {
    global $PRODUCTS;
    $total_sum = 0;

    foreach ($form_data["product"] as $key => $selected_product) {
        $quantity = intval($selected_product["quantity"]); // string > int to control decimals
        $price = $selected_product["price"]; 
        $total_sum += $price * $quantity;
    }
        return $total_sum;
}

//loops through the to see if user has added product if not cart is empty, function called on line 96
function is_cart_empty($form_data){
    foreach ($form_data["product"] as $key => $selected_product) {
        $quantity = intval($selected_product["quantity"]); 
        
        if ($quantity > 0){
            return false;
        }        
    }
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include "head.html"; ?>
<body>

    <header>
        <h1>Dunder Mifflin Cool Stuff Company</h1>
    </header>   

<main>
<table class="product-confirmation">
    <thead class="product-confirmation__head">
        <tr>
            <th class="product-confirmation__label">Product</th>
            <th class="product-confirmation__label">Color</th>
            <th class="product-confirmation__label">Price</th>
            <th class="product-confirmation__label">#</th>
            <th class="product-confirmation__label">Total</th>
        </tr>
    </thead>

    <!-- loops through product array and sees what has been added to users cart  -->
    <tbody>
    <?php foreach ($form_data["product"] as $key => $selected_product): ?> 
    <?php 
        $quantity = $selected_product["quantity"];
        $total = intval($quantity) * intval($selected_product["price"]); //calculates subtotal for each item
        $formated_price = floatval($selected_product["price"]); //string > float, formats to show only 2 decimals
    ?>

       <!-- if user has chosen product, information on each item is printed -->
    <?php if ($quantity > 0): ?>
        <tr class="product-confirmation">
            <td class="product-confirmation__item">
                <?= $selected_product["name"]; ?>
            </td>
            <td class="product-confirmation__item">
                <?= $selected_product["color"]; ?>
            </td>
            <td class="product-confirmation__item product-confirmation__item--wrap">
                <?= number_format($formated_price, 2, ".", "") . " SEK"; ?>
            </td>
            <td class="product-confirmation__item">
                <?= $quantity; ?>
            </td>
            <td class="product-confirmation__item product-confirmation__item--wrap">
                <?= number_format($total, 2, ".", "") . " SEK"; ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>

    <!-- if the cart is empty ($quantity > 0) the message below is shown instead of product info -->
    <?php if (is_cart_empty($form_data)): ?> 
        <p class="is-cart-empty-message">Your cart is empty</p>
    <?php endif; ?>    

    <!-- calls on the function to get TOTALprice for all products-->
    <div class="total-sum__text">
        <?php 
            $total_sum_no_sale = calc_totalsum($form_data);
            $formated_sum = number_format($total_sum_no_sale, 2, ".", "") ; 
        ?>
        <?= "Total amount: " . $formated_sum . " SEK"; ?>
    </div>

    <!-- prints the contactinformation from index -->
    <div class="contact-confirmation">
        <p class="contact-confirmation__header">Your order will be sent to:</p>
            <ul class="contact-information__list">
                <li class="contact-confirmation__info"><p>Name: <?= $form_data["name"]; ?></p></li>
                <li class="contact-confirmation__info"><p>Adress: <?= $form_data["adress"]; ?></p></li>
                <li class="contact-confirmation__info"><p>Phone: <?= $form_data["phone"]; ?></p></li>
                <li class="contact-confirmation__info"><p>E-mail: <?= $form_data["mail"]; ?></p></li>
            </ul>
    </div>
    </main>
    <div class="return-btn-container">
        <a href="."><button class="submit-btn">Place a new order</button></a>
    </div>
</body>
</html>