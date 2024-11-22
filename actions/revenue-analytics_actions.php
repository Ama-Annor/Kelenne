<?php
require '../db/database.php';

function getAnalyticsData($currentDay) {
    global $conn;

    // Previous code remains the same for monthly revenue, total services, etc.
    $monthlyQuery = "SELECT SUM(price) as monthly_revenue FROM services";
    $monthlyResult = mysqli_query($conn, $monthlyQuery);
    $monthlyData = mysqli_fetch_assoc($monthlyResult);
    $monthlyRevenue = $monthlyData['monthly_revenue'] ?? 0;

    $dailyRevenue = $monthlyRevenue / $currentDay;

    $servicesQuery = "SELECT COUNT(DISTINCT name) as total_services FROM services";
    $servicesResult = mysqli_query($conn, $servicesQuery);
    $servicesData = mysqli_fetch_assoc($servicesResult);
    $totalServices = $servicesData['total_services'] ?? 0;

    $topServicesQuery = "SELECT 
                            name,
                            SUM(price) as total_price,
                            COUNT(*) as service_count
                        FROM services 
                        GROUP BY name
                        ORDER BY total_price DESC 
                        LIMIT 4";
    $topServicesResult = mysqli_query($conn, $topServicesQuery);
    $topServices = [];
    while ($row = mysqli_fetch_assoc($topServicesResult)) {
        $topServices[] = [
            'name' => $row['name'],
            'price' => $row['total_price']
        ];
    }

    $chartData = getChartData();

    return [
        'monthlyRevenue' => $monthlyRevenue,
        'dailyRevenue' => $dailyRevenue,
        'totalServices' => $totalServices,
        'topServices' => $topServices,
        'chartData' => $chartData
    ];
}

function getChartData($period = 'daily') {
    global $conn;

    // Get services data grouped by day
    $dailyQuery = "SELECT 
                    DATE(created_at) as service_date,
                    SUM(price) as daily_revenue,
                    COUNT(*) as service_count
                   FROM services 
                   WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                   GROUP BY DATE(created_at)
                   ORDER BY service_date";

    $dailyResult = mysqli_query($conn, $dailyQuery);

    $dailyRevenue = array_fill(0, 7, 0); // Initialize 7 days with 0
    $dates = [];

    // Get the last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $dates[6 - $i] = date('Y-m-d', strtotime("-$i days"));
    }

    // Fill in actual revenue data
    while ($row = mysqli_fetch_assoc($dailyResult)) {
        $dayIndex = array_search($row['service_date'], $dates);
        if ($dayIndex !== false) {
            $dailyRevenue[$dayIndex] = floatval($row['daily_revenue']);
        }
    }

    // Get services for pie chart
    $servicesQuery = "SELECT 
                        name,
                        SUM(price) as total_price,
                        COUNT(*) as service_count
                    FROM services 
                    GROUP BY name
                    ORDER BY total_price DESC";
    $servicesResult = mysqli_query($conn, $servicesQuery);

    $serviceLabels = [];
    $serviceValues = [];

    while ($row = mysqli_fetch_assoc($servicesResult)) {
        $serviceLabels[] = $row['name'];
        $serviceValues[] = $row['total_price'];
    }

    // If this is an AJAX request for chart data
    if (isset($_GET['action']) && $_GET['action'] === 'getChartData') {
        $periodData = [];
        switch($_GET['period']) {
            case 'daily':
                $periodData = [
                    'labels' => array_map(function($date) {
                        return date('D', strtotime($date));
                    }, $dates),
                    'values' => $dailyRevenue
                ];
                break;
            case 'weekly':
                // Calculate weekly totals from daily data
                $weeklyRevenue = array_chunk($dailyRevenue, 7);
                $periodData = [
                    'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    'values' => array_map('array_sum', $weeklyRevenue)
                ];
                break;
            case 'monthly':
                // Calculate monthly projection from daily average
                $dailyAverage = array_sum($dailyRevenue) / count(array_filter($dailyRevenue));
                $monthlyProjection = $dailyAverage * 30;
                $periodData = [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'values' => array_fill(0, 6, $monthlyProjection)
                ];
                break;
        }
        header('Content-Type: application/json');
        echo json_encode($periodData);
        exit;
    }

    return [
        'serviceLabels' => $serviceLabels,
        'serviceValues' => $serviceValues,
        'dailyRevenue' => $dailyRevenue,
        'dailyLabels' => array_map(function($date) {
            return date('D', strtotime($date));
        }, $dates)
    ];
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'getChartData':
            getChartData($_GET['period']);
            break;
    }
}
?>