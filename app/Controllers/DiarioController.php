<?php
// app/Controllers/DiarioController.php
namespace App\Controllers;

use App\Services\DiarioIndexerService;

class DiarioController extends BaseController
{
    private DiarioIndexerService $indexSvc;

    public function __construct()
    {
        $this->indexSvc = new DiarioIndexerService();
    }

    public function index()
    {
      return view('diario/busca');
    }

    public function buscar()
    {
        $termo = trim($this->request->getGet('termo') ?? '');

        if (strlen($termo) < 3) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Digite ao menos 3 caracteres.',
            ]);
        }

        $resultados = $this->indexSvc->buscar($termo);

        $formatados = array_map(function ($r) use ($termo) {
            return [
                'edicao'       => $r['edition'],
                'tipo'         => $r['edition_type'],
                'data'         => date('d/m/Y', strtotime($r['published_at'])),
                'pagina'       => $r['pagina'],
                'pdf_url'      => $r['pdf_url'],
                'trecho'       => $this->destacarTermo($r['texto'], $termo),
            ];
        }, $resultados);

        return $this->response->setJSON([
            'success'    => true,
            'total'      => count($formatados),
            'resultados' => $formatados,
        ]);
    }

    private function destacarTermo(string $texto, string $termo): string
    {
        $pos = stripos($texto, $termo);
        if ($pos === false) return '';

        $inicio  = max(0, $pos - 120);
        $trecho  = substr($texto, $inicio, 300);
        $trecho  = htmlspecialchars($trecho);
        $trecho  = preg_replace('/(' . preg_quote(htmlspecialchars($termo), '/') . ')/i', '<mark>$1</mark>', $trecho);
        return ($inicio > 0 ? '...' : '') . $trecho . '...';
    }
}