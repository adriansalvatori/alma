<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;

class ChartTool
{
    public function definition()
    {
        return Tool::as('chart')
            ->for('Generate a chart image for given data')
            ->withStringParameter('type', 'The type of chart (e.g., bar, line)', 'bar')
            ->withStringParameter('labels', 'Comma-separated list of labels for the chart (e.g., 2019,2020,2021)')
            ->withStringParameter('dataset_label', 'Label for the dataset (e.g., Users)', 'Data')
            ->withStringParameter('data', 'Comma-separated list of data values (e.g., 120,60,50)')
            ->withStringParameter('size', 'The size of the chart image (e.g., 500x300)', '500x300')
            ->using(function (string $type = 'bar', string $labels, string $dataset_label = 'Data', string $data, string $size = '500x300') {
                // Validate inputs
                $labelsArray = array_map('trim', explode(',', $labels));
                $dataArray = array_map('trim', explode(',', $data));

                if (empty($labelsArray) || empty($dataArray)) {
                    Log::debug("Invalid labels or data provided for chart: labels=$labels, data=$data");
                    return json_encode(['type' => 'error', 'data' => ['message' => 'Labels and data must not be empty.'], 'view' => 'livewire.tools.error']);
                }

                if (count($labelsArray) !== count($dataArray)) {
                    Log::debug("Mismatched labels and data counts: labels=" . count($labelsArray) . ", data=" . count($dataArray));
                    return json_encode(['type' => 'error', 'data' => ['message' => 'The number of labels must match the number of data points.'], 'view' => 'livewire.tools.error']);
                }

                // Validate data contains only numbers
                foreach ($dataArray as $value) {
                    if (!is_numeric($value)) {
                        Log::debug("Invalid data value for chart: $value");
                        return json_encode(['type' => 'error', 'data' => ['message' => 'Data values must be numeric.'], 'view' => 'livewire.tools.error']);
                    }
                }

                // Validate size format (e.g., 500x300)
                if (!preg_match('/^\d+x\d+$/', $size)) {
                    Log::debug("Invalid size format for chart: $size");
                    return json_encode(['type' => 'error', 'data' => ['message' => 'Invalid size format. Use format like "500x300".'], 'view' => 'livewire.tools.error']);
                }

                // Construct chart configuration
                $chartConfig = [
                    'type' => $type,
                    'data' => [
                        'labels' => $labelsArray,
                        'datasets' => [
                            [
                                'label' => $dataset_label,
                                'data' => array_map('floatval', $dataArray),
                            ],
                        ],
                    ],
                ];

                // Encode chart config as JSON
                $chartConfigJson = json_encode($chartConfig);
                if ($chartConfigJson === false) {
                    Log::debug("Failed to encode chart configuration: " . json_last_error_msg());
                    return json_encode(['type' => 'error', 'data' => ['message' => 'Failed to encode chart configuration.'], 'view' => 'livewire.tools.error']);
                }

                // Construct QuickChart API URL
                $apiUrl = "https://quickchart.io/chart";
                $queryParams = [
                    'c' => $chartConfigJson,
                    'width' => explode('x', $size)[0],
                    'height' => explode('x', $size)[1],
                ];

                $chartUrl = $apiUrl . '?' . http_build_query($queryParams);
                Log::debug("Generated chart URL for type=$type, labels=$labels, data=$data, size=$size: $chartUrl");

                // Return structured JSON with view
                return json_encode([
                    'type' => 'chart',
                    'data' => [
                        'dataset_label' => $dataset_label,
                        'image_url' => $chartUrl,
                        'size' => $size,
                    ],
                    'view' => 'livewire.tools.chart',
                ]);
            });
    }
}