<?php
require_once __DIR__ . '/../helpers/EnvLoader.php';

class SentimentService
{
    private $model = "tabularisai/multilingual-sentiment-analysis";
    private $apiToken; 

    public function __construct() {
        EnvLoader::load(__DIR__ . '/../../.env');
        $this->apiToken = getenv('HF_API_TOKEN');
    }

    public function analyze($text)
    {
        if (empty(trim($text))) return 'Neutral';

        $response = $this->callInferenceAPI($text);

        if (isset($response['error'])) {
            error_log("Sentiment API Error: " . $response['error']);
            return 'Neutral';
        }

        // Multilingual model result structure: [[{"label": "...", "score": ...}, ...]]
        if ($response && is_array($response) && isset($response[0]) && is_array($response[0])) {
            $bestLabel = '';
            $bestScore = -1;

            foreach ($response[0] as $prediction) {
                if ($prediction['score'] > $bestScore) {
                    $bestScore = $prediction['score'];
                    $bestLabel = $prediction['label'];
                }
            }

            return $this->mapLabel($bestLabel);
        }

        return 'Neutral';
    }

    private function mapLabel($label)
    {
        $label = trim(strtolower($label));
        // tabularisai model uses descriptive labels (positive, negative, etc.) 
        // or LABEL_X if not mapped in config, so we handle both
        if (strpos($label, 'pos') !== false || $label === 'label_2') return 'Positive';
        if (strpos($label, 'neg') !== false || $label === 'label_0') return 'Negative';
        return 'Neutral';
    }

    private function callInferenceAPI($text)
    {
        $payload = json_encode([
            "inputs" => $text,
            "options" => ["wait_for_model" => true]
        ]);
        
        // VERIFIED 2025 ENDPOINT for Multilingual Model
        $url = "https://router.huggingface.co/hf-inference/models/" . $this->model;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->apiToken,
            "X-Wait-For-Model: true",
            "X-Use-Cache: true"
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Sentiment API Hub Error ($httpCode): " . $result);
            return ["error" => "Inference Error $httpCode"];
        }

        return json_decode($result, true);
    }

    public function train($text, $label) {
        return true; 
    }
}
