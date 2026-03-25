<?php
// app/Commands/IndexarDiarios.php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\DiarioOficialService;
use App\Services\DiarioIndexerService;

class IndexarDiarios extends BaseCommand
{
    protected $group       = 'Diario';
    protected $name        = 'diario:indexar';
    protected $description = 'Indexa os diários oficiais dos últimos 2 dias';

    public function run(array $params)
    {
        $dias     = $params[0] ?? 2;
        $apiSvc   = new DiarioOficialService();
        $indexSvc = new DiarioIndexerService();

        CLI::write("Buscando diários dos últimos {$dias} dias...", 'yellow');
        $diarios = $apiSvc->listarRecentes((int) $dias);
        $total   = count($diarios);

        CLI::write("Encontrados: {$total} diários.", 'green');

        $ok = $erro = 0;
        foreach ($diarios as $i => $diario) {
            $num = $i + 1;
            CLI::showProgress($num, $total);
            CLI::write(" Indexando edição {$diario['edition']} ({$diario['editionType']})...");

            if ($indexSvc->indexar($diario)) {
                $ok++;
            } else {
                $erro++;
                CLI::write("  ERRO na edição {$diario['edition']}", 'red');
            }
        }

        CLI::write("\nConcluído! Sucesso: {$ok} | Erros: {$erro}", 'green');
    }
}