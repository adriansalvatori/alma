<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;
use Illuminate\Support\Facades\Http;

class WeatherTool
{
    public function definition()
    {
        return Tool::as('weather')
            ->for('Get current weather information for a city')
            ->withStringParameter('city', 'The name of the city to check the weather for')
            ->using(function (string $city) {
                $cityCoordinates = [
                    'London' => ['lat' => 51.5074, 'lon' => -0.1278],
                    'New York' => ['lat' => 40.7128, 'lon' => -74.0060],
                    'Tokyo' => ['lat' => 35.6762, 'lon' => 139.6503],
                    'Paris' => ['lat' => 48.8566, 'lon' => 2.3522],
                    'Sydney' => ['lat' => -33.8688, 'lon' => 151.2093],
                ];

                if (!array_key_exists($city, $cityCoordinates)) {
                    Log::debug("Weather data for $city is not available.");
                    return "Sorry, weather data for $city is not available.";
                }

                $coords = $cityCoordinates[$city];
                Log::debug("Requesting weather data for $city: lat: {$coords['lat']}, lon: {$coords['lon']}");

                $response = Http::get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lon'],
                    'current_weather' => true,
                ]);

                $data = $response->json();
                Log::debug("Response for $city: " . json_encode($data));

                if (isset($data['current_weather'])) {
                    $condition = $this->weatherCodeToDescription($data['current_weather']['weathercode']);
                    Log::debug("Weather data for $city: {$data['current_weather']['temperature']}°C, $condition, Wind: {$data['current_weather']['windspeed']} m/s from {$data['current_weather']['winddirection']}°, Time: {$data['current_weather']['time']}");
                    return "Current weather in $city: {$data['current_weather']['temperature']}°C, $condition, Wind: {$data['current_weather']['windspeed']} m/s from {$data['current_weather']['winddirection']}°, Time: {$data['current_weather']['time']}";
                }
                Log::debug("Error fetching weather data for $city: " . ($response->failed() ? $response->status() : 'No data'));
                return "Error fetching weather data for $city: " . ($response->failed() ? $response->status() : 'No data');
            });
    }

    private function weatherCodeToDescription($code)
    {
        $conditions = [
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            61 => 'Light rain',
            63 => 'Moderate rain',
        ];
        return $conditions[$code] ?? 'Unknown condition';
    }
}
