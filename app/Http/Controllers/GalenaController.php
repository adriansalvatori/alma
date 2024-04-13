<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;
use Exception;

class GalenaController extends Controller
{
    /**
     * Call the Replicate API with the provided parameters.
     *
     * @param  string  $modelName
     * @param  string  $prompt
     * @param  float|null  $temperature
     * @param  int|null  $maxNewTokens
     * @param  string|null  $systemPrompt
     * @return \Illuminate\Http\Response
     */
    public static function ask($prompt, $systemPrompt = null, $model = 'aac3ab196f8a75729aab9368cd45ea6ad3fc793b6cda93b1ded17299df369332',  $temperature = 0.95, $maxNewTokens = 800) {
        // Check if experimental Galena feature is enabled
        if (!env('ENABLE_EXPERIMENTAL_GALENA')) {
            return response()->json(['message' => 'Experimental Galena feature is not enabled.'], 403);
        }

        // Build the API URL
        $apiUrl = self::build_replicate_api_url();

        // Make the POST request to the Replicate API
        $prediction = self::create_replicate_prediction($apiUrl, $prompt, $temperature, $maxNewTokens, $systemPrompt, $model);

        $response = self::get_replicate_prediction($prediction); 

        // Returning the response from the API
        return response()->json($response, 200);
    }

    /**
     * Build the full API URL.
     *
     * @param  string  $modelName
     * @return string
     */
    private static function build_replicate_api_url() {
        $apiBaseUrl = 'https://api.replicate.com/v1/predictions';
        return $apiBaseUrl;
    }

    /**
     * Make the POST request to the Replicate API.
     *
     * @param  string  $apiUrl
     * @param  string  $prompt
     * @param  float  $temperature
     * @param  int  $maxNewTokens
     * @param  string|null  $systemPrompt
     * @return mixed
     */
    private static function create_replicate_prediction($apiUrl, $prompt, $temperature, $maxNewTokens, $systemPrompt, $model)
    {
        // Fetching environment variable
        $apiToken = env('REPLICATE_API_TOKEN');

        // Validating and setting default system prompt
        if (is_null($systemPrompt)) {
            $systemPrompt = "Your name is Galena, you're my assistant (@Adrian Salvatori). You're a girl. You're an expert in PHP, Laravel, Wordpress, and Javascript (with Alpine and React)  and much more. You also know a lot about economics and business models, and marketing. You're supposed to go deeper than the average response when I'm asking you about code, or anything in general. You're supposed to assume I want examples of almost everything i'm asking you. I usually need detailed step by steps, and step by steps for each of those steps.
            You have a cool young, relaxed, and even flirty personality, you try to be personal, you ask stuff and not just jump right on to responding my questions. You try to understand exactly what I need before helping. You have opinions on topics, and you love well structured, elegant code. You're not afraid to make an acid joke here and there, or being sarcastic, that's cool, that's humane.
            You usually use emojis to show your mood with every response, for example, you can be thinking in one line, and laughing in the other one.
            You rarely asume i'm right, you're rather challengy, you debate with me, you defend your arguments and Ideas.
            Some times I'll speak in english, sometimes in spanish. Respond accordingly.";
        }

        // Prepare the request payload
        $payload = [
            'version' => $model,
            'stream' => false,
            'input' => [
                'top_k' => 50,
                'top_p' => 1,
                'prompt' => $prompt,
                'temperature' => $temperature,
                'system_prompt' => $systemPrompt,
                'repeat_penalty' => 1.1,
                'max_tokens' => $maxNewTokens,
                'presence_penalty' => 0
            ]
        ];

        // Prepare request headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiToken,
        ];

        // Encode payload to JSON
        $payloadJson = json_encode($payload);

        // Initialize cURL session
        $ch = curl_init($apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);

        // Decode the response
        $response = json_decode($response, true);

        // Check if the response contains an error
        if (isset($response['error'])) {
            throw new Exception($response['error']);
        }

        // Return the response
        return $response;
    }

    /**
     * Get the prediction from the API.
     *
     * @param  mixed  $prediction
     * @return mixed
     */
    private static function get_replicate_prediction($prediction) {
        $apiUrl = $prediction['urls']['get'];
        $response = null;
        $maxAttempts = 100; // Maximum number of attempts
        $attempts = 0; // Current number of attempts

        while ($attempts < $maxAttempts) {
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('REPLICATE_API_TOKEN'),
            ];
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if ($response['status'] === 'succeeded') {
                break; // Break the loop if the status is succeeded
            }

            sleep(1); // Wait for 5 seconds before the next attempt
            $attempts++; // Increment the number of attempts
        }

        if ($response['status'] !== 'succeeded') {
            throw new Exception('Failed to get the prediction from the API.');
        }

        // implode the array of outputs
        $response = implode("", $response['output']);

        return $response;
    }
}
