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
            ->for('Get current weather information for a city. You will need to provide the coordinates of that city, you will not ask the user for these coordinates, you will search in your own knowledge.')
            ->withStringParameter('city', 'the name of the city', true)
            ->withStringParameter('latitude', 'the latitude coordinate of the city', true)
            ->withStringParameter('longitude', 'the longitude coordinate of the city', true)
            ->using(function (string $city, string $latitude, string $longitude) {
                // Fetch coordinates for the city using Nominatim API
                $lat = $latitude;
                $lon = $longitude;
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
                    $result = [
                        'type' => 'weather',
                        'data' => [
                            'city' => $city,
                            'temperature' => $data['current_weather']['temperature'],
                            'condition' => $condition,
                            'windspeed' => $data['current_weather']['windspeed'],
                            'winddirection' => $data['current_weather']['winddirection'],
                            'time' => $data['current_weather']['time'],
                        ],
                        'view' => 'livewire.tools.weather',
                    ];
                    Log::debug("Weather data for $city: " . json_encode($result));
                    return json_encode($result);
                }

                Log::debug("Error fetching weather data for $city: " . ($weatherResponse->failed() ? $weatherResponse->status() : 'No data'));
                return json_encode(['type' => 'error', 'data' => ['message' => "Error fetching weather data for $city: " . ($weatherResponse->failed() ? $weatherResponse->status() : 'No data')], 'view' => 'livewire.tools.error']);
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