* English version: [README.en.md](README.en.md)

# Měření konverzí Zboží.cz

Pro získání výhod spojených s měřením konverzí Zboží.cz, jakými jsou např. zjišťování spokojenosti zákazníků s nákupem nebo přístup ke statistikám výkonu přes API, je třeba zasílat informace z frontend měřícího kódu (JavaScript) i z vašeho backend rozhraní.

Pro autentizaci a autorizaci se využívá ID provozovny a tajný klíč. Tyto údaje získáte v [administraci](https://admin.zbozi.cz), kde je také třeba schválit souhlas se smluvními podmínkami pro měření konverzí a uzavřít dohodu o zpracování osobních údajů.

Pro odladění a ověření funkčnosti své implementace měření konverzí můžete využít testovací prostředí – [Sandbox](http://sandbox.zbozi.cz). K dispozici je vám i [nápověda Zboží.cz](https://napoveda.zbozi.cz/konverzni-kod/standardni-konverzni-kod/)


## Předávaná data
Z důvodu zabezpečení a spolehlivosti dat je třeba odeslat údaje z frontendu i backendu, náš systém pak následně data spojí přes společné orderId.
Všechny textové údaje musí být v kódování `utf-8`. Znaky nepatřící do `utf-8` jsou při zpracování ignorovány.

## Frontend

Název proměnné | Povinný       | Popis
:------------- | :------------ | :---------
zboziId | Ano | (int) ID provozovny, získáte v [administraci své provozovny](https://admin.zbozi.cz), případně na testovacím Sandboxu.
orderId | Ano | (string, maximum 255 znaků) Číslo/kód objednávky vygenerovaný vaším e-shopem. Je třeba aby se shodovalo u frontend i backend konverzního kódu, aby mohly být údaje spojené.
zboziType | Ne | (string) "standard" = standardní měření konverzí (default); "limited" = omezené měření; "sandbox" = testovací režim standardního měření
consent | Ano | (int) Souhlas od návštěvníka na odeslání konverzního hitu, povolené hodnoty: 0 (není souhlas) nebo 1 (je souhlas). Podrobnější informace o souhlasu [najdete níže](#souhlas-u%C5%BEivatele-s-m%C4%9B%C5%99en%C3%ADm--consent).
eid | Doporučený | (string) E-mail zákazníka, lze posílat prostý text nebo hash, popis je [v dokumentaci identity (sekce Hashovaný email)](https://vyvojari.seznam.cz/identita/eid). Předávejte, jen máte-li výslovný souhlas zákazníka. Vizte též sekci [eid vs. email](#eid-vs-email).
aid | Doporučený | (objekt s adresou) Bydliště nebo poštovní adresa zákazníka. Posílejte, jen máte-li jeho výslovný souhlas. Obsahuje pole a1 až a6, popis je [v dokumentaci identity (sekce Adresa)](https://vyvojari.seznam.cz/identita/adresa).
tid | Doporučený | (string) Telefonní číslo zákazníka, ve formátu +420724123456. Posílejte, jen máte-li jeho výslovný souhlas. Podrobný popis je [v dokumentaci identity (sekce Telefonní číslo)](https://vyvojari.seznam.cz/identita/telefon).
id | Ne | (int) ID konverzního kódu Sklik, používá se pro měření konverzí v Skliku
value | Ne | (int) Hodnota objednávky v Kč; pro měření konverzí v Skliku, standardní měření konverzí Zboží.cz ji nezohledňuje


#### Souhlas uživatele s měřením – consent

Konverzní kód potřebuje od inzerenta informace, má-li od návštěvníka svého webu souhlas k tomu, aby ho mohl prostřednictvím cookies opětovně oslovovat v reklamní síti a aby ho mohl sledovat pro doměřování konverzí (zákon č. 127/2005 Sb., o elektronických komunikacích).

Informaci o objednávce odešlete i v případě, že tento souhlas nemáte – opačný přístup by zbytečně vyvolávat chyby při spojování konverzních dat, a znemožnil by vám u takových objednávek sbírat hodnocení zákazníků (to funguje nezávisle na cookies). K předání informace o souhlasu s cílenou reklamou a měřením konverzí využijte parametr `consent`. Pokud souhlas existuje (hodnota 1) nebo parametr není přiložen, Seznam.cz data zpracuje běžným způsobem vč. čtení cookies. Pokud souhlas není (hodnota 0), systém data zpracuje pro zpřesnění měření konverzí pomocí modelování, avšak v souladu se zákonem (neukládá ani nečte informace ze zařízení uživatele).


### Konverzní JavaScript kód

Frontend kód by měl být na stránce zobrazující se po odeslání/potvrzení objednávky a může být umístěn do hlavičky stránky (před `</head>`) nebo do těla stránky (před `</body>`). **Nevkládejte kód do stránky jako asynchronní s atributem async.**

```html
<script type="text/javascript" src="https://c.seznam.cz/js/rc.js"></script>
<script>
    /* nastavení objektu identity */
    if (window.sznIVA && window.sznIVA.IS) {
        window.sznIVA.IS.updateIdentities({
            eid: "EMAIL_ZAKAZNIKA", // email či zahashovaný email
            aid: {
                "a1":"STAT", // stát
                "a2":"MESTO", // město
                "a3":"ULICE", // ulice
                "a4":"CP", // číslo popisné
                "a5":"PSC", // poštovní směrovací číslo
            },
            tid: "TELEFON", // telefonní číslo
        });
    }

    var conversionConf = {
        zboziId: ID_PROVOZOVNY, // ID provozovny na Zboží
        orderId: "CISLO OBJEDNAVKY",  // Číslo objednávky
        zboziType: "standard", // Typ měření konverzí Zboží.cz, pro testovací režim uvádějte "sandbox"
        consent: SOUHLAS, // Souhlas od návštěvníka na odeslání konverzního hitu
        
        id: SKLIK_ID, // ID konverzního kódu Skliku (pro měření konverzí i pro Sklik)
        value: HODNOTA_OBJEDNAVKY, // Hodnota objednávky v Kč (pro měření konverzí pro Sklik)
    };
    
    if (window.rc && window.rc.conversionHit) {
        window.rc.conversionHit(conversionConf);
    }
</script>
```

#### Nastavení Content Security Policy

Používáte-li na webu omezení Content Security Policy, je nutné aby povolovala alespoň:

* `script-src 'unsafe-inline' https://*.seznam.cz https://*.zbozi.cz;`
* `img-src https://*.seznam.cz;`
* `frame-src https://*.zbozi.cz;`

`script-src 'unsafe-inline'` je nutné kvůli volání skriptu přímo z tagu `<script>`, pokud používáte jinou metodu vkládání kódů (např. GTM), ovlivní to i potřebná nastavení Content Security Policy.

## Backend

### Autentizace a autorizace
Název proměnné | Povinný       | Popis
:------------- | :------------ | :---------
SHOP_ID | Ano | (int) ID provozovny, získáte v [administraci své provozovny](https://admin.zbozi.cz), případně na testovacím Sandboxu.
PRIVATE_KEY | Ano | (string, maximum 255 znaků) Tajný klíč využívaný výhradně pro autorizaci požadavků z backendu, získáte také v [administraci své provozovny](https://admin.zbozi.cz), případně na testovacím Sandboxu. Při prozrazení tohoto kódu si vygenerujte nový.

### Vlastnosti objednávky

Název proměnné | Povinný       | Popis
:------------- | :------------ | :---------
orderId | Ano | (string, maximum 255 znaků) Číslo objednávky vygenerované e-shopem. Je třeba, aby se shodovalo u dat zaslaných z frontendu i backendu, aby mohlo dojít k jejich spojení.
email | Doporučený | (email, maximum 100 znaků) E-mail zákazníka. Může být využit pro ověření spokojenosti s nákupem a k žádosti o ohodnocení zakoupeného produktu. Nezasílat v případě, kdy zákazník neudělil souhlas s jeho poskytnutím. Vizte též sekci [eid vs. email](#eid-vs-email).
cart | Ano | (array) Obsah nákupního košíku.
deliveryType | Doporučený | (string, maximum 100 znaků) Způsob dopravy, pokud možno [DELIVERY_ID z feedu](https://napoveda.zbozi.cz/xml-feed/specifikace/#DELIVERY )
deliveryPrice | Doporučený | (number) Cena dopravy v Kč včetně DPH. (Znaménkový 32bitový integer, 0 – (2<sup>31</sup>-1)/100.)
otherCosts | Doporučený | (number) Další náklady či slevy na objednávku, platbu kartou, instalace, množstevní sleva apod. Slevy jsou uvedeny jako záporné číslo. (Znaménkový 32bitový integer, -2<sup>31</sup>/100 – (2<sup>31</sup>-1)/100.)
paymentType | Ne | (string, maximum 100 znaků) Způsob platby. Může být libovolný řetězec (např. kartou, hotovost apod.).

### Vlastnosti jednotlivých položek košíku (obsah proměnné "cart")

Název proměnné | Povinný | Popis
:------------- | :------ | :----
itemId | Ano | (string, maximum 255 znaků) ID položky v e-shopu (ITEM_ID z feedu)
productName | Ano | (string, maximum 255 znaků) Název položky, ideálně PRODUCTNAME z feedu
unitPrice | Doporučený | (number) Jednotková cena položky v Kč včetně DPH. (0 – (2<sup>31</sup>-1).)
quantity | Doporučený | (number) Počet zakoupených kusů. (1 – (2<sup>31</sup>-1).)

### eid vs. email

E-mailová adresa zákazníka může být předána ve dvou různých polích: `eid` v rámci identity (na front-endu), a `email` (na back-endu, v rámci informací o objednávce). Ačkoli obě pole typicky obsahují stejná data, jejich účel a smysl je odlišný:

* `eid` je součástí identity pro měření konverzí, cílení reklamy a retargeting. Jeho zpracování je podmíněno souhlasem zákazníka s tímto účelem (["consent"](#souhlas-u%C5%BEivatele-s-m%C4%9B%C5%99en%C3%ADm--consent)), a e-mailovaná adresa může být předávána i hashovaná, resp. pokud není, je zahashovaná před dalším zpracováním.
* `email` v rámci back-endové informace je používaný pro ověření spokojenosti s nákupem a hodnocení zakoupeného produktu. Obecnou praxí je, že pro tento účel stačí poskytnout zákazníkovu možnost opt-outu &ndash; to se typicky řeší checkboxem "Nesouhlasím s předáním údajů za účelem nezávislého hodnocení nákupu." v nákupním procesu. Zde adresa hashována být nesmí, protože na ní bude Zboží posílat e-maily.

Tedy:
* `eid` posílejte, právě když máte od zákazníka souhlas s cílením a měřením reklamy
* `email` posílejte, pokud vám zákazník nezakázal předávat údaje pro hodnocení nákupu

### PHP

Pokud je váš e-shop v PHP, můžete pro usnadnění použít třídu `ZboziKonverze.php`, kterou jsme pro vás připravili. Pokud něco nefunguje jak má (třeba při potížích na síti, nebo když e-shop měření konverzí vypne v administraci Zboží), vyhazuje třída výjimky. Výjimky doporučujeme zpracovávat nebo alespoň odchytávat, aby nenarušily zpracování objednávky.

Příklad použití:

```php
<?php

include_once("ZboziKonverze.php");

try {

    // inicializace
    $zbozi = new ZboziKonverze(ID PROVOZOVNY, "TAJNY KLIC");

    // testovací režim
    //$zbozi->useSandbox(true);

    // nastavení informací o objednávce
    $zbozi->setOrder(array(
        "orderId" => "CISLO OBJEDNAVKY",
        "email" => "email@example.com",
        "deliveryType" => "CESKA_POSTA",
        "deliveryPrice" => 80,
        "otherCosts" => 20,
        "paymentType" => "dobírka",
    ));

    // přidáni zakoupené položky
    $zbozi->addCartItem(array(
        "itemId" => "1357902468",
        "productName" => "Samsung Galaxy S3 (i9300)",
        "quantity" => 1,
        "unitPrice" => 5000.50,
    ));

    // přidání další zakoupené položky
    $zbozi->addCartItem(array(
        "itemId" => "2468013579",
        "productName" => "BARUM QUARTARIS 165/70 R14 81 T",
        "quantity" => 4,
        "unitPrice" => 600,
    ));

    // odeslání
    $zbozi->send();

} catch (ZboziKonverzeException $e) {
    // zalogování případné chyby
    error_log("Chyba konverze: " . $e->getMessage());
}

?>
```

### Alternativní moduly třetích stran

* [soukicz/zbozicz](https://github.com/soukicz/zbozicz) – PHP s využitím namespaců a podporou asychronního odesílání objednávek

### Vlastní implementace

V případě, že nemůžete či nechcete použít některý z připravených modulů, je nutné vytvořit vlastní implementaci.

Údaje o nákupu mohou být předány dvěma způsoby. Prvním a doporučovaným způsobem je zaslání HTTP POST requestu, jehož obsahem je JSON s údaji o nákupu. Druhou možností je zaslání HTTP GET requestu, ve kterém se údaje o nákupu serializují do URL parametrů.

Request je nutné odeslat na adresu `https://www.zbozi.cz/action/SHOP_ID/conversion/backend`, kde `SHOP_ID` je unikátní pro každý e-shop.
Pro testování lze použít adresu `https://sandbox.zbozi.cz/action/TEST_ID/conversion/backend`, kde vám `TEST_ID` bude náhodně vygenerováno.

> **Upozornění pro testovací režim:**
>
> Data zaslaná na testovací rozhraní může vidět každý, kdo zná `TEST_ID`, proto testovací rozhraní nepoužívejte na posílání skutečných dat!

#### HTTP POST

Příklad requestu:

```http
POST /action/ID PROVOZOVNY/conversion/backend HTTP/1.1
Host: www.zbozi.cz
Content-Length: 688
Content-Type: application/json


{
    "PRIVATE_KEY": "TAJNY KLIC",
    "sandbox": false,
    "orderId": "CISLO OBJEDNAVKY",
    "email": "email@example.com",
    "deliveryType": "CESKA_POSTA",
    "deliveryPrice": 80,
    "paymentType": "dobírka",
    "otherCosts": 20,
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

Údaje o objednávce je možné poslat jako parametry GET requestu. Názvy parametrů jsou stejné jako v případě v JSONu. Jediný rozdíl je u parametru `cart`, který tvoří serializovaný řetězec z jeho atributů a hodnot oddělených středníkem (př.: `productName:jmenoProduktu;itemId:idProduktu;unitPrice:100;quantity:3`).

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

`https://www.zbozi.cz/action/ID PROVOZOVNY/conversion/backend?orderId=CISLO OBJEDNAVKY&PRIVATE_KEY=TAJNY KLIC&deliveryType=CESKA_POSTA&paymentType=dob%C3%ADrka&email=email%40example.com&cart=itemId:1357902468;quantity:1;unitPrice:5000.5;productName:Samsung+Galaxy+S3+%28i9300%29&cart=itemId:2468013579;quantity:4;unitPrice:600;productName:BARUM+QUARTARIS+165%2F70+R14+81+T`
