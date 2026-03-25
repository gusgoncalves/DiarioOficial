<?php
// app/Services/DiarioIndexerService.php
namespace App\Services;

use Smalot\PdfParser\Parser;

class DiarioIndexerService
{
    private DiarioOficialService $apiService;
    private Parser               $parser;
    private int                  $timeout = 120;

    public function __construct()
    {
        $this->apiService = new DiarioOficialService();
        $this->parser     = new Parser();
    }

    public function indexar(array $diario): bool
    {
        $db = \Config\Database::connect();

        $urlPdf = $this->apiService->obterUrlPdf($diario['id']);
        if (!$urlPdf) return false;

        $db->table('diarios_oficiais')->upsert([
            'id'           => $diario['id'],
            'edition'      => $diario['edition'],
            'edition_type' => $diario['editionType'],
            'published_at' => substr($diario['publishedAt'], 0, 10),
            'file_size'    => $diario['fileSize'] ?? 0,
            'pdf_url'      => $urlPdf,
            'indexado'     => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $conteudo = $this->baixarPdf($urlPdf);
        if (!$conteudo) return false;

        try {
            $pdf    = $this->parser->parseContent($conteudo);
            $paginas = $pdf->getPages();

            foreach ($paginas as $numPagina => $pagina) {
                $texto = $pagina->getText();
                if (empty(trim($texto))) continue;

                $db->table('diarios_oficiais_paginas')->upsert([
                    'diario_id' => $diario['id'],
                    'pagina'    => $numPagina + 1,
                    'texto'     => $texto,
                ]);
                unset($texto);
                unset($pagina);
            }
            unset($pdf, $paginas);
            gc_collect_cycles();

            $db->table('diarios_oficiais')
               ->where('id', $diario['id'])
               ->update(['indexado' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

            return true;
        } catch (\Exception $e) {
            log_message('error', "Erro ao indexar diário {$diario['id']}: " . $e->getMessage());
            return false;
        }
    }

    public function buscar(string $termo, int $limite = 50): array
    {
        $db = \Config\Database::connect();

        return $db->table('diarios_oficiais_paginas p')
            ->select('d.id, d.edition, d.edition_type, d.published_at, d.pdf_url, p.pagina, p.texto')
            ->join('diarios_oficiais d', 'd.id = p.diario_id')
            ->like('p.texto', $termo, 'both')
            ->orderBy('d.published_at', 'DESC')
            ->limit($limite)
            ->get()
            ->getResultArray();
    }

    private function baixarPdf(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code === 200 && $body) ? $body : null;
    }
}