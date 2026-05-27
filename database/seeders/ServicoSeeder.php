<?php
// database/seeders/ServicoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servico;

class ServicoSeeder extends Seeder
{
    public function run()
    {
        $servicos = [
            [
                'nome' => 'Fumigação Profissional',
                'descricao' => 'Oferecemos serviços de fumigação especializados para eliminar insetos, pragas e agentes nocivos de residências, empresas e indústrias. Produtos certificados e métodos seguros garantem um ambiente saudável e protegido.',
                'preco' => 'Sob consulta',
                'duracao' => '2-4 horas',
                'categoria' => 'fumigacao',
                'tipo_servico' => 'Para residências, empresas e indústrias',
                'beneficios' => json_encode([
                    'Avaliação detalhada do local',
                    'Aplicação profissional com produtos certificados',
                    'Prevenção de infestações',
                    'Áreas cobertas: residências, empresas, armazéns, clínicas',
                    'Equipe especializada e treinada'
                ]),
                'detalhes' => json_encode([
                    'Inspeção detalhada para identificar áreas críticas',
                    'Produtos certificados e seguros',
                    'Eliminação completa de pragas',
                    'Orientação sobre medidas preventivas'
                ])
            ],
            [
                'nome' => 'Desratização Profissional',
                'descricao' => 'Nossa equipe oferece soluções completas de desratização, garantindo ambientes limpos, seguros e livres de roedores. Utilizamos métodos e produtos certificados para eliminar ratos e prevenir novas infestações.',
                'preco' => 'Sob consulta',
                'duracao' => '1-3 horas',
                'categoria' => 'desratizacao',
                'tipo_servico' => 'Para residências, empresas e indústrias',
                'beneficios' => json_encode([
                    'Identificação de focos e trilhas',
                    'Eliminação segura de roedores',
                    'Produtos certificados e seguros',
                    'Prevenção e monitoramento contínuo',
                    'Atendimento profissional especializado'
                ]),
                'detalhes' => json_encode([
                    'Inspeção detalhada para localizar trilhas e esconderijos',
                    'Aplicação de métodos profissionais',
                    'Proteção para família e alimentos',
                    'Planos de manutenção contínua'
                ])
            ],
            [
                'nome' => 'Tratamento Térmico',
                'descricao' => 'Solução avançada para eliminação de pragas através de calor controlado. Método ecológico e eficaz para tratamento de percevejos, cupins e outras pragas, sem uso de produtos químicos.',
                'preco' => 'Sob consulta',
                'duracao' => '3-6 horas',
                'categoria' => 'termico',
                'tipo_servico' => 'Para residências, empresas e indústrias',
                'beneficios' => json_encode([
                    'Método ecológico sem produtos químicos',
                    'Eliminação completa de ovos e adultos',
                    'Tratamento profundo e duradouro',
                    'Seguro para pessoas e animais',
                    'Ideal para móveis e estruturas'
                ]),
                'detalhes' => json_encode([
                    'Aquecimento controlado do ambiente',
                    'Eliminação de percevejos, cupins e outras pragas',
                    'Tecnologia avançada e segura',
                    'Resultados imediatos'
                ])
            ]
        ];

        foreach ($servicos as $servico) {
            Servico::create($servico);
        }
    }
}