<?php

/**
 * Output a string to the command line
 */
function output(string $message, string $type = "error")
{
    switch ($type) {
        case 'error':
            $color = 31;
            break;
        case 'success':
            $color = 32;
            break;
        case 'warning':
            $color = 33;
            break;  
        case 'info':
            $color = 36;
            break;      
        default:
            $color = 97;
    }

    print("\033[{$color}m" . PHP_EOL . date("H:i:s") . " - $message \033[0m" . PHP_EOL);

    if ($type == "error") {
        exit();
    }
}

/**
 * Get page content via curl
 */
function get_page(string $url) : string
{
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FAILONERROR => true,
        CURLOPT_TIMEOUT => 30
    ];
    
    $handler = curl_init();
    curl_setopt_array($handler, $options);
    $output = curl_exec($handler);

    if (curl_errno($handler)) {
        output(curl_error($handler), 'error');
    }

    curl_close($handler);

    if (!$output) {
        output('No content found', 'error');
    }
    
    return $output;
}

/**
 * 
 */
function init_db(string $hostname = null, string $username = null, 
    string $password = null, string $database = null, string $table = null) 
    : mysqli
{
    $hostname ??= "localhost";
    $username ??= "root";
    $password ??= "";
    $database ??= "test";
    $table ??= "vehicles";
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $db = new mysqli($hostname, $username, $password, $database);
        
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
                `sku` varchar(15) DEFAULT NULL,
                `vin` varchar(17) DEFAULT NULL,
                `make` varchar(50) DEFAULT NULL,
                `model` varchar(50) DEFAULT NULL,
                `mileage` int(11) unsigned DEFAULT NULL,
                `price` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        $db->query($sql);
    } catch (Exception $e) {
        output($e->getMessage(), 'error');
    }
    
    return $db;
}
