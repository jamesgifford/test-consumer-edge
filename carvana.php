<?php

declare(strict_types = 1);

include_once('helpers.php');

function fetch_carvana_inventory_by_page(int $page_id) : array
{
    // Get the page content
    $page = get_page("https://www.carvana.com/cars?page={$page_id}");

    $page_size = 0;
    if (preg_match('/\"pageSize\"\:([0-9]*)/s', $page, $matches)) {
        $page_size = $matches[1];
    }

    $total_inventory = 0;
    if (preg_match('/\"totalMatchedInventory\"\:([0-9]*)/s', $page, $matches)) {
        $total_inventory = $matches[1];
    }
    
    if ($page_id * $page_size > $total_inventory) {
        output("Maximum page number reached", 'warning');
        return [];
    }

    // Load the DOM for the page
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($page);
    libxml_use_internal_errors(false);
    $path = new DOMXPath($dom);

    // Search the DOM for vehicle JSON data and extract it into an array
    $inventory = [];
    $nodes = $path->query('//script[@type="application/ld+json"]');
    foreach ($nodes as $node) {
        $inventory[] = json_decode($node->textContent, true);
    }
    
    if (count($inventory)) {
        output("Fetched page {$page_id}", 'success');
    }
    else {
        output("No inventory found on page {$page_id}", 'warning');
    }

    return $inventory;
}

function save_carvana_inventory(array $vehicle, mysqli $db) : bool
{
    // Find the price within the vehicle description
    $price = null;
    if (preg_match('/\$([0-9]*)/s', $vehicle['description'], $matches)) {
        $price = $matches[1];
    }

    $statement = $db->prepare("INSERT INTO vehicles (sku, vin, make, model, mileage, price) VALUES (?, ?, ?, ?, ?, ?)");

    $statement->bind_param('ssssss',
        $vehicle['sku'],
        $vehicle['vehicleIdentificationNumber'],
        $vehicle['manufacturer'],
        $vehicle['model'],
        $vehicle['mileageFromOdometer'],
        $price
    );
    
    $statement->execute();
    $result = $statement->affected_rows > 0;
    $statement->close();
    
    return $result;
}


$db = init_db();
$page = isset($argv[1]) ? $argv[1] : 1;

if ($page == 'all') {
    // Fetch and save all pages from Carvana:
    $page = 1;
    do {
        $inventory = fetch_carvana_inventory_by_page($page++);
        foreach ($inventory as $vehicle) {
            save_carvana_inventory($vehicle, $db);
        }
    } while(count($inventory));
}
else {
    // Fetch and save a single page from Carvana
    foreach (fetch_carvana_inventory_by_page((int)$page) as $vehicle) {
        save_carvana_inventory($vehicle, $db);
    }
}

$db->close();
