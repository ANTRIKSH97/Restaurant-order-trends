<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    // ✅ Get all restaurants
    public function getRestaurants() {
        $data = json_decode(Storage::get('restaurants.json'), true);
        return response()->json($data);
    }

    // ✅ Get all orders
    public function getOrders() {
        $data = json_decode(Storage::get('orders.json'), true);
        return response()->json($data);
    }

    // ✅ Get trends for a specific restaurant
    public function getRestaurantTrends($id, Request $request) {
        $orders = json_decode(Storage::get('orders.json'), true);
        
        $from = $request->query('from', '2025-08-01');
        $to = $request->query('to', '2025-08-31');

        $filtered = array_filter($orders, function($order) use ($id, $from, $to) {
            return $order['restaurant_id'] == $id &&
                   $order['date'] >= $from &&
                   $order['date'] <= $to;
        });

        $dailyOrders = [];
        foreach($filtered as $order) {
            $date = $order['date'];
            if (!isset($dailyOrders[$date])) {
                $dailyOrders[$date] = ['count' => 0, 'revenue' => 0, 'hours' => []];
            }
            $dailyOrders[$date]['count']++;
            $dailyOrders[$date]['revenue'] += $order['amount'];
            $hour = date('H', strtotime($order['time']));
            $dailyOrders[$date]['hours'][] = $hour;
        }

        $result = [];
        foreach($dailyOrders as $date => $data) {
            $peakHour = array_count_values($data['hours']);
            arsort($peakHour);
            $peak = array_key_first($peakHour);

            $result[] = [
                'date' => $date,
                'orders' => $data['count'],
                'revenue' => $data['revenue'],
                'avgOrderValue' => round($data['revenue'] / $data['count'], 2),
                'peakHour' => $peak
            ];
        }

        return response()->json(array_values($result));
    }

    // ✅ Get top 3 restaurants by revenue
    public function getTopRestaurants(Request $request) {
        $orders = json_decode(Storage::get('orders.json'), true);
        $from = $request->query('from', '2025-08-01');
        $to = $request->query('to', '2025-08-31');

        $filtered = array_filter($orders, fn($order) => $order['date'] >= $from && $order['date'] <= $to);

        $revenueByRestaurant = [];
        foreach($filtered as $order) {
            if (!isset($revenueByRestaurant[$order['restaurant_id']])) {
                $revenueByRestaurant[$order['restaurant_id']] = 0;
            }
            $revenueByRestaurant[$order['restaurant_id']] += $order['amount'];
        }

        arsort($revenueByRestaurant);
        return response()->json(array_slice($revenueByRestaurant, 0, 3, true));
    }
}
