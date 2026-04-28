<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    private Tenant $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new Tenant();
    }

    /**
     * Resolve o tenant a partir da requisição atual.
     * Estratégias (em ordem):
     *  1. Domínio personalizado (escola.com.br)
     *  2. Subdomínio (escolaabc.edusaas.com.br)
     *  3. Sessão (tenant já resolvido)
     */
    public function resolve(): ?array
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $host = explode(':', $host)[0]; // Remove a porta

        // 1. Tenta por domínio personalizado
        $tenant = $this->tenantModel->findByDomain($host);
        if ($tenant) return $this->hydrate($tenant);

        // 2. Tenta por subdomínio
        $slug = $this->extractSubdomain($host);
        if ($slug && $slug !== 'www') {
            $tenant = $this->tenantModel->findBySlug($slug);
            if ($tenant) return $this->hydrate($tenant);
        }

        // 3. Se não encontrou por URL, usa sessão (já estava logado)
        if (!empty($_SESSION['tenant_id'])) {
            $tenant = $this->tenantModel->findById((int) $_SESSION['tenant_id']);
            if ($tenant && $tenant['ativo']) return $this->hydrate($tenant);
        }

        return null;
    }

    /**
     * Hidrata o tenant com suas configurações visual e acadêmica.
     */
    public function hydrate(array $tenant): array
    {
        $tenant['visual']    = $this->tenantModel->getVisual($tenant['id']) ?? $this->defaultVisual();
        $tenant['academico'] = $this->tenantModel->getAcademico($tenant['id']) ?? $this->defaultAcademico();
        return $tenant;
    }

    /**
     * Injeta o CSS dinâmico do tenant como variáveis CSS.
     */
    public function generateCssVars(array $tenant): string
    {
        $v = $tenant['visual'];
        $primary   = $v['cor_primaria']   ?? '#1e40af';
        $secondary = $v['cor_secundaria'] ?? '#3b82f6';
        $text      = $v['cor_texto']      ?? '#ffffff';

        // Gera cor de hover (10% mais escura) via hex
        $hoverPrimary = $this->darken($primary, 15);

        return ":root {
            --color-primary:        {$primary};
            --color-primary-hover:  {$hoverPrimary};
            --color-secondary:      {$secondary};
            --color-primary-text:   {$text};
        }";
    }

    private function extractSubdomain(string $host): ?string
    {
        // Remove porta, se houver
        $host = explode(':', $host)[0];
        $parts = explode('.', $host);

        // Considera subdomínio se tiver 3+ partes (sub.dominio.com)
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }

    private function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = str_repeat($hex[0], 2) . str_repeat($hex[1], 2) . str_repeat($hex[2], 2);
        }

        $r = max(0, hexdec(substr($hex, 0, 2)) - round(255 * $percent / 100));
        $g = max(0, hexdec(substr($hex, 2, 2)) - round(255 * $percent / 100));
        $b = max(0, hexdec(substr($hex, 4, 2)) - round(255 * $percent / 100));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function defaultVisual(): array
    {
        return [
            'cor_primaria'   => '#1e40af',
            'cor_secundaria' => '#3b82f6',
            'cor_texto'      => '#ffffff',
            'logo_path'      => null,
            'tema_padrao'    => 'claro',
        ];
    }

    private function defaultAcademico(): array
    {
        return [
            'tipo_periodo'               => 'bimestre',
            'qtd_periodos'               => 4,
            'qtd_avaliacoes_por_periodo' => 2,
            'formula_media'              => 'simples',
            'nota_minima_aprovacao'      => 6.0,
            'percentual_maximo_faltas'   => 25,
            'plano_aula_habilitado'      => 0,
            'recuperacao_automatica'     => 1,
        ];
    }
}
