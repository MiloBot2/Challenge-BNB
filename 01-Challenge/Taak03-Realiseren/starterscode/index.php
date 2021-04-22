<?php
// Je hebt een database nodig om dit bestand te gebruiken....
require "database.php";

if (!isset($db_conn)) { //deze if-statement checkt of er een database-object aanwezig is. Kun je laten staan.
    return;
}

$error = null;
$totale_bedrag = 0;
$database_gegevens = null;
$poolIsChecked = false;
$bathIsChecked = false;

$sql = "SELECT * FROM homes"; //Selecteer alle huisjes uit de database

if (isset($_GET['filter_submit'])) {

    /* Is er een bad? */
    if ($_GET['faciliteiten'] == "ligbad") {
        $bathIsChecked = true;
        $sql = "SELECT * FROM homes WHERE bath_present > 0";
    }

    /* Is er een zwembad?! */
    if ($_GET['faciliteiten'] == "zwembad") {
        $poolIsChecked = true;
        $sql = "SELECT * FROM homes WHERE pool_present > 0";
    }

    /* Is er een bbq? */
    if ($_GET['faciliteiten'] == "bbq") {
        $bbqIsChecked = true;
        $sql = "SELECT * FROM homes WHERE bbq_present > 0";
    }

    /* Is er WIFI?! */
    if ($_GET['faciliteiten'] == "wifi") {
        $wifiIsChecked = true;
        $sql = "SELECT * FROM homes WHERE wifi_present > 0";
    }

    /* Is er een openhaard? */
    if ($_GET['faciliteiten'] == "fireplace") {
        $fireplaceIsChecked = true;
        $sql = "SELECT * FROM homes WHERE fireplace_present > 0";
    }

    /* Is er een vaatwasser? */
    if ($_GET['faciliteiten'] == "dishwasher") {
        $dishwasherIsChecked = true;
        $sql = "SELECT * FROM homes WHERE dishwasher_present > 0";
    }

    /* Is er een fiets?! */
    if ($_GET['faciliteiten'] == "bike_rental") {
        $bikerentalIsChecked = true;
        $sql = "SELECT * FROM homes WHERE bike_rental > 0";
    }
}

function isTrue($value)
{
    if ($value === "true") {
        return true;
    } elseif ($value === true) {
        return true;
    } else {
        return false;
    }
}

function getPrice($pp, $ppPrice, $days, $beddengoed, $bdPrice, $fiets, $bikePrice)
{
    $totaleBedrag = 0;



    $totaleBedrag = $ppPrice * $pp * $days;

    if (isTrue($beddengoed))
        $totaleBedrag += $bdPrice * $pp;

    if (isTrue($fiets))
        $totaleBedrag += $bikePrice * $pp;

    return $totaleBedrag;
}

function getBikeSelection($bike)
{
    if (isTrue($bike)) {
        return 1;
    }
    return 0;
}

/* Deze if-statement controleert of een sql-query correct geschreven is en dus data ophaalt uit de DB. */
if (is_object($db_conn->query($sql))) {
    $database_gegevens = $db_conn->query($sql)->fetchAll(PDO::FETCH_ASSOC); // Zeer belangrijke code!!!
}

if (isset($_GET['reserveer'])) {
    $sql1 = "SELECT * FROM homes WHERE id = ";
    $sql1 .= $_GET['gekozen_huis'] ?? null;
    $pp = $_GET['pp'] ?? null;
    $days = $_GET['aantal_dagen'] ?? null;

    if (!isset($pp) || $pp != null) {
        if (!isset($days) || $days != null) {

            if (is_object($db_conn->query($sql1))) {
                $database_gegevens1 = $db_conn->query($sql1)->fetchAll(PDO::FETCH_ASSOC); // Zeer belangrijke code!!!
            }
            foreach ($database_gegevens1 as $huisje) {
                if ($pp <= $huisje['max_capacity']) {
                    if (getBikeSelection($_GET['bikeRental'] ?? null) == $huisje['bike_rental'] || getBikeSelection($_GET['bikeRental'] ?? null) == 0) {
                        $totale_bedrag = getPrice($pp, $huisje['price_p_p_p_n'], $days, $_GET['beddengoed'] ?? null, $huisje['price_bed_sheets'], $_GET['bikeRental'] ?? null, $huisje['price_bike_rental']);
                    } else {
                        $error = "Er is geen fietverhuur beschikbaar op deze locatie!";
                    }
                } else {
                    $error = "Je hebt teveel personen er kunnen maximaal " . $huisje['max_capacity'] . " personen verblijven!";
                }
            }
        } else {
            $error = "Vul het aantal dagen in!";
        }
    } else {
        $error = "Vul het aantal personen in!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />

    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <link href="css/index.css" rel="stylesheet">
    <style>
        img {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <header>
        <h1>Quattro Cottage Rental</h1>
    </header>
    <main>
        <div class="left">
            <div id="mapid"></div>
            <div class="book">
                <h3>Reservering maken</h3>
                <form action="index.php" method="GET">
                    <!-- Huizen keuze. -->
                    <div class="form-control">
                        <label for="pp">Vakantiehuis</label>
                        <?php if (!isset($error) || $error != null) : ?>
                            <h1 style="color:red"><?= $error ?></h1>
                        <?php endif; ?>
                        <select name="gekozen_huis" id="gekozen_huis">
                            <option value="1">IJmuiden Cottage</option>
                            <option value="2">Assen Bungalow</option>
                            <option value="3">Espelo Entree</option>
                            <option value="4">Weustenrade Woning</option>
                        </select>
                    </div>
                    <!-- Aantal personen keuze. -->
                    <div class="form-control">
                        <label for="pp">Aantal personen</label>
                        <input type="number" name="pp" id="pp">
                    </div>
                    <!-- Aantal dagen keuze. -->
                    <div class="form-control">
                        <label for="aantal_dagen">Aantal dagen</label>
                        <input type="number" name="aantal_dagen" id="aantal_dagen">
                    </div>
                    <!-- Beddengoed keuze. -->
                    <div class="form-control">
                        <h5>Beddengoed</h5>
                        <label for="beddengoed_ja">Ja</label>
                        <input type="radio" id="beddengoed_ja" name="beddengoed" value="true">
                        <label for="beddengoed_nee">Nee</label>
                        <input type="radio" id="beddengoed_nee" name="beddengoed" value="false" checked>
                    </div>
                    <!-- Fietsen verhuur keuze. -->
                    <div class="form-control">
                        <h5>Fiets verhuur</h5>
                        <label for="bikeRental_ja">Ja</label>
                        <input type="radio" id="bikeRental_ja" name="bikeRental" value="true">
                        <label for="beddengoed_nee">Nee</label>
                        <input type="radio" id="bikeRental_nee" name="bikeRental" value="false" checked>
                    </div>
                    <button name="reserveer" value="true" id="reserveer_knop">Reserveer huis</button>
                </form>
            </div>
            <div class="currentBooking">
                <div class="bookedHome"></div>
                <div class="totalPriceBlock">Totale prijs &euro;<span class="totalPrice"><?php echo $totale_bedrag; ?></span></div>
            </div>
        </div>
        <div class="right">
            <div class="filter-box">
                <form class="filter-form">
                    <div class="form-control">
                        <a href="index.php">Reset Filters</a>
                    </div>
                    <div class="form-control">
                        <label for="ligbad">Ligbad</label>
                        <input type="radio" id="ligbad" name="faciliteiten" value="ligbad" <?php if ($bathIsChecked) echo 'checked' ?>>
                    </div>
                    <div class="form-control">
                        <label for="zwembad">Zwembad</label>
                        <input type="radio" id="zwembad" name="faciliteiten" value="zwembad" <?php if ($poolIsChecked) echo 'checked' ?>>
                    </div>
                    <button type="submit" name="filter_submit">Filter</button>
                </form>
                <div class="homes-box">
                    <?php if (isset($database_gegevens) && $database_gegevens != null) : ?>
                        <?php foreach ($database_gegevens as $huisje) : ?>
                            <img src="images/<?= $huisje['image'] ?>">
                            <h4>
                                <?php echo $huisje['name']; ?>
                            </h4>

                            <p>
                                <?php echo $huisje['description'] ?>
                            </p>
                            <div class="kenmerken">
                                <h6>Kenmerken</h6>
                                <ul>

                                    <?php
                                    if ($huisje['bath_present'] == 1)
                                        echo "<li>Er is een ligbad</li>";

                                    if ($huisje['pool_present'] == 1)
                                        echo "<li>Er is een zwembad</li>";

                                    if ($huisje['bbq_present'] == 1)
                                        echo "<li>Er is een barbecue</li>";

                                    if ($huisje['wifi_present'] == 1)
                                        echo "<li>Er is wifi</li>";

                                    if ($huisje['fireplace_present'] == 1)
                                        echo "<li>Er is een openhaard</li>";

                                    if ($huisje['dishwasher_present'] == 1)
                                        echo "<li>Er is een vaatwasser</li>";

                                    if ($huisje['bike_rental'] == 1)
                                        echo "<li>Er is een fiets verhuur</li>";

                                    if ($huisje['max_capacity'] > 0) {
                                        $caps = $huisje['max_capacity'];
                                        echo "<li>Er kunnen maximaal " . $caps . " personen in</li>";
                                    }
                                    ?>
                                </ul>

                            </div>

                            <div>
                                <h5>PP nacht:</h5>
                                Totale prijs &euro;
                                <?php
                                echo $huisje['price_p_p_p_n'];
                                ?>

                                <h5>Prijs beddengoed:</h5>
                                Totale prijs &euro;
                                <?php
                                echo $huisje['price_bed_sheets'];
                                ?>

                                <h5>Fietsen verhuur:</h5>
                                Totale prijs &euro;
                                <?php
                                echo $huisje['price_bike_rental'];
                                echo "<br></br>";
                                ?>
                            </div class="prijs">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div></div>
        <div>copyright Quattro Rentals BV.</div>
        <div></div>

    </footer>
    <script src="js/map_init.js"></script>
    <script>
        // De verschillende markers moeten geplaatst worden. Vul de longitudes en latitudes uit de database hierin
        var coordinates = [
            [52.44902, 4.61001],
            [52.99864, 6.64928],
            [52.30340, 6.36800],
            [50.89720, 5.90979]
        ];

        var bubbleTexts = [
            "IJmuiden Cottage",
            "Assen Bungalow",
            "Espelo Entree",
            "Weustenrade Woning"
        ];
    </script>
    <script src="js/place_markers.js"></script>
</body>

</html>