<?php
/**
 * Database Connectie - Aurora Theater
 * 
 * Dit bestand zorgt voor de verbinding met de MySQL database via MySQLi.
 * Het bevat automatische host/poort detectie voor WAMP/localhost.
 */

// Schakel mysqli foutrapportage uitzonderingen uit om nette foutafhandeling mogelijk te maken
mysqli_report(MYSQLI_REPORT_OFF);

// Database configuratie parameters
$db_user = 'root';
$db_pass = '';
$db_name = 'aurora_theater';

// Lijst van mogelijke hosts en poorten om automatisch te proberen op WAMP/localhost
$possible_hosts = [
    'localhost',
    '127.0.0.1',
    '127.0.0.1:3308', // WAMP MySQL/MariaDB alternatieve poort
    'localhost:3308',
    '127.0.0.1:3306',
    'localhost:3306',
    '127.0.0.1:3307',
    'localhost:3307'
];

$conn = null;
$connected = false;
$db_host = 'localhost';

// Probeer direct verbinding te maken met de database op een van de hosts
foreach ($possible_hosts as $host) {
    // We geven direct $db_name mee. Als de database is verwijderd of niet bestaat, mislukt de verbinding.
    $conn = @new mysqli($host, $db_user, $db_pass, $db_name);
    if ($conn && !$conn->connect_error) {
        $connected = true;
        $db_host = $host;
        break;
    }
}

// Foutcontrole: als de verbinding is mislukt (bijvoorbeeld omdat de database is verwijderd of MySQL uit staat)
if (!$connected || !$conn || $conn->connect_error) {
    // Nette foutmelding tonen aan de gebruiker (Unhappy Scenario)
    die("<!DOCTYPE html>
    <html lang='nl'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Fout - Aurora Theater</title>
        <style>
            body {
                background-color: #050505;
                color: #ffffff;
                font-family: 'Segoe UI', Arial, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .error-container {
                background-color: #121212;
                border: 1px solid #ff3b4f;
                padding: 40px;
                border-radius: 12px;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 4px 20px rgba(255, 59, 79, 0.15);
            }
            h1 {
                color: #ff3b4f;
                margin-top: 0;
                font-size: 28px;
            }
            p {
                color: #aaa;
                line-height: 1.6;
                margin-bottom: 25px;
            }
            .retry-btn {
                background-color: #ff3b4f;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 30px;
                cursor: pointer;
                font-weight: bold;
                text-decoration: none;
                transition: background 0.3s;
            }
            .retry-btn:hover {
                background-color: #e01a4f;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <h1>Verbindingsfout</h1>
            <p>Er kon geen verbinding worden gemaakt met de database van Aurora Theater.<br><br>
               Zorg ervoor dat <strong>WAMP/MAMP</strong> actief is en dat de database <strong>aurora_theater</strong> is geïmporteerd via het meegeleverde <code>database/schema.sql</code> bestand.</p>
            <a href='#' onclick='window.location.reload();' class='retry-btn'>Probeer opnieuw</a>
        </div>
    </body>
    </html>");
}

// Zet de karakterset op UTF-8 voor correcte weergave van speciale tekens
$conn->set_charset("utf8mb4");

// De variabele $conn is nu gereed voor gebruik in andere scripts.
