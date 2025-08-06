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
                // Fetch coordinates for the city using Nominatim API
                $response = Http::get('https://nominatim.openstreetmap.org/search', [
                    'q' => $city,
                    'format' => 'json',
                    'limit' => 1,
                ]);

                $geoData = $response->json();
                Log::debug("Geocoding response for $city: " . json_encode($geoData));

                if (empty($geoData) || !isset($geoData[0]['lat'], $geoData[0]['lon'])) {
                    Log::debug("Coordinates for $city not found.");
                    return "Sorry, coordinates for $city could not be found.";
                }

                $lat = $geoData[0]['lat'];
                $lon = $geoData[0]['lon'];
                Log::debug("Coordinates for $city: lat: $lat, lon: $lon");

                // Fetch weather data using Open-Meteo API
                $weatherResponse = Http::get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current_weather' => true,
                ]);

                $data = $weatherResponse->json();
                Log::debug("Weather response for $city: " . json_encode($data));

                if (isset($data['current_weather'])) {
                    $condition = $this->weatherCodeToDescription($data['current_weather']['weathercode']);
                    Log::debug("Weather data for $city: {$data['current_weather']['temperature']}°C, $condition, Wind: {$data['current_weather']['windspeed']} m/s from {$data['current_weather']['winddirection']}°, Time: {$data['current_weather']['time']}");
                    return "Current weather in $city: {$data['current_weather']['temperature']}°C, $condition, Wind: {$data['current_weather']['windspeed']} m/s from {$data['current_weather']['winddirection']}°, Time: {$data['current_weather']['time']}";
                }

                Log::debug("Error fetching weather data for $city: " . ($weatherResponse->failed() ? $weatherResponse->status() : 'No data'));
                return "Error fetching weather data for $city: " . ($weatherResponse->failed() ? $weatherResponse->status() : 'No data');
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