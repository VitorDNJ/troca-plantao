<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Gera códigos únicos no padrão PREFIXO-ANO-000001, com contador atômico por
 * prefixo/ano armazenado na própria tabela (evita colisão sob concorrência).
 */
class CodigoService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function gerar(string $prefixo): string
    {
        $ano = date('Y');
        $chaveConfig = "contador_{$prefixo}_{$ano}";

        // Pode ser chamado isoladamente ou já dentro da transação de um service
        // maior (criação de solicitação, exceção, etc.). Só abre/fecha transação
        // própria quando ainda não há uma ativa — PDO não suporta aninhamento.
        $transacaoPropria = !$this->db->inTransaction();
        if ($transacaoPropria) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare("SELECT valor FROM configuracoes WHERE chave = :c FOR UPDATE");
            $stmt->execute(['c' => $chaveConfig]);
            $atual = $stmt->fetchColumn();

            if ($atual === false) {
                $proximo = 1;
                $ins = $this->db->prepare("INSERT INTO configuracoes (chave, valor, descricao) VALUES (:c, :v, :d)");
                $ins->execute(['c' => $chaveConfig, 'v' => (string)$proximo, 'd' => "Contador sequencial de {$prefixo} em {$ano}"]);
            } else {
                $proximo = (int)$atual + 1;
                $upd = $this->db->prepare("UPDATE configuracoes SET valor = :v WHERE chave = :c");
                $upd->execute(['v' => (string)$proximo, 'c' => $chaveConfig]);
            }

            if ($transacaoPropria) {
                $this->db->commit();
            }
        } catch (\Throwable $e) {
            if ($transacaoPropria && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return sprintf('%s-%s-%06d', $prefixo, $ano, $proximo);
    }
}
