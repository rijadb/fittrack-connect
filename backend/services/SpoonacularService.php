<?php
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/ApiResponse.php';

class SpoonacularService
{
    public function __construct(private string $apiKey)
    {
    }

    public function search(string $query): array
    {
        $query = trim($query);
        if (strlen($query) < 2 || strlen($query) > 80) {
            throw new ApiException('Search term must contain 2 to 80 characters.');
        }
        $response = $this->request('/food/ingredients/search', ['query'=>$query,'number'=>10]);
        $foods = [];
        foreach (($response['results'] ?? []) as $item) {
            if (!isset($item['id'], $item['name'])) continue;
            $foods[] = ['id'=>(int)$item['id'], 'name'=>(string)$item['name'],
                        'image'=>$item['image'] ?? null];
        }
        return ['query'=>$query, 'foods'=>$foods];
    }

    public function nutrition(int $id, float $amount = 100, string $unit = 'grams'): array
    {
        if ($id < 1) throw new ApiException('Invalid ingredient ID.');
        if ($amount <= 0 || $amount > 10000) throw new ApiException('Amount must be greater than zero and at most 10000.');
        if (!preg_match('/^[A-Za-z ]{1,30}$/', $unit)) throw new ApiException('Invalid unit.');
        $response = $this->request("/food/ingredients/{$id}/information", [
            'amount'=>$amount, 'unit'=>$unit,
        ]);
        $values = ['calories_kcal'=>null, 'protein_g'=>null, 'carbs_g'=>null, 'fat_g'=>null];
        $keys = ['calories'=>'calories_kcal', 'protein'=>'protein_g',
                 'carbohydrates'=>'carbs_g', 'fat'=>'fat_g'];
        foreach (($response['nutrition']['nutrients'] ?? []) as $nutrient) {
            $name = strtolower((string)($nutrient['name'] ?? ''));
            if (isset($keys[$name]) && isset($nutrient['amount']) && is_numeric($nutrient['amount'])) {
                $values[$keys[$name]] = (float)$nutrient['amount'];
            }
        }
        return [
            'id'=>(int)($response['id'] ?? $id),
            'name'=>(string)($response['name'] ?? ''),
            'amount'=>$amount, 'unit'=>$unit,
            'nutrition'=>$values,
        ];
    }

    private function request(string $path, array $params): array
    {
        if ($this->apiKey === '' || $this->apiKey === 'YOUR_API_KEY') {
            throw new ApiException('Food search is temporarily unavailable.', 503);
        }
        if (!function_exists('curl_init')) {
            throw new ApiException('Food search is temporarily unavailable.', 503);
        }
        // The API key stays on the server and is never returned in the response.
        $url = 'https://api.spoonacular.com' . $path . '?'
            . http_build_query([...$params, 'apiKey'=>$this->apiKey], '', '&', PHP_QUERY_RFC3986);
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $body = curl_exec($handle);
        $httpCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $errorNumber = curl_errno($handle);
        curl_close($handle);

        if ($body === false || $errorNumber !== 0 || $httpCode < 200 || $httpCode >= 300) {
            $this->logFailure($path, $httpCode, $errorNumber);
            throw new ApiException('Food information is unavailable right now. Please try again later.', 503);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            $this->logFailure($path, $httpCode, 0);
            throw new ApiException('Food information is unavailable right now. Please try again later.', 503);
        }
        return $decoded;
    }

    private function logFailure(string $path, int $httpCode, int $curlError): void
    {
        $directory = __DIR__ . '/../logs';
        if (!is_dir($directory)) @mkdir($directory, 0755, true);
        @error_log(sprintf("[%s] Spoonacular %s HTTP=%d curl=%d\n",
            date('Y-m-d H:i:s'), $path, $httpCode, $curlError),
            3, $directory . '/error.log');
    }
}
