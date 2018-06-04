<?php

include("ZboziKonverze.php");

try {

    $zbozi = new ZboziKonverze(1234567890, "fedcba9876543210123456789abcdef");

    // testovací režim
    //$zbozi->useSandbox(true);

    $zbozi->setEmail('jan.novak@example.com');
    $zbozi->addOrderId(123456);
    $zbozi->addProductItemId('B1234');
    $zbozi->addProduct("iPhone 7S");
    $zbozi->send();

} catch (ZboziKonverzeException $e) {
    // handle errors
    error_log("Chyba konverze: " . $e->getMessage());
}
