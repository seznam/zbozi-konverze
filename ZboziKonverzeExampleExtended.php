<?php

include("ZboziKonverze.php");

try {

    $zbozi = new ZboziKonverze(1234567890, "fedcba9876543210123456789abcdef");
    $zbozi->useSandbox(true);

    $zbozi->addCartItem(array(
        "productName" => "Název položky",
        "itemId" => "id_polozky",
        "unitPrice" => 225,
        "quantity" => 2,
    ));

    $zbozi->addCartItem(array(
        "productName" => "Jiná položka",
        "itemId" => "jine_id",
        "unitPrice" => 600,
        "quantity" => 1,
    ));

    $zbozi->setOrder(array(
        "email" => "ferda@mraven.ec",
        "deliveryType" => "balik_do_ruky",
        "deliveryPrice" => 100,
        "deliveryDate" => "2016-02-01",
        "orderId" => 123456,
        "otherCosts" => 5.33,
        "paymentType" => "prevodem_z_uctu",
        "totalPrice" => 1155.33,
    ));

    $zbozi->send();

} catch (ZboziKonverzeException $e) {
    // handle errors
    print "Error: " . $e->getMessage();
}

?>
