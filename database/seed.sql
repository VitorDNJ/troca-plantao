USE troca_plantao;

-- PERFIS
INSERT INTO perfis (codigo, nome) VALUES
('ADMIN', 'RH / Administrador'),
('COORDENADOR', 'Coordenador'),
('COLABORADOR', 'Colaborador');

-- SETORES
INSERT INTO setores (nome, status) VALUES
('UTI Adulto', 'ATIVO'),
('Pronto Socorro', 'ATIVO'),
('Enfermaria', 'ATIVO');

-- USUARIOS DE TESTE
-- senha: admin123
INSERT INTO usuarios (matricula, nome, cpf, email, setor_id, funcao, perfil_id, status, senha_hash, trocar_senha_primeiro_acesso)
VALUES ('0001', 'Administrador do Sistema', '00000000000', 'admin@instituicao.com.br', 1, 'RH', 1, 'ATIVO',
'$2b$10$GbmWSfZgJMarvatD7Zwb4OFfKRzLylsYTJwveUy4mFLDRe4S01ilS', 0);

-- senha: coord123
INSERT INTO usuarios (matricula, nome, cpf, email, setor_id, funcao, perfil_id, status, senha_hash, trocar_senha_primeiro_acesso)
VALUES ('0002', 'Carlos Coordenador', '11111111111', 'carlos@instituicao.com.br', 1, 'Enfermeiro Coordenador', 2, 'ATIVO',
'$2b$10$p222TDQuH1C3C4OPJ5Zlhe1dWwB5U3tt2egsdH8H.sCzG2TlGyWBy', 0);

INSERT INTO usuarios_setores (usuario_id, setor_id) VALUES (2, 1), (2, 2);

-- senha: colab123
INSERT INTO usuarios (matricula, nome, cpf, email, setor_id, funcao, perfil_id, status, senha_hash, trocar_senha_primeiro_acesso)
VALUES ('1001', 'João Silva', '22222222222', 'joao@instituicao.com.br', 1, 'Técnico de Enfermagem', 3, 'ATIVO',
'$2b$10$SwqIERwEJlr29h4ur3hqR.2zIG67gICWz/deKJ7MHvqwjP/D3vgam', 0);

INSERT INTO usuarios (matricula, nome, cpf, email, setor_id, funcao, perfil_id, status, senha_hash, trocar_senha_primeiro_acesso)
VALUES ('1002', 'Maria Souza', '33333333333', 'maria@instituicao.com.br', 1, 'Técnico de Enfermagem', 3, 'ATIVO',
'$2b$10$SwqIERwEJlr29h4ur3hqR.2zIG67gICWz/deKJ7MHvqwjP/D3vgam', 0);

-- PERIODO ATIVO (exemplo do escopo)
INSERT INTO periodos_controle (nome, data_inicial, data_final, limite_trocas, limite_passagens, status, regra_troca_entre_periodos, observacao, criado_por)
VALUES ('Período 06/09 a 05/10/2026', '2026-09-06', '2026-10-05', 2, 2, 'ATIVO', 'SOMENTE_AUTORIZACAO', 'Período de referência inicial', 1);

INSERT INTO periodos_controle (nome, data_inicial, data_final, limite_trocas, limite_passagens, status, regra_troca_entre_periodos, observacao, criado_por)
VALUES ('Período 06/10 a 05/11/2026', '2026-10-06', '2026-11-05', 2, 2, 'FUTURO', 'SOMENTE_AUTORIZACAO', NULL, 1);

-- CONFIGURACOES
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('nome_sistema', 'Sistema de Troca e Passagem de Plantão', 'Nome exibido no sistema'),
('ano_codigo_atual', '2026', 'Ano usado na geração de códigos sequenciais');
