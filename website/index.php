<html>
    <head>
    	<!-- Site Titel -->
        <title>About | Milo's Website</title>

        <!-- Hier wordt de main css file geimport die over de gehele website van kracht is. -->
        <link href="http://localhost/milo/css/main.css" rel="stylesheet">

        <!-- Hier is nog wat basis style voor deze pagina. -->
        <style>
            .info {
                margin: auto;
                margin-top: 50px;
                padding: 18px;
                border-radius: 12px;
                border: 2px solid #99aab5;
                text-align: center;
                color: #23272a;
                width: 70%;
                background-color: white;
            }

            /* Media queries helpen er bij om de website responsive te maken voor andere apparaten.
            Hier worden bijvoorbeeld bepaalde onderdelen van de website vergroot op kleinere scherm verhoudingen. */
            @media screen and (max-width: 800px) {
                .card {
                    width: 95%;
                }
                
                .info {
                    width: 90%;
                }
            }
        </style>
    </head>
    <body>
    	<!-- Hier wordt de navigatie balk doormiddel van PHP op de pagina gezet. -->
        <?php
            include("includes/navbar.php");
        ?>

        <!-- De page-wrapper is eigenlijk de basis van de pagina, en zorgt ervoor dat alles mooi geoutlined is. 
        	De container is de div waar alle pagina content in zit, met in dit geval dus de info div waarin wat informatie staat. -->
        <div class="page-wrapper">
            <div class="container">
            <div class="info">
                    <h1>Miep's BnB</h1>
                </div>
        </div>
    </body>
</html>