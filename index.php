<?php 
setlocale(LC_TIME, 'sv');
//hardcoded date for the purpose of seeing if my pricefunctions work
$current_time = strtotime("2017-10-10 16:00");

include "products.php";

//starts session > tells browser to save cookies
session_start();
//saves posted data in a var for cleaner code
$form_data = $_POST;

//function to check if its happy hours (even day, uneven week, between 13pm and 17pm) or not
function is_happy_hours($current_time) {
    
        $is_even_day = date('d', $current_time) % 2 == 0;
        $is_even_week = !date('W', $current_time) % 2 == 0;
        $is_after_13_clock = date('H', $current_time) > 13;
        $is_before_17_clock = date('H', $current_time) < 17;
    
        if ($is_even_day
            && $is_even_week
            && $is_after_13_clock
            && $is_before_17_clock) {
    
            return true;
    
        } else {
    
            return false;
        }
    }

//calculates product price depending on day and happy hours
function get_price($price, $current_time){

    if (is_happy_hours($current_time)){
        $sum = ($price = $price * 0.95); //if happy hours
    } 

    if ((date('N', $current_time) == 1)){ //if monday
        return $price * 0.5; 
    } elseif (date('N', $current_time) == 3){ //if wednesday
        return $price + $price  * 1.1; 
    } elseif (date('N', $current_time) == 5){ //if friday
        if ($price > 200){
            return $price - 20; 
        }
    }

    return $price;
}

//conditions for how the contactform should be filled in
$order_complete = false;
$name_error = false;
$adress_error = false;
$phone_empty_error = false;
$phone_error = false;
$mail_empty_error = false;
$mail_error = false;

if (isset($form_data["submit"])){
    if (empty($form_data["name"])){
       $name_error = true;
    } 

    if (empty($form_data["adress"])){
        $adress_error = true;
    } 
    
    if (empty($form_data["phone"])){
        $phone_empty_error = true;
    } 

    if (!is_numeric($form_data["phone"]) && !empty($form_data["phone"])){
        $phone_error = true;
    }
    
    if (empty($form_data["mail"])){
        $mail_empty_error = true;
    } 
    
    if (strpbrk($form_data["mail"], "@") == false && !empty($form_data["mail"])){
        $mail_error = true;
    }
    
    $order_complete = !$name_error 
        && !$adress_error 
        && !$phone_error 
        && !$mail_error 
        && !$phone_empty_error 
        && !$mail_empty_error;

    if ($order_complete){
        //saves postdata from the form, so the user don't have to fill it again (if error)
        $_SESSION["form-data"] = $_POST;

        //if user has filled in everything correct, s/he is sent to form.php 
        header("Location: form.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- loads the header from a document for a cleaner view -->
<?php include "head.html"; ?>

<body>

    <header>
        <h1>Dunder Mifflin Cool Stuff Company</h1>
        <div class="date-container">
            <?= date("l dS F Y", $current_time);?> 
        </div>
    </header>
    
    <!-- shows message depending on if its happy hours or not -->
    <?php if (is_happy_hours($current_time)): ?>
        <div class="happy-hours-message">
            <p class="happy-hours-message__text happy-hours-message__header">It's happy hours!</p>
            <p class="happy-hours-message__text">Today between 13PM and 17PM you get 5% off all orders</p>
            <p class="happy-hours-message__text">and your order will be shipped as soon as tomorrow.</p>
        </div>
    <?php endif ?>

    <main>
        <form class="order-form"  method="POST">
            <div class="product-container">
            <!-- loops through the products array and makes a container for each item -->
            <?php foreach ($PRODUCTS as $key => $product): ?>    
                <div class="product-container__item">
                    <div class="product-container__title"> 
                            <?= $product["name"]; ?>
                            <div class="product-container__attribute">
                                (<?= $product["color"]; ?>)
                            </div>
                    </div>

                    <img class="product-img" src="<?= $product["img"] ?>"/>

                    <div class="product-container__attribute product-container__attribute--padding">
                        <?php
                            $org_price = $product["price"];
                            //gets price depending on sale/increase 
                            $new_price = get_price($product["price"], $current_time);
                            //formats the price so its limited to 2 decimals
                            $formatted_price = number_format($new_price, 2, ".", "");
                        ?>

                         <!-- controlls if broswer should show just the originalprice 
                         or if its sale/increase show the orginal and new price -->    
                        <?php if ($org_price != $new_price): ?>
                            <span class="org-price"><?= $org_price . " SEK"; ?></span>
                            <span class="new-price"><?= $formatted_price. " SEK"; ?></span>
                        <?php endif; ?>
                        <?php if ($org_price == $new_price): ?>
                            <?= $org_price . " SEK"; ?>
                        <?php endif; ?>
                    </div>

                    <!-- makes a input field for each product where user chooses how many
                    products s/he wants. "Value" stores the input data so the user don't have to 
                    fill it out again if gets inputserror in contactform and have to "restart" -->
                    <div class="product-container__attribute product-container__attribute--right">
                        <span>Quantity</span>
                        <input class="quantity" type="number" min="0" max="999" 
                            name="product[<?= $key ?>][quantity]" 
                            value="<?= $form_data["product"][$key]["quantity"] ?>" />
                    </div>

                    <!-- "saves" information to be used in form -->
                    <input type="hidden" name="product[<?= $key ?>][price]" value="<?= get_price($product["price"], $current_time); ?>" />
                    <input type="hidden" name="product[<?= $key ?>][color]" value="<?= $product["color"] ?>" />
                    <input type="hidden" name="product[<?= $key ?>][name]" value="<?= $product["name"] ?>"  />
                </div>
            <?php endforeach; ?>    

            <!-- contact form. if statements checks if its filled in correctly see line 54-85 for conditions -->
            <div class="contact">
                <p class="contact__header">Shipping details</p>
            
                <p class="contact__label">Name</p>
                <input class="contact__input" type="text" value="<?= $form_data['name'] ?? '' ?>" name="name"/><br />
                <?php if ($name_error): ?>
                    <p class="input-error">Field is empty, enter your name to continue</p>
                <?php endif; ?>
            
                <p class="contact__label">Adress</p>
                <input class="contact__input" type="text" value="<?= $form_data['adress'] ?? '' ?>" name="adress"/><br />
                <?php if ($adress_error): ?> 
                    <p class="input-error">Field is empty, enter your address to continue</p>
                <?php endif; ?>
            
                <p class="contact__label">Phone</p>
                <input class="contact__input" type="text" value="<?= $form_data['phone'] ?? '' ?>" name="phone"/><br />
                <?php if ($phone_error): ?> 
                    <p class="input-error">Only digits are valid as phone number</p>
                <?php endif; ?>
                <?php if ($phone_empty_error): ?>
                    <p class="input-error">Field is empty, enter your phone number to continue</p>
                <?php endif; ?>
            
                <p class="contact__label">E-mail</p>
                <input class="contact__input" type="text" value="<?= $form_data['mail'] ?? ''?>" name="mail"/><br />
                <?php if ($mail_empty_error): ?>
                    <p class="input-error">Field is empty, enter your email to continue</p>
                <?php endif; ?>
                <?php if ($mail_error): ?> 
                    <p class="input-error">Email address was written in unvalid format </p>
                <?php endif; ?>

                <div class="submit-btn-container">
                    <input class="submit-btn" type="submit" name="submit" value="Order now!"/>
                </div> 
            </div>
        </form>
    </main>
</body>
</html>