<?php
require "database.php"; //DATABASE

if (!isset($conn)) //CHECK VOOR DATABASE
    return;

//DEFAULT VARIABLES
$error = null;
$totale_bedrag = 0;
$berekening = "";

//TEST FUNCTIE VOOR TRUE WAARDE
function isTrue($value)
{
    if ($value === "true" || $value === true) return true;
    else return false;
}

//BEREKENEN PRIJS
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
    return isTrue($bike) ? 1 : 0;
}


$stmt = $conn->prepare("SELECT * FROM homes"); //STANDAARD SQL STATEMENT OM HUIZEN OP TE VRAGEN (GEEN FILTER)
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (isset($_POST['filter_faciliteiten'])) {
        $filter = $_POST['filter_faciliteiten'];
        $filters = array('bath', 'pool', 'bbq', 'wifi', 'fireplace', 'dishwasher', 'bike_rental'); //MOGELIJKE FILTERS

        if (in_array($filter, $filters)) { //CHECK OF DE FILTER MOGELIJK IS
            switch ($filter) {
                default:
                    $stmt = $conn->prepare("SELECT * FROM homes WHERE " . $filter . "_present > 0"); //VERANDER SQL STATEMENT NAAR FILTER
                    break;
                case "bike_rental":
                    $stmt = $conn->prepare("SELECT * FROM homes WHERE bike_rental > 0"); //VERANDER SQL STATEMENT NAAR FILTER
                    break;
            }
        }
    }

    if (isset($_POST['reserveren'])) { //RESERVEER REQUEST?
        if (isset($_POST['huis']) && isset($_POST['personen']) && isset($_POST['dagen']) && isset($_POST['beddengoed']) && isset($_POST['fietsverhuur'])) {
            $huis = $_POST['huis'];
            $personen = $_POST['personen'];
            $dagen = $_POST['dagen'];
            $beddengoed = $_POST['beddengoed'];
            $fietsverhuur = $_POST['fietsverhuur'];

            $stmt2 = $conn->prepare("SELECT * FROM homes WHERE id = ?"); //SQL STATEMENT VOOR GESELECTEERDE HUIS
            $stmt2->bind_param("s", $huis); //VUL WAARDE IN (EN VOORKOM SQL INJECTION)
            $stmt2->execute(); //EXECUTE STATEMENT
            $result2 = $stmt2->get_result(); //ZET HET HUIS IN EEN RESULTSET

            while ($huisje = $result2->fetch_assoc()) { //CHECK VOOR HUISJE
                if ($personen <= $huisje['max_capacity']) {
                    if (getBikeSelection($fietsverhuur) == $huisje['bike_rental'] || getBikeSelection($fietsverhuur) == 0) {
                        $totale_bedrag = getPrice($personen, $huisje['price_p_p_p_n'], $dagen, $beddengoed, $huisje['price_bed_sheets'], $fietsverhuur, $huisje['price_bike_rental']);

                        $berekening =
                            '(' . (int) $huisje['price_p_p_p_n'] . ' x ' .
                            $personen . ' x ' .
                            $dagen . ') + (' .
                            (int) $huisje['price_bed_sheets'] . ' x ' .
                            $personen . ') + (' .
                            (int) $huisje['price_bike_rental'] . ' x ' .
                            $personen . ') = ';
                    } else {
                        $error = "Er is geen fietverhuur beschikbaar op deze locatie!";
                    }
                } else {
                    $error = "Je hebt teveel personen, er kunnen maximaal " . $huisje['max_capacity'] . " personen verblijven in dit huisje!";
                }
            }
        } else {
            $error = "Vul alles in!";
        }
    }
}

$stmt->execute(); //EXECUTE STATEMENT
$result = $stmt->get_result(); //ZET ALLE HUIZEN IN EEN RESULTSET
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!--Site Identity-->
    <title>Quattro Cottage Rental</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--External Imports-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://kit.fontawesome.com/fcbf751360.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.typekit.net/tmr5upa.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <!--Internal Imports-->
    <link href="css/index.css" rel="stylesheet">
</head>

<body>

    <div class="page-content">

        <header class="page-header">
            <div class="wrapper content">
                <p class="content-text title">Quattro Cottage Rental</p>
                <p class="content-text smallsub">Reserveer het perfecte vakantiehuisje!</p>
            </div>
        </header>

        <div style="background-color: #CCDBDC;">
            <div class="wrapper content">
                <p class="content-text subtitle">Ons aanbod</p>
                <p class="content-text text">We hebben huisjes door het hele land. Hier vindt u al onze locaties!</p>

                <?php
                if ($result->num_rows > 0) { //ZIJN ER HUISJES GEVONDEN?
                    echo "<div class='flex-grid' style='margin-top: 5px;'>";
                    while ($huisje = $result->fetch_assoc()) { //LAAT ELK HUISJE ZIEN
                        echo "<div class='col'>";
                        echo "<div class='huisje'>";
                        echo "<div class='huisje-header' style='background-image: url(images/" . $huisje['image'] . ");'>";
                        echo "<p class='content-text' style='color: white; font-weight: 600; font-size: 28px;'>" . $huisje['name'] . "</p>";
                        echo "</div>";

                        echo "<p class='content-text' style='font-size: 15px;'>" . $huisje['description'] . "</p>";

                        echo "<div class='flex-grid' style='margin-top: 10px;'>";
                        echo "<div class='col'>";
                        echo "<p class='content-text' style='font-size: 18px;'>Kenmerken</p>";
                        if ($huisje['bath_present'] == 1)
                            echo "<li><span>Ligbad <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['pool_present'] == 1)
                            echo "<li><span>Zwembad <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['bbq_present'] == 1)
                            echo "<li><span>Barbecue <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['wifi_present'] == 1)
                            echo "<li><span>Wifi <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['fireplace_present'] == 1)
                            echo "<li><span>Openhaard <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['dishwasher_present'] == 1)
                            echo "<li><span>Vaatwasser <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['bike_rental'] == 1)
                            echo "<li><span>Fietsverhuur <i class='fas fa-check' style='color: green;'></i></span></li>";

                        if ($huisje['max_capacity'] > 0) {
                            $caps = $huisje['max_capacity'];
                            echo "<li>Max. " . $caps . " personen</li>";
                        }
                        echo "</div>";
                        echo "<div class='col'>";
                        echo "<p class='content-text' style='font-size: 18px;'>PP nacht:</p>";
                        echo "<p class='content-text text'>&euro;" . $huisje['price_p_p_p_n'] . "</p>";

                        echo "<p class='content-text' style='font-size: 18px;'>Beddengoed:</p>";
                        echo "<p class='content-text text'>&euro;" . $huisje['price_bed_sheets'] . "</p>";

                        echo "<p class='content-text' style='font-size: 18px;'>Fietsverhuur:</p>";
                        echo "<p class='content-text text'>&euro;" . $huisje['price_bike_rental'] . "</p>";
                        echo "</div>";
                        echo "</div>";


                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p class='content-text error'>Geen resultaten voor filter!</p>";
                }
                ?>


                <div style="padding: 100px 0;">
                    <p class="content-text smallsub">Filter</p>
                    <form method="POST" style="padding: 5px;">
                        <p class="content-text input-title">Wat wilt u erbij?</p>
                        <label>Ligbad</label>
                        <input type="radio" name="filter_faciliteiten" value="bath">
                        <label>Zwembad</label>
                        <input type="radio" name="filter_faciliteiten" value="pool">

                        <label>BBQ</label>
                        <input type="radio" name="filter_faciliteiten" value="bbq">

                        <label>WiFi</label>
                        <input type="radio" name="filter_faciliteiten" value="wifi">

                        <label>Openhaard</label>
                        <input type="radio" name="filter_faciliteiten" value="fireplace">

                        <label>Vaatwasser</label>
                        <input type="radio" name="filter_faciliteiten" value="dishwasher">

                        <label>Fietsverhuur</label>
                        <input type="radio" name="filter_faciliteiten" value="bike_rental">

                        <a href="index.php">Reset Filters</a>
                        <button class="content-text input-button" type="submit">Filter</button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div id="mapid"></div>
        </div>

        <div style="background-color: white;">
            <div class="wrapper content">
                <p class="content-text subtitle">Reserveren</p>
                <p class="content-text text">Reserveer het perfecte vakantiehuisje!</p>
                <div style="margin: 5px 0; border-top: 2px gray solid;">
                    <?php
                    if ($error != null) {
                        echo "<p class='content-text error'><strong>⚠</strong> $error</p>";
                    }
                    ?>
                    <form method="POST">
                        <div class="flex-grid" style="margin-top: 5px;">
                            <div class="col">
                                <p class="content-text input-title">Vakantiehuis:</p>
                                <select class="content-text input" name="huis">
                                    <option value="1">IJmuiden Cottage</option>
                                    <option value="2">Assen Bungalow</option>
                                    <option value="3">Espelo Entree</option>
                                    <option value="4">Weustenrade Woning</option>
                                </select>

                                <p class="content-text input-title">Aantal personen:</p>
                                <input class="content-text input" type="number" name="personen" required>

                                <p class="content-text input-title">Aantal dagen:</p>
                                <input class="content-text input" type="number" name="dagen" required>
                            </div>
                            <div class="col">
                                <div style="padding: 40px;">
                                    <p class="content-text smallsub">Extra opties</p>
                                    <p class="content-text input-title">Beddengoed?</p>
                                    <label>Ja</label>
                                    <input type="radio" name="beddengoed" value="true">
                                    <label>Nee</label>
                                    <input type="radio" name="beddengoed" value="false" checked>

                                    <p class="content-text input-title">Fietsen nodig?</p>
                                    <label>Ja</label>
                                    <input type="radio" name="fietsverhuur" value="true">
                                    <label>Nee</label>
                                    <input type="radio" name="fietsverhuur" value="false" checked>
                                </div>
                            </div>
                        </div>

                        <button class="content-text input-button" name="reserveren" type="submit">Reserveer</button>
                    </form>

                    <div class="currentBooking">
                        <div class="content-text text">Totale prijs:<span class="totalPrice">
                                <?php
                                echo ($berekening . ($berekening <= 0 ? "€0.0" : "") . $totale_bedrag);
                                ?>
                            </span></div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="wrapper">
                <p>© Copyright Quattro Rentals BV.</p>
            </div>
        </footer>
    </div>
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

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <script src="js/place_markers.js"></script>
</body>

</html>