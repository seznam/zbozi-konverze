# Pokročilé měření konverzí Zboží.cz

Pro získání výhod spojených s využíváním pokročilého měření konverzí Zboží.cz, jakými jsou např. rozšířené statistiky o konverzích, pozicích a návratnosti investic, je třeba rozšířit a autorizovat informace ze základního (JavaScript) měřícího kódu  pomocí ověřeného požadavku z vašeho backend rozhraní.

ID eshopu a tajný API klíč získáte v [administraci vaší provozovny](https://admin.zbozi.cz), kde také *musíte schválit souhlas s obchodními podmínkami pro Pokročilé měření konverzí*.

K dispozici je také [Nástroj pro ověření funkčnosti pokročilého měření konverzí (dále jen Sandbox)](http://sandbox.zbozi.cz) využívající testovací údaje. Další nápovědu naleznete v [Nápovědě Zboží](http://napoveda.seznam.cz/cz/zbozi/napoveda-pro-internetove-obchody/mereni-konverzi-internetoveho-obchodu-na-zbozicz/)


## Předávaná data
S výjimkou PRIVATE_KEY lze všechny proměnné odeslat z frontendu i backendu, doporučujeme využít vždy obou metod, náš systém pak následně údaje spojí s pomocí společného orderId.

Všechny textové údaje musí být v kódování `utf-8`. Znaky nepatřící do `utf-8` jsou při zpracování ignorovány.

### Autentizace a autorizace
Název proměnné | Povinný       | Popis
:------------- | :------------ | :---------
SHOP_ID | Ano | (int &#124; string) ID provozovny, získáte v [administraci vaší provozovny](https://admin.zbozi.cz), případně na testovacím Sandboxu. Toto ID se využívá ve frontend (JavaScript) i backend kódu.
PRIVATE_KEY | Ano | (string) Tajný klíč využívaný výhradně pro autorizaci požadavků z backendu, získáte také v [administraci vaší provozovny](https://admin.zbozi.cz), případně na testovacím Sandboxu. Při prozrazení tohoto kódu si vygenerujte nový.

### Společné vlastnosti objednávky

Název proměnné | Povinný       | Popis
:------------- | :------------ | :---------
orderId | Ano | (string) Číslo/kód objednávky vygenerovaný vaším e-shopem. Je třeba aby se shodovalo u frontend i backend konverzního kódu, aby mohly být údaje spojené.
email | Ano* | (email) E-mail zákazníka. Může být využit pro ověření spokojenosti s nákupem a k žádosti o ohodnocení zakoupeného produktu. Povinný pro získání přístupu k pokročilým statistikám, nezasílat v případě, kdy zákazník neudělil souhlas s jeho poskytnutím.
cart | Ano | (array) Obsah nákupního košíku
deliveryType | Doporučený | (string) Způsob dopravy. Může být libovolný řetězec (např. Česká pošta, osobní odběr apod.). V administraci pak získáte agregované statistiky jednodlivých způsobů dopravy.
deliveryDate | Doporučený | (yyyy-mm-dd) Datum, kdy má objednávka být předána dopravci nebo připravena k osobnímu odběru. (je-li jich více termínů pro více položek, vyberte nejzazší či takový, ve kterém půjde nejvíce zboží)
deliveryPrice | Doporučený | (number) Cena dopravy (bez ceny dobírky) v Kč včetně DPH. (Znaménkový 32bitový integer, -2<sup>31</sup> – 2<sup>31</sup>-1.)
paymentType | Doporučený | (string) Způsob platby. Může být libovolný řetězec (např. kartou, hotovost apod.).
otherCosts | Doporučený | (number) Další náklady či slevy na objednávku, poplatek za dobírku, platbu kartou, instalace, množstevní sleva apod. Slevy jsou uvedeny jako záporné číslo. (Znaménkový 32bitový integer, -2<sup>31</sup> – 2<sup>31</sup>-1.)
totalPrice | Ne | (number) Celková cena objednávky v Kč včetně DPH. Pokud není uvedena, či je nulová, bude vypočítána jako součet ceny nákupního košíku, ceny dopravy a dalších nákladů na objednávku. (0 – 2<sup>31</sup>-1.)

### Vlastnosti jednotlivých položek košíku (obsah proměnné "cart")

Název proměnné | Povinný | Popis
:------------- | :------ | :----
itemId | Ano | (string) ID položky v e-shopu (ITEM_ID z feedu)
productName | Ano | (string) Název položky, ideálně PRODUCTNAME z feedu
unitPrice | Ano | (number) Jednotková cena položky v Kč včetně DPH. (1 – 2<sup>31</sup>-1.)
quantity | Ano | (number) Počet zakoupených kusů. (1 – 2<sup>31</sup>-1.)


## PHP

Pokud je váš e-shop v PHP, můžete pro usnadnění použít třídu `ZboziKonverze.php`, kterou jsme pro vás připravili.

Příklad použití:

```php
<?php

include_once("ZboziKonverze.php");

try {

    // inicializace
    $zbozi = new ZboziKonverze(1234567890, "fedcba9876543210123456789abcdef");

    // testovací režim
    //$zbozi->useSandbox(true);

    // nastavení informací o objednávce
    $zbozi->setOrder(array(
        "deliveryType" => "Česká pošta (do ruky)",
        "deliveryDate" => "2016-02-29",
        "deliveryPrice" => 80,
        "email" => "email@example.com",
        "orderId" => "2016-007896",
        "otherCosts" => 20,
        "paymentType" => "dobírka",
        "totalPrice" => 7500.50  //1×5000.50 + 4×600 + 80 + 20
    ));

    // přidáni zakoupené položky
    $zbozi->addCartItem(array(
        "itemId" => "1357902468",
        "productName" => "Samsung Galaxy S3 (i9300)",
        "quantity" => 1,
        "unitPrice" => 5000.50,
    ));

    // přidáni další zakoupené položky
    $zbozi->addCartItem(array(
        "itemId" => "2468013579",
        "productName" => "BARUM QUARTARIS 165/70 R14 81 T",
        "quantity" => 4,
        "unitPrice" => 600,
    ));

    // odeslání
    $zbozi->send();

} catch (ZboziKonverzeException $e) {
    print "Error: " . $e->getMessage();
}

?>
```

## Alternativní moduly třetích stran

* [soukicz/zbozicz](https://github.com/soukicz/zbozicz) – PHP s využitím namespaců a podporou asychronního odesílání objednávek

## Vlastní implementace

V případě, že nemůžete či nechcete použít některý z připravených modulů, je nutné vytvořit vlastní implementaci.

Údaje o nákupu mohou být předány dvěma způsoby. Prvním a doporučovaným způsobem je zaslání HTTP POST requestu, jehož obsahem je JSON s údaji o nákupu. Druhou možností je zaslání HTTP GET requestu, ve kterém se údaje o nákupu serializují do URL parametrů.

Request je nutné odeslat na adresu `https://www.zbozi.cz/action/SHOP_ID/conversion/backend`, kde `SHOP_ID` je unikátní po každý e-shop.
Pro testování lze použít adresu `https://sandbox.zbozi.cz/action/TEST_ID/conversion/backend`, kde vám `TEST_ID` bude náhodně vygenerováno.

> **Upozornění pro testovací režim:**
>
> Data zaslaná na testovací rozhraní může vidět každý, kdo zná `TEST_ID`, proto testovací rozhraní nepoužívejte na posílání skutečných dat!

#### HTTP POST

Příklad requestu:

```
POST /action/1234567890/conversion/backend HTTP/1.1
Host: www.zbozi.cz
Content-Length: 688
Content-Type: application/json


{
    "PRIVATE_KEY": "fedcba9876543210123456789abcdef",
    "sandbox": false,
    "orderId": "2016007896",
    "email": "email@example.com",
    "deliveryType": "Česká pošta (do ruky)",
    "deliveryDate": "2016-02-29",
    "deliveryPrice": 80,
    "paymentType": "dobírka",
    "otherCosts": 20,
    "totalPrice": 7500.50,
    "cart": [
        {
            "itemId": "1357902468",
            "productName": "Samsung Galaxy S3 (i9300)",
            "unitPrice": 5000.50,
            "quantity": 1
        },
        {
            "itemId": "2468013579",
            "productName": "BARUM QUARTARIS 165/70 R14 81 T",
            "unitPrice": 600,
            "quantity": 4
        }
    ]
}
```

#### HTTP GET

Údaje o objednávce je možné poslat jako parametry GET requestu. Názvy parametrů jsou stejné jako v případě v JSONu. Jediný rozdíl je u parametru `cart`, který tvoří serializovaný řetězec z jeho atributů a hodnot oddělených středníkem (př.: productName:jmenoProduktu;itemId:idProduktu;unitPrice:100;quantity:3).

Například

```
"cart": [
    {
        "itemId": "1357902468",
        "productName": "Samsung Galaxy S3 (i9300)",
        "unitPrice": 5000.50,
        "quantity": 1
    },
    {
        "itemId": "2468013579",
        "productName": "BARUM QUARTARIS 165/70 R14 81 T",
        "unitPrice": 600,
        "quantity": 4
    }
]
```

je nutné vyjádřit jako:

`cart=itemId:1357902468;quantity:1;unitPrice:5000.5;productName:Samsung+Galaxy+S3+%28i9300%29&cart=itemId:2468013579;quantity:4;unitPrice:600;productName:BARUM+QUARTARIS+165%2F70+R14+81+T`

Příklad requestu:

`https://www.zbozi.cz/action/1234567890/conversion/backend?orderId=2016007896&PRIVATE_KEY=fedcba9876543210123456789abcdef&deliveryType=%C4%8Cesk%C3%A1+po%C5%A1ta+%28do+ruky%29&paymentType=dob%C3%ADrka&deliveryDate=2016-02-29&email=email%40example.com&cart=itemId:1357902468;quantity:1;unitPrice:5000.5;productName:Samsung+Galaxy+S3+%28i9300%29&cart=itemId:2468013579;quantity:4;unitPrice:600;productName:BARUM+QUARTARIS+165%2F70+R14+81+T`



