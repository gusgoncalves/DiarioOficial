<?php
// app/Services/DiarioOficialService.php
namespace App\Services;

class DiarioOficialService
{
    private string $baseUrl    = 'https://servicos.pontagrossa.pr.gov.br/portaltransparencia-api/api/legislacao/diarios-oficiais';
    private int    $timeout    = 30;

    public function listarRecentes(int $dias = 30): array
    {
        $pagina   = 0;
        $tamanho  = 50;
        $todos    = [];
        $corte    = date('Y-m-d', strtotime("-{$dias} days"));

        do {
            $url  = "{$this->baseUrl}/publicados?sort=edition%2Cdesc&page={$pagina}&size={$tamanho}";
            $resp = $this->get($url);

            if (empty($resp['content'])) break;

            foreach ($resp['content'] as $item) {
                $data = substr($item['publishedAt'], 0, 10);
                if ($data < $corte) return $todos;
                $todos[] = $item;
            }

            $pagina++;
        } while ($pagina < ($resp['totalPages'] ?? 1));

        return $todos;
    }

    public function obterUrlPdf(string $id): ?string
    {
        $url  = "{$this->baseUrl}/url-download/{$id}";
        $resp = $this->get($url, raw: true);
        return $resp ?: null;
    }

    private function get(string $url, bool $raw = false): mixed
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || $body === false) return $raw ? null : [];
        return $raw ? $body : (json_decode($body, true) ?? []);
    }
}